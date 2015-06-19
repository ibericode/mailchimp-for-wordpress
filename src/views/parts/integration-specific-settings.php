<?php defined( 'ABSPATH' ) or exit; ?>
<table class="form-table">

	<?php $isset = ( isset( $opts['lists'] ) ); ?>
	<tr valign="top">
		<th scope="row"><?php _e( 'MailChimp Lists', 'mailchimp-for-wp' ); ?></th>
		<?php // loop through lists
		if( empty( $lists ) ) {
			?><td colspan="2"><?php printf( __( 'No lists found, <a href="%s">are you connected to MailChimp</a>?', 'mailchimp-for-wp' ), admin_url( 'admin.php?page=mailchimp-for-wp' ) ); ?></td><?php
		} else { ?>
			<td class="nowrap">
				<?php foreach( $lists as $list ) { ?>
					<label>
						<input type="checkbox" name="mc4wp_integrations[custom_settings][<?php echo $type; ?>][lists][<?php echo esc_attr( $list->id ); ?>]" value="<?php echo esc_attr( $list->id ); ?>" <?php checked( $isset && array_key_exists( $list->id, $opts['lists'] ) ); ?>>
						<?php echo esc_html( $list->name ); ?>
					</label>
					<br />
				<?php } ?>
			</td>
			<td class="desc"><?php _e( 'Select the list(s) to which people who check the checkbox should be subscribed.' ,'mailchimp-for-wp' ); ?></td>
		<?php
		}
		?>
	</tr>

	<?php $isset = ( isset( $opts['double_optin'] ) ); ?>
	<tr valign="top">
		<th scope="row"><?php _e( 'Double opt-in?', 'mailchimp-for-wp' ); ?></th>
		<td class="nowrap">
			<label>
				<input type="radio" name="mc4wp_integrations[custom_settings][<?php echo $type; ?>][double_optin]" value="1" <?php checked( $isset && $opts['double_optin'], 1 ); ?> />
				<?php _e( 'Yes', 'mailchimp-for-wp' ); ?>
			</label> &nbsp;
			<label>
				<input type="radio" name="mc4wp_integrations[custom_settings][<?php echo $type; ?>][double_optin]" value="0" <?php checked( $isset && $opts['double_optin'], 0 ); ?> />
				<?php _e( 'No', 'mailchimp-for-wp' ); ?>
			</label>
			<label>
				<input type="radio" value="0" <?php checked( ! $isset ); ?> />
				<?php _e( 'Inherit', 'mailchimp-for-wp' ); ?>
			</label>
		</td>
		<td class="desc"><?php _e( 'Select "yes" if you want people to confirm their email address before being subscribed (recommended)', 'mailchimp-for-wp' ); ?></td>
	</tr>

	<?php $isset = ( isset( $opts['label'] ) ); ?>
	<tr valign="top">
		<th scope="row"><label for="mc4wp_checkbox_label"><?php _e( 'Checkbox label text', 'mailchimp-for-wp' ); ?></label></th>
		<td colspan="2">
			<input type="text"  class="widefat" name="mc4wp_integrations[custom_settings][<?php echo $type; ?>][label]" value="<?php if( $isset ) echo esc_attr( $opts['label'] ); ?>" placeholder="<?php echo esc_attr( $inherited['label'] ); ?>" />
			<p class="help"><?php printf( __( 'HTML tags like %s are allowed in the label text.', 'mailchimp-for-wp' ), '<code>' . esc_html( '<strong><em><a>' ) . '</code>' ); ?></p>
		</td>
	</tr>

	<?php $isset = ( isset( $opts['precheck'] ) ); ?>
	<tr valign="top">
		<th scope="row"><?php _e( 'Pre-check the checkbox?', 'mailchimp-for-wp' ); ?></th>
		<td class="nowrap">
			<label>
				<input type="radio" name="mc4wp_integrations[custom_settings][<?php echo $type; ?>][precheck]" value="1" <?php checked( $isset && $opts['precheck'], 1 ); ?> />
				<?php _e( 'Yes', 'mailchimp-for-wp' ); ?>
			</label> &nbsp;
			<label>
				<input type="radio" name="mc4wp_integrations[custom_settings][<?php echo $type; ?>][precheck]" value="0" <?php checked( $isset && $opts['precheck'], 0 ); ?> />
				<?php _e( 'No', 'mailchimp-for-wp' ); ?>
			</label>
			<label>
				<input type="radio" value="0" <?php checked( ! $isset ); ?> />
				<?php _e( 'Inherit', 'mailchimp-for-wp' ); ?>
			</label>
		</td>
		<td class="desc"></td>
	</tr>

	<?php if( $type === 'woocommerce' ) { ?>
	<tr valign="top">
		<th scope="row"><?php _e( 'WooCommerce checkbox position', 'mailchimp-for-wp' ); ?></th>
		<td class="nowrap">
			<select name="mc4wp_integrations[custom_settings][<?php echo $type; ?>][position]">
				<option value="billing" <?php selected( $opts['position'], 'billing' ); ?>><?php _e( 'After the billing details', 'mailchimp-for-wp' ); ?></option>
				<option value="order" <?php selected( $opts['position'], 'order' ); ?>><?php _e( 'After the additional information', 'mailchimp-for-wp' ); ?></option>
			</select>
		</td>
		<td class="desc"><?php _e( 'Choose the position for the checkbox in your WooCommerce checkout form.', 'mailchimp-for-wp' ); ?></td>
	</tr>
	<?php } ?>
</table>

<?php submit_button(); ?>