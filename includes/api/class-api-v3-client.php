<?php

class MC4WP_API_v3_Client {

    /**
     * @var string
     */
    private $api_key;

    /**
     * @var string
     */
    private $api_url = 'https://api.mailchimp.com/3.0/';

    /**
     * @var array
     */
    private $last_response;

    /**
     * Constructor
     *
     * @param string $api_key
     */
    public function __construct( $api_key ) {
        $this->api_key = $api_key;

        $dash_position = strpos( $api_key, '-' );
        if( $dash_position !== false ) {
            $this->api_url = str_replace( '//api.', '//' . substr( $api_key, $dash_position + 1 ) . ".api.", $this->api_url );
        }
    }


    /**
     * @param string $resource
     * @param array $args
     *
     * @return mixed
     */
    public function get( $resource, array $args = array() ) {
        return $this->request( 'GET', $resource, $args );
    }

    /**
     * @param string $resource
     * @param array $data
     *
     * @return mixed
     */
    public function post( $resource, array $data ) {
        return $this->request( 'POST', $resource, $data );
    }

    /**
     * @param string $resource
     * @param array $data
     * @return mixed
     */
    public function put( $resource, array $data ) {
        return $this->request( 'PUT', $resource, $data );
    }

    /**
     * @param string $resource
     * @param array $data
     * @return mixed
     */
    public function patch( $resource, array $data ) {
        return $this->request( 'PATCH', $resource, $data );
    }

    /**
     * @param string $resource
     * @return mixed
     */
    public function delete( $resource ) {
        return $this->request( 'DELETE', $resource );
    }

    /**
     * @param string $method
     * @param string $resource
     * @param array $data
     *
     * @return mixed
     *
     * @throws MC4WP_API_Exception
     */
    private function request( $method, $resource, array $data = array() ) {
        $this->reset();

        // don't bother if no API key was given.
        if( empty( $this->api_key ) ) {
            throw new MC4WP_API_Exception( "Missing API key", 001 );
        }

        $url = $this->api_url . ltrim( $resource, '/' );
        $args = array(
            'method' => $method,
            'headers' => $this->get_headers(),
            'timeout' => 10,
            'sslverify' => apply_filters( 'mc4wp_use_sslverify', true ),
        );

        // attach arguments (in body or URL)
        if( $method === 'GET' ) {
            $url = add_query_arg( $data, $url );
        } else {
            $args['body'] = json_encode( $data );
        }

        // perform request
        $response = wp_remote_request( $url, $args );
        $this->last_response = $response;

        // parse response
        $data = $this->parse_response( $response );

        return $data;
    }

    /**
     * @return array
     */
    private function get_headers() {
        global $wp_version;

        $headers = array();
        $headers['Authorization'] = 'Basic ' . base64_encode( 'mc4wp:' . $this->api_key );
        $headers['Accept'] = 'application/json';
        $headers['Content-Type'] = 'application/json';
        $headers['User-Agent'] = 'mc4wp/' . MC4WP_VERSION . '; WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' );

        // Copy Accept-Language from browser headers
        if( ! empty( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) {
            $headers['Accept-Language'] = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        }

        return $headers;
    }

    /**
     * @param array|WP_Error $response
     *
     * @return mixed
     *
     * @throws MC4WP_API_Exception
     */
    private function parse_response( $response ) {

        if( $response instanceof WP_Error ) {
            throw new MC4WP_API_Connection_Exception( $response->get_error_message(), (int) $response->get_error_code() );
        }

        // decode response body
        $code = (int) wp_remote_retrieve_response_code( $response );
        $message = wp_remote_retrieve_response_message( $response );
        $body = wp_remote_retrieve_body( $response );

        // set body to "true" in case MailChimp returned No Content
        if( $code < 300 && empty( $body ) ) {
            $body = "true";
        }

        $data = json_decode( $body );

        if( $code >= 400 ) {
            if( $code === 404 ) {
                throw new MC4WP_API_Resource_Not_Found_Exception( $message, $code, $response, $data );
            }

            throw new MC4WP_API_Exception( $message, $code, $response, $data );
        }

        if( ! is_null( $data ) ) {
            return $data;
        }

        // unable to decode response
        throw new MC4WP_API_Exception( $message, $code, $response );
    }

    /**
     * Empties all data from previous response
     */
    private function reset() {
        $this->last_response = null;
    }

    /**
     * @return string
     */
    public function get_last_response_body() {
        return wp_remote_retrieve_body( $this->last_response );
    }

    /**
     * @return array
     */
    public function get_last_response_headers() {
        return wp_remote_retrieve_headers( $this->last_response );
    }

    /**
     * @return array|WP_Error
     */
    public function get_last_response() {
        return $this->last_response;
    }


}