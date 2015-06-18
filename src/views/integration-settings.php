<?php
if( ! defined( 'MC4WP_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}


/** @var $integrations MC4WP_Integrations */
?>
<div id="mc4wp-admin" class="wrap mc4wp-settings">

	<h2><img src="<?php echo MC4WP_PLUGIN_URL . 'assets/img/menu-icon.png'; ?>" /> <?php _e( 'MailChimp for WordPress', 'mailchimp-for-wp' ); ?>: <?php _e( 'Integration Settings', 'mailchimp-for-wp' ); ?></h2>

	<h2 class="nav-tab-wrapper" class="mc4wp-tabs">

		<a class="nav-tab <?php if( $current_tab === 'general' ) { echo 'nav-tab-active'; } ?>" href="<?php echo admin_url( 'admin.php?page=mailchimp-for-wp-integration-settings&tab=general' ); ?>"><?php _e( 'General', 'mailchimp-for-wp' ); ?></a>

		<?php foreach( $integrations->get_available_integrations() as $type => $name ) { ?>
			<a class="nav-tab <?php if( $current_tab === $type ) { echo 'nav-tab-active'; } ?>" href="<?php echo admin_url( 'admin.php?page=mailchimp-for-wp-integration-settings&tab='. $type ); ?>"><?php echo $name; ?></a>
		<?php } ?>

	</h2>

	<?php settings_errors(); ?>

	<form action="<?php echo admin_url( 'options.php' ); ?>" method="post">
		<?php settings_fields( 'mc4wp_integrations_settings' ); ?>

	<div id="mc4wp-content">

		<div id="tab-general" class="mc4wp-tab" style="<?php if( $current_tab === 'general' ) { echo 'display: block;'; } ?>">

			<h3 class="mc4wp-title"><?php _e( 'MailChimp settings for integrations', 'mailchimp-for-wp' ); ?></h3>

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
								<label><input type="checkbox" name="mc4wp_integrations[lists][<?php echo esc_attr( $list->id ); ?>]" value="<?php echo esc_attr( $list->id ); ?>" <?php checked( array_key_exists( $list->id, $opts['lists'] ), true ); ?>> <?php echo esc_html( $list->name ); ?></label><br />
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
							<input type="radio" name="mc4wp_integrations[double_optin]" value="1" <?php checked( $opts['double_optin'], 1 ); ?> />
							<?php _e( 'Yes', 'mailchimp-for-wp' ); ?>
						</label> &nbsp;
						<label>
							<input type="radio" id="mc4wp_checkbox_double_optin_0" name="mc4wp_integrations[double_optin]" value="0" <?php checked( $opts['double_optin'], 0 ); ?> />
							<?php _e( 'No', 'mailchimp-for-wp' ); ?>
						</label>
					</td>
					<td class="desc"><?php _e( 'Select "yes" if you want people to confirm their email address before being subscribed (recommended)', 'mailchimp-for-wp' ); ?></td>
				</tr>
			</table>

			<h3 class="mc4wp-title"><?php _e( 'Checkbox settings', 'mailchimp-for-wp' ); ?></h3>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><?php _e( 'Add to these forms', 'mailchimp-for-wp' ); ?></th>
					<td class="nowrap">
						<?php foreach( $integrations->get_available_integrations() as $type => $name ) { ?>
							<label><input name="mc4wp_integrations[custom_settings][<?php echo $type; ?>][enabled]" value="1" type="checkbox" <?php checked( $integrations->is_enabled( $type ) ); ?>> <?php echo esc_html( $name ); ?></label><br />
						<?php } ?>
					</td>
					<td class="desc">
						<?php _e( 'Selecting a form will automatically add the sign-up checkbox to it.', 'mailchimp-for-wp' ); ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="mc4wp_checkbox_label"><?php _e( 'Checkbox label text', 'mailchimp-for-wp' ); ?></label></th>
					<td colspan="2">
						<input type="text"  class="widefat" id="mc4wp_checkbox_label" name="mc4wp_integrations[label]" value="<?php echo esc_attr( $opts['label'] ); ?>" required />
						<p class="help"><?php printf( __( 'HTML tags like %s are allowed in the label text.', 'mailchimp-for-wp' ), '<code>' . esc_html( '<strong><em><a>' ) . '</code>' ); ?></p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e( 'Pre-check the checkbox?', 'mailchimp-for-wp' ); ?></th>
					<td class="nowrap"><label><input type="radio" name="mc4wp_integrations[precheck]" value="1" <?php checked( $opts['precheck'], 1 ); ?> /> <?php _e( 'Yes', 'mailchimp-for-wp' ); ?></label> &nbsp; <label><input type="radio" name="mc4wp_integrations[precheck]" value="0" <?php checked( $opts['precheck'], 0 ); ?> /> <?php _e( 'No', 'mailchimp-for-wp' ); ?></label></td>
					<td class="desc"></td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e( 'Load some default CSS?', 'mailchimp-for-wp' ); ?></th>
					<td class="nowrap"><label><input type="radio" name="mc4wp_integrations[css]" value="1" <?php checked( $opts['css'], 1 ); ?> /> <?php _e( 'Yes', 'mailchimp-for-wp' ); ?></label> &nbsp; <label><input type="radio" name="mc4wp_integrations[css]" value="0" <?php checked( $opts['css'], 0 ); ?> /> <?php _e( 'No', 'mailchimp-for-wp' ); ?></label></td>
					<td class="desc"><?php _e( 'Select "yes" if the checkbox appears in a weird place.', 'mailchimp-for-wp' ); ?></td>
				</tr>
				<tr valign="top" id="woocommerce-settings" <?php if( ! $integrations->is_enabled('woocommerce') ) { ?>style="display: none;"<?php } ?>>
					<th scope="row"><?php _e( 'WooCommerce checkbox position', 'mailchimp-for-wp' ); ?></th>
					<td class="nowrap">
						<select name="mc4wp_integrations[woocommerce_position]">
							<option value="billing" <?php selected( $opts['woocommerce_position'], 'billing' ); ?>><?php _e( 'After the billing details', 'mailchimp-for-wp' ); ?></option>
							<option value="order" <?php selected( $opts['woocommerce_position'], 'order' ); ?>><?php _e( 'After the additional information', 'mailchimp-for-wp' ); ?></option>
						</select>
					</td>
					<td class="desc"><?php _e( 'Choose the position for the checkbox in your WooCommerce checkout form.', 'mailchimp-for-wp' ); ?></td>
				</tr>
			</table>

		<?php submit_button(); ?>

	</div>

	<?php foreach( $integrations->get_available_integrations() as $type => $name ) { ?>
	<div id="tab-<?php echo $type; ?>" class="mc4wp-tab" style="<?php if( $current_tab === $type ) { echo 'display: block;'; } ?>">
		<p>Custom integration settings go here.. <?php // todo ?></p>
	</div>
	<?php } ?>

</form>

<?php include 'parts/admin-footer.php'; ?>

</div>
<div id="mc4wp-sidebar">
	<?php do_action( 'mc4wp_admin_before_sidebar' ); ?>
	<?php include 'parts/admin-need-support.php'; ?>
	<?php do_action( 'mc4wp_admin_after_sidebar' ); ?>
</div>


</div>