<?php

/**
 * Echoes a sign-up checkbox.
 *
 * @since 1.0
 * @deprecated 3.0
 *
 */
function mc4wp_checkbox() {
	_deprecated_function( __FUNCTION__, 'MailChimp for WordPress v3.0' );
}

/**
 * Echoes a MailChimp for WordPress form
 *
 * @param   int     $id     The form ID
 * @since 1.0
 * @deprecated 3.0
 * @use mc4wp_show_form()
 * @return MC4WP_Form
 */
function mc4wp_form( $id = 0, $attributes = array() ) {
	_deprecated_function( __FUNCTION__, 'MailChimp for WordPress v3.0', 'mc4wp_show_form' );
	return mc4wp_show_form( $id, $attributes );
}
