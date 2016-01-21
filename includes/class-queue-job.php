<?php

/**
 * Class MC4WP_Queue_Job
 *
 * @ignore
 */
class MC4WP_Queue_Job {

	/**
	 * @var string
	 */
	public $id;

	/**
	 * @var mixed
	 */
	public $data;

	/**
	 * MC4WP_Queue_Job constructor.
	 *
	 * @param $data
	 */
	public function __construct( $data ) {
		$this->id = (string) microtime( true ) . rand( 1, 10000 );
		$this->data = $data;
	}
}