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
     * @param WP_Post|int $post Post instance or post ID.
     * @return MC4WP_Form
     * @throws Exception
     */
    public static function get_instance( $post = 0 ) {

        if( $post instanceof WP_Post ) {
            $post_id = $post->ID;
        } else {
            $post_id = (int) $post;

            if( empty( $post_id ) ) {
                $post_id = (int) get_option( 'mc4wp_default_form_id', 0 );
            }
        }

        if( isset( self::$instances[ $post_id ] ) ) {
            return self::$instances[ $post_id ];
        }

        // get post object if we don't have it by now
        if( ! $post instanceof WP_Post ) {
            $post = get_post( $post_id );
        }

        // check post object
        if( ! is_object( $post ) || ! isset( $post->post_type ) || $post->post_type !== 'mc4wp-form' ) {
            $message = sprintf( __( 'There is no form with ID %d, perhaps it was deleted?', 'mailchimp-for-wp' ), $post_id );
            throw new Exception( $message );
        }

        $post_meta = get_post_meta( $post_id );
        $form = new MC4WP_Form( $post_id, $post, $post_meta );

        // store instance
        self::$instances[ $post_id ] = $form;

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
     * @var array Array of messages
     */
    public $messages = array();

    /**
     * @var array Array of notices to be shown when this form is rendered
     */
    public $notices = array();

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
    * @var string
    */
    public $last_event = '';

    /**
    * @var string
    */
    public $status;

    /**
     * @param int $id The post ID
     * @param WP_Post $post
     * @param array $post_meta
     */
    public function __construct( $id, $post, $post_meta = array() ) {
        $this->ID = $id = (int) $id;
        $this->name = $post->post_title;
        $this->content = $post->post_content;
        $this->status = $post->post_status;
        $this->settings = $this->load_settings( $post_meta );
        $this->messages = $this->load_messages( $post_meta );

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
    protected function load_settings( $post_meta = array() ) {

        $form = $this;
        static $default_settings;

        // get default settings
        if( ! $default_settings ) {
            $default_settings = include MC4WP_PLUGIN_DIR . 'config/default-form-settings.php';
        }

        // start with defaults
        $settings = $default_settings;

        // get custom settings from meta
        if( ! empty( $post_meta['_mc4wp_settings'] ) ) {
            $meta = $post_meta['_mc4wp_settings'][0];
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
    protected function load_messages( $post_meta = array() ) {

        $form = $this;

        // get default messages
        $default_messages = include MC4WP_PLUGIN_DIR . 'config/default-form-messages.php';
    
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

        // for backwards compatiblity, grab text of each message (if is array)
        foreach( $messages as $key => $message ) {
            if( is_array( $message ) && isset( $message['text'] ) ) {
                $messages[$key] = $message['text'];
            }
        }

        foreach( $messages as $key => $message_text ) {

            // overwrite default text with text in form meta.
            if( isset( $post_meta[ 'text_' . $key ][0] ) ) {
                $message_text = $post_meta[ 'text_' . $key ][0];
            }

            $messages[$key] = $message_text;
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
    * Add notice to this form when it is rendered
    * @param string $text
    * @param string $type
    */
    public function add_notice( $text, $type = 'notice' ) {
        $this->notices[] = new MC4WP_Form_Notice( $text, $type );
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
        $errors = array();

        if( empty( $this->config['lists'] ) ) {
            $errors[] = 'no_lists_selected';
        }

        if( ! isset( $this->raw_data['_mc4wp_timestamp'] ) || $this->raw_data['_mc4wp_timestamp'] > ( time() - 2 ) ) {
            $errors[] = 'spam';
        } else if( ! isset( $this->raw_data['_mc4wp_honeypot'] ) || ! empty( $this->raw_data['_mc4wp_honeypot'] ) ) {
            $errors[] = 'spam';
        }

        if( empty( $errors ) ) {
            // validate email field
            if( empty( $this->data['EMAIL'] ) || ! is_email( $this->data['EMAIL'] ) ) {
                $errors[] = 'invalid_email';
            }

            // validate other required fields
            foreach( $this->get_required_fields() as $field ) {
                $value = mc4wp_array_get( $this->data, $field );

                // check for empty string or array here instead of empty() since we want to allow for "0" values.
                if( $value === "" || $value === array() ) {
                    $errors[] = 'required_field_missing';
                    break;
                }
            }
        }   

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

        // set property on self
        $this->errors = $errors;

        // return whether we have errors
        return ! $this->has_errors();
    }

    /**
     * Handle an incoming request. Should be called before calling validate() method.
     *
     * @see MC4WP_Form::validate
     * @param array $data
     * @return void
     */
    public function handle_request( array $data ) {
        $this->is_submitted = true;
        $this->raw_data = $data;
        $this->data = $this->parse_request_data( $data );
        $this->last_event = '';

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
                $value = $this->raw_data[ $param_key ];
                if( is_array( $value ) ) {
                    $value = array_filter( $value );
                }

                $config[ $config_key ] = $value;
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
     * @param array $data
     *
     * @return array
     */
    protected function parse_request_data( array $data ) {
        $form = $this;
        $filtered = array();
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

        foreach( $data as $key => $value ) {
            // skip fields in ignored field names
            if( $key[0] === '_' || in_array( $key, $ignored_field_names) ) {
                continue;
            }

            // uppercase key
            $key = strtoupper( $key );

            // filter empty array values
            if( is_array( $value ) ) {
                $value = array_filter( $value );
            }

            $filtered[$key] = $value;
        }

        return $filtered;
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

        // filter out empty array elements
        $lists = array_filter( $lists );

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

        // EMAIL is not a required field as it has its own validation rules
        $required_fields = array_diff( $required_fields, array( 'EMAIL' ) );

        // filter duplicate & empty values
        $required_fields = array_unique( $required_fields );
        $required_fields = array_filter( $required_fields );

        // fix uppercased subkeys, see https://github.com/ibericode/mailchimp-for-wordpress/issues/516
        foreach( $required_fields as $key => $value ) {
            $pos = strpos( $value, '.');
            if($pos > 0) {
                 $required_fields[$key] = substr( $value, 0, $pos) . strtolower( substr( $value, $pos ) );
            }
        }

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
    * @param string $key
    * @return string
    */
    public function get_message( $key ) {
        $message = isset( $this->messages[ $key] ) ? $this->messages[ $key ] : $this->messages['error'] ;

        if( $key === 'no_lists_selected' && current_user_can( 'manage_options' ) ) {
            $message .= sprintf( ' (<a href="%s">%s</a>)', mc4wp_get_edit_form_url( $this->ID, 'settings' ), 'edit form settings' );
        }

        return $message;
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

    /**
    * Add a notice to this form
    */
    public function add_message( $key ) {
        _deprecated_function( __METHOD__, '3.3' );
        $this->add_notice( $this->get_message( $key ) );
    }

}
