<?php

final class MC4WP_Form extends MC4WP_Form_Base {

	/**
	 * @var MC4WP_Form
	 */
	private static $instance;

	/**
	 * @param int $form_id Unused
	 * @param iMC4WP_Request $request
	 * @return MC4WP_Form
	 */
	public static function get( $form_id = 0, iMC4WP_Request $request = null ) {

		// has instance been created already?
		if( self::$instance instanceof MC4WP_Form ) {
			$form = self::$instance;
		} else {
			// create a new instance
			$form = new MC4WP_Form( $request );
			self::$instance = $form;
		}

		// attach request to form
		if( $request && ! $form->has( $request ) ) {
			$form->attach( $request );
		}

		return $form;
	}

	/**
	 * @param iMC4WP_Request $request
	 */
	public function __construct( iMC4WP_Request $request = null ) {
		parent::__construct( $request );

		$this->content = $this->settings['markup'];
	}

}