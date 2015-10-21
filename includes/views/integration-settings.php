<?php defined( 'ABSPATH' ) or exit;

$integrations = MC4WP_Integration_Manager::instance();
?>
<div id="mc4wp-admin" class="wrap mc4wp-settings">

	<div class="main-content row">

		<!-- Main Content -->
		<div class="col col-4">

			<h1 class="page-title"><?php _e( 'Checkbox Integrations', 'mailchimp-for-wp' ); ?></h1>

			<table class="widefat striped">

				<tbody>

				<?php foreach( array_keys( $integrations->registered_integrations ) as $slug ) {

					$integration = $integrations->integration( $slug );
					$installed = $integration->is_installed();
					?>
					<tr style="<?php if( ! $installed ) { echo 'opacity: 0.5;'; } ?>">
						<td class="row-title">
							<?php

							if( $installed ) {
								printf( '<a href="%s">%s</a>', $integration->slug, $integration->name );
							} else {
								echo $integration->name ;
							} ?>
						</td>
						<td class="desc">
							<?php echo $integration->description; ?>
						</td>
					</tr>
				<?php } ?>

				</tbody>
			</table>

		</div>

		<!-- Sidebar -->
		<div class="sidebar col col-2">
			<?php do_action( 'mc4wp_admin_before_sidebar' ); ?>
			<?php include 'parts/admin-need-support.php'; ?>
			<?php do_action( 'mc4wp_admin_after_sidebar' ); ?>
		</div>

	</div>

	<?php if( isset( $_GET['old'] ) ) { ?>

	<div id="mc4wp-content">

		<?php settings_errors(); ?>
		<p><?php _e( 'To use sign-up checkboxes, select at least one list and one form to add the checkbox to.', 'mailchimp-for-wp' ); ?></p>

		<form action="<?php echo admin_url( 'options.php' ); ?>" method="post">
			<?php settings_fields( 'mc4wp_integrations_settings' ); ?>

			<h3 class="mc4wp-title"><?php _e( 'MailChimp settings for checkboxes', 'mailchimp-for-wp' ); ?></h3>

			<?php if( empty( $general_opts['lists'] ) ) { ?>
				<div class="mc4wp-info">
					<p><?php _e( 'If you want to use sign-up checkboxes, select at least one MailChimp list to subscribe people to.', 'mailchimp-for-wp' ); ?></p>
				</div>
			<?php } ?>

			<table class="form-table">
				<tr valign="top">
					<th scope="row"><?php _e( 'MailChimp Lists', 'mailchimp-for-wp' ); ?></th>
					
					<?php // loop through lists
					if( ! $lists || empty( $lists ) ) {
						?><td><?php printf( __( 'No lists found, <a href="%s">are you connected to MailChimp</a>?', 'mailchimp-for-wp' ), admin_url( 'admin.php?page=mailchimp-for-wp' ) ); ?></td><?php
					} else { ?>
						<td class="nowrap">
							<?php foreach( $lists as $list ) {
							?><label><input type="checkbox" name="mc4wp_integrations[general][lists][<?php echo esc_attr( $list->id ); ?>]" value="<?php echo esc_attr( $list->id ); ?>" <?php checked( array_key_exists( $list->id, $general_opts['lists'] ), true ); ?>> <?php echo esc_html( $list->name ); ?></label><br /><?php
} ?>
							<p class="help">
								<?php _e( 'Select the list(s) to which people who check the checkbox should be subscribed.' ,'mailchimp-for-wp' ); ?>
							</p>
						</td>
					<?php
					}
					?>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e( 'Double opt-in?', 'mailchimp-for-wp' ); ?></th>
					<td class="nowrap">
						<label>
							<input type="radio" name="mc4wp_integrations[general][double_optin]" value="1" <?php checked( $general_opts['double_optin'], 1 ); ?> />
							<?php _e( 'Yes', 'mailchimp-for-wp' ); ?>
						</label> &nbsp;
						<label>
							<input type="radio" id="mc4wp_checkbox_double_optin_0" name="mc4wp_integrations[general][double_optin]" value="0" <?php checked( $general_opts['double_optin'], 0 ); ?> />
							<?php _e( 'No', 'mailchimp-for-wp' ); ?>
						</label>
						<p class="help">
							<?php _e( 'Select "yes" if you want people to confirm their email address before being subscribed (recommended)', 'mailchimp-for-wp' ); ?>
						</p>
					</td>
				</tr>
			</table>

		<h3 class="mc4wp-title"><?php _e( 'Checkbox settings', 'mailchimp-for-wp' ); ?></h3>
		<table class="form-table">

		<tr valign="top">
			<th scope="row"><label for="mc4wp_checkbox_label"><?php _e( 'Checkbox label text', 'mailchimp-for-wp' ); ?></label></th>
			<td>
				<input type="text"  class="widefat" id="mc4wp_checkbox_label" name="mc4wp_integrations[general][label]" value="<?php echo esc_attr( $general_opts['label'] ); ?>" required />
				<p class="help"><?php printf( __( 'HTML tags like %s are allowed in the label text.', 'mailchimp-for-wp' ), '<code>' . esc_html( '<strong><em><a>' ) . '</code>' ); ?></p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e( 'Pre-check the checkbox?', 'mailchimp-for-wp' ); ?></th>
			<td class="nowrap"><label><input type="radio" name="mc4wp_integrations[general][precheck]" value="1" <?php checked( $general_opts['precheck'], 1 ); ?> /> <?php _e( 'Yes', 'mailchimp-for-wp' ); ?></label> &nbsp; <label><input type="radio" name="mc4wp_integrations[general][precheck]" value="0" <?php checked( $general_opts['precheck'], 0 ); ?> /> <?php _e( 'No', 'mailchimp-for-wp' ); ?></label></td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e( 'Load some default CSS?', 'mailchimp-for-wp' ); ?></th>
			<td class="nowrap">
				<label><input type="radio" name="mc4wp_integrations[general][css]" value="1" <?php checked( $general_opts['css'], 1 ); ?> /> <?php _e( 'Yes', 'mailchimp-for-wp' ); ?></label> &nbsp;
				<label><input type="radio" name="mc4wp_integrations[general][css]" value="0" <?php checked( $general_opts['css'], 0 ); ?> /> <?php _e( 'No', 'mailchimp-for-wp' ); ?></label>
				<p class="help"><?php _e( 'Select "yes" if the checkbox appears in a weird place.', 'mailchimp-for-wp' ); ?></p>
			</td>
		</tr>
		<tr valign="top" id="woocommerce-settings" <?php if( ! $general_opts['show_at_woocommerce_checkout'] ) { ?>style="display: none;"<?php } ?>>
			<th scope="row"><?php _e( 'WooCommerce checkbox position', 'mailchimp-for-wp' ); ?></th>
			<td class="nowrap">
				<select name="mc4wp_integrations[general][woocommerce_position]">
					<option value="billing" <?php selected( $general_opts['woocommerce_position'], 'billing' ); ?>><?php _e( 'After the billing details', 'mailchimp-for-wp' ); ?></option>
					<option value="order" <?php selected( $general_opts['woocommerce_position'], 'order' ); ?>><?php _e( 'After the additional information', 'mailchimp-for-wp' ); ?></option>
				</select>
				<p class="help">
					<?php _e( 'Choose the position for the checkbox in your WooCommerce checkout form.', 'mailchimp-for-wp' ); ?>
				</p>
			</td>
		</tr>
		
	</table>

	<?php submit_button(); ?>



	</form>

	<?php include 'parts/admin-footer.php'; ?>

	</div>




	<?php } // end of old ?>

</div>