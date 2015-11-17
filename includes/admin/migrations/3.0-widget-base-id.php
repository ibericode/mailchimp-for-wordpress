<?php

defined( 'ABSPATH' ) or exit;

$section_widgets = get_option( 'sidebars_widgets', array() );
$replaced = false;

foreach( $section_widgets as $section => $widgets ) {

	// WP has an "array_version" key that is not an array...
	if( ! is_array( $widgets ) ) {
		continue;
	}

	// loop through widget ID's
	foreach( $widgets as $key => $widget_id ) {

		// does this widget ID start with "mc4wp_widget"?
		if( strpos( $widget_id, 'mc4wp_widget' ) === 0 ) {

			// replace "mc4wp_widget" with "mc4wp_form_widget"
			$new_widget_id = str_replace( 'mc4wp_widget', 'mc4wp_form_widget', $widget_id );
			$section_widgets[ $section ][ $key ] = $new_widget_id;
			$replaced = true;
		}
	}
}


// update option if we made changes
if( $replaced ) {
	update_option( 'sidebars_widgets', $section_widgets );
}

// update widget options
$options = get_option( 'widget_mc4wp_widget', false );
if( $options ) {
	update_option( 'widget_mc4wp_form_widget', $options );

	// delete old option
	delete_option( 'widget_mc4wp_widget' );
}

