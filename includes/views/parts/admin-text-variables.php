<?php
defined( 'ABSPATH' ) or exit;

$language = defined( 'ICL_LANGUAGE_CODE' ) ? ICL_LANGUAGE_CODE : get_locale();
?>
<p>
	<?php echo sprintf( __( 'The following list of variables can be used to <a href="%s">add some dynamic content to your form or success and error messages</a>.', 'mailchimp-for-wp' ), 'https://mc4wp.com/kb/using-variables-in-your-form-or-messages/' ) . ' ' . __( 'This allows you to personalise your form or response messages.', 'mailchimp-for-wp' ); ?>
</p>
<table class="mc4wp-help">
    <tr>
        <th>{email}</th>
		<td><?php _e( 'Replaced with the visitor\'s email (if set in URL or cookie).', 'mailchimp-for-wp' ); ?></td>
    </tr>
    <tr>
        <th>{response}</th>
		<td><?php _e( 'Replaced with the form response (error or success messages).', 'mailchimp-for-wp' ); ?></td>
    </tr>
    <tr>
        <th>{subscriber_count}</th>
		<td><?php _e( 'Replaced with the number of subscribers on the selected list(s)', 'mailchimp-for-wp' ); ?></td>
    </tr>
    <tr>
        <th>{language}</th>
		<td><?php printf( __( 'Replaced with the current site language, eg: %s', 'mailchimp-for-wp' ), '<em>' . $language . '</em>' ); ?></td>
    </tr>
    <tr>
        <th>{ip}</th>
		<td><?php _e( 'Replaced with the visitor\'s IP address', 'mailchimp-for-wp' ); ?></td>
    </tr>
    <tr>
        <th>{date}</th>
		<td><?php printf( __( 'Replaced with the current date (yyyy/mm/dd eg: %s)', 'mailchimp-for-wp' ), '<em>' . date( 'Y/m/d' ) . '</em>' ); ?></td>
    </tr>
    <tr>
        <th>{time}</th>
		<td><?php printf( __( 'Replaced with the current time (hh:mm:ss eg: %s)', 'mailchimp-for-wp' ), '<em>' . date( 'H:i:s' ) . '</em>' ); ?></td>
    </tr>
    <tr>
        <th>{user_email}</th>
		<td><?php _e( 'Replaced with the logged in user\'s email (or nothing, if there is no logged in user)', 'mailchimp-for-wp' ); ?></td>
    </tr>
    <tr>
        <th>{user_firstname}</th>
		<td><?php _e( 'First name of the current user', 'mailchimp-for-wp' ); ?></td>
    </tr>
    <tr>
        <th>{user_lastname}</th>
		<td><?php _e( 'Last name of the current user', 'mailchimp-for-wp' ); ?></td>
    </tr>
    <tr>
        <th>{user_id}</th>
		<td><?php _e( 'Current user ID', 'mailchimp-for-wp' ); ?></td>
    </tr>
    <tr>
        <th>{current_url}</th>
		<td><?php _e( 'Current URL', 'mailchimp-for-wp' ); ?></td>
    </tr>
	<tr>
		<th>{current_path}</th>
		<td><?php _e( 'Current URL path', 'mailchimp-for-wp' ); ?></td>
	</tr>
	<tr>
		<th>{data_FNAME}</th>
		<td><?php _e( 'The value of the <strong>FNAME</strong> field, if set.', 'mailchimp-for-wp' ); ?></td>
	</tr>
</table>
