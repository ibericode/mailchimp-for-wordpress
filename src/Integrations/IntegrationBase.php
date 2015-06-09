<?php

abstract class MC4WP_Integration_Base {

	/**
	 * @var string
	 */
	public $name = 'Integration';

	/**
	 * @var string
	 */
	public $type ='integration';

	/**
	 * @var string
	 */
	protected $checkbox_name = '_mc4wp_subscribe';

	/**
	 * @var array
	 */
	protected $options;

	/**
	* Constructor
	*/
	public function __construct() {
		$this->checkbox_name = '_mc4wp_subscribe' . '_' . $this->type;
		$this->options = mc4wp_get_options( 'checkbox' );
	}

	/**
	 * Called upon loading the integration
	 */
	public function init() {
		$this->add_hooks();
	}

	/**
	 * Adds the various hooks for this integration
	 */
	public function add_hooks() {}

	/**
	 * Is this a spam request?
	 *
	 * @return bool
	 */
	protected function is_spam() {

		// check if honeypot was filled
		if( $this->is_honeypot_filled() ) {
			return true;
		}

		// check user agent
		$user_agent = substr( $_SERVER['HTTP_USER_AGENT'], 0, 254 );
		if( strlen( $user_agent ) < 2 ) {
			return true;
		}

		/**
		 * @filter `mc4wp_is_spam`
		 * @expects boolean True if this is a spam request
		 * @default false
		 */
		return apply_filters( 'mc4wp_is_spam', false );
	}

	/**
	 * Was the honeypot filled?
	 *
	 * @return bool
	 */
	protected function is_honeypot_filled() {

		// Check if honeypot was filled (by spam bots)
		if( isset( $_POST['_mc4wp_required_but_not_really'] ) && ! empty( $_POST['_mc4wp_required_but_not_really'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Should the checkbox be pre-checked?
	 *
	 * @return bool
	 */
	public function is_prechecked() {
		return (bool) $this->options['precheck'];
	}

	/**
	 * Get the text for the label element
	 *
	 * @return string
	 */
	public function get_label_text() {

		$opts = $this->options;

		// Get general label text
		$label = $opts['label'];

		// Override label text if a specific text for this integration is set
		if ( isset( $opts['text_' . $this->type . '_label'] ) && ! empty( $opts['text_' . $this->type . '_label'] ) ) {
			// custom label text was set
			$label = $opts['text_' . $this->type . '_label'];
		}

		// replace label variables
		$label = MC4WP_Tools::replace_variables( $label, array(), array_values( $opts['lists'] ) );

		return $label;
	}

	/**
	* @return bool
	*/
	public function checkbox_was_checked() {
		return ( isset( $_POST[ $this->checkbox_name ] ) && $_POST[ $this->checkbox_name ] == 1 );
	}

	/**
	* Outputs a checkbox
	*/
	public function output_checkbox() {
		echo $this->get_checkbox();
	}

	/**
	* @param mixed $args Array or string
	* @return string
	*/
	public function get_checkbox( $args = array() ) {

		$checked = ( $this->is_prechecked() ) ? 'checked ' : '';

		// set label text
		if ( isset( $args['labels'][0] ) ) {
			// cf 7 shortcode
			$label = $args['labels'][0];
		} else {
			$label = $this->get_label_text();
		}

		// CF7 checkbox?
		if( is_array( $args ) && isset( $args['options'] ) ) {

			// check for default:0 or default:1 to set the checked attribute
		 	if( in_array( 'default:1', $args['options'] ) ) {
		 		$checked = 'checked';
		 	} else if( in_array( 'default:0', $args['options'] ) ) {
		 		$checked = '';
		 	}

		}

		// before checkbox HTML (comment, ...)
		$before = '<!-- MailChimp for WordPress Pro v'. MC4WP_VERSION .' - https://mc4wp.com/ -->';
		$before .= apply_filters( 'mc4wp_before_checkbox', '', $this->type );

		// checkbox
		$content = '<p id="mc4wp-checkbox" class="mc4wp-checkbox-' . $this->type .'">';
		$content .= '<label>';
		$content .= '<input type="checkbox" name="'. esc_attr( $this->checkbox_name ) .'" value="1" '. $checked . '/> ';
		$content .= $label;
		$content .= '</label>';
		$content .= '</p>';

		// after checkbox HTML (..., honeypot, closing comment)
		$after = apply_filters( 'mc4wp_after_checkbox', '', $this->type );
		$after .= '<textarea name="_mc4wp_required_but_not_really" style="display: none !important;"></textarea>';
		$after .= '<!-- / MailChimp for WordPress Pro -->';

		return $before . $content . $after;
	}

	/**
	 * @return array
	 */
	protected function get_lists() {

		// get checkbox lists options
		$lists = $this->options['lists'];

		// get lists from form, if set.
		if( isset( $_POST['_mc4wp_lists'] ) && ! empty( $_POST['_mc4wp_lists'] ) ) {

			$lists = $_POST['_mc4wp_lists'];

			// make sure lists is an array
			if( ! is_array( $lists ) ) {

				// sanitize value
				$lists = sanitize_text_field( $lists );
				$lists = array( $lists );
			}

		}

		// allow plugins to filter final
		$lists = apply_filters( 'mc4wp_lists', $lists );

		return $lists;
	}

	/**
	* Makes a subscription request
	*
	* @param string $email
	* @param array $merge_vars
	 * @param int $related_object_id
	* @return string|boolean
	*/
	protected function subscribe( $email, array $merge_vars = array(), $related_object_id = null ) {

		$lists = $this->get_lists();
		if( empty( $lists) ) {
			error_log( sprintf( "MailChimp for WordPress: No lists selected for the %s integration.", $this->name ) );
			return false;
		}

		$merge_vars = MC4WP_Tools::guess_merge_vars( $merge_vars );
		if( ! isset( $merge_vars['OPTIN_IP'] ) ) {
			$merge_vars['OPTIN_IP'] = MC4WP_tools::get_client_ip();
		}

		$result = false;
		$config = array(
			'double_optin' => $this->options['double_optin'],
			'update_existing' => $this->options['update_existing'],
			'send_welcome' => $this->options['send_welcome']
		);
		$extra = array(
			'related_object_id' => $related_object_id,
			'referer' => $_SERVER['HTTP_REFERER'],
			'type' => 'integration',
			'integration' => $this->name
		);

		foreach( $lists as $list_id ) {
			$request = new MC4WP_API_Request( 'subscribe', $list_id, $email, $merge_vars, $config, $extra );

			/** @var MC4WP_API_Response $response */
			$response = $request->process();
		}

		if( ! $response->success ) {
			error_log( sprintf( 'MailChimp for WP: Subscribe request from %s integration failed. The following error was returned by the MailChimp API. "%s"', $this->name, $response->error ) );
		}

		return $result;
	}
}