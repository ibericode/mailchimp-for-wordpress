<?php

/**
 * Class MC4WP_Visitor_Tracking
 *
 * @internal
 */
class MC4WP_Visitor_Tracking {

	/**
	 * @var array
	 */
	protected $data;

	/**
	 * @const string
	 */
	const COOKIE_NAME = 'mc4wp';

	/**
	 * @var MC4WP_Visitor_Tracking
	 */
	protected static $instance;

	/**
	 * @return MC4WP_Visitor_Tracking
	 */
	public static function instance() {
		if( ! self::$instance instanceof MC4WP_Visitor_Tracking ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

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
	 * @todo Register dynamic content tag?
	 */
	public function save( array $data ) {
		$this->data = $data;

		/**
		 * @filter `mc4wp_cookie_expiration_time`
		 * @expects timestamp
		 * @default timestamp for 90 days from now
		 *
		 * Timestamp indicating when the email cookie expires, defaults to 90 days
		 */
		$expiration_time = apply_filters( 'mc4wp_cookie_expiration_time', strtotime( '+90 days' ) );

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