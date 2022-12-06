<?php

/**
* The snippet below will set the correct usermeta value to ensure the Registration Form integration sets the opt-in status for MailChimp User Sync too.
*/
add_action( 'user_register', function( $user_id ) {
   // do nothing if checkbox was not checked
   if( empty( $_POST['_mc4wp_subscribe_wp-registration-form'] ) ) {
      return;
   }

   $list_id = '6d93b47d25'; // change this to your MailChimp list ID
   $meta_key = sprintf( 'mailchimp_sync_%s_opted_in', $list_id );
   update_user_meta( $user_id, $meta_key, '1' );
});
