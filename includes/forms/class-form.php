<?php

/**
 * Class MC4WP_Form
 *
 * Represents a Form object.
 *
 * To get a form instance, use `mc4wp_get_form( $id );` where `$id` is the post ID.
 *
 * @access public
 * @since 3.0
 */
class MC4WP_Form {

    /**
     * @var array Array of instantiated form objects.
     */
    public static $instances = array();


    /**
     * Get a shared form instance.
     *
     * @param int|WP_Post $form_id
     * @return MC4WP_Form
     * @throws Exception
     */
    public static function get_instance( $form_id = 0 ) {

        if( $form_id instanceof WP_Post ) {
            $form_id = $form_id->ID;
        } else {
            $form_id = (int) $form_id;

            if( empty( $form_id ) ) {
                $form_id = (int) get_option( 'mc4wp_default_form_id', 0 );
            }
        }

        if( isset( self::$instances[ $form_id ] ) ) {
            return self::$instances[ $form_id ];
        }

        $form = new MC4WP_Form( $form_id );

        self::$instances[ $form_id ] = $form;

        return $form;
    }

    /**
     * @var int The form ID, matches the underlying post its ID
     */
    public $ID = 0;

    /**
     * @var string The form name
     */
    public $name = 'Default Form';

    /**
     * @var string The form HTML content
     */
    public $content = '';

    /**
     * @var array Array of settings
     */
    public $settings = array();

    /**
     * @var array Array of message codes that will show when this form renders
     */
    public $messages = array();

    /**
     * @var array
     */
    private $message_objects = array();

    /**
     * @var WP_Post The internal post object that represents this form.
     */
    public $post;

    /**
     * @var array Raw array of post_meta values.
     */
    protected $post_meta = array();

    /**
     * @var array Array of error codes
     */
    public $errors = array();

    /**
     * @var bool Was this form submitted?
     */
	public $is_submitted = false;

    /**
     * @var array Array of the data that was submitted, in name => value pairs.
     *
     * Keys in this array are uppercased and keys starting with _ are stripped.
     */
    private $data = array();

    /**
     * @var array Array of the raw form data that was submitted.
     */
    public $raw_data = array();

    /**
     * @var array
     */
    public $config = array(
        'action' => 'subscribe',
        'lists' => array(),
        'email_type' => '',
        'element_id' => ''
    );

    /**
     * @param int $id The post ID
     * @throws Exception
     */
    public function __construct( $id ) {
        $this->ID = $id = (int) $id;
        $this->post = $post = get_post( $this->ID );
        $this->post_meta = get_post_meta( $this->ID );

        if( ! is_object( $post ) || ! isset( $post->post_type ) || $post->post_type !== 'mc4wp-form' ) {
            $message = sprintf( __( 'There is no form with ID %d, perhaps it was deleted?', 'mailchimp-for-wp' ), $id );
            throw new Exception( $message );
        }

        $this->name = $post->post_title;
        $this->content = $post->post_content;
        $this->settings = $this->load_settings();

        // update config from settings
        $this->config['lists'] = $this->settings['lists'];
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function __get( $name ) {
        $method_name = sprintf( "get_%s", $name );
        if( method_exists( $this, $method_name ) ) {
            return $this->$method_name();
        }
    }


    /**
     * Gets the form response string
     *
     * This does not take the submitted form element into account.
     *
     * @see MC4WP_Form_Element::get_response_html()
     *
     * @return string
     */
    public function get_response_html() {
        return $this->get_element()->get_response_html( true );
    }

    /**
     * @param string $element_id
     * @param array $config
     * @return MC4WP_Form_element
     */
    public function get_element( $element_id = 'mc4wp-form', $config = array() ) {
        return new MC4WP_Form_Element( $this, $element_id, $config );
    }

    /**
     * Get HTML string for this form.
     *
     * If you want to output a form, use `mc4wp_show_form` instead as it.
     *
     * @param string $element_id
     * @param array $config
     *
     * @return string
     */
    public function get_html( $element_id = 'mc4wp-form', array $config = array() ) {
        $element = $this->get_element( $element_id, $config );
        $html = $element->generate_html();
        return $html;
    }


    /**
     * @staticvar $defaults
     * @return array
     */
    protected function load_settings() {

        $form = $this;
        static $defaults;

        // get default settings
        if( ! $defaults ) {
            $defaults = include MC4WP_PLUGIN_DIR . 'config/default-form-settings.php';
        }

        // start with defaults
        $settings = $defaults;

        // get custom settings from meta
        if( ! empty( $this->post_meta['_mc4wp_settings'] ) ) {
            $meta = $this->post_meta['_mc4wp_settings'][0];
            $meta = (array) maybe_unserialize( $meta );

            // ensure lists is an array
            if( empty( $meta['lists'] ) ) {
                $meta['lists'] = array();
            }

            // merge with current settings (defaults)
            $settings = array_merge( $settings, $meta );
        }

        /**
         * Filters the form settings
         *
         * @since 3.0
         *
         * @param array $settings
         * @param MC4WP_Form $form
         */
        $settings = (array) apply_filters( 'mc4wp_form_settings', $settings, $form );

        return $settings;
    }

    /**
     * @staticvar $default_messages
     * @return array
     */
    protected function load_messages() {

        $form = $this;

        // get default messages
        static $default_messages;
        if( ! $default_messages ) {
            $default_messages = include MC4WP_PLUGIN_DIR . 'config/default-form-messages.php';
        }

        // start with default messages
        $messages = $default_messages;

        /**
         * Filters the form messages
         *
         * @since 3.0
         *
         * @param array $registered_messages
         * @param MC4WP_Form $form
         */
        $messages = (array) apply_filters( 'mc4wp_form_messages', $messages, $form );

        foreach( $messages as $key => $message ) {

            $type = ! empty( $message['type'] ) ? $message['type'] : '';
            $text = isset( $message['text'] ) ? $message['text'] : $message;

            // overwrite default text with text in form meta.
            if( isset( $this->post_meta[ 'text_' . $key ][0] ) ) {
                $text = $this->post_meta[ 'text_' . $key ][0];
            }

            $message = new MC4WP_Form_Message( $text, $type );
            $messages[ $key ] = $message;
        }

        return $messages;
    }

    /**
     * Does this form has a field of the given type?
     *
     * @param $type
     *
     * @return bool
     */
    public function has_field_type( $type ) {
        return in_array( strtolower( $type ), $this->get_field_types() );
    }

    /**
     * Get an array of field types which are present in this form.
     *
     * @return array
     */
    public function get_field_types() {
        preg_match_all( '/type=\"(\w+)?\"/', strtolower( $this->content ), $result );
        $field_types = $result[1];
        return $field_types;
    }

    /**
     * @param $key
     */
    public function add_message( $key ) {
        $this->messages[] = $key;
    }

    /**
     * Get message object
     *
     * @param string $key
     *
     * @return MC4WP_Form_Message
     */
    public function get_message( $key ) {

        // load messages once
        if( empty( $this->message_objects ) ) {
            $this->message_objects = $this->load_messages();
        }

        if( isset( $this->message_objects[ $key ] ) ) {
            return $this->message_objects[ $key ];
        }

        // default to general error message
        return $this->message_objects['error'];
    }

    /**
     * Output this form
     *
     * @return string
     */
    public function __toString() {
        return mc4wp_show_form( $this->ID, array(), false );
    }

    /**
     * Get "redirect to url after success" setting for this form
     *
     * @return string
     */
    public function get_redirect_url() {
        $form = $this;
        $url = trim( $this->settings['redirect'] );

        /**
         * Filters the redirect URL setting
         *
         * @since 3.0
         *
         * @param string $url
         * @param MC4WP_Form $form
         */
        $url = (string) apply_filters( 'mc4wp_form_redirect_url', $url, $form );
        return $url;
    }

    /**
     * Is this form valid?
     *
     * Will always return true if the form is not yet submitted. Otherwise, it will run validation and store any errors.
     * This method should be called before `get_errors()`
     *
     * @return bool
     */
    public function validate() {

        if( ! $this->is_submitted ) {
            return true;
        }

        $form = $this;

        // validate config
        $validator = new MC4WP_Validator( $this->config );
        $validator->add_rule( 'lists', 'not_empty', 'no_lists_selected' );
        $valid = $validator->validate();

        // validate internal fields
        if( $valid ) {
            $validator = new MC4WP_Validator( $this->raw_data );
            $validator->add_rule( '_mc4wp_timestamp', 'range', 'spam', array( 'max' => time() - 2 ) );
            $validator->add_rule( '_mc4wp_honeypot', 'empty', 'spam' );
            $valid = $validator->validate();

            // validate actual (visible) fields
            if( $valid ) {
                $validator = new MC4WP_Validator( $this->data );

                $validator->add_rule( 'EMAIL', 'email', 'invalid_email' );

                foreach( $this->get_required_fields() as $field ) {
                    $validator->add_rule( $field, 'not_empty', 'required_field_missing' );
                }

                $valid = $validator->validate();
            }
        }

        // get validation errors
        $errors = $validator->get_errors();

        /**
         * Filters whether this form has errors. Runs only when a form is submitted.
         * Expects an array of message keys with an error type (string).
         *
         * Beware: all non-string values added to this array will be filtered out.
         *
         * @since 3.0
         *
         * @param array $errors
         * @param MC4WP_Form $form
         */
        $errors = (array) apply_filters( 'mc4wp_form_errors', $errors, $form );

        /**
         * @ignore
         * @deprecated 3.0 Use `mc4wp_form_errors` instead
         */
        $form_validity = apply_filters( 'mc4wp_valid_form_request', true, $this->data );
        if( is_string( $form_validity ) ) {
            $errors[] = $form_validity;
        }

        // filter out all non-string values
        $errors = array_filter( $errors, 'is_string' );

        // add each error to this form
        array_map( array( $this, 'add_error' ), $errors );

        // return whether we have errors
        return ! $this->has_errors();
    }

    /**
     * Handle an incoming request. Should be called before calling validate() method.
     *
     * @see MC4WP_Form::validate
     * @param MC4WP_Request $request
     * @return void
     */
    public function handle_request( MC4WP_Request $request ) {
        $this->is_submitted = true;
        $this->raw_data = $request->post->all();
        $this->data = $this->parse_request_data( $request );

        // update form configuration from given data
        $config = array();
        $map = array(
            '_mc4wp_lists' => 'lists',
            '_mc4wp_action' => 'action',
            '_mc4wp_form_element_id' => 'element_id',
            '_mc4wp_email_type' => 'email_type'
        );

        // use isset here to allow empty lists (which should show a notice)
        foreach( $map as $param_key => $config_key ) {
            if( isset( $this->raw_data[ $param_key ] ) ) {
                $config[ $config_key ] = $this->raw_data[ $param_key ];
            }
        }

        if( ! empty( $config ) ) {
            $this->set_config( $config );
        }
    }

    /**
     * Parse a request for data which should be binded to `$data` property.
     *
     * This does the following on all post data.
     *
     * - Removes fields starting with an underscore.
     * - Remove fields which are set to be ignored.
     * - Uppercase all field names
     *
     * @param MC4WP_Request $request
     *
     * @return array
     */
    protected function parse_request_data( MC4WP_Request $request ) {
        $form = $this;

        // get all fields that do NOT start with an underscore.
        $data = $request->post->all_without_prefix( '_' );

        // get rid of ignored field names
        $ignored_field_names = array();

        /**
         * Filters field names which should be ignored when showing data.
         *
         * @since 3.0
         *
         * @param array $ignored_field_names Array of ignored field names
         * @param MC4WP_Form $form The form instance.
         */
        $ignored_field_names = apply_filters( 'mc4wp_form_ignored_field_names', $ignored_field_names, $form );
        $data = array_diff_key( $data, array_flip( $ignored_field_names ) );

        // uppercase all field keys
        $data = array_change_key_case( $data, CASE_UPPER );

        return $data;
    }

    /**
     * Update configuration for this form
     *
     * @param array $config
     * @return array
     */
    public function set_config( array $config ) {
        $this->config = array_merge( $this->config, $config );

        // make sure lists is an array
        if( ! is_array( $this->config['lists'] ) ) {
            $this->config['lists'] = array_map( 'trim', explode(',', $this->config['lists'] ) );
        }

        // make sure action is valid
        if( ! in_array( $this->config['action'], array( 'subscribe', 'unsubscribe' ) ) ) {
            $this->config['action'] = 'subscribe';
        }

        // email_type should be a valid value
        if( ! in_array( $this->config['email_type'], array( 'html', 'text' ) ) ) {
            $this->config['email_type'] = '';
        }

        return $this->config;
    }

    /**
     * Get MailChimp lists this form subscribes to
     *
     * @return array
     */
    public function get_lists() {

        $lists = $this->config['lists'];
        $form = $this;

        /**
         * Filters MailChimp lists new subscribers should be added to.
         *
         * @param array $lists
         */
        $lists = (array) apply_filters( 'mc4wp_lists', $lists );

        /**
         * Filters MailChimp lists new subscribers coming from this form should be added to.
         *
         * @param array $lists
         * @param MC4WP_Form $form
         */
        $lists = (array) apply_filters( 'mc4wp_form_lists', $lists, $form );

        return $lists;
    }

    /**
     * Does this form have errors?
     *
     * Should always evaluate to false when form has not been submitted.
     *
     * @see `mc4wp_form_errors` filter.
     * @return bool
     */
    public function has_errors() {
        return count( $this->errors ) > 0;
    }

    /**
     * Add an error to this form
     *
     * @param string $error_code
     */
    public function add_error( $error_code ) {
        // only add each error once
        if( ! in_array( $error_code, $this->errors ) ) {
            $this->errors[] = $error_code;
            $this->add_message( $error_code );
        }
    }

    /**
     * Get the form action
     *
     * Valid return values are "subscribe" and "unsubscribe"
     *
     * @return string
     */
    public function get_action() {
        return $this->config['action'];
    }

    /**
     * @return array
     */
    public function get_data() {
        $data = $this->data;
        $form = $this;

        /**
         * Filters the form data.
         *
         * @param array $data
         * @param MC4WP_Form $form
         */
        $data = apply_filters( 'mc4wp_form_data', $data, $form );

        return $data;
    }

    /**
     * Get array of name attributes for the required fields in this form.
     *
     * @return array
     */
    public function get_required_fields() {
        $form = $this;

        // explode required fields (generated in JS) to an array (uppercased)
        $required_fields_string = strtoupper( $this->settings['required_fields'] );

        // remove array-formatted fields
        // workaround for #261 (https://github.com/ibericode/mailchimp-for-wordpress/issues/261)
        $required_fields_string = preg_replace( '/\[\w+\]/', '', $required_fields_string );

        // turn into an array
        $required_fields = explode( ',', $required_fields_string );

        // We only need unique values here.
        $required_fields = array_unique( $required_fields );

        // EMAIL is not a required field as it has its own validation rules
        $required_fields = array_diff( $required_fields, array( 'EMAIL' ) );

        // filter empty values
        $required_fields = array_filter( $required_fields );

        /**
         * Filters the required fields for a form
         *
         * By default, this holds the following fields.
         *
         * - All fields which are required for the selected MailChimp lists
         * - All fields in the form with a `required` attribute.
         *
         * @param array $required_fields
         * @param MC4WP_Form $form
         */
        $required_fields = (array) apply_filters( 'mc4wp_form_required_fields', $required_fields, $form );

        return $required_fields;
    }

    /**
     * Get "email_type" setting for new MailChimp subscribers added by this form.
     *
     * @return string
     */
    public function get_email_type() {
        $email_type = $this->config['email_type'];

        if( empty( $email_type ) ) {
            $email_type = mc4wp_get_email_type();
        }

        return $email_type;
    }

    /**
     * Gets the filename of the stylesheet to load for this form.
     *
     * @return string
     */
    public function get_stylesheet() {
        $stylesheet = $this->settings['css'];

        if( empty( $stylesheet ) ) {
            return '';
        }

        // form themes live in the same stylesheet
        if( strpos( $stylesheet, 'theme-' ) !== false ) {
            $stylesheet = 'themes';
        }

        return $stylesheet;
    }

    /**
     * Get HTML string for a message, including wrapper element.
     *
     * @deprecated 3.1
     *
     * @param string $key
     *
     * @return string
     */
    public function get_message_html( $key ) {
        _deprecated_function( __METHOD__, '3.2' );
        return '';
    }

}