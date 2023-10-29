<?php 
/* This example assumes you add a field to the form with the name language. 
The value send in should be 2 letter lower case language code. 

Example code to add to the form:

<select name="language">
<option value='en'>English</option>
<option value='nl'>Dutch</option>
<option value='de'>German</option>
</select>

If you use WPML you may want to use https://wordpress.org/plugins/mc4wp-wpml/ to send in the WPML lanugage. 
*/


add_filter( 'mc4wp_subscriber_data', function( MC4WP_MailChimp_Subscriber $subscriber ) {
  $language_code = sanitize_text_field( $_POST[ 'language' ] ); 
  $subscriber->language = strtolower( substr( $language_code, 0, 2 ) );
  return $subscriber;
});
