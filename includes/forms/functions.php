<?php

/**
 * Returns a Form instance
 *
 * @access public
 *
 * @param int|WP_Post $form_id.
 *
 * @return MC4WP_Form
 */
function mc4wp_get_form( $form_id = 0 ) {
	return MC4WP_Form::get_instance( $form_id );
}

/**
 * Get an array of Form instances
 *
 * @access public
 *
 * @param array $args Array of parameters
 *
 * @return MC4WP_Form[]
 */
function mc4wp_get_forms( array $args = array() ) {
	// parse function arguments
	$default_args      = array(
		'post_status'         => 'publish',
		'posts_per_page'      => -1,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	);
	$args              = array_merge( $default_args, $args );

	// set post_type here so it can't be overwritten using function arguments
	$args['post_type'] = 'mc4wp-form';

	$q                 = new WP_Query();
	$posts             = $q->query( $args );
	$forms = array();
	foreach ( $posts as $post ) {
		try {
			$form = mc4wp_get_form( $post );
		} catch ( Exception $e ) {
			continue;
		}

		$forms[] = $form;
	}
	return $forms;
}

/**
 * Echoes the given form
 *
 * @access public
 *
 * @param int $form_id
 * @param array $config
 * @param bool $echo
 *
 * @return string
 */
function mc4wp_show_form( $form_id = 0, $config = array(), $echo = true ) {
	/** @var MC4WP_Form_Manager $forms */
	$forms = mc4wp( 'forms' );
	return $forms->output_form( $form_id, $config, $echo );
}


/**
 * Gets an instance of the submitted form, if any.
 *
 * @access public
 *
 * @return MC4WP_Form|null
 */
function mc4wp_get_submitted_form() {
	return mc4wp( 'forms' )->get_submitted_form();
}
