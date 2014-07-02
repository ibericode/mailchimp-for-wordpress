<?php 
if( ! defined("MC4WP_LITE_VERSION") ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

?>
<div id="mc4wp" class="wrap mc4wp-settings">

	<h2><img src="<?php echo MC4WP_LITE_PLUGIN_URL . 'assets/img/menu-icon.png'; ?>" /> <?php _e( 'MailChimp for WordPress', 'mailchimp-for-wp' ); ?>: <?php _e( 'Checkbox Settings', 'mailchimp-for-wp' ); ?></h2>
	
	<div id="mc4wp-content">

		<?php settings_errors(); ?>
		<p><?php _e( 'To use sign-up checkboxes, select at least one list and one form to add the checkbox to.', 'mailchimp-for-wp' ); ?></p>

		<form action="options.php" method="post">
			<?php settings_fields( 'mc4wp_lite_checkbox_settings' ); ?>

			<h3 class="mc4wp-title"><?php _e( 'MailChimp settings for checkboxes', 'mailchimp-for-wp' ); ?></h3>

			<?php if( empty( $opts['lists'] ) ) { ?>
				<div class="mc4wp-info">
					<p><?php _e( 'If you want to use sign-up checkboxes, select at least one MailChimp list to subscribe people to.', 'mailchimp-for-wp' ); ?></p>
				</div>
			<?php } ?>

			<table class="form-table">
				<tr valign="top">
					<th scope="row"><?php _e( 'MailChimp Lists', 'mailchimp-for-wp' ); ?></th>
					
					<?php // loop through lists
					if( ! $lists || empty( $lists ) ) {
						?><td colspan="2"><?php printf( __( 'No lists found, %sare you connected to MailChimp?%s', 'mailchimp-for-wp' ), '<a href="'. admin_url( 'admin.php?page=mc4wp-lite' ) .'">', '</a>' ); ?></td><?php
					} else { ?>
						<td class="nowrap">
							<?php foreach($lists as $list) { 
							?><label><input type="checkbox" name="mc4wp_lite_checkbox[lists][<?php echo esc_attr( $list->id ); ?>]" value="<?php echo esc_attr($list->id); ?>" <?php checked( array_key_exists( $list->id, $opts['lists'] ), true ); ?>> <?php echo esc_html( $list->name ); ?></label><br /><?php
							} ?>
						</td>
						<td class="desc"><?php _e( 'Select the list(s) to which people who check the checkbox should be subscribed.' ,'mailchimp-for-wp' ); ?></td>
					<?php 
					} 
					?>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e( 'Double opt-in?', 'mailchimp-for-wp' ); ?></th>
					<td class="nowrap"><label><input type="radio" name="mc4wp_lite_checkbox[double_optin]" value="1" <?php checked($opts['double_optin'], 1); ?> /> <?php _e( 'Yes' ); ?></label> &nbsp; <label><input type="radio" id="mc4wp_checkbox_double_optin_0" name="mc4wp_lite_checkbox[double_optin]" value="0" <?php checked($opts['double_optin'], 0); ?> /> <?php _e( 'No' ); ?></label></td>
					<td class="desc"><?php _e( 'Select "yes" if you want people to confirm their email address before being subscribed (recommended)', 'mailchimp-for-wp' ); ?></td>
				</tr>
			</table>

		<h3 class="mc4wp-title"><?php _e( 'Checkbox settings', 'mailchimp-for-wp' ); ?></h3>
		<table class="form-table">
		
		<tr valign="top">
			<th scope="row"><?php _e( 'Add the checkbox to these forms', 'mailchimp-for-wp' ); ?></th>
			<td class="nowrap">
				<?php foreach($this->get_checkbox_compatible_plugins() as $code => $name) {

					if($code[0] !== '_') {
						?><label><input name="mc4wp_lite_checkbox[show_at_<?php echo $code; ?>]" value="1" type="checkbox" <?php checked( $opts['show_at_' . $code], 1 ); ?>> <?php echo esc_html( $name ); ?></label><br /><?php
					} else {
						?><label class="pro-feature"><input type="checkbox" disabled> <?php echo esc_html( $name ); ?></label><br /><?php
					}
				} ?>
			</td>
			<td class="desc">
				<?php _e( 'Selecting a form will automatically add the sign-up checkbox to it.', 'mailchimp-for-wp' ); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="mc4wp_checkbox_label"><?php _e( 'Checkbox label text', 'mailchimp-for-wp' ); ?></label></th>
			<td colspan="2">
				<input type="text"  class="widefat" id="mc4wp_checkbox_label" name="mc4wp_lite_checkbox[label]" value="<?php echo esc_attr( $opts['label'] ); ?>" required />
				<p class="help"><?php printf( __( 'HTML tags like %s are allowed in the label text.', 'mailchimp-for-wp' ), '<code>' . esc_html( '<strong><em><a>' ) . '</code>' ); ?></p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e( 'Pre-check the checkbox?', 'mailchimp-for-wp' ); ?></th>
			<td class="nowrap"><label><input type="radio" name="mc4wp_lite_checkbox[precheck]" value="1" <?php checked( $opts['precheck'], 1 ); ?> /> <?php _e( 'Yes' ); ?></label> &nbsp; <label><input type="radio" name="mc4wp_lite_checkbox[precheck]" value="0" <?php checked( $opts['precheck'], 0 ); ?> /> <?php _e( 'No' ); ?></label></td>
			<td class="desc"></td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e( 'Load some default CSS?', 'mailchimp-for-wp' ); ?></th>
			<td class="nowrap"><label><input type="radio" name="mc4wp_lite_checkbox[css]" value="1" <?php checked( $opts['css'], 1 ); ?> /> <?php _e( 'Yes' ); ?></label> &nbsp; <label><input type="radio" name="mc4wp_lite_checkbox[css]" value="0" <?php checked( $opts['css'], 0 ); ?> /> <?php _e( 'No' ); ?></label></td>
			<td class="desc"><?php _e( 'Select "yes" if the checkbox appears in a weird place.', 'mailchimp-for-wp' ); ?></td>
		</tr>
		
		
	</table>

	<?php submit_button(); ?>
</form>

<?php include 'parts/admin-footer.php'; ?>

</div>
<div id="mc4wp-sidebar">
	<?php include 'parts/admin-upgrade-to-pro.php'; ?>
	<?php include 'parts/admin-need-support.php'; ?>
</div>


</div>