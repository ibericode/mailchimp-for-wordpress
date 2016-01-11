<?php

/**
 * Class MC4WP_Field_Formatter
 *
 * Formats values based on what the MailChimp API expects or accepts for the given field types.
 */
class MC4WP_Field_Formatter {

	/**
	 * @param mixed $value
	 *
	 * @return array
	 */
	public function address( $value ) {
		// auto-format if this is a string
		if( is_string( $value ) ) {

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

			if( ! empty( $address_pieces[4] ) ) {
				$value['country'] = $address_pieces[4];
			}

		} elseif( is_array( $value ) ) {
			// merge with array of empty defaults to allow skipping certain fields
			$default = array_fill_keys( array( 'addr1', 'city', 'state', 'zip' ), '' );
			$value = array_merge( $default, $value );
		}

		return $value;
	}

	/**
	 * @param mixed $value
	 *
	 * @return string
	 */
	public function birthday( $value ) {
		if( is_array( $value ) ) {
			// allow for "day" and "month" fields
			if( isset( $value['month'] ) && isset( $value['day'] ) ) {
				$value = $value['month'] . '/' . $value['day'];
			} else {

				// if other array, just join together
				$value = join( '/', $value );
			}
		}

		// if first part looks like a day, flip order so month (or even year) comes first
		// this allows `strtotime` to understand `dd/mm` values
		$values = explode( '/', $value );
		if( $values[0] > 12 && $values[0] <= 31 ) {
			$values = array_reverse ( $values );
			$value = join( '/', $values );
		}

		$value = (string) date( 'm/d', strtotime( $value ) );
		return $value;
	}

	/**
	 * @param mixed $value
	 *
	 * @return string
	 */
	public function date( $value ) {

		if( is_array( $value ) ) {

			// allow for "year", "month" and "day" keys
			if( isset( $value['year'] ) && isset( $value['month'] ) && isset( $value['day'] ) ) {
				$value = $value['year'] . '/' . $value['month'] . '/' . $value['day'];
			} else {
				// if other array, just join together
				$value = join( '/', $value );
			}
		}

		return (string) date('Y-m-d', strtotime( $value ) );
	}
}