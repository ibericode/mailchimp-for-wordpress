<?php
  add_filter( 'mc4wp_integration_woocommerce_subscriber_data', function( MC4WP_MailChimp_Subscriber $subscriber ) {
    if( defined( 'ICL_LANGUAGE_CODE') ) {
	switch( ICL_LANGUAGE_CODE ) {
		// spanish
		case 'es':
			// replace "interest-id" with the actual ID of your interest.
        		$subscriber->interests[ "interest-id" ] = true;
			break;
			// english
		case 'en':
			// replace "interest-id" with the actual ID of your interest.
        		$subscriber->interests[ "interest-id" ] = true;
			break;
		}
	}
	return $subscriber;
});
