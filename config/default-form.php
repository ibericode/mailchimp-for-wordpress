<?php

$email_label = __( 'Email address', 'mailchimp-for-wp' );
$email_placeholder = __( 'Your email address', 'mailchimp-for-wp' );
$signup_button = __( 'Sign up', 'mailchimp-for-wp' );

$markup = "<p>\n\t<label>{$email_label}: </label>\n";
$markup .= "\t<input type=\"email\" name=\"EMAIL\" placeholder=\"{$email_placeholder}\" required />\n</p>\n\n";
$markup .= "<p>\n\t<input type=\"submit\" value=\"{$signup_button}\" />\n</p>";

return $markup;