<?php defined( 'ABSPATH' ) or exit; $messages = $form->messages; ?>

<h2><?php _e( 'Form Messages', 'mailchimp-for-wp' ); ?></h2>

<table class="form-table mc4wp-form-messages">

	<?php do_action( 'mc4wp_admin_form_before_messages_settings_rows', $opts, $form ); ?>

	<tr valign="top">
		<th scope="row"><label for="mc4wp_form_subscribed"><?php _e( 'Successfully subscribed', 'mailchimp-for-wp' ); ?></label></th>
		<td>
			<input type="text" class="widefat" id="mc4wp_form_subscribed" name="mc4wp_form[messages][subscribed]" value="<?php echo esc_attr( $form->messages['subscribed'] ); ?>" required />
			<p class="help"><?php _e( 'The text that shows when an email address is successfully subscribed to the selected list(s).', 'mailchimp-for-wp' ); ?></p>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><label for="mc4wp_form_invalid_email"><?php _e( 'Invalid email address', 'mailchimp-for-wp' ); ?></label></th>
		<td>
			<input type="text" class="widefat" id="mc4wp_form_invalid_email" name="mc4wp_form[messages][invalid_email]" value="<?php echo esc_attr( $form->messages['invalid_email'] ); ?>" required />
			<p class="help"><?php _e( 'The text that shows when an invalid email address is given.', 'mailchimp-for-wp' ); ?></p>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><label for="mc4wp_form_required_field_missing"><?php _e( 'Required field missing', 'mailchimp-for-wp' ); ?></label></th>
		<td>
			<input type="text" class="widefat" id="mc4wp_form_required_field_missing" name="mc4wp_form[messages][required_field_missing]" value="<?php echo esc_attr( $form->messages['required_field_missing'] ); ?>" required />
			<p class="help"><?php _e( 'The text that shows when a required field for the selected list(s) is missing.', 'mailchimp-for-wp' ); ?></p>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><label for="mc4wp_form_already_subscribed"><?php _e( 'Already subscribed', 'mailchimp-for-wp' ); ?></label></th>
		<td>
			<input type="text" class="widefat" id="mc4wp_form_already_subscribed" name="mc4wp_form[messages][already_subscribed]" value="<?php echo esc_attr( $form->messages['already_subscribed'] ); ?>" required />
			<p class="help"><?php _e( 'The text that shows when the given email is already subscribed to the selected list(s).', 'mailchimp-for-wp' ); ?></p>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><label for="mc4wp_form_error"><?php _e( 'General error' ,'mailchimp-for-wp' ); ?></label></th>
		<td>
			<input type="text" class="widefat" id="mc4wp_form_error" name="mc4wp_form[messages][error]" value="<?php echo esc_attr( $form->messages['error'] ); ?>" required />
			<p class="help"><?php _e( 'The text that shows when a general error occured.', 'mailchimp-for-wp' ); ?></p>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><label for="mc4wp_form_unsubscribed"><?php _e( 'Unsubscribed', 'mailchimp-for-wp' ); ?></label></th>
		<td>
			<input type="text" class="widefat" id="mc4wp_form_unsubscribed" name="mc4wp_form[messages][unsubscribed]" value="<?php echo esc_attr( $form->messages['unsubscribed'] ); ?>" required />
			<p class="help"><?php _e( 'When using the unsubscribe method, this is the text that shows when the given email address is successfully unsubscribed from the selected list(s).', 'mailchimp-for-wp' ); ?></p>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><label for="mc4wp_form_not_subscribed"><?php _e( 'Not subscribed', 'mailchimp-for-wp' ); ?></label></th>
		<td>
			<input type="text" class="widefat" id="mc4wp_form_not_subscribed" name="mc4wp_form[messages][not_subscribed]" value="<?php echo esc_attr( $form->messages['not_subscribed'] ); ?>" required />
			<p class="help"><?php _e( 'When using the unsubscribe method, this is the text that shows when the given email address is not on the selected list(s).', 'mailchimp-for-wp' ); ?></p>
		</td>
	</tr>

	<?php do_action( 'mc4wp_admin_form_after_messages_settings_rows', $messages, $form ); ?>

	<tr valign="top">
		<th></th>
		<td>
			<p class="help"><?php printf( __( 'HTML tags like %s are allowed in the message fields.', 'mailchimp-for-wp' ), '<code>' . esc_html( '<strong><em><a>' ) . '</code>' ); ?></p>
		</td>
	</tr>

</table>

<?php submit_button(); ?>