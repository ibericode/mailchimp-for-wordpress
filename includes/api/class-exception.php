<?php

class MC4WP_API_Exception extends Exception {

    /**
     * @var array
     */
    public $response;

    /**
    * @var mixed
    */
    public $data;
    public $type = '';
    public $title = '';
    public $status = '';
    public $detail = '';
    public $instance = '';
    public $errors = array();

    /**
     * MC4WP_API_Exception constructor.
     *
     * @param string $message
     * @param int $code
     * @param array $response
     * @param mixed $data
     */
    public function __construct( $message, $code, $response = null, $data = null ) {
        parent::__construct( $message, $code );

        $this->response = $response;
        $this->data = $data;

        if( ! empty( $data ) ) {
            // fill error properties from json data
            $error_properties = array( 'type', 'title', 'status', 'detail', 'instance', 'errors' );
            foreach( $error_properties as $key ) {
                if( ! empty( $data->$key ) ) {
                    $this->$key = $data->$key;
                }
            }
        }
    }

    /**
     * @return string
     */
    public function __toString() {
        $string = $this->message . '.';

        // add detail message
        if( ! empty( $this->title ) ) {
            $string .= ' ' . $this->title;
        }

        // add detail message
        if( ! empty( $this->detail ) ) {
            $string .= ' ' . $this->detail;
        }

        // add field specific errors
        if( ! empty( $this->errors ) && isset( $this->errors[0]->field ) ) {

            // strip off obsolete msg
            $string = str_replace( 'For field-specific details, see the \'errors\' array.', '', $string );

            // generate list of field errors
            $field_errors = array();
            foreach( $this->errors as $error ) {
                if( ! empty( $error->field ) ) {
                    $field_errors[] = sprintf( '- %s : %s', $error->field, $error->message );
                } else {
                    $field_errors[] = sprintf( '- %s', $error->message );
                }
            }

            $string .= " \n" . join( "\n", $field_errors );
        }

        return $string;
    }
}
