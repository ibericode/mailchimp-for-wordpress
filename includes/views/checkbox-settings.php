<?php 
if( ! defined("MC4WP_LITE_VERSION") ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

?>
<div id="mc4wp-<?php echo $tab; ?>" class="wrap mc4wp-settings">

	<h2><img src="<?php echo MC4WP_LITE_PLUGIN_URL . 'assets/img/menu-icon.png'; ?>" /> MailChimp for WordPress: Checkbox Settings</h2>	
	
	<div id="mc4wp-content">

		<?php settings_errors(); ?>
		<p>To use the MailChimp for WP sign-up checkboxes, select at least one list and one form to add the checkbox to.</p>

		<h3 class="mc4wp-title">MailChimp settings for checkboxes</h3>
		<form action="options.php" method="post">
			<?php settings_fields( 'mc4wp_lite_checkbox_settings' ); ?>

			<?php if(empty($opts['lists'])) { ?>
			<div class="mc4wp-info">
				<p>If you want to use sign-up checkboxes, select at least one MailChimp list to subscribe people to.</p>
			</div>
			<?php } ?>

			<table class="form-table">
				<tr valign="top">
					<th scope="row">Lists</th>
					
					<?php // loop through lists
					if(empty($lists)) 
					{ 
						?><td colspan="2">No lists found, are you connected to MailChimp?</td><?php
					} 
					else 
					{ ?>
						<td class="nowrap">
							<?php foreach($lists as $list) { 
							?><label><input type="checkbox" name="mc4wp_lite_checkbox[lists][<?php echo $list->id; ?>]" value="<?php echo esc_attr($list->id); ?>" <?php if(array_key_exists($list->id, $opts['lists'])) echo 'checked="checked"'; ?>> <?php echo $list->name; ?></label><br /><?php
							} ?>
						</td>
						<td class="desc">Select the list(s) to which people who tick the checkbox should be subscribed.</td>
					<?php 
					} 
					?>
				</tr>
				<tr valign="top">
					<th scope="row">Double opt-in?</th>
					<td class="nowrap"><label><input type="radio" name="mc4wp_lite_checkbox[double_optin]" value="1" <?php checked($opts['double_optin'], 1); ?> /> Yes</label> &nbsp; <label><input type="radio" id="mc4wp_checkbox_double_optin_0" name="mc4wp_lite_checkbox[double_optin]" value="0" <?php checked($opts['double_optin'], 0); ?> /> No</label></td>
					<td class="desc">Select "yes" if you want subscribers to have to confirm their email address (recommended)</td>
				</tr>
			</table>

		<h3 class="mc4wp-title">Checkbox settings</h3>
		<table class="form-table">
		
		<tr valign="top">
			<th scope="row">Add the checkbox to these forms</th>
			<td colspan="2" class="nowrap">
				<?php foreach($this->get_checkbox_compatible_plugins() as $code => $name) {

					if($code[0] != '_') {
						?><label><input name="mc4wp_lite_checkbox[show_at_<?php echo $code; ?>]" value="1" type="checkbox" <?php checked($opts['show_at_'.$code], 1); ?>> <?php echo $name; ?></label><br /><?php
					} else {
						?><label class="pro-feature"><input type="checkbox" disabled> <?php echo $name; ?></label><br /><?php
					}
				} ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="mc4wp_checkbox_label">Checkbox label text</label></th>
			<td colspan="2">
				<input type="text"  class="widefat" id="mc4wp_checkbox_label" name="mc4wp_lite_checkbox[label]" value="<?php echo esc_attr($opts['label']); ?>" required />
				<p class="help">HTML tags like <code>&lt;strong&gt;</code> and <code>&lt;em&gt;</code> are allowed in the label text.</p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">Pre-check the checkbox?</th>
			<td class="nowrap"><label><input type="radio" name="mc4wp_lite_checkbox[precheck]" value="1" <?php checked($opts['precheck'], 1); ?> /> Yes</label> &nbsp; <label><input type="radio" name="mc4wp_lite_checkbox[precheck]" value="0" <?php checked($opts['precheck'], 0); ?> /> No</label></td>
			<td class="desc"></td>
		</tr>
		<tr valign="top">
			<th scope="row">Load some default CSS?</th>
			<td class="nowrap"><label><input type="radio" name="mc4wp_lite_checkbox[css]" value="1" <?php checked($opts['css'], 1); ?> /> Yes</label> &nbsp; <label><input type="radio" name="mc4wp_lite_checkbox[css]" value="0" <?php checked($opts['css'], 0); ?> /> No</label></td>
			<td class="desc">Select "yes" if the checkbox appears in a weird place.</td>
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