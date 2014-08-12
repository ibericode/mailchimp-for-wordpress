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
	 * @var MC4WP_Form_Request|boolean
	 */
	private $form_request = false;

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
			$this->form_request = new MC4WP_Lite_Form_Request;
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
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		// register placeholder script, which will later be enqueued for IE only
		wp_register_script( 'mc4wp-placeholders', MC4WP_LITE_PLUGIN_URL . 'assets/js/placeholders.min.js', array(), MC4WP_LITE_VERSION, true );

		// register non-AJAX script (that handles form submissions)
		wp_register_script( 'mc4wp-form-request', MC4WP_LITE_PLUGIN_URL . 'assets/js/form-request' . $suffix . '.js', array(), MC4WP_LITE_VERSION, true );
	}

	/**
	* Load the form stylesheet(s)
	*/
	public function load_stylesheet( ) {
		$opts = mc4wp_get_options('form');

        if( $opts['css'] == false ) {
            return false;
        }

		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

        if( $opts['css'] != 1 && $opts['css'] !== 'default' ) {

            $form_theme = $opts['css'];
            if( in_array( $form_theme, array( 'blue', 'green', 'dark', 'light', 'red' ) ) ) {
                wp_enqueue_style( 'mailchimp-for-wp-form-theme-' . $opts['css'], MC4WP_LITE_PLUGIN_URL . 'assets/css/form-theme-' . $opts['css'] . $suffix . '.css', array(), MC4WP_LITE_VERSION, 'all' );
            }

        } else {
            wp_enqueue_style( 'mailchimp-for-wp-form', MC4WP_LITE_PLUGIN_URL . 'assets/css/form' . $suffix . '.css', array(), MC4WP_LITE_VERSION, 'all' );
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

		// Add form classes if a Form Request was captured
		if( is_object( $this->form_request ) ) {

			if( $this->form_request->is_successful() ) {
				$css_classes[] = 'mc4wp-form-success';
			} else {
				$css_classes[] = 'mc4wp-form-error';
			}

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
	public function form( $atts = array(), $content = '' ) {

		// make sure template functions are loaded
		if ( ! function_exists( 'mc4wp_replace_variables' ) ) {
			include_once MC4WP_LITE_PLUGIN_DIR . 'includes/functions/template.php';
		}

		// Get form options
		$opts = mc4wp_get_options('form');

		// was this form submitted?
		$was_submitted = ( is_object( $this->form_request ) && $this->form_request->get_form_instance_number() === $this->form_instance_number );

		/**
		 * @filter mc4wp_form_action
		 * @expects string
		 *
		 * Sets the `action` attribute of the form element. Defaults to the current URL.
		 */
		$form_action = apply_filters( 'mc4wp_form_action', mc4wp_get_current_url() );

		// Generate opening HTML
		$opening_html = "<!-- Form by MailChimp for WordPress plugin v". MC4WP_LITE_VERSION ." - https://dannyvankooten.com/mailchimp-for-wordpress/ -->";
		$opening_html .= '<form method="post" action="'. $form_action .'" id="mc4wp-form-'.$this->form_instance_number.'" class="'. $this->get_css_classes() .'">';

		// Generate before & after fields HTML
		$before_fields = apply_filters( 'mc4wp_form_before_fields', '' );
		$after_fields = apply_filters( 'mc4wp_form_after_fields', '' );

		// Process fields, if not submitted or not successfull or hide_after_success disabled
		if( ! $was_submitted || ! $opts['hide_after_success'] || ! $this->form_request->is_successful() ) {
			// add form fields from settings
			$visible_fields = __( $opts['markup'] );

			// replace special values
			$visible_fields = str_ireplace( array( '%N%', '{n}' ), $this->form_instance_number, $visible_fields );
			$visible_fields = mc4wp_replace_variables( $visible_fields, array_values( $opts['lists'] ) );

			// insert captcha
			if( function_exists( 'cptch_display_captcha_custom' ) ) {
				$captcha_fields = '<input type="hidden" name="_mc4wp_has_captcha" value="1" /><input type="hidden" name="cntctfrm_contact_action" value="true" />' . cptch_display_captcha_custom();
				$visible_fields = str_ireplace( array( '{captcha}', '[captcha]' ), $captcha_fields, $visible_fields );
			}

			/**
			 * @filter mc4wp_form_content
			 * @param       int     $form_id    The ID of the form that is being shown
			 * @expects     string
			 *
			 * Can be used to customize the content of the form mark-up, eg adding additional fields.
			 */
			$visible_fields = apply_filters( 'mc4wp_form_content', $visible_fields );

			// hidden fields
			$hidden_fields = '<textarea name="_mc4wp_required_but_not_really" style="display: none !important;"></textarea>';
			$hidden_fields .= '<input type="hidden" name="_mc4wp_form_submit" value="1" />';
			$hidden_fields .= '<input type="hidden" name="_mc4wp_form_instance" value="'. $this->form_instance_number .'" />';
			$hidden_fields .= '<input type="hidden" name="_mc4wp_form_nonce" value="'. wp_create_nonce( '_mc4wp_form_nonce' ) .'" />';
		} else {
			$visible_fields = '';
			$hidden_fields = '';
		}



		if( $was_submitted ) {

			// Enqueue script (only after submit)
			wp_enqueue_script( 'mc4wp-form-request' );
			wp_localize_script( 'mc4wp-form-request', 'mc4wpFormRequestData', array(
					'success' => ( $this->form_request->is_successful() ) ? 1 : 0,
					'submittedFormId' => $this->form_request->get_form_instance_number(),
					'postData' => stripslashes_deep( $_POST )
				)
			);

			// Add form response to content
			$response = $this->get_form_message_html();

			// check if form contains {response} tag
			if( stristr( $visible_fields, '{response}' ) !== false ) {
				$visible_fields = str_ireplace( '{response}', $response, $visible_fields );
			} else {

				/**
				 * @filter mc4wp_form_message_position
				 * @expects string before|after
				 *
				 * Can be used to change the position of the form success & error messages.
				 * Valid options are 'before' or 'after'
				 */
				$message_position = apply_filters( 'mc4wp_form_message_position', 'after' );

				switch( $message_position ) {
					case 'before':
						$before_fields = $before_fields . $response;
						break;

					case 'after':
						$after_fields = $response . $after_fields;
						break;
				}
			}

		}

		// Generate closing HTML
		$closing_html = "</form>";
		$closing_html .= "<!-- / MailChimp for WP Plugin -->";

		// increase form instance number in case there is more than one form on a page
		$this->form_instance_number++;

		// make sure scripts are enqueued later
		global $is_IE;
		if( isset( $is_IE ) && $is_IE ) {
			wp_enqueue_script( 'mc4wp-placeholders' );
		}

		// concatenate and return the HTML parts
		return $opening_html . $before_fields . $visible_fields . $hidden_fields . $after_fields . $closing_html;
	}

	/**
	 * Returns the HTML for success or error messages
	 *
	 * @return string
	 */
	private function get_form_message_html( $form_id = 0 ) {

		// don't show message if form wasn't submitted
		if( ! is_object( $this->form_request ) ) {
			return '';
		}

		// get all form messages
		$messages = $this->get_form_messages( $form_id );

		// retrieve correct message
		$type = ( $this->form_request->is_successful() ) ? 'success' : $this->form_request->get_error_code();
		$message = ( isset( $messages[ $type ] ) ) ? $messages[ $type ] : $messages['error'];

		/**
		 * @filter mc4wp_form_error_message
		 * @deprecated 2.0.5
		 * @use mc4wp_form_messages
		 *
		 * Used to alter the error message, don't use. Use `mc4wp_form_messages` instead.
		 */
		$message['text'] = apply_filters('mc4wp_form_error_message', $message['text'], $this->form_request->get_error_code() );

		$html = '<div class="mc4wp-alert mc4wp-'. $message['type'].'">' . $message['text'] . '</div>';

		// show additional MailChimp API errors to administrators
		if( false === $this->form_request->is_successful() && current_user_can( 'manage_options' ) ) {
			// show MailChimp error message (if any) to administrators
			$api = mc4wp_get_api();
			if( $api->has_error() ) {
				$html .= '<div class="mc4wp-alert mc4wp-error"><strong>Admin notice:</strong> '. $api->get_error_message() . '</div>';
			}
		}

		return $html;
	}

	/**
	 * Returns the various error and success messages in array format
	 *
	 * Example:
	 * array(
	 *      'invalid_email' => array(
	 *          'type' => 'css-class',
	 *          'text' => 'Message text'
	 *      ),
	 *      ...
	 * );
	 *
	 * @param   int     $form_id
	 * @return array
	 */
	public function get_form_messages( $form_id = 0 ) {

		$opts = mc4wp_get_options( 'form' );

		$messages = array(
			'already_subscribed' => array(
				'type' => 'notice',
				'text' => $opts['text_already_subscribed']
			),
			'error' => array(
				'type' => 'error',
				'text' => $opts['text_error']
			),
			'invalid_email' => array(
				'type' => 'error',
				'text' => $opts['text_invalid_email']
			),
			'success' => array(
				'type' => 'success',
				'text' => $opts['text_success']
			),
			'invalid_captcha' => array(
				'type' => 'error',
				'text' => $opts['text_invalid_captcha']
			),
			'required_field_missing' => array(
				'type' => 'error',
				'text' => $opts['text_required_field_missing']
			)
		);

		/**
		 * @filter mc4wp_form_messages
		 *
		 * Allows registering custom form messages, useful if you're using custom validation using the `mc4wp_valid_form_request` filter.
		 */
		$messages = apply_filters( 'mc4wp_form_messages', $messages );

		return $messages;
	}

}
