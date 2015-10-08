<?php

class MC4WP_Form_Repository {

	/**
	 * @var array
	 */
	protected static $instances = array();

	/**
	 * @param array $options
	 */
	public function __construct( array $options ) {
		$this->options = $options;
	}

	/**
	 * @param int $id
	 * @param MC4WP_Request
	 * @return MC4WP_Form
	 */
	public function get( $id = 0, $request = null ) {

		$form = $this->get_instance( $id );

		// attach request
		// todo: this should probably not be here
		if( $request && ! $form->request instanceof MC4WP_Request ) {
			$form->request = $request;
		}

		return $form;
	}

	/**
	 * @param $id
	 * @return MC4WP_Form
	 */
	protected function get_instance( $id ) {

		if( ! isset( self::$instances[ $id ] ) ) {

			// allow this method to be filtered
			$form = apply_filters( 'mc4wp_get_form', null, $id );
			if( ! $form instanceof MC4WP_Form ) {
				$content = $this->options['markup'];
				$settings = $this->options;
				$form = new MC4WP_Form(
					$id,
					null,
					$content,
					$settings
				);
			}

			self::$instances[ $id ] = $form;
		}

		return self::$instances[ $id ];
	}

}