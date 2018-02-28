<?php

class MC4WP_API_Exception extends Exception {

    /**
     * @var array
     */
    public $response;

    /**
    * @var object
    */
    public $response_data = array();

    /**
    * @var object
    */
    public $request_data = array();

    /**
     * MC4WP_API_Exception constructor.
     *
     * @param string $message
     * @param int $code
     * @param array $response
     * @param object $data
     */
    public function __construct( $message, $code, $response = null, $data = null ) {
        parent::__construct( $message, $code );

        $this->response = $response;
        $this->response_data = $data;
    }

    /**
     * @return string
     */
    public function __toString() {
        $string = $this->message . '.';

        // add errors from response data returned by MailChimp
        if( ! empty( $this->response_data ) ) {
            if( ! empty( $this->response_data->title ) ) {
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

        // Add request data to the string representation of this error, if set.
        if( ! empty( $this->request_data ) ) {
             $string .= "\n" . sprintf( 'Request body: %s', json_encode( $this->request_data ) );
        }

        return $string;
    }
}
