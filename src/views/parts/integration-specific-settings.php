<?php $opts = $integrations->get_integration_options( $type ); ?>
<?php if( empty( $opts['lists'] ) ) { ?>
	<div class="mc4wp-info">
		<p><?php _e( 'If you want to use sign-up checkboxes, select at least one MailChimp list to subscribe people to.', 'mailchimp-for-wp' ); ?></p>
	</div>
<?php } ?>

<table class="form-table">
	<tr valign="top">
		<th scope="row"><?php _e( 'MailChimp Lists', 'mailchimp-for-wp' ); ?></th>

		<?php // loop through lists
		if( empty( $lists ) ) {
			?><td colspan="2"><?php printf( __( 'No lists found, <a href="%s">are you connected to MailChimp</a>?', 'mailchimp-for-wp' ), admin_url( 'admin.php?page=mailchimp-for-wp' ) ); ?></td><?php
		} else { ?>
			<td class="nowrap">
				<?php foreach( $lists as $list ) { ?>
					<label><input type="checkbox" name="mc4wp_integrations[custom_settings][<?php echo $type; ?>][lists][<?php echo esc_attr( $list->id ); ?>]" value="<?php echo esc_attr( $list->id ); ?>" <?php checked( array_key_exists( $list->id, $opts['lists'] ), true ); ?>> <?php echo esc_html( $list->name ); ?></label><br />
				<?php } ?>
			</td>
			<td class="desc"><?php _e( 'Select the list(s) to which people who check the checkbox should be subscribed.' ,'mailchimp-for-wp' ); ?></td>
		<?php
		}
		?>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e( 'Double opt-in?', 'mailchimp-for-wp' ); ?></th>
		<td class="nowrap">
			<label>
				<input type="radio" name="mc4wp_integrations[custom_settings][<?php echo $type; ?>][double_optin]" value="1" <?php checked( $opts['double_optin'], 1 ); ?> />
				<?php _e( 'Yes', 'mailchimp-for-wp' ); ?>
			</label> &nbsp;
			<label>
				<input type="radio" name="mc4wp_integrations[custom_settings][<?php echo $type; ?>][double_optin]" value="0" <?php checked( $opts['double_optin'], 0 ); ?> />
				<?php _e( 'No', 'mailchimp-for-wp' ); ?>
			</label>
		</td>
		<td class="desc"><?php _e( 'Select "yes" if you want people to confirm their email address before being subscribed (recommended)', 'mailchimp-for-wp' ); ?></td>
	</tr>
</table>

<?php submit_button(); ?>