<?php

// find all form posts
$posts = get_posts(
	array(
		'post_type' => 'mc4wp-form',
		'post_status' => 'publish',
		'numberposts' => -1
	)
);

$map = array(
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
	$options = get_post_meta( $post->ID, '_mc4wp_settings', true );

	if( empty( $options ) || empty( $options['css'] ) ) {
		continue;
	}

	// change option value
	if( isset( $map[ $options['css'] ] ) ) {
		$options['css'] = $map[ $options['css'] ];
		update_post_meta( $post->ID, '_mc4wp_settings', $options );
	}

}