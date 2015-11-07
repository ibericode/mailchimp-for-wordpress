<?php

class MC4WP_Service_Container {

	/**
	 * @var MC4WP_Service_Container
	 */
	protected static $instance;

	/**
	 * @var array
	 */
	protected $services = array();

	/**
	 * @return MC4WP_Service_Container
	 */
	public static function instance() {

		if( ! self::$instance instanceof self ) {
			self::$instance = new MC4WP_Service_Container();
		}

		return self::$instance;
	}

	/**
	 * @param $name
	 * @param $instance
	 */
	public function register( $name, $instance ) {
		$this->services[ $name ] = $instance;

		// return instance for chaining
		return $instance;
	}

	/**
	 * @param $name
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function get( $name ) {
		if( ! isset( $this->services[ $name ] ) ) {
			throw new Exception( sprintf( 'No service named %s was registered.', $name ) );
		}

		return $this->services[ $name ];
	}

}