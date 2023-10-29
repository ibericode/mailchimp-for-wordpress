<?php 
/**
* The following snippet allows you to subscribe & unsubscribe from a user's "My Account" page.
*/

$mailchimp_list_id = '01853015ba';

add_action( 'woocommerce_edit_account_form', function() use ( $mailchimp_list_id ) {
	$user = wp_get_current_user();

	if( empty( $user->newsletter ) ) {
		$mailchimp = new MC4WP_MailChimp();
		$subscribed = $mailchimp->list_has_subscriber( $mailchimp_list_id, $user->billing_email );
		update_user_meta( $user->ID, 'newsletter', $subscribed ? 1 : 0 );
	}
	
	
	echo '<p><label><input type="checkbox" name="subscribe_to_newsletter" value="1" ' . ( $user->newsletter ? 'checked' : '' ) . ' /> Subscribe to our newsletter</label></p>';
});

add_action( 'woocommerce_save_account_details', function($user_id) use ( $mailchimp_list_id ) {
	$mailchimp = new MC4WP_MailChimp();
	$user = get_userdata( $user_id );
	$update_existing = true;
	$subscribe_to_newsletter = ! empty( $_POST['subscribe_to_newsletter'] ) ? 1 : 0;
	update_user_meta( $user_id, 'newsletter', $subscribe_to_newsletter );

	if( $subscribe_to_newsletter ) {
		$mailchimp->list_subscribe( $mailchimp_list_id, $user->billing_email, array(
			'merge_fields' => array(
				// merge fields go here
			)
		), $update_existing );
	} else {
		$mailchimp->list_unsubscribe( $mailchimp_list_id, $user->billing_email );
	}
});
