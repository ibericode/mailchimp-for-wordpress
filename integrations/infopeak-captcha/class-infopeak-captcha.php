<?php

defined('ABSPATH') || exit;


/**
 * Class MC4WP_InfoPeak_Captcha
 *
 * @private
 */
class MC4WP_InfoPeak_Captcha
{
    private const SCRIPT_URL      = 'https://captcha.infopeak.io/infopeak-captcha.js';
    private const FORM_FIELD_NAME = 'infopeak-captcha-response';
    private const API_URL         = 'https://captcha.infopeak.io/api/v1/pow/siteverify';

    /**
     * @var MC4WP_InfoPeak_Captcha
     */
    private static $instance;

    /**
     * @var bool
     */
    private $is_in_use;
    /**
     * @var bool
     */
    private $is_enabled;
    /**
     * @var bool
     */
    private $is_displayed_for_authorized;
    /**
     * @var string
     */
    private $site_key;
    /**
     * @var string
     */
    private $secret_key;

    private function __construct()
    {
        $this->is_in_use                   = false;
        $this->is_enabled                  = false;
        $this->is_displayed_for_authorized = false;
        $this->site_key                    = '';
        $this->secret_key                  = '';

        $this->read_settings();
    }

    /**
     * @return void
     */
    protected function read_settings()
    {
        $integrations = get_option('mc4wp_integrations', []);
        if (
            false === is_array($integrations) ||
            false === key_exists('infopeak-captcha', $integrations) ||
            false === is_array($integrations['infopeak-captcha'])
        ) {
            return;
        }

        $settings = $integrations['infopeak-captcha'];

        $this->is_enabled                  = true === key_exists('enabled', $settings) &&
            '1' === $settings['enabled'];
        $this->is_displayed_for_authorized = true === key_exists('display_for_authorized', $settings) &&
            '1' === $settings['display_for_authorized'];
        $this->site_key                    = true === key_exists('site_key', $settings) &&
        true === is_string($settings['site_key']) ?
            $settings['site_key'] :
            '';
        $this->secret_key                  = true === key_exists('secret_key', $settings) &&
        true === is_string($settings['secret_key']) ?
            $settings['secret_key'] :
            '';
    }

    /**
     * @return MC4WP_InfoPeak_Captcha
     */
    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Verifies the posted token with the InfoPeak Captcha API.
     *
     * Tokens can only be verified once. The submission is rejected when the API cannot be reached,
     * so that unverified sign-ups never reach Mailchimp.
     *
     * @return string Empty string if the request was made by a human, an error key otherwise.
     */
    protected function get_verification_error()
    {
        // phpcs:ignore WordPress.Security.NonceVerification -- the captcha token is its own proof
        $token = wp_unslash($_POST[self::FORM_FIELD_NAME] ?? '');
        $token = true === is_string($token) ? trim($token) : '';

        // bail early if the token is empty.
        if ('' === $token) {
            return 'infopeak_captcha_required';
        }

        $response = wp_remote_post(
            self::API_URL,
            [
                'timeout' => 10,
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => (string) wp_json_encode(
                    [
                        'sitekey' => $this->site_key,
                        'secret' => $this->secret_key,
                        'token' => $token,
                    ]
                ),
            ]
        );

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if (true === is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response) || false === is_array($data)) {
            /** @var MC4WP_Debug_Log */
            $logger = mc4wp_get_service('log');
            $logger->error(sprintf('InfoPeak Captcha request error: %s', true === is_wp_error($response) ? $response->get_error_message() : wp_remote_retrieve_response_code($response) . ' ' . wp_remote_retrieve_body($response)));

            return 'infopeak_captcha_unavailable';
        }

        if (true === isset($data['valid']) && true === $data['valid']) {
            return '';
        }

        // a genuine token, refused because the site's monthly quota is used up: trying again will not help.
        if (true === isset($data['quota_exceeded']) && true === $data['quota_exceeded']) {
            /** @var MC4WP_Debug_Log */
            $logger = mc4wp_get_service('log');
            $logger->error('InfoPeak Captcha: the monthly quota for this sitekey is used up.');

            return 'infopeak_captcha_unavailable';
        }

        return 'infopeak_captcha_required';
    }

    /**
     * @return bool
     */
    public function is_enabled()
    {
        return $this->is_enabled;
    }

    /**
     * @param bool $is_forced_render E.g. if it's a preview.
     *
     * @return string
     */
    public function print_captcha_element($is_forced_render = false)
    {
        if (
            false === $this->is_displayed_for_authorized &&
            true === is_user_logged_in() &&
            false === $is_forced_render
        ) {
            return '';
        }

        $this->is_in_use = true;

        return sprintf('<div class="infopeak-captcha mc4wp-infopeak-captcha" data-sitekey="%s"></div>', esc_attr($this->site_key));
    }

    /**
     * @return void
     */
    public function maybe_enqueue_captcha_js()
    {
        if (false === $this->is_in_use) {
            return;
        }

        wp_enqueue_script(
            'infopeak-captcha',
            self::SCRIPT_URL,
            [],
            null,
            [
                'in_footer' => true,
                'strategy' => 'defer',
            ]
        );

        // Tokens can only be used once. When a form is submitted without reloading the page (AJAX forms),
        // the widget fetches a fresh token so that the visitor can submit the form again.
        wp_add_inline_script(
            'infopeak-captcha',
            'document.addEventListener("DOMContentLoaded",function(){if(!window.mc4wp||!window.mc4wp.forms){return;}window.mc4wp.forms.on("submitted",function(form){var input=form.element.querySelector(\'input[name="' . self::FORM_FIELD_NAME . '"]\');var widget=form.element.querySelector(".infopeak-captcha");if(input&&input.value&&widget&&window.infopeakCaptcha){window.infopeakCaptcha.reset(widget);}});});',
            'before'
        );
    }

    /**
     * @param array<string,string> $messages
     * @return array<string,string>
     */
    public function register_error_message(array $messages)
    {
        $messages['infopeak_captcha_required']    = 'Captcha verification failed. Please try again.';
        $messages['infopeak_captcha_unavailable'] = 'This form cannot be checked for spam right now. Please try again later.';

        return $messages;
    }

    /**
     * @param string[] $error_keys
     * @param MC4WP_Form $form
     *
     * @return string[]
     */
    public function validate_form($error_keys, $form)
    {
        if (
            false === strpos($form->content, $this->get_field_stub()) ||
            (false === $this->is_displayed_for_authorized && true === is_user_logged_in())
        ) {
            return $error_keys;
        }

        $error = $this->get_verification_error();
        if ('' !== $error) {
            $error_keys[] = $error;
        }

        return $error_keys;
    }

    /**
     * Keeps the captcha token out of the data that is sent to Mailchimp.
     *
     * @param string[] $field_names
     * @return string[]
     */
    public function ignore_response_field($field_names)
    {
        $field_names[] = self::FORM_FIELD_NAME;

        return $field_names;
    }

    /**
     * @param string $html
     * @return string
     */
    public function inject_captcha_element($html)
    {
        $stub = $this->get_field_stub();

        if (false === strpos($html, $stub)) {
            return $html;
        }

        return str_replace($stub, $this->print_captcha_element(), $html);
    }

    /**
     * @return string
     */
    protected function get_field_stub()
    {
        return '<input type="hidden" name="infopeak-captcha">';
    }

    public function set_hooks(): void
    {
        if (false === $this->is_enabled) {
            return;
        }

        add_filter('mc4wp_form_messages', [$this, 'register_error_message']);
        add_action('mc4wp_form_content', [$this, 'inject_captcha_element']);
        add_filter('mc4wp_form_errors', [$this, 'validate_form'], 10, 2);
        add_filter('mc4wp_form_ignored_field_names', [$this, 'ignore_response_field']);

        $hook = true === is_admin() ?
            'admin_print_footer_scripts' :
            'wp_print_footer_scripts';

        // priority must be less than 10, to make sure the wp_enqueue_script still has effect.
        add_action($hook, [$this, 'maybe_enqueue_captcha_js'], 9);
    }
}
