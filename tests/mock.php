<?php

if( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', '/' );
}


function add_filter( $hook, $callback, $prio = 10, $arguments = 1 ) {

}

function add_action( $hook, $callback, $prio = 10, $arguments = 1) {

}

function is_user_logged_in() {
	return false;
}

function stripslashes_deep( $data ) {
	return $data;
}

function sanitize_text_field( $value ) {
	return $value;
}