<?php

if( ! defined("MC4WP_LITE_VERSION") ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

abstract class MC4WP_Integration {

    protected $checkbox_name_value = '_mc4wp_subscribe';
	
	/**
	* Constructor
	*/
	public function __construct() {}

	/**
	* @return boolean
	*/
	public function checkbox_was_checked() {
		return ( isset( $_POST[ $this->checkbox_name_value ] ) && $_POST[ $this->checkbox_name_value ] == 1 );
	}

	/**
	* Outputs a checkbox
	*
	* @param string $hook
	*/
	public function output_checkbox( $hook = '' ) {
		echo $this->get_checkbox( $hook );
	}

	/**
	* @param mixed $hook Array or string
	* @return string
	*/
	public function get_checkbox( $hook = '' ) {

		$args = $hook;
		$opts = mc4wp_get_options( 'checkbox' );

		$checked = $opts['precheck'] ? "checked" : '';

		// set label text
		if ( $hook && is_string( $hook ) && isset( $opts['text_'.$hook.'_label'] ) && !empty( $opts['text_'.$hook.'_label'] ) ) {
			// custom label text was set
			$label = __( $opts['text_' . $hook . '_label'] );
		} elseif ( $args && is_array( $args ) && isset( $args['labels'][0] ) ) {
			// cf 7 shortcode
			$label = $args['labels'][0];
		} else {
			// default label text
			$label = __( $opts['label'] );
		}

		// replace label variables
		$label = mc4wp_replace_variables( $label, $opts['lists'] );

		// CF7 checkbox?
		if( is_array( $args ) && isset( $args['type'] ) ) {

			// check for default:0 or default:1 to set the checked attribute
		 	if( in_array( 'default:1', $args['options'] ) ) {
		 		$checked = 'checked';
		 	} else if( in_array( 'default:0', $args['options'] ) ) {
		 		$checked = '';
		 	}
		 	
		}

        // add starting debug marker
		$content = "\n<!-- MailChimp for WordPress v". MC4WP_LITE_VERSION ." - http://wordpress.org/plugins/mailchimp-for-wp/ -->\n";

		do_action( 'mc4wp_before_checkbox' ); 

		$content .= '<p id="mc4wp-checkbox">';
		$content .= '<label>';
		$content .= '<input type="checkbox" name="' . esc_attr( $this->checkbox_name_value ) . '" value="1" '. $checked . ' /> ';
		$content .= $label;
		$content .= '</label>';
		$content .= '</p>';

		do_action( 'mc4wp_after_checkbox' );

        // add ending debug marker
        $content .= "\n<!-- / MailChimp for WordPress -->\n";

		return $content;
	}

	/**
	* Makes a subscription request
	*
	* @param string $email
	* @param array $merge_vars
	* @param string $signup_type
	* @param int $comment_ID
	* @return boolean
	*/
	protected function subscribe( $email, array $merge_vars = array(), $signup_type = 'comment', $comment_ID = null ) {
		$api = mc4wp_get_api();
		$opts = mc4wp_get_options( 'checkbox' );

		if( ! isset( $opts['lists'] ) || empty( $opts['lists'] ) ) {
			if( ( ! defined( "DOING_AJAX" ) || ! DOING_AJAX ) && current_user_can( 'manage_options' ) ) {
				wp_die('
					<h3>MailChimp for WordPress - Error</h3>
					<p>Please select a list to subscribe to in the <a href="'. admin_url( 'admin.php?page=mc4wp-lite-checkbox-settings' ) .'">checkbox settings</a>.</p>
					<p style="font-style:italic; font-size:12px;">This message is only visible to administrators for debugging purposes.</p>
					', "Error - MailChimp for WP", array( 'back_link' => true ) );
			}

			return 'no_lists_selected';
		}

		// maybe guess first and last name
		if ( isset( $merge_vars['NAME'] ) && ! isset( $merge_vars['FNAME'] ) && ! isset( $merge_vars['LNAME'] ) ) {

			$strpos = strpos( $merge_vars['NAME'], ' ' );
			if ( $strpos ) {
				$merge_vars['FNAME'] = substr( $merge_vars['NAME'], 0, $strpos );
				$merge_vars['LNAME'] = substr( $merge_vars['NAME'], $strpos );
			} else {
				$merge_vars['FNAME'] = $merge_vars['NAME'];
			}
		}

		$result = false;
		$merge_vars = apply_filters( 'mc4wp_merge_vars', $merge_vars, $signup_type );
		$email_type = apply_filters( 'mc4wp_email_type', 'html' );
		$lists = apply_filters( 'mc4wp_lists', $opts['lists'], $merge_vars );

		do_action( 'mc4wp_before_subscribe', $email, $merge_vars );

		foreach( $lists as $list_ID ) {
			$result = $api->subscribe( $list_ID, $email, $merge_vars, $email_type, $opts['double_optin'], false, true, $opts['send_welcome'] );
		}

		do_action( 'mc4wp_after_subscribe', $email, $merge_vars, $result );

		// check if result succeeded, show debug message to administrators (only in NON-AJAX requests)
		if ( $result !== true && $api->has_error() && current_user_can( 'manage_options' ) && ( ! defined( "DOING_AJAX" ) || ! DOING_AJAX ) ) {
			wp_die( "<h3>MailChimp for WP - Error</h3>
					<p>The MailChimp server returned the following error message as a response to our sign-up request:</p>
					<pre>" . $api->get_error_message() . "</pre>
					<p>This is the data that was sent to MailChimp: </p>
					<strong>Email</strong>
					<pre>{$email}</pre>
					<strong>Merge variables</strong>
					<pre>" . print_r( $merge_vars, true ) . "</pre>
					<p style=\"font-style:italic; font-size:12px; \">This message is only visible to administrators for debugging purposes.</p>
					", "Error - MailChimp for WP", array( 'back_link' => true ) );
		}


		return $result;
	}
}