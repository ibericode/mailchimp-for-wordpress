<?php

/**
 * This snippet enables every marketing permission in the list a new subscriber is about to be added to (or updated in)
 */
add_filter( 'mc4wp_subscriber_data', function( $subscriber, $list_id ) {
    $mailchimp = new MC4WP_MailChimp();
	$marketing_permissions = $mailchimp->get_list_marketing_permissions( $list_id );
	foreach ( $marketing_permissions as $mp ) {
		$subscriber->marketing_permissions[] = (object) array(
			'marketing_permission_id' => $mp->marketing_permission_id,
			'enabled'                 => true,
		);
	}
}, 10, 2 );
