<?php

/**
 * Class MC4WP_Array_Bag
 *
 * @ignore
 */
class MC4WP_Array_Bag {

	/**
	 * @var array
	 */
	protected $array;

	/**
	 * @param array $array
	 */
	public function __construct( array $array ) {
		$this->array = $array;
	}

	/**
	 * @return array
	 */
	public function keys() {
		return array_keys( $this->array );
	}

	/**
	 * @param string $name
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public function get( $name, $default = null ) {

		if( isset( $this->array[ $name ] ) ) {
			return $this->array[ $name ];
		}

		return $default;
	}

	/**
	 * @param $case
	 * @return array
	 */
	public function all( $case = null ) {
		$data = $this->array;

		if( null !== $case ) {
			$data = array_change_key_case( $data, $case );
		}

		return $data;
	}

	/**
	 * @param $prefix
	 * @param $case
	 *
	 * @return array
	 */
	public function all_with_prefix( $prefix, $case = null ) {
		$return = array();
		$length = strlen( $prefix );

		$data = $this->all( $case );

		foreach( $data as $key => $value ) {
			if( strpos( $key, $prefix ) === 0 ) {

				$new_key = substr( $key, $length );
				$return[ $new_key ] = $value;
			}
		}

		return $return;
	}

	/**
	 * @param $prefix
	 * @param $case
	 * @return array
	 */
	public function all_without_prefix( $prefix, $case = null ) {
		$return = array();

		foreach( $this->all( $case ) as $key => $value ) {
			if( strpos( $key, $prefix ) !== 0 ) {
				$return[ $key ] = $value;
			}
		}

		return $return;
	}
}