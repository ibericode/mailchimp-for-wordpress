<?php

class MC4WP_API_Exception extends Exception {

    /**
     * @var array
     */
    public $response;
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

        if( ! empty( $data ) ) {
            // fill error properties from json data
            $error_properties = array( 'type', 'title', 'status', 'detail', 'instance', 'errors' );
            foreach( $error_properties as $key ) {
                if( ! empty( $data->$key ) ) {
                    $this->$key = $data->$key;
                }
            }

            // use MailChimp error as message
            if( ! empty( $data->detail ) ) {
                $this->message = $data->detail;
            }
        }
    }
}