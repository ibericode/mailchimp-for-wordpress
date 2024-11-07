<?php

defined('ABSPATH') or exit;

/**
 * Class MC4WP_Procaptcha
 *
 * @private
 */
class MC4WP_Procaptcha
{
	const SCRIPT_URL      = 'https://js.prosopo.io/js/procaptcha.bundle.js';
	const FORM_FIELD_NAME = 'procaptcha-response';
	const API_URL         = 'https://api.prosopo.io/siteverify';

	/**
	 * @var MC4WP_Procaptcha
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
	/**
	 * @var string
	 */
	private $theme;
	/**
	 * @var string
	 */
	private $type;

	private function __construct()
	{
		$this->is_in_use                   = false;
		$this->is_enabled                  = false;
		$this->is_displayed_for_authorized = false;
		$this->site_key                    = '';
		$this->secret_key                  = '';
		$this->theme                       = '';
		$this->type                        = '';

		$this->read_settings();
	}

	/**
	 * @return void
	 */
	protected function read_settings()
	{
		$integrations = get_option('mc4wp_integrations', array());
		if (
			false === is_array($integrations) ||
			false === key_exists('prosopo-procaptcha', $integrations) ||
			false === is_array($integrations['prosopo-procaptcha'])
		) {
			return;
		}

		$settings = $integrations['prosopo-procaptcha'];

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
		$this->theme                       = true === key_exists('theme', $settings) &&
		true === is_string($settings['theme']) ?
			$settings['theme'] :
			'';
		$this->type                        = true === key_exists('type', $settings) &&
		true === is_string($settings['type']) ?
			$settings['type'] :
			'';
	}

	/**
	 * @return MC4WP_Procaptcha
	 */
	public static function get_instance()
	{
		if (null === self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * @return void
	 */
	protected function print_captcha_js()
	{
		$attributes = array(
			'siteKey' => $this->site_key,
			'theme' => $this->theme,
			'captchaType' => $this->type,
		);
		?>
		<script data-name="prosopo-procaptcha-element" type="module">
			let attributes = <?php echo json_encode($attributes); ?>;

			class MC4WPProcaptcha extends HTMLElement {
				constructor() {
					super();

					this.isValid = false;
					this.validationErrorElement = null;
				}

				connectedCallback() {
					// wait window.load to make sure 'window.procaptcha' is available.
					"complete" !== document.readyState ?
						window.addEventListener("load", this.setup.bind(this)) :
						this.setup();
				}


				validatedCallback(output) {
					this.isValid = true;

					// the element is optional.
					if (null !== this.validationErrorElement) {
						this.validationErrorElement.style.visibility = 'hidden';
					}
				}

				maybePreventSubmission(event) {
					if (true === this.isValid ||
						// the element is optional.
						null === this.validationErrorElement) {
						return;
					}

					event.preventDefault();
					event.stopPropagation();

					this.validationErrorElement.style.visibility = 'visible';
				}

				setup() {
					this.validationErrorElement = this.querySelector('.mc4wp-procaptcha__validation-error')
					attributes.callback = this.validatedCallback.bind(this);

					window.procaptcha.render(this.querySelector('.mc4wp-procaptcha__captcha'), attributes);
					this.closest('form').addEventListener('submit', this.maybePreventSubmission.bind(this));
				}
			}

			customElements.define("mc4wp-procaptcha", MC4WPProcaptcha);
		</script>
		<?php
	}

	/**
	 * @return bool
	 */
	protected function is_human_made_request()
	{
		$token = $_POST[self::FORM_FIELD_NAME] ?? '';
		$token = true === is_string($token) ?
			$token :
			'';

		// bail early if the token is empty.
		if ('' === $token) {
			return false;
		}

		$response = wp_remote_post(
			self::API_URL,
			array(
				'method' => 'POST',
				// limit waiting time to 20 seconds.
				'timeout' => 20,
				'headers' => array(
					'Content-Type' => 'application/json',
				),
				'body' => (string) wp_json_encode(
					array(
						'secret' => $this->secret_key,
						'token' => $token,
					)
				),
			)
		);

		// Check if request failed, either locally or remotely
		if (true === is_wp_error($response) || wp_remote_retrieve_response_code($response) >= 400) {
			/** @var MC4WP_Debug_Log */
			$logger = mc4wp('log');
			$logger->error(sprintf('ProCaptcha request error: %d %s - %s', wp_remote_retrieve_response_code($response), wp_remote_retrieve_response_message($response), wp_remote_retrieve_body($response)));
			return false;
		}

		$body = wp_remote_retrieve_body($response);
		$body = json_decode($body, true);
		$is_verified = is_array($body) && isset($body['verified']) && $body['verified'];

		return true === $is_verified;
	}

	public function maybe_add_type_module_attribute(string $tag, string $handle, string $src): string
	{
		if (
			'prosopo-procaptcha' !== $handle ||
			// make sure we don't make it twice if other Procaptcha integrations are present.
			false !== strpos('type="module"', $tag)
		) {
			return $tag;
		}

		// for old WP versions.
		$tag = str_replace(' type="text/javascript"', '', $tag);

		return str_replace(' src=', ' type="module" src=', $tag);
	}

	/**
     * @return bool
     */
	public function is_enabled()
	{
		return $this->is_enabled;
	}

	/**
	 * @param bool $is_without_validation_element
	 * @param bool $is_forced_render E.g. if it's a preview.
	 *
	 * @return string
	 */
	public function print_captcha_element($is_without_validation_element = false, $is_forced_render=false)
	{
		if (
			false === $this->is_displayed_for_authorized &&
			true === is_user_logged_in() &&
			false === $is_forced_render
		) {
			return '';
		}

		$this->is_in_use = true;

		$html  = '<mc4wp-procaptcha class="mc4wp-procaptcha" style="display: block;">';
		$html .= '<div class="mc4wp-procaptcha__captcha"></div>';

		// The element is optional, e.g. should be missing on the settings page.
		if (false === $is_without_validation_element) {
			$html .= '<p class="mc4wp-procaptcha__validation-error" style="visibility: hidden;color:red;line-height:1;font-size: 12px;padding: 7px 0 10px 10px;margin:0;">';
			$html .= esc_html__('Please verify that you are human.', 'mailchimp-for-wp');
			$html .= '</p>';
		}

		$html .= '</mc4wp-procaptcha>';

		return $html;
	}

	/**
	 * @return void
	 */
	public function maybe_enqueue_captcha_js()
	{
		if (false === $this->is_in_use) {
			return;
		}

		// do not use wp_enqueue_module() because it doesn't work on the login screens.
		wp_enqueue_script(
			'prosopo-procaptcha',
			self::SCRIPT_URL,
			array(),
			null,
			array(
				'in_footer' => true,
				'strategy' => 'defer',
			)
		);

		$this->print_captcha_js();
	}

	/**
	 * @param array<string,string> $messages
	 * @return array<string,string>
	 */
	public function register_error_message(array $messages)
	{
		$messages['procaptcha_required'] = 'Please verify that you are human.';

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
			(false === $this->is_displayed_for_authorized && true === is_user_logged_in()) ||
            true === $this->is_human_made_request()
		) {
		return $error_keys;
	    }

		$error_keys[] = 'procaptcha_required';

		return $error_keys;
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

		$captcha_element = $this->print_captcha_element();

		return str_replace($stub, $captcha_element, $html);
	}

	/**
	 * @return string
	 */
	protected function get_field_stub()
	{
		return '<input type="hidden" name="procaptcha">';
	}

	public function set_hooks(): void
	{
		if (false === $this->is_enabled) {
			return;
		}

		add_filter('mc4wp_form_messages', array($this, 'register_error_message'));
		add_action('mc4wp_form_content', array($this, 'inject_captcha_element'));
		add_filter('mc4wp_form_errors', array($this, 'validate_form'), 10, 2);

		add_filter('script_loader_tag', array($this, 'maybe_add_type_module_attribute'), 10, 3);

		$hook = true === is_admin() ?
			'admin_print_footer_scripts' :
			'wp_print_footer_scripts';

		// priority must be less than 10, to make sure the wp_enqueue_script still has effect.
		add_action($hook, array($this, 'maybe_enqueue_captcha_js'), 9);
	}
}
