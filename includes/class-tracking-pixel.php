<?php

defined('ABSPATH') or exit;


/**
 * Outputs the Mailchimp Site Tracking Pixel SDK script
 * and identifies subscribers after successful form submissions.
 *
 * @since 4.13.0
 * @access private
 * @ignore
 */
class MC4WP_Tracking_Pixel
{
    /**
     * @var string The foreign_id / site_id used to identify the connected site in Mailchimp.
     */
    private $site_id;

    /**
     * @var string Email address of a subscriber to identify, set during form processing.
     */
    private $identify_email = '';

    /**
     * @param string $site_id The connected site ID stored in the plugin options.
     */
    public function __construct(string $site_id)
    {
        $this->site_id = $site_id;
    }

    /**
     * Register hooks for outputting the tracking pixel.
     *
     * @return void
     */
    public function add_hooks(): void
    {
        add_action('wp_head', [$this, 'output_tracking_script']);
        add_action('mc4wp_form_subscribed', [$this, 'capture_subscriber_email'], 10, 2);
        add_action('wp_footer', [$this, 'output_identify_script'], 99);
    }

    /**
     * Output the Mailchimp Site Tracking Pixel SDK script tag.
     *
     * @return void
     */
    public function output_tracking_script(): void
    {
        if (empty($this->site_id)) {
            return;
        }

        $opts = mc4wp_get_options();

        if (! empty($opts['tracking_pixel_script_url'])) {
            $url = $opts['tracking_pixel_script_url'];
        } else if (! empty($opts['tracking_pixel_id'])) {
            // BC: support legacy tracking_pixel_id for users who configured it before this auto-connect feature
            $url = sprintf('https://chimpstatic.com/mcjs-connected/js/users/%s.js', $opts['tracking_pixel_id']);
        } else {
            return;
        }

        wp_enqueue_script('mc4wp-mailchimp-site-tracking-pixel', $url, [], MC4WP_VERSION, [
            'strategy' => 'defer',
        ]);
    }

    /**
     * Capture the subscriber email after a successful form subscription.
     *
     * Fires during the mc4wp_form_subscribed action hook.
     *
     * @since 4.13.0
     *
     * @param MC4WP_Form $form  The submitted form instance.
     * @param string     $email The subscriber's email address.
     * @return void
     */
    public function capture_subscriber_email($form, string $email): void
    {
        $this->identify_email = $email;
    }

    /**
     * Output inline script to identify the subscriber via the Mailchimp pixel SDK.
     *
     * Only outputs when a subscriber email was captured during this request.
     *
     * @since 4.13.0
     *
     * @return void
     */
    public function output_identify_script(): void
    {
        if (empty($this->identify_email) || empty($this->site_id)) {
            return;
        }

        echo '<script>';
        echo 'if(window.$mcSite&&window.$mcSite.pixel&&window.$mcSite.pixel.api){';
        echo 'window.$mcSite.pixel.api.identify({type:"EMAIL",value:"' . esc_js($this->identify_email) . '"});';
        echo '}';
        echo '</script>' . "\n";
    }

    /**
     * Auto-fetch an existing Mailchimp Connected Site that matches the current domain,
     * or create a new one if none is found.
     *
     * @since 4.13.0
     *
     * @return array{site_id: string, script_url: string}|false  Array with site data on success, false on failure.
     */
    public static function fetch_or_create_connected_site()
    {
        try {
            /** @var MC4WP_API_V3 $api */
            $api    = mc4wp('api');
            $domain = self::get_site_domain();
            $sites  = $api->get_connected_sites();

            // Try to find an existing site that matches our domain.
            $matched_site = null;
            foreach ($sites as $site) {
                $site_domain = isset($site->domain) ? self::normalize_domain($site->domain) : '';
                if ($site_domain === self::normalize_domain($domain)) {
                    $matched_site = $site;
                    break;
                }
            }

            if (null === $matched_site) {
                // No match — create a new connected site using the e-commerce store ID as foreign_id
                // so it reuses any existing store connection where possible.
                $foreign_id   = self::get_foreign_id();
                $matched_site = $api->add_connected_site([
                    'foreign_id' => $foreign_id,
                    'domain'     => $domain,
                ]);
            }

            return [
                'site_id'    => sanitize_text_field($matched_site->foreign_id ?? $matched_site->id ?? ''),
                'script_url' => esc_url_raw($matched_site->site_script->url ?? ''),
            ];
        } catch (Exception $e) {
            mc4wp('log')->error(sprintf('Tracking Pixel: error fetching/creating connected site. %s', $e->getMessage()));
            return false;
        }
    }

    /**
     * Returns the domain of the current site stripped of protocol.
     *
     * @return string
     */
    public static function get_site_domain(): string
    {
        return (string) self::normalize_domain(get_home_url());
    }

    /**
     * Strips protocol and trailing slashes from a URL/domain for comparison.
     *
     * @param string $url
     * @return string
     */
    private static function normalize_domain(string $url): string
    {
        $domain = str_ireplace(['https://', 'http://', '://'], '', trim($url));
        return rtrim($domain, '/');
    }

    /**
     * Returns a foreign_id to use when registering a new connected site.
     * Reuses the e-commerce store ID when available so Mailchimp can link them.
     *
     * @return string
     */
    private static function get_foreign_id(): string
    {
        $ecommerce_settings = get_option('mc4wp_ecommerce', []);
        if (! empty($ecommerce_settings['store_id'])) {
            return (string) $ecommerce_settings['store_id'];
        }

        return 'mc4wp-' . sanitize_title(get_bloginfo('name')) . '-' . get_current_blog_id();
    }

    /**
     * Returns true if the MC4WP Premium e-commerce integration is already
     * managing the tracking pixel (mcjs script), so we avoid injecting a
     * duplicate script on the frontend.
     *
     * @return bool
     */
    public static function is_premium_ecommerce_pixel_active(): bool
    {
        $ecommerce_settings = get_option('mc4wp_ecommerce', []);
        return ! empty($ecommerce_settings['load_mcjs_script']);
    }
}
