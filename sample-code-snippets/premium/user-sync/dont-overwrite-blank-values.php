<?php
// By default, if a field in Wordpress is empty it will send this empty value to MailChimp, 
// possibly overwriting the field if it did have some data in it in the MailChimp audience. 
// This code will stop UserSync from sending in Empty values so that if a field has no value
// in Wordpress, but it has a value in MailChimp, the MailChimp value will ke kept in place. 


add_filter( 'mc4wp_user_sync_subscriber_data', function($subscriber) {
	$subscriber->merge_fields = array_filter($subscriber->merge_fields, function($value) {
		return $value !== '';
	});
});
