<?php

class MC4WP_Tools {

	public static function guess_merge_vars( $merge_vars ) {

		// maybe guess first and last name
		if ( isset( $merge_vars['NAME'] ) ) {
			if( ! isset( $merge_vars['FNAME'] ) && ! isset( $merge_vars['LNAME'] ) ) {
				$strpos = strpos( $merge_vars['NAME'], ' ' );
				if ( $strpos !== false ) {
					$merge_vars['FNAME'] = substr( $merge_vars['NAME'], 0, $strpos );
					$merge_vars['LNAME'] = substr( $merge_vars['NAME'], $strpos );
				} else {
					$merge_vars['FNAME'] = $merge_vars['NAME'];
				}
			}
		}

		return $merge_vars;
	}

}