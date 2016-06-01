<?php

class MC4WP_API_Exception extends Exception {

    /**
     * @var array
     */
    public $response;

    public $type;
    public $title;
    public $status;
    public $detail;
    public $instance;

    /**
     * MC4WP_API_Exception constructor.
     *
     * @param string $message
     * @param int $code
     * @param array $response
     * @param object $error_data
     */
    public function __construct( $message, $code, $response = null, $error_data = null ) {
        parent::__construct( $message, $code );

        $this->response = $response;

        static $error_properties = array( 'type', 'title', 'status', 'detail', 'instance' );
        foreach( $error_properties as $key ) {
            if( ! empty( $error_data->$key ) ) {
                $this->$key = $error_data->$key;
            }
        }
    }
}