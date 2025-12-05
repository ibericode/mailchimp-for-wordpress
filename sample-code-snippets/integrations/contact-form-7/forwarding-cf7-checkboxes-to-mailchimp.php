<?php

/**
 * Contact Form 7 checkboxes are passed as an array.  
 * To be able to forward their values to Mailchimp we need to change the selection(s) into a string
 * to prevent an error such "Contact Form 7 > MailChimp API Error: Bad Request. The resource submitted could not be validated."
 *
 * This code takes the checkbox named CHECKBOX1 from the CF7 form, and translates it to a semicolon separated string and send that to the Mailchimp field MMERGE8
 * 
 * Change MMERGE8 to your Mailchimp field and CHECKBOX1 to your CF7 field. 
 * The checkbox name in CF7 still needs the mc4wp- prefix, eg mc4wp-checkbox-1
 */

add_filter( 'mc4wp_integration_contact-form-7_data', function( $data ) {
   $data['MMERGE8'] = join( ';', $data['CHECKBOX-1'] ?? [] );
   return $data;
});
