<?php

/**
 * Class MC4WP_Array_Bag
 *
 * @access private
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
	 * @param mixed $value
	 */
	public function set( $name, $value ) {
		$this->array[ $name ] = $value;
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

        // allow dot access for nested array keys: key.subkey
        $array = $this->array;
        foreach(explode('.', $name) as $segment) {
            if ( ! array_key_exists( $segment, $array )) {
                return $default;
            }

            $array = $array[$segment];
         }

         return $array;
	}

	/**
	 * @return array
	 */
	public function all() {
		return $this->array;
	}

	/**
	 * @param $prefix
	 *
	 * @return array
	 */
	public function all_with_prefix( $prefix ) {
		$return = array();
		$length = strlen( $prefix );

		foreach( $this->array as $key => $value ) {
			if( strpos( $key, $prefix ) === 0 ) {

				$new_key = substr( $key, $length );
				$return[ $new_key ] = $value;
			}
		}

		return $return;
	}

	/**
	 * @param $prefix
	 * @return array
	 */
	public function all_without_prefix( $prefix ) {
		$return = array();

		foreach( $this->array as $key => $value ) {
			if( strpos( $key, $prefix ) !== 0 ) {
				$return[ $key ] = $value;
			}
		}

		return $return;
	}
}