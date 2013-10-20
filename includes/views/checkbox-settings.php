<div id="mc4wp-<?php echo $tab; ?>" class="wrap mc4wp-settings">

	<h2>Checkbox Settings</h2>

	<div id="mc4wp-content">

		<?php settings_errors(); ?>
		<p>To use the MailChimp for WP sign-up checkboxes, select at least one list and one form to add the checkbox to.</p>

		<h3>List settings</h3>
		<form action="options.php" method="post">
			<?php settings_fields( 'mc4wp_lite_checkbox_settings' ); ?>

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
						<td>
							<?php foreach($lists as $list) { 
							?><input type="checkbox" id="mc4wp_checkbox_list_<?php echo $list->id; ?>_cb" name="mc4wp_lite_checkbox[lists][<?php echo $list->id; ?>]" value="<?php echo $list->id; ?>" <?php if(array_key_exists($list->id, $opts['lists'])) echo 'checked="checked"'; ?>> <label for="mc4wp_checkbox_list_<?php echo $list->id; ?>_cb"><?php echo $list->name; ?></label><br /><?php
							} ?>
						</td>
						<td class="desc" <?php if(empty($opts['lists'])) { ?>style="color:red;"<?php } ?>>Select at least one MailChimp list to which people who tick a checkbox should be subscribed.</td>
					<?php 
					} 
					?>
				</tr>
			</table>

		<h3>Checkbox settings</h3>
		<table class="form-table">
		<tr valign="top">
			<th scope="row">Double opt-in?</th>
			<td class="nowrap"><input type="radio" id="mc4wp_checkbox_double_optin_1" name="mc4wp_lite_checkbox[double_optin]" value="1" <?php if($opts['double_optin'] == 1) echo 'checked="checked"'; ?> /> <label for="mc4wp_checkbox_double_optin_1">Yes</label> &nbsp; <input type="radio" id="mc4wp_checkbox_double_optin_0" name="mc4wp_lite_checkbox[double_optin]" value="0" <?php if($opts['double_optin'] == 0) echo 'checked="checked"'; ?> /> <label for="mc4wp_checkbox_double_optin_0">No</label></td>
			<td class="desc">Tick "yes" if you want subscribers to have to confirm their email address (recommended)</td>
		</tr>
		<tr valign="top">
			<th scope="row">Add the checkbox to these forms</th>
			<td colspan="2">
				<?php foreach($this->get_checkbox_compatible_plugins() as $code => $name) { ?>
					<label><input name="mc4wp_lite_checkbox[show_at_<?php echo $code; ?>]" value="1" type="checkbox" <?php checked($opts['show_at_'.$code], 1); ?>> <?php echo $name; ?></label> &nbsp; 
				<?php } ?>
				<label><input name="mc4wp_lite_checkbox[show_at_other_forms]" value="1" type="checkbox" <?php if($opts['show_at_other_forms']) echo 'checked '; ?>> Other forms (manual)</label> &nbsp; 
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="mc4wp_checkbox_label">Checkbox label text</label></th>
			<td colspan="2"><input type="text"  class="widefat" id="mc4wp_checkbox_label" name="mc4wp_lite_checkbox[label]" value="<?php echo esc_attr($opts['label']); ?>" /></td>
		</tr>
		<tr valign="top">
			<th scope="row">Pre-check the checkbox?</th>
			<td class="nowrap"><input type="radio" id="mc4wp_checkbox_precheck_1" name="mc4wp_lite_checkbox[precheck]" value="1" <?php if($opts['precheck'] == 1) echo 'checked="checked"'; ?> /> <label for="mc4wp_checkbox_precheck_1">Yes</label> &nbsp; <input type="radio" id="mc4wp_checkbox_precheck_0" name="mc4wp_lite_checkbox[precheck]" value="0" <?php if($opts['precheck'] == 0) echo 'checked="checked"'; ?> /> <label for="mc4wp_checkbox_precheck_0">No</label></td>
			<td class="desc"></td>
		</tr>
		<tr valign="top">
			<th scope="row">Load some default CSS?</th>
			<td class="nowrap"><input type="radio" id="mc4wp_checbox_css_1" name="mc4wp_lite_checkbox[css]" value="1" <?php if($opts['css'] == 1) echo 'checked="checked"'; ?> /> <label for="mc4wp_checbox_css_1">Yes</label> &nbsp; <input type="radio" id="mc4wp_checbox_css_0" name="mc4wp_lite_checkbox[css]" value="0" <?php if($opts['css'] == 0) echo 'checked="checked"'; ?> /> <label for="mc4wp_checbox_css_0">No</label></td>
			<td class="desc">Tick "yes" if the checkbox appears in a weird place.</td>
		</tr>
		
		
	</table>

	<?php submit_button(); ?>
</form>

<?php include 'parts/admin-footer.php'; ?>

</div>
<div id="mc4wp-sidebar">
	<?php include 'parts/admin-upgrade-to-pro.php'; ?>

	<div class="mc4wp-box">
		<h3>Styling the Sign-Up Checkbox</h3>
		<p>Custom or additional styling can be done by applying CSS rules to <b>#mc4wp-checkbox</b> or its child elements.</p>
		<p>You should add the CSS rules to your theme stylesheet using the <a href="<?php echo admin_url('theme-editor.php?file=style.css'); ?>">Theme Editor</a> or by editing <em><?php echo get_stylesheet_directory(); ?>/style.css</em> over FTP.</p>
	</div>

	<?php include 'parts/admin-need-support.php'; ?>
</div>


</div>