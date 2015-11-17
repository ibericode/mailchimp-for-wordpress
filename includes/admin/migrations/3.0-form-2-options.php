<?php
$global_options = get_option( 'mc4wp_form', array() );

// find all form posts
$posts = get_posts(
	array(
		'post_type' => 'mc4wp-form',
		'post_status' => 'publish',
		'numberposts' => -1
	)
);

$css_map = array(
	'default'       => 'form-basic',
	'custom'        => 'styles-builder',
	'light'         => 'form-theme-light',
	'dark'          => 'form-theme-dark',
	'red'           => 'form-theme-red',
	'green'         => 'form-theme-green',
	'blue'          => 'form-theme-blue',
	'custom-color'  => 'form-theme-custom-color'
);

foreach( $posts as $post ) {

	// get form options from post meta directly
	$options = (array) get_post_meta( $post->ID, '_mc4wp_settings', true );

	// update option value
	if( isset( $options['css'] ) && isset( $css_map[ $options['css'] ] ) ) {
		$options['css'] = $css_map[ $options['css'] ];
	}

	// set all empty options to global value
	foreach( $options as $key => $value ) {
		if( $value === '' && ! empty( $global_options[ $key ] ) ) {
			$global_value = $global_options[ $key ];
			$options[ $key ] = $global_value;
		}
	}

	update_post_meta( $post->ID, '_mc4wp_settings', $options );
}

// delete old options
delete_option( 'mc4wp_form' );