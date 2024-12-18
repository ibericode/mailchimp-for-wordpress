<?php

mc4wp_register_integration('ninja-forms', 'MC4WP_Ninja_Forms_Integration', true);

add_filter('ninja_forms_register_fields', function ($fields) {
	$fields['mc4wp_optin'] = new MC4WP_Ninja_Forms_Field();
	return $fields;
});

add_filter('ninja_forms_register_actions', function ($actions) {
	$actions['mc4wp_subscribe'] = new MC4WP_Ninja_Forms_Action();
	return $actions;
});
