<?php

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
     * @var string The tracking pixel ID from settings.
     */
    private $tracking_id;

    /**
     * @var string Email address of a subscriber to identify, set during form processing.
     */
    private $identify_email = '';

    /**
     * @param string $tracking_id The Mailchimp tracking pixel ID.
     */
    public function __construct(string $tracking_id)
    {
        $this->tracking_id = $tracking_id;
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
        if (empty($this->tracking_id)) {
            return;
        }

        $url = 'https://mc.mailchimp.com/mcjs/' . urlencode($this->tracking_id) . '.js';

        printf(
            '<script id="mcjs" defer src="%s"></script>' . "\n",
            esc_url($url)
        );
    }

    /**
     * Capture the subscriber email after a successful form subscription.
     *
     * Fires during the mc4wp_form_subscribed action hook.
     *
     * @since 4.13.0
     *
     * @param MC4WP_Form $form The submitted form instance.
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
        if (empty($this->identify_email) || empty($this->tracking_id)) {
            return;
        }

        $email = esc_js($this->identify_email);
        echo '<script>';
        echo 'if(window.$mcSite&&window.$mcSite.pixel&&window.$mcSite.pixel.api){';
        echo 'window.$mcSite.pixel.api.identify({type:"EMAIL",value:"' . $email . '"});';
        echo '}';
        echo '</script>' . "\n";
    }
}
