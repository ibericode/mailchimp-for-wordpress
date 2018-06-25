<?php

class MC4WP_API_Exception extends Exception {

  
    /**
    * @var object
    */
    public $response = array();

    /**
    * @var object
    */
    public $request = array();

    /**
    * @var array
    */
    public $response_data = array();

    /**
     * MC4WP_API_Exception constructor.
     *
     * @param string $message
     * @param int $code
     * @param array $request
     * @param array $response
     * @param object $data
     */
    public function __construct( $message, $code, $request = null, $response = null, $data = null ) {
        parent::__construct( $message, $code );

        $this->request = $request;
        $this->response = $response;

        $this->response_data = $data;
    }

    /**
    * Backwards compatibility for direct property access.
    */
    public function __get( $property ) {
        if( in_array( $property, array( 'title', 'detail', 'errors' ) ) ) {
            if( ! empty( $this->response_data ) && isset( $this->response_data->{$property} ) ) {
                return $this->response_data->{$property};
            }

            return '';
        }
    }

    /**
     * @return string
     */
    public function __toString() {
        $string = $this->message . '.';

        // add errors from response data returned by MailChimp
        if( ! empty( $this->response_data ) ) {
            if( ! empty( $this->response_data->title ) && $this->response_data->title !== $this->getMessage() ) {
                $string .= ' ' . $this->response_data->title . '.';
            }

            // add detail message
            if( ! empty( $this->response_data->detail ) ) {
                $string .= ' ' . $this->response_data->detail;
            }

            // add field specific errors
            if( ! empty( $this->response_data->errors ) && isset( $this->response_data->errors[0]->field ) ) {

                // strip off obsolete msg
                $string = str_replace( 'For field-specific details, see the \'errors\' array.', '', $string );

                // generate list of field errors
                $field_errors = array();
                foreach( $this->response_data->errors as $error ) {
                    if( ! empty( $error->field ) ) {
                        $field_errors[] = sprintf( '- %s : %s', $error->field, $error->message );
                    } else {
                        $field_errors[] = sprintf( '- %s', $error->message );
                    }
                }

                $string .= " \n" . join( "\n", $field_errors );
            }
        }

        // Add request data
        if( ! empty( $this->request ) && is_array( $this->request ) ) {
            $string .= "\n" . sprintf( 'Request: %s %s', $this->request['method'], $this->request['url'] );

            if( ! empty( $this->request['body'] ) ) {
                $string .= sprintf( ' - %s', $this->request['body'] );
            }
        }

        // Add response data
        if( ! empty( $this->response ) && is_array( $this->response ) ) {
            $response_code = wp_remote_retrieve_response_code( $this->response );
            $response_message = wp_remote_retrieve_response_message( $this->response );
            $response_body = wp_remote_retrieve_body( $this->response );
            $string .= "\n" . sprintf( 'Response: %d %s - %s', $response_code, $response_message, $response_body );
        }

        return $string;
    }
}
