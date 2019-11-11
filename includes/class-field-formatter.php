<?php

/**
 * Class MC4WP_Field_Formatter
 *
 * Formats values based on what the Mailchimp API expects or accepts for the given field types.
 */
class MC4WP_Field_Formatter {


	/**
	 * @param mixed $value
	 * @param object $options
	 * @return array
	 */
	public function address( $value, $options = null ) {
		// auto-format if this is a string
		if ( is_string( $value ) ) {

			// addr1, addr2, city, state, zip, country
			$address_pieces = explode( ',', $value );
			$address_pieces = array_filter( $address_pieces );
			$address_pieces = array_values( $address_pieces );

			// try to fill it.... this is a long shot
			$value = array(
				'addr1' => $address_pieces[0],
				'city'  => isset( $address_pieces[1] ) ? $address_pieces[1] : '',
				'state' => isset( $address_pieces[2] ) ? $address_pieces[2] : '',
				'zip'   => isset( $address_pieces[3] ) ? $address_pieces[3] : '',
			);

			if ( ! empty( $address_pieces[4] ) ) {
				$value['country'] = $address_pieces[4];
			}
		} elseif ( is_array( $value ) ) {
			// merge with array of empty defaults to allow skipping certain fields
			$default = array_fill_keys( array( 'addr1', 'city', 'state', 'zip' ), '' );
			$value   = array_merge( $default, $value );
		}

		return $value;
	}

	/**
	 * @param mixed $value
	 * @param object $options
	 * @return string
	 */
	public function birthday( $value, $options = null ) {
		$format = is_object( $options ) && isset( $options->date_format ) ? $options->date_format : 'MM/DD';

		if ( is_array( $value ) ) {
			// allow for "day" and "month" fields
			if ( isset( $value['month'] ) && isset( $value['day'] ) ) {
				$value = $value['month'] . '/' . $value['day'];
			} else {
				// if other array, just join together
				$value = join( '/', $value );
			}
		}

		$value = trim( $value );
		if ( empty( $value ) ) {
			return $value;
		}

		// always use slashes as delimiter, so next part works
		$value = str_replace( array( '.', '-' ), '/', $value );

		// if format = DD/MM  OR if first part is definitely a day value (>12), then flip order
		// this allows `strtotime` to understand `dd/mm` values
		$values = explode( '/', $value );
		if ( $format === 'DD/MM' || ( $values[0] > 12 && $values[0] <= 31 && isset( $values[1] ) && $values[1] <= 12 ) ) {
			$values = array_reverse( $values );
			$value  = join( '/', $values );
		}

		// Mailchimp expects a MM/DD format, regardless of their display preference
		$value = (string) gmdate( 'm/d', strtotime( $value ) );
		return $value;
	}

	/**
	 * @param mixed $value
	 * @param object $options
	 * @return string
	 */
	public function date( $value, $options = null ) {
		$format = is_object( $options ) && isset( $options->date_format ) ? $options->date_format : 'Y-m-d';

		if ( is_array( $value ) ) {

			// allow for "year", "month" and "day" keys
			if ( isset( $value['year'] ) && isset( $value['month'] ) && isset( $value['day'] ) ) {
				$value = $value['year'] . '/' . $value['month'] . '/' . $value['day'];
			} else {
				// if other array, just join together
				$value = join( '/', $value );
			}
		}

		$value = trim( $value );
		if ( empty( $value ) ) {
			return $value;
		}

		return (string) gmdate( $format, strtotime( $value ) );
	}

	/**
	 * @param string $value
	 * @param object $options
	 * @return string
	 */
	public function language( $value, $options = null ) {
		$value = trim( $value );

		$exceptions = array(
			'pt_PT',
			'es_ES',
			'fr_CA',
		);

		if ( ! in_array( $value, $exceptions, true ) ) {
			$value = substr( $value, 0, 2 );
		}

		return $value;
	}

	/**
	 * @param mixed $value
	 * @param object $options
	 * @return bool
	 */
	public function boolean( $value, $options = null ) {
		$falsey = array( 'false', '0' );

		if ( in_array( $value, $falsey, true ) ) {
			return false;
		}

		// otherwise, just cast.
		return (bool) $value;
	}
}
