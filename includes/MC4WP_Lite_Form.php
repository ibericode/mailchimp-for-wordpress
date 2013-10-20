<?php

class MC4WP_Lite_Form {
	private $form_instance_number = 1;
	private $error = null;
	private $success = false;
	private $submitted_form_instance = 0;

	public function __construct() {
		$opts = $this->get_options();

		if($opts['css'] == 1) {
			add_filter('mc4wp_stylesheets', array($this, 'add_stylesheet'));
		}

		add_shortcode( 'mc4wp_form', array( $this, 'output_form' ) );
		add_shortcode( 'mc4wp-form', array( $this, 'output_form' ) );

		// enable shortcodes in text widgets
		add_filter( 'widget_text', 'shortcode_unautop' );
		add_filter( 'widget_text', 'do_shortcode', 11 );

		if ( isset( $_POST['mc4wp_form_submit'] ) ) {
			$this->ensure_backwards_compatibility();
			add_action( 'init', array( $this, 'submit' ) );
			add_action( 'wp_footer', array( $this, 'print_scroll_js' ), 99);
		}


	}

	public function get_options() {
		$options = MC4WP_Lite::instance()->get_options();
		return $options['form'];
	}

	public function add_stylesheet($stylesheets) {
		$stylesheets['form'] = 1;
		return $stylesheets;
	}

	public function output_form( $atts, $content = null ) {
		$opts = $this->get_options();

		if ( !function_exists( 'mc4wp_replace_variables' ) ) {
			include_once MC4WP_LITE_PLUGIN_DIR . 'includes/template-functions.php';
		}

		// add some useful css classes
		$css_classes = ' ';
		if ( $this->error ) $css_classes .= 'mc4wp-form-error ';
		if ( $this->success ) $css_classes .= 'mc4wp-form-success ';

		$content = "\n<!-- Form by MailChimp for WP plugin v". MC4WP_LITE_VERSION ." - http://dannyvankooten.com/wordpress-plugins/mailchimp-for-wordpress/ -->\n";
		$content .= '<form method="post" action="#mc4wp-form-'. $this->form_instance_number .'" id="mc4wp-form-'.$this->form_instance_number.'" class="mc4wp-form form'.$css_classes.'">';

		// maybe hide the form
		if ( !( $this->success && $opts['hide_after_success'] ) ) {
			$form_markup = __( $opts['markup'] );

			// replace special values
			$form_markup = str_replace( array( '%N%', '{n}' ), $this->form_instance_number, $form_markup );
			$form_markup = mc4wp_replace_variables( $form_markup, array_values( $opts['lists'] ) );
			$content .= $form_markup;

			// hidden fields
			$content .= '<textarea name="mc4wp_required_but_not_really" style="display: none;"></textarea>';
			$content .= '<input type="hidden" name="mc4wp_form_submit" value="1" />';
			$content .= '<input type="hidden" name="mc4wp_form_instance" value="'. $this->form_instance_number .'" />';
			$content .= '<input type="hidden" name="mc4wp_form_nonce" value="'. wp_create_nonce( '_mc4wp_form_nonce' ) .'" />';
		}

		if ( $this->form_instance_number == $this->submitted_form_instance ) {

			if ( $this->success ) {
				$content .= '<div class="mc4wp-alert mc4wp-success">' . __( $opts['text_success'] ) . '</div>';
			} elseif ( $this->error ) {

				$api = MC4WP_Lite::api();
				$e = $this->error;

				if ( $e == 'already_subscribed' ) {
					$text = ( empty( $opts['text_already_subscribed'] ) ) ? $api->get_error_message() : $opts['text_already_subscribed'];
					$content .= '<div class="mc4wp-alert mc4wp-notice">'. __( $text ) .'</div>';
				} elseif ( isset( $opts['text_' . $e] ) && !empty( $opts['text_'. $e] ) ) {
					$content .= '<div class="mc4wp-alert mc4wp-error">' . __( $opts['text_' . $e] ) . '</div>';
				}

				if ( current_user_can( 'manage_options' ) ) {

					if ( $api->has_error() ) {
						$content .= '<div class="mc4wp-alert mc4wp-error"><strong>Admin notice:</strong> '. $api->get_error_message() . '</div>';
					}
				}

			}
			// endif
		}

		if ( current_user_can( 'manage_options' ) && empty( $opts['lists'] ) ) {
			$content .= '<div class="mc4wp-alert mc4wp-error"><strong>Admin notice:</strong> you have not selected a MailChimp list for this sign-up form to subscribe to yet. <a href="'. admin_url( 'admin.php?page=mc4wp-lite-form-settings' ) .'">Edit your form settings</a> and select at least 1 list.</div>';
		}

		$content .= "</form>";
		$content .= "\n<!-- / MailChimp for WP Plugin -->\n";

		// increase form instance number in case there is more than one form on a page
		$this->form_instance_number++;

		return $content;
	}

	public function submit() {
		$opts = $this->get_options();
		$this->submitted_form_instance = (int) $_POST['mc4wp_form_instance'];

		if ( !isset( $_POST['mc4wp_form_nonce'] ) || !wp_verify_nonce( $_POST['mc4wp_form_nonce'], '_mc4wp_form_nonce' ) ) {
			$this->error = 'invalid_nonce';
			return false;
		}

		if ( !isset( $_POST['EMAIL'] ) || !is_email( $_POST['EMAIL'] ) ) {
			// no (valid) e-mail address has been given
			$this->error = 'invalid_email';
			return false;
		}

		if ( isset( $_POST['mc4wp_required_but_not_really'] ) && !empty( $_POST['mc4wp_required_but_not_really'] ) ) {
			// spam bot filled the honeypot field
			return false;
		}

		$email = $_POST['EMAIL'];

		// setup merge vars
		$merge_vars = array();

		foreach ( $_POST as $name => $value ) {

			// only add uppercases fields to merge variables array
			if ( !empty( $name ) && $name == 'EMAIL' || $name !== strtoupper( $name ) ) { continue; }

			if ( $name === 'GROUPINGS' ) {

				$groupings = $value;

				// malformed
				if ( !is_array( $groupings ) ) { continue; }

				// setup groupings array
				$merge_vars['GROUPINGS'] = array();

				foreach ( $groupings as $grouping_id_or_name => $groups ) {

					$grouping = array();

					if ( is_numeric( $grouping_id_or_name ) ) {
						$grouping['id'] = $grouping_id_or_name;
					} else {
						$grouping['name'] = $grouping_id_or_name;
					}

					if ( is_array( $groups ) ) {
						$grouping['groups'] = implode( ',', $groups );
					} else {
						$grouping['groups'] = $groups;
					}

					// add grouping to array
					$merge_vars['GROUPINGS'][] = $grouping;
				}

				if ( empty( $merge_vars['GROUPINGS'] ) ) { unset( $merge_vars['GROUPINGS'] ); }

			} else {
				$merge_vars[$name] = $value;
			}

		}

		$result = $this->subscribe( $email, $merge_vars );

		if ( $result === true ) {
			$this->success = true;

			// check if we want to redirect the visitor
			if ( !empty( $opts['redirect'] ) ) {
				wp_redirect( $opts['redirect'] );
				exit;
			}

			return true;
		} else {

			$this->success = false;
			$this->error = $result;

			return false;
		}
	}

	/*
		Ensure backwards compatibility so sign-up forms that contain old form mark-up rules don't break
		- Uppercase $_POST variables that should be sent to MailChimp
		- Format GROUPINGS in one of the following formats.
			$_POST[GROUPINGS][$group_id] = "Group 1, Group 2"
			$_POST[GROUPINGS][$group_name] = array("Group 1", "Group 2")
	*/
	public function ensure_backwards_compatibility() {

		// upercase $_POST variables
		foreach ( $_POST as $name => $value ) {

			// only uppercase variables which are not already uppercased, skip mc4wp internal vars
			if ( $name === strtoupper( $name ) || in_array( $name, array( 'mc4wp_form_instance', 'mc4wp_form_nonce', 'mc4wp_required_but_not_really', 'mc4wp_form_submit' ) ) ) continue;
			$uppercased_name = strtoupper( $name );

			// set new (uppercased) $_POST variable, unset old one.
			$_POST[$uppercased_name] = $value;
			unset( $_POST[$name] );
		}

		// detect old style GROUPINGS, then fix it.
		if ( isset( $_POST['GROUPINGS'] ) && is_array( $_POST['GROUPINGS'] ) && isset( $_POST['GROUPINGS'][0] ) ) {

			$old_groupings = $_POST['GROUPINGS'];
			unset( $_POST['GROUPINGS'] );
			$new_groupings = array();

			foreach ( $old_groupings as $grouping ) {

				if ( !isset( $grouping['id'] ) && !isset( $grouping['name'] ) ) { continue; }

				$key = ( isset( $grouping['id'] ) ) ? $grouping['id'] : $grouping['name'];

				$new_groupings[$key] = $grouping['groups'];

			}

			// re-fill $_POST array with new groupings
			if ( !empty( $new_groupings ) ) { $_POST['GROUPINGS'] = $new_groupings; }

		}

		return;
	}

	public function subscribe( $email, array $merge_vars = array() ) {
		$api = MC4WP_Lite::api();
		$opts = $this->get_options();

		$lists = $opts['lists'];

		if ( empty( $lists ) ) {
			return 'no_lists_selected';
		}

		// guess FNAME and LNAME
		if ( isset( $merge_vars['NAME'] ) && !isset( $merge_vars['FNAME'] ) && !isset( $merge_vars['LNAME'] ) ) {

			$strpos = strpos( $name, ' ' );

			if ( $strpos ) {
				$merge_vars['FNAME'] = substr( $name, 0, $strpos );
				$merge_vars['LNAME'] = substr( $name, $strpos );
			} else {
				$merge_vars['FNAME'] = $name;
			}
		}

		$merge_vars = apply_filters('mc4wp_merge_vars', $merge_vars);
		$email_type = apply_filters('mc4wp_email_type', 'html');

		foreach ( $lists as $list ) {
			$result = $api->subscribe( $list, $email, $merge_vars, $email_type, $opts['double_optin'] );

			if($result === true) {
				$from_url = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : '';
				do_action('mc4wp_subscribe_form', $email, $list, 0, $merge_vars, $from_url); 
			}
		}

		if ( $result === true ) {
			$this->success = true;
		} else {
			$this->success = false;
			$this->error = $result;
		}

		// flawed
		// this will only return the result of the last list a subscribe attempt has been sent to
		return $result;
	}

	public function print_scroll_js() {
		?><script type="text/javascript"> (function(){if(undefined==window.jQuery){return}var e=window.jQuery,t=window.location.hash;if(t.substring(1,12)=="mc4wp-form-"){var n=e(t);if(!n.length){return false}var r=n.offset().top,i=(e(window).innerHeight()-n.outerHeight())/2;r=i>0?r-i:r;if(r>0){var s=500+r;e("html,body").animate({scrollTop:r},s)}return false}})()</script><?php 
		/*
			(function() {
				if ( undefined == window.jQuery ) { return; }
				var $ = window.jQuery, hash = window.location.hash;
			    if (hash.substring(1, 12) == 'mc4wp-form-') {
				    var $target = $(hash);
				    if(!$target.length) { return false; }

				    var yOffset = $target.offset().top, substract = (($(window).innerHeight() - $target.outerHeight()) / 2);
					yOffset = (substract > 0) ? yOffset - substract : yOffset;

					if(yOffset > 0) {
						var animationTime = (500 + yOffset);
						$('html,body').animate({ scrollTop: yOffset }, animationTime);
					}

				    return false;
			   	}
			})(); */ ?>
		
		<?php
	}

}
