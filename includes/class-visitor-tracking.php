<?php

/**
 * Class MC4WP_Visitor_Tracking
 *
 * @ignore
 * @access private
 * @deprecated 3.1
 */
class MC4WP_Visitor_Tracking {

	/**
	 * @var array
	 */
	protected $data;

	/**
	 * @const string
	 */
	const COOKIE_NAME = '_mc4wp';

	/**
	 * Add hooks
	 *
	 * @todo Hook into integration success as well
	 */
	public function add_hooks() {
		add_action( 'mc4wp_form_subscribed', array( $this, 'on_form_success' ) );
	}

	/**
	 * @hooked `mc4wp_form_subscribed`
	 * @param MC4WP_Form $form
	 */
	public function on_form_success( MC4WP_Form $form ) {
		$this->save( $form->data );
	}

	/**
	 * @param string $key
	 * @param null $default
	 * @return mixed
	 */
	public function get_field( $key, $default = null ) {
		$data = $this->load();

		if( isset( $data[ $key ] ) ) {
			return $data[ $key ];
		}

		return $default;
	}

	/**
	 * @param array $data
	 *
	 * @todo Now, all previous is data is overwritten whenever this method is called.
	 */
	public function save( array $data ) {
		$this->data = $data;

		$timestamp = strtotime( '+90 days' );

		/**
		 * Filters the total expiration time for the tracking cookie.
		 *
		 * Defaults to 90 days in the future.
		 *
		 * @since 3.0
		 * @param int $timestamp
		 */
		$expiration_time = apply_filters( 'mc4wp_cookie_expiration_time', $timestamp );

		setcookie( self::COOKIE_NAME, json_encode( $data ), $expiration_time, '/' );
	}

	/**
	 * Load stored data from cookie.
	 */
	public function load() {

		if( empty( $this->data ) ) {
			if( ! empty( $_COOKIE[ self::COOKIE_NAME ] ) ) {
				$raw = stripslashes( $_COOKIE[ self::COOKIE_NAME ] );
				$data = json_decode( $raw, true );

				if( is_array( $data ) ) {
					$this->data = mc4wp_sanitize_deep( $data );
				}

			}
		}

		return $this->data;
	}


}