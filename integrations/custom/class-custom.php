<?php

defined('ABSPATH') or exit;

/**
 * Class MC4WP_Custom_Integration
 * @ignore
 */
class MC4WP_Custom_Integration extends MC4WP_Integration
{
    /**
     * @var string
     */
    protected $checkbox_name = 'mc4wp-subscribe';

    /**
     * @var string
     */
    public $name = 'Custom';

    /**
     * @var string
     */
    public $description = 'Integrate with custom third-party forms.';

    /**
    * Add hooks
    */
    public function add_hooks()
    {
        add_action('init', array( $this, 'listen' ), 50);
    }

    /**
     * Was the integration checkbox checked?
     *
     * @return bool
     */
    public function checkbox_was_checked()
    {
        $data          = $this->get_data();
        $value         = isset($data[ $this->checkbox_name ]) ? $data[ $this->checkbox_name ] : '';
        $truthy_values = array( 1, '1', 'yes', true, 'true', 'y' );
        return in_array($value, $truthy_values, true);
    }

    /**
     * Maybe fire a general subscription request
     *
     * @return bool|string
     */
    public function listen()
    {
        if (! $this->checkbox_was_checked()) {
            return false;
        }

		// ignore requests from bots, crawlers and link previews
	    if (empty($_SERVER['HTTP_USER_AGENT']) || preg_match('/bot|crawl|spider|seo|lighthouse|facebookexternalhit|preview/i', $_SERVER['HTTP_USER_AGENT'])) {
	        return false;
	    }

	    // ignore requests without an HTTP referrer
	    if (empty($_SERVER['HTTP_REFERER'])) {
	    	return false;
	    }

	    // ignore requests where HTTP Referer does not contain hostname from home_url
	    $site_hostname = parse_url(get_home_url(), PHP_URL_HOST);
	    if (strpos($_SERVER['HTTP_REFERER'], $site_hostname) === false) {
	    	return false;
	    }

        $data = $this->get_data();

        // don't run for CF7 or Events Manager requests
        // (since they use the same "mc4wp-subscribe" trigger)
        $disable_triggers = array(
            '_wpcf7' => '',
            'action' => 'booking_add',
        );

        foreach ($disable_triggers as $trigger => $trigger_value) {
            if (isset($data[ $trigger ])) {
                $value = $data[ $trigger ];

                // do nothing if trigger value is optional
                // or if trigger value matches
                if (empty($trigger_value) || $value === $trigger_value) {
                    return false;
                }
            }
        }

        // run!
        return $this->process();
    }

    /**
     * Process custom form
     *
     * @return bool|string
     */
    public function process()
    {
        $parser = new MC4WP_Field_Guesser($this->get_data());
        $data   = $parser->combine(array( 'guessed', 'namespaced' ));

        // do nothing if no email was found
        if (empty($data['EMAIL'])) {
            $this->get_log()->warning(sprintf('%s > Unable to find EMAIL field.', $this->name));
            return false;
        }

        return $this->subscribe($data);
    }

    /**
     * @return bool
     */
    public function is_installed()
    {
        return true;
    }

    /**
     * @return array
     */
    public function get_ui_elements()
    {
        return array( 'lists', 'double_optin', 'update_existing', 'replace_interests' );
    }
}
