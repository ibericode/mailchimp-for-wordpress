<?php

/**
 * Gets the absolute url to edit a form
 *
 * @param int $form_id ID of the form
 * @param string $tab Tab identifier to open
 *
 * @return string
 */
function mc4wp_get_edit_form_url( $form_id, $tab = '' ) {
	$url = admin_url( sprintf( 'admin.php?page=mailchimp-for-wp-forms&view=edit-form&form_id=%d', $form_id ) );

	if ( ! empty( $tab ) ) {
		$url .= sprintf( '&tab=%s', $tab );
	}

	return $url;
}

/**
 * Get absolute URL to create a new form
 *
 * @return string
 */
function mc4wp_get_add_form_url() {
	$url = admin_url( 'admin.php?page=mailchimp-for-wp-forms&view=add-form' );
	return $url;
}

/**
 * @param        $key
 * @param        $label
 * @param        $value
 * @param string $help_text
 *
 * @return string
 */
//function mc4wp_form_message_setting_row( $key, $label, $value = '', $help_text = '' ) {
//
//
//	$id = 'mc4wp_form_message_' . $key;
//	echo $name = sprintf( 'mc4wp_form[messages][%s]', $key );
//
//	echo '<tr valign="top">';
//
//	# Label
//	echo '<th scope="row">';
//	echo sprintf( '<label for="%s">%s</label>', $id, $label );
//	echo '</th>';
//
//	# Input
//	echo '<td>';
//	echo sprintf( '<input type="text" class="widefat" id="%s" name="%s" value="%s" />', $id, $name, esc_attr( $value ) );
//
//	# Help text
//	if( ! empty( $help_text ) ) {
//		echo sprintf( '<p class="description">%s</p>', $help_text );
//	}
//
//	echo '</td>';
//	echo '</tr>';
//
//	return '';
//}
