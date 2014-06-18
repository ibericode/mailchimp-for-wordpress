<?php

if( ! defined("MC4WP_LITE_VERSION") ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

/**
* This class takes care of all form related functionality
*/ 
class MC4WP_Lite_Form_Manager {
	
	/**
	* @var int
	*/
	private $form_instance_number = 1;

	/**
	* @var string
	*/
	private $error = '';

	/**
	* @var boolean
	*/
	private $success = false;

	/**
	* @var int
	*/
	private $submitted_form_instance = 0;

	/**
	* Constructor
	*/
	public function __construct() {

		add_action( 'init', array( $this, 'initialize' ) );

		add_shortcode( 'mc4wp_form', array( $this, 'form' ) );

		// enable shortcodes in text widgets
		add_filter( 'widget_text', 'shortcode_unautop' );
		add_filter( 'widget_text', 'do_shortcode', 11 );

        // load checkbox css if necessary
        add_action('wp_enqueue_scripts', array( $this, 'load_stylesheet' ) );

		// has a MC4WP form been submitted?
		if ( isset( $_POST['_mc4wp_form_submit'] ) ) {
			$this->ensure_backwards_compatibility();
			add_action( 'init', array( $this, 'submit' ) );
		}

		/**
		* @deprecated
		*/
		add_shortcode( 'mc4wp-form', array( $this, 'form' ) );
	}

	/**
	* Initializes the Form functionality
	*
	* - Registers scripts so developers can override them, should they want to.
	*/
	public function initialize()
	{
		// register placeholder script, which will later be enqueued for IE only
		wp_register_script( 'mc4wp-placeholders', MC4WP_LITE_PLUGIN_URL . 'assets/js/placeholders.min.js', array(), MC4WP_LITE_VERSION, true );
	
		// register non-AJAX script (that handles form submissions)
		wp_register_script( 'mc4wp-forms', MC4WP_LITE_PLUGIN_URL . 'assets/js/forms.js', array(), MC4WP_LITE_VERSION, true );
	}

	/**
	* Load the form stylesheet(s)
	*/
	public function load_stylesheet( ) {
		$opts = mc4wp_get_options('form');

        if( $opts['css'] == false ) {
            return false;
        }

        if( $opts['css'] != 1 && $opts['css'] !== 'default' ) {

            $form_theme = $opts['css'];
            if( in_array( $form_theme, array( 'blue', 'green', 'dark', 'light', 'red' ) ) ) {
                wp_enqueue_style( 'mailchimp-for-wp-form-theme-' . $opts['css'], MC4WP_LITE_PLUGIN_URL . "assets/css/form-theme-{$opts['css']}.css", array(), MC4WP_LITE_VERSION, 'all' );
            }

        } else {
            wp_enqueue_style( 'mailchimp-for-wp-form', MC4WP_LITE_PLUGIN_URL . "assets/css/form.css", array(), MC4WP_LITE_VERSION, 'all' );
        }

        return true;
	}

	/**
	* Gets CSS classess to add to the form element
	*
	* @return string
	*/
	private function get_css_classes() {

		// Allow devs to add CSS classes
		$css_classes = apply_filters( 'mc4wp_form_css_classes', array( 'form' ) );

		// the following classes MUST be used
		$css_classes[] = 'mc4wp-form';

		if( $this->error !== '' ) {
			$css_classes[] = 'mc4wp-form-error';
		}

		if( $this->success === true ) {
			$css_classes[] = 'mc4wp-form-success';
		}

		return implode( ' ', $css_classes );
	}

	/**
	* Returns the MailChimp for WP form mark-up
	*
	* @param array $atts
	* @param string $content
	*
	* @return string
	*/
	public function form( $atts, $content = null ) {
		$opts = mc4wp_get_options('form');

		if ( ! function_exists( 'mc4wp_replace_variables' ) ) {
			include_once MC4WP_LITE_PLUGIN_DIR . 'includes/functions/template.php';
		}

		// allow developers to add css classes
		$css_classes = $this->get_css_classes();

		$form_action = apply_filters( 'mc4wp_form_action', mc4wp_get_current_url() );

		$content = "\n<!-- Form by MailChimp for WordPress plugin v". MC4WP_LITE_VERSION ." - https://dannyvankooten.com/mailchimp-for-wordpress/ -->\n";
		$content .= '<form method="post" action="'. $form_action .'" id="mc4wp-form-'.$this->form_instance_number.'" class="'.$css_classes.'">';

		// maybe hide the form
		if ( !( $this->success && $opts['hide_after_success'] ) ) {
			$form_markup = __( $opts['markup'] );

			// replace special values
			$form_markup = str_ireplace( array( '%N%', '{n}' ), $this->form_instance_number, $form_markup );
			$form_markup = mc4wp_replace_variables( $form_markup, array_values( $opts['lists'] ) );

			// insert captcha
			if( function_exists( 'cptch_display_captcha_custom' ) ) {
				$captcha_fields = '<input type="hidden" name="_mc4wp_has_captcha" value="1" /><input type="hidden" name="cntctfrm_contact_action" value="true" />' . cptch_display_captcha_custom();
				$form_markup = str_ireplace( '[captcha]', $captcha_fields, $form_markup );
			}

			// allow plugins to add form fieldsq
			do_action( 'mc4wp_before_form_fields', 0 );

			// allow plugins to alter form content
			$content .= apply_filters( 'mc4wp_form_content', $form_markup );

			// allow plugins to add form fields
			do_action( 'mc4wp_after_form_fields', 0 );

			// hidden fields
			$content .= '<textarea name="_mc4wp_required_but_not_really" style="display: none !important;"></textarea>';
			$content .= '<input type="hidden" name="_mc4wp_form_submit" value="1" />';
			$content .= '<input type="hidden" name="_mc4wp_form_instance" value="'. $this->form_instance_number .'" />';
			$content .= '<input type="hidden" name="_mc4wp_form_nonce" value="'. wp_create_nonce( '_mc4wp_form_nonce' ) .'" />';
		}

		if ( $this->form_instance_number === $this->submitted_form_instance ) {

			if ( $this->success ) {
				$content .= '<div class="mc4wp-alert mc4wp-success">' . __( $opts['text_success'] ) . '</div>';
			} elseif ( $this->error !== '' ) {

				$api = mc4wp_get_api();
				$e = $this->error;

				$error_type = ( $e === 'already_subscribed' ) ? 'notice' : 'error';
				$error_message = isset( $opts['text_' . $e] ) ? $opts['text_' . $e] : $opts['text_error'];
				
				// allow developers to customize error message
				$error_message = apply_filters('mc4wp_form_error_message', $error_message, $e );
				
				$content .= '<div class="mc4wp-alert mc4wp-'. $error_type .'">'. __( $error_message, 'mailchimp-for-wp' ) . '</div>';

				// show the eror returned by MailChimp?
				if ( $api->has_error() && current_user_can( 'manage_options' ) ) {
					$content .= '<div class="mc4wp-alert mc4wp-error"><strong>Admin notice:</strong> '. $api->get_error_message() . '</div>';
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

		// make sure scripts are enqueued later
		global $is_IE;
		if( isset( $is_IE ) && $is_IE ) {
			wp_enqueue_script( 'mc4wp-placeholders' );
		}

		return $content;
	}

	/**
	* Submits the form
	* Creates a subscribe request from the posted data
	*
	* @return boolean
	*/
	public function submit() {
		// store number of submitted form
		$this->submitted_form_instance = absint( $_POST['_mc4wp_form_instance'] );

		// validate form nonce
		if ( ! isset( $_POST['_mc4wp_form_nonce'] ) || ! wp_verify_nonce( $_POST['_mc4wp_form_nonce'], '_mc4wp_form_nonce' ) ) {
			$this->error = 'invalid_nonce';
			return false;
		}

		// ensure honeypot was not filed
		if ( isset( $_POST['_mc4wp_required_but_not_really'] ) && ! empty( $_POST['_mc4wp_required_but_not_really'] ) ) {
			$this->error = 'spam';
			return false;
		}

		// check if captcha was present and valid
		if( isset( $_POST['_mc4wp_has_captcha'] ) && $_POST['_mc4wp_has_captcha'] == 1 && function_exists( 'cptch_check_custom_form' ) && cptch_check_custom_form() !== true ) {
			$this->error = 'invalid_captcha';
			return false;
		}

		// allow plugins to add additional validation
		$valid_form_request = apply_filters( 'mc4wp_valid_form_request', true );
		if( $valid_form_request !== true ) {
			$this->error = $valid_form_request;
			return false;
		}

		// setup array of data entered by user
		// not manipulating anything yet.
		$data = $this->get_posted_form_data();

		$success = $this->subscribe($data);

		// enqueue scripts (in footer)
		wp_enqueue_script( 'mc4wp-forms' );
		wp_localize_script( 'mc4wp-forms', 'mc4wp', array(
			'success' => ( $success ) ? 1 : 0,
			'submittedFormId' => $this->submitted_form_instance,
			'postData' => $data
			)
		);

		if ($success) {

			$opts = mc4wp_get_options('form');

			// check if we want to redirect the visitor
			if ( ! empty( $opts['redirect'] ) ) {
				wp_redirect( $opts['redirect'] );
				exit;
			}

			return true;
		} else {

			return false;
		}
	}

	/**
	* Get posted form data
	*
	* Strips internal MailChimp for WP variables from the posted data array
	*
	* @return array
	*/
	public function get_posted_form_data() {

		$data = array();

		foreach( $_POST as $name => $value ) {
			if( $name[0] !== '_' ) {
				$data[$name] = $value;
			}
		}

		// store data somewhere safe
		$this->posted_data = $data;

		return $data;
	}

	/*
		Ensure backwards compatibility so sign-up forms that contain old form mark-up rules don't break
		- Uppercase $_POST variables that should be sent to MailChimp
		- Format GROUPINGS in one of the following formats.
			$_POST[GROUPINGS][$group_id] = "Group 1, Group 2"
			$_POST[GROUPINGS][$group_name] = array("Group 1", "Group 2")
	*/
	public function ensure_backwards_compatibility() {

		// detect old style GROUPINGS, then fix it.
		if ( isset( $_POST['GROUPINGS'] ) && is_array( $_POST['GROUPINGS'] ) && isset( $_POST['GROUPINGS'][0] ) ) {

			$old_groupings = $_POST['GROUPINGS'];
			unset( $_POST['GROUPINGS'] );
			$new_groupings = array();

			foreach ( $old_groupings as $grouping ) {

				if( ! isset( $grouping['groups'] ) ) { 
					continue; 
				}

				if ( isset( $grouping['id'] ) ) {
					$key = $grouping['id'];
				} elseif( isset( $grouping['name'] ) ) { 
					$key = $grouping['name'];
				} else { 
					continue; 
				}

				$new_groupings[$key] = $grouping['groups'];

			}

			// re-fill $_POST array with new groupings
			if ( ! empty( $new_groupings ) ) { 
				$_POST['GROUPINGS'] = $new_groupings; 
			}

		}

		return;
	}

	/**
	* Act on posted data
	*
	* @var array $data
	*/
	public function subscribe( array $data ) {

		$email = null;
		$merge_vars = array();

		foreach ( $data as $name => $value ) {

			// uppercase all variables
			$name = trim( strtoupper( $name ) );
			$value = ( is_scalar( $value ) ) ? trim( stripslashes( $value ) ) : $value;

			if( $name === 'EMAIL' && is_email($value) ) {
				// set the email address
				$email = $value;
			} else if ( $name === 'GROUPINGS' ) {

				$groupings = $value;

				// malformed
				if ( ! is_array( $groupings ) ) { 
					continue; 
				}

				// setup groupings array
				$merge_vars['GROUPINGS'] = array();

				foreach ( $groupings as $grouping_id_or_name => $groups ) {

					$grouping = array();

					if ( is_numeric( $grouping_id_or_name ) ) {
						$grouping['id'] = $grouping_id_or_name;
					} else {
						$grouping['name'] = stripslashes( $grouping_id_or_name );
					}

					// comma separated list should become an array
					if( ! is_array( $groups ) ) {
						$groups = explode( ',', $groups );
					}
					
					$grouping['groups'] = array_map( 'stripslashes', $groups );

					// add grouping to array
					$merge_vars['GROUPINGS'][] = $grouping;
				}

				if ( empty( $merge_vars['GROUPINGS'] ) ) { 
					unset( $merge_vars['GROUPINGS'] ); 
				}

			} else if( $name === 'BIRTHDAY' ) {
				// format birthdays in the DD/MM format required by MailChimp
				$merge_vars['BIRTHDAY'] = date( 'm/d', strtotime( $value ) );
			} else if( $name === 'ADDRESS' ) {

				if( ! isset( $value['addr1'] ) ) {
					// addr1, addr2, city, state, zip, country 
					$addr_pieces = explode( ',', $value );

					// try to fill it.... this is a long shot
					$merge_vars['ADDRESS'] = array(
						'addr1' => $addr_pieces[0],
						'city' => (isset($addr_pieces[1])) ? $addr_pieces[1] : '',
						'state' => (isset($addr_pieces[2])) ? $addr_pieces[2] : '',
						'zip' => (isset($addr_pieces[3])) ? $addr_pieces[3] : ''
					);

				} else {
					// form contains the necessary fields already: perfection
					$merge_vars['ADDRESS'] = $value;
				}

			} else {
				// just add to merge vars array
				$merge_vars[$name] = $value;
			}	
		}

		// check if an email address has been found
		if( ! $email ) {
			$this->error = 'invalid_email';
			return false;
		}

		// Try to guess FNAME and LNAME if they are not given, but NAME is
		if( isset( $merge_vars['NAME'] ) && !isset( $merge_vars['FNAME'] ) && ! isset( $merge_vars['LNAME'] ) ) {

			$strpos = strpos($merge_vars['NAME'], ' ');
			if( $strpos !== false ) {
				$merge_vars['FNAME'] = substr($merge_vars['NAME'], 0, $strpos);
				$merge_vars['LNAME'] = substr($merge_vars['NAME'], $strpos);
			} else {
				$merge_vars['FNAME'] = $merge_vars['NAME'];
			}
		}

		$api = mc4wp_get_api();
		$opts = mc4wp_get_options( 'form' );

		$lists = $this->get_lists();

		if ( empty( $lists ) ) {
			$this->error = 'no_lists_selected';
			return false;
		}

		do_action( 'mc4wp_before_subscribe', $email, $merge_vars, 0 );

		$result = false;
		
		$email_type = $this->get_email_type();

		foreach ( $lists as $list_id ) {
			// allow plugins to alter merge vars for each individual list
			$list_merge_vars = apply_filters( 'mc4wp_merge_vars', $merge_vars, 0, $list_id );

			// send a subscribe request to MailChimp for each list
			$result = $api->subscribe( $list_id, $email, $list_merge_vars, $email_type, $opts['double_optin'] );
		}

		do_action( 'mc4wp_after_subscribe', $email, $merge_vars, 0, $result );

		if ( $result !== true ) {
			// subscribe request failed, store error.
			$this->success = false;
			$this->error = $result;
			return false;
		} 

		// store user email in a cookie
		$this->set_email_cookie( $email );

		// Store success result
		$this->success = true;

		return true;
	}

	/**
	* Gets the email_type
	*
	* @return string The email type to use for subscription coming from this form
	*/
	public function get_email_type( ) {

		$email_type = 'html';

		// get email type from form
		if( isset( $_POST['_mc4wp_email_type'] ) ) {
			$email_type = trim( $_POST['_mc4wp_email_type'] );
		}

		// allow plugins to override this email type
		$email_type = apply_filters( 'mc4wp_email_type', $email_type );

		return $email_type;
	}

	/**
	* Get MailChimp Lists to subscribe to
	* 
	* @return array Array of selected MailChimp lists
	*/
	public function get_lists(  ) {

		$opts = mc4wp_get_options('form');

		$lists = $opts['lists'];

		// get lists from form, if set.
		if( isset( $_POST['_mc4wp_lists'] ) && ! empty( $_POST['_mc4wp_lists'] ) ) {

			$lists = $_POST['_mc4wp_lists'];

			// make sure lists is an array
			if( ! is_array( $lists ) ) {
				$lists = array( trim( $lists ) );
			}

		}

		// allow plugins to alter the lists to subscribe to
		$lists = apply_filters( 'mc4wp_lists', $lists );

		return $lists;
	}

	/**
	* Stores the given email in a cookie for 30 days
	*
	* @param string $email
	*/
	public function set_email_cookie( $email ) {
		setcookie( 'mc4wp_email', $email, strtotime( '+30 days' ), '/' );
	}

}
