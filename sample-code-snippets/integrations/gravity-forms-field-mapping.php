<?php

// The problem with Gravity forms in this regard is that it generates field names sort of randomly, such as name=”input_4″ so it’s hard for MC4WP to know what field is what.
// The only way to do it is if you manually map those field names (you can get them via “right click > inspect” when you view the form) to the correct MailChimp fields with some custom code.

add_filter( 'mc4wp_integration_gravity-forms_subscriber_data', function( MC4WP_MailChimp_Subscriber $subscriber ) {
	$subscriber->merge_fields[ "FNAME" ] = sanitize_text_field( $_POST['input_2'] );
	$subscriber->merge_fields[ "COUNTRY" ] = sanitize_text_field( $_POST['input_4'] );
	return $subscriber;
});


//This example would map input_2 to FNAME and input_4 to the MailChimp field COUNTRY.
