<?php defined( 'ABSPATH' ) or exit;
/** @var MC4WP_Integration $integration */
/** @var array $opts */
?>
<div id="mc4wp-admin" class="wrap mc4wp-settings">

	<div class="row">

		<!-- Main Content -->
		<div class="main-content col col-4 col-sm-6">

			<p>
				<a href="<?php echo remove_query_arg('integration'); ?>">&lsaquo; <?php _e( 'Back to integrations overview', 'mailchimp-for-wp' ); ?></a>
			</p>

			<h1 class="page-title">
				<?php printf( __( '%s integration', 'mailchimp-for-wp' ), $integration->name ); ?>
			</h1>

			<!-- Settings form -->
			<form method="post" action="<?php echo admin_url( 'options.php' ); ?>">
				<?php settings_fields( 'mc4wp_integrations_settings' ); ?>

				<table class="form-table">
					<tr valign="top">
						<th scope="row"><?php _e( 'Enabled?', 'mailchimp-for-wp' ); ?></th>
						<td class="nowrap integration-toggles-wrap">
							<label><input type="radio" name="mc4wp_integrations[<?php echo $integration->slug; ?>][enabled]" value="1" <?php checked( $opts['enabled'], 1 ); ?> /> <?php _e( 'Yes', 'mailchimp-for-wp' ); ?></label> &nbsp;
							<label><input type="radio" name="mc4wp_integrations[<?php echo $integration->slug; ?>][enabled]" value="0" <?php checked( $opts['enabled'], 0 ); ?> /> <?php _e( 'No', 'mailchimp-for-wp' ); ?></label>
						</td>
					</tr>

					<tbody class="integration-toggled-settings" <?php if( ! $opts['enabled'] ) echo 'style="opacity: 0.5;"';?>>
						<tr valign="top">
							<th scope="row"><?php _e( 'MailChimp Lists', 'mailchimp-for-wp' ); ?></th>
							<?php if( ! empty( $lists ) ) {
								echo '<td>';
								foreach( $lists as $list ) {
									echo '<label>';
									echo sprintf( '<input type="checkbox" name="mc4wp_integrations[%s][lists][]" value="%s" %s> ', $integration->slug, $list->id, checked( in_array( $list->id, $opts['lists'] ), true, false ) );
									echo $list->name;
									echo '</label><br />';
								}

								echo '<p class="help">';
								_e( 'Select the list(s) to which people who check the checkbox should be subscribed.' ,'mailchimp-for-wp' );
								echo '</p>';
								echo '</td>';
							} else {
								echo '<td>' . sprintf( __( 'No lists found, <a href="%s">are you connected to MailChimp</a>?', 'mailchimp-for-wp' ), admin_url( 'admin.php?page=mailchimp-for-wp' ) ) . '</td>';
							} ?>
						</tr>
						<tr valign="top">
							<th scope="row"><label for="mc4wp_checkbox_label"><?php _e( 'Checkbox label text', 'mailchimp-for-wp' ); ?></label></th>
							<td>
								<input type="text"  class="widefat" id="mc4wp_checkbox_label" name="mc4wp_integrations[<?php echo $integration->slug; ?>][label]" value="<?php echo esc_attr( $opts['label'] ); ?>" required />
								<p class="help"><?php printf( __( 'HTML tags like %s are allowed in the label text.', 'mailchimp-for-wp' ), '<code>' . esc_html( '<strong><em><a>' ) . '</code>' ); ?></p>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><?php _e( 'Double opt-in?', 'mailchimp-for-wp' ); ?></th>
							<td class="nowrap">
								<label>
									<input type="radio" name="mc4wp_integrations[<?php echo $integration->slug; ?>][double_optin]" value="1" <?php checked( $opts['double_optin'], 1 ); ?> />
									<?php _e( 'Yes', 'mailchimp-for-wp' ); ?>
								</label> &nbsp;
								<label>
									<input type="radio" id="mc4wp_checkbox_double_optin_0" name="mc4wp_integrations[<?php echo $integration->slug; ?>][double_optin]" value="0" <?php checked( $opts['double_optin'], 0 ); ?> />
									<?php _e( 'No', 'mailchimp-for-wp' ); ?>
								</label>
								<p class="help">
									<?php _e( 'Select "yes" if you want people to confirm their email address before being subscribed (recommended)', 'mailchimp-for-wp' ); ?>
								</p>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><?php _e( 'Pre-check the checkbox?', 'mailchimp-for-wp' ); ?></th>
							<td class="nowrap">
								<label><input type="radio" name="mc4wp_integrations[<?php echo $integration->slug; ?>][precheck]" value="1" <?php checked( $opts['precheck'], 1 ); ?> /> <?php _e( 'Yes', 'mailchimp-for-wp' ); ?></label> &nbsp;
								<label><input type="radio" name="mc4wp_integrations[<?php echo $integration->slug; ?>][precheck]" value="0" <?php checked( $opts['precheck'], 0 ); ?> /> <?php _e( 'No', 'mailchimp-for-wp' ); ?></label>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><?php _e( 'Load some default CSS?', 'mailchimp-for-wp' ); ?></th>
							<td class="nowrap">
								<label><input type="radio" name="mc4wp_integrations[<?php echo $integration->slug; ?>][css]" value="1" <?php checked( $opts['css'], 1 ); ?> /> <?php _e( 'Yes', 'mailchimp-for-wp' ); ?></label> &nbsp;
								<label><input type="radio" name="mc4wp_integrations[<?php echo $integration->slug; ?>][css]" value="0" <?php checked( $opts['css'], 0 ); ?> /> <?php _e( 'No', 'mailchimp-for-wp' ); ?></label>
								<p class="help"><?php _e( 'Select "yes" if the checkbox appears in a weird place.', 'mailchimp-for-wp' ); ?></p>
							</td>
						</tr>
					</tbody>
				</table>

				<?php submit_button(); ?>

			</form>


		</div>

		<!-- Sidebar -->
		<div class="sidebar col col-2 col-sm-6">
			<?php include dirname( __FILE__ ) . '/parts/admin-sidebar.php'; ?>
		</div>

	</div>

	<?php if( isset( $_GET['old'] ) ) { ?>

	<div id="mc4wp-content">

		<?php settings_errors(); ?>
		<p><?php _e( 'To use sign-up checkboxes, select at least one list and one form to add the checkbox to.', 'mailchimp-for-wp' ); ?></p>

		<form action="<?php echo admin_url( 'options.php' ); ?>" method="post">
			<?php settings_fields( 'mc4wp_integrations_settings' ); ?>


		<table class="form-table">



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