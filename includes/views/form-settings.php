<?php 
if( ! defined("MC4WP_LITE_VERSION") ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
} ?>
<div id="mc4wp-<?php echo $tab; ?>" class="wrap mc4wp-settings">

	<h2><img src="<?php echo MC4WP_LITE_PLUGIN_URL . 'assets/img/menu-icon.png'; ?>" /> MailChimp for WordPress: Form settings</h2>

	<div id="mc4wp-content">

		<?php settings_errors(); ?>

		<p>To use the MailChimp for WP sign-up form, configure the form below and then paste <input size="10" type="text" onfocus="this.select();" readonly="readonly" value="[mc4wp_form]" class="mc4wp-shortcode-example"> in a post, page or text widget.</p>

			<form action="options.php" method="post">
				<?php settings_fields( 'mc4wp_lite_form_settings' ); ?>
				
				<h3 class="mc4wp-title">Required form settings</h3>
				<table class="form-table">

					<tr valign="top">
						<th scope="row"><label for="mc4wp_load_stylesheet_select">Load styles or theme?</label></th>
						<td class="nowrap valigntop">
							<select name="mc4wp_lite_form[css]" id="mc4wp_load_stylesheet_select">
								<option value="0" <?php selected($opts['css'], 0); ?>>No</option>
								<option value="default" <?php selected($opts['css'], 'default'); ?><?php selected($opts['css'], 1); ?>>Yes, load basic formatting styles</option>
								<option disabled>(PRO ONLY) Yes, load my custom form styles</option>
								<optgroup label="Load a default form theme">
									<option value="light" <?php selected($opts['css'], 'light'); ?>>Light Theme</option>
									<option value="red" <?php selected($opts['css'], 'red'); ?>>Red Theme</option>
									<option value="green" <?php selected($opts['css'], 'green'); ?>>Green Theme</option>
									<option value="blue" <?php selected($opts['css'], 'blue'); ?>>Blue Theme</option>
									<option value="dark" <?php selected($opts['css'], 'dark'); ?>>Dark Theme</option>
									<option disabled>(PRO ONLY) Custom Color Theme</option>
								</optgroup>
							</select>
						</td>
						<td class="desc">
							If you want to load some default styles, select "basic formatting styles" or one of the default themes.
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">MailChimp list(s)</th>
					<?php // loop through lists
					if(empty($lists)) { 
						?><td colspan="2">No lists found, are you connected to MailChimp?</td><?php
					} else { ?>
					<td>
						<ul id="mc4wp-lists">
						<?php foreach($lists as $list) { ?>
							<li>
								<label>
									<input type="checkbox" name="mc4wp_lite_form[lists][<?php echo esc_attr($list->id); ?>]" value="<?php echo esc_attr($list->id); ?>" data-list-groupings="<?php echo esc_attr(json_encode($list->interest_groupings)); ?>" data-list-fields="<?php echo esc_attr(json_encode($list->merge_vars)); ?>" <?php if(array_key_exists($list->id, $opts['lists'])) echo 'checked="checked"'; ?>> <?php echo $list->name; ?>
								</label>
							</li>
						<?php } ?>
						</ul>
					</td>
					<td class="desc">Select the list(s) to which people who submit this form should be subscribed.</td>
					<?php } ?>

					</tr>
					<tr valign="top">
						<td colspan="3">
							<h4>Form mark-up</h4>
							<div class="mc4wp-wrapper">
								<div class="mc4wp-col mc4wp-first">
									<?php 
									if(function_exists('wp_editor')) {
										wp_editor( esc_textarea($opts['markup']), 'mc4wpformmarkup', array('tinymce' => false, 'media_buttons' => false, 'textarea_name' => 'mc4wp_lite_form[markup]'));
									} else {
										?><textarea class="widefat" cols="160" rows="20" id="mc4wpformmarkup" name="mc4wp_lite_form[markup]"><?php echo esc_textarea($opts['markup']); ?></textarea><?php
									} ?>
									<p class="help">Use the shortcode <input type="text" onfocus="this.select();" readonly="readonly" value="[mc4wp_form]" size="12" class="mc4wp-shortcode-example"> inside a post, page or text widget to display your sign-up form. <strong>Do not copy and paste the above form mark-up, that will not work.</strong></p>		

								</div>

								<div class="mc4wp-col mc4wp-last">
									<?php include('parts/admin-field-wizard.php'); ?>
								</div>
							</div>
						</td>
					</tr>

			</table>

	<?php submit_button(); ?>

		<h3 class="mc4wp-title">MailChimp Settings</h3>
		<table class="form-table">
			<tr valign="top">
				<th scope="row">Double opt-in?</th>
				<td class="nowrap"><input type="radio" id="mc4wp_form_double_optin_1" name="mc4wp_lite_form[double_optin]" value="1" <?php if($opts['double_optin'] == 1) echo 'checked="checked"'; ?> /> <label for="mc4wp_form_double_optin_1">Yes</label> &nbsp; <input type="radio" id="mc4wp_form_double_optin_0" name="mc4wp_lite_form[double_optin]" value="0" <?php if($opts['double_optin'] == 0) echo 'checked="checked"'; ?> /> <label for="mc4wp_form_double_optin_0">No</label></td>
				<td class="desc">Select "yes" if you want subscribers to confirm their email address (recommended)</td>
			</tr>
			<tr class="pro-feature" valign="top">
				<th scope="row">Send Welcome Email?</th>
				<td class="nowrap">
					<input type="radio" readonly /> 
					<label><?php _e("Yes"); ?></label> &nbsp; 
					<input type="radio" checked readonly /> 
					<label><?php _e("No"); ?></label> &nbsp; 
				</td>
				<td class="desc">Select "yes" if you want to send your lists Welcome Email if a subscribe succeeds. Only when double opt-in is disabled.</td>
			</tr>
			<tr class="pro-feature" valign="top">
				<th scope="row">Update existing subscribers?</th>
				<td class="nowrap">
					<input type="radio" readonly /> 
					<label><?php _e("Yes"); ?></label> &nbsp; 
					<input type="radio" checked readonly /> 
					<label><?php _e("No"); ?></label> &nbsp; 
				</td>
				<td class="desc">Select "yes" if you want to update existing subscribers instead of showing the "already subscribed" message.</td>
			</tr>
			<tr class="pro-feature" valign="top">
				<th scope="row">Replace interest groups?</th>
				<td class="nowrap">
					<input type="radio" checked readonly /> 
					<label><?php _e("Yes"); ?></label> &nbsp; 
					<input type="radio" readonly /> 
					<label><?php _e("No"); ?></label> &nbsp; 
				</td>
				<td class="desc">Select "yes" if you want to replace the interest groups with the groups provided instead of adding the provided groups to the member's interest groups. Only when updating a subscriber.</td>
			</tr>
		</table>

		<h3 class="mc4wp-title">Form Settings & Messages</h3>

		<table class="form-table mc4wp-form-messages">
			<tr valign="top" class="pro-feature">
				<th scope="row">Enable AJAX?</th>
				<td class="nowrap">
					<input type="radio" readonly /> <label><?php _e("Yes"); ?></label> &nbsp; 
					<input type="radio" checked readonly /> <label><?php _e("No"); ?></label>
				</td>
				<td class="desc">Select "yes" if you want to use AJAX to submit forms, meaning the page doesn't need to reload so everything happens inline. <a href="http://dannyvankooten.com/mailchimp-for-wordpress/demo/?utm_source=lite-plugin&utm_medium=link&utm_campaign=settings-demo-link">(demo)</a></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="mc4wp_form_hide_after_success">Hide form after a successful sign-up?</label></th>
				<td class="nowrap"><input type="radio" id="mc4wp_form_hide_after_success_1" name="mc4wp_lite_form[hide_after_success]" value="1" <?php if($opts['hide_after_success'] == 1) echo 'checked="checked"'; ?> /> <label for="mc4wp_form_hide_after_success_1">Yes</label> &nbsp; <input type="radio" id="mc4wp_form_hide_after_success_0" name="mc4wp_lite_form[hide_after_success]" value="0" <?php if($opts['hide_after_success'] == 0) echo 'checked="checked"'; ?> /> <label for="mc4wp_form_hide_after_success_0">No</label></td>
				<td class="desc">Select "yes" to hide the form fields after a successful sign-up.</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="mc4wp_form_redirect">Redirect to this URL after a successful sign-up</label></th>
				<td colspan="2">
					<input type="text" class="widefat" name="mc4wp_lite_form[redirect]" id="mc4wp_form_redirect" placeholder="Example: <?php echo esc_attr(site_url('/thank-you/')); ?>"value="<?php echo $opts['redirect']; ?>" />
					<p class="help">Leave empty or enter <strong>0</strong> (zero) for no redirection. Use complete (absolute) URL's, including <code>http://</code></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="mc4wp_form_text_success">Success message</label></th>
				<td colspan="2" ><input type="text" class="widefat" id="mc4wp_form_text_success" name="mc4wp_lite_form[text_success]" value="<?php echo esc_attr($opts['text_success']); ?>" required /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="mc4wp_form_text_error">General error message</label></th>
				<td colspan="2" ><input type="text" class="widefat" id="mc4wp_form_text_error" name="mc4wp_lite_form[text_error]" value="<?php echo esc_attr($opts['text_error']); ?>" required /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="mc4wp_form_text_invalid_email">Invalid email address message</label></th>
				<td colspan="2" ><input type="text" class="widefat" id="mc4wp_form_text_invalid_email" name="mc4wp_lite_form[text_invalid_email]" value="<?php echo esc_attr($opts['text_invalid_email']); ?>" required /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="mc4wp_form_text_already_subscribed">Email address is already on list message</label></th>
				<td colspan="2" ><input type="text" class="widefat" id="mc4wp_form_text_already_subscribed" name="mc4wp_lite_form[text_already_subscribed]" value="<?php echo esc_attr($opts['text_already_subscribed']); ?>" required /></td>
			</tr>
			<tr>
				<th></th>
				<td colspan="2"><p class="help">HTML tags like <code>&lt;strong&gt;</code> and <code>&lt;em&gt;</code> are allowed in the message fields.</p></td>
			</tr>
		</table>

	<?php submit_button(); ?>
	</form>

	<?php include 'parts/admin-footer.php'; ?>
</div>
<div id="mc4wp-sidebar">
	<?php include 'parts/admin-upgrade-to-pro.php'; ?>

	<div class="mc4wp-box" id="mc4wp-info-tabs">
		<h3 class="mc4wp-title">Building your sign-up form</h3>
		<p>At a minimum, your form should include just an <strong>EMAIL</strong> field and a submit button.</p>
		<p>Add more fields to your form if your list requires more fields. Field names should match your MailChimp list field tags. Use the "Add a new field" tool to have the correct HTML generated for you.</p>

		<h3 class="mc4wp-title">Form Styling</h3>
		<p>Alter the visual appearance of the form by applying CSS rules to <b>.mc4wp-form</b> and its child elements.</p>
		<p>You should add the CSS rules to your theme stylesheet using the <a href="<?php echo admin_url('theme-editor.php?file=style.css'); ?>">Theme Editor</a> or by editing <em><?php echo get_stylesheet_directory(); ?>/style.css</em> over FTP.</p>

		<p>The <a href="http://wordpress.org/plugins/mailchimp-for-wp/faq/" target="_blank">FAQ</a> lists the various CSS selectors you can use to target the different elements.</p>
		
		<h3 class="mc4wp-title">Form variables</h3>
		<p>Use the following variables to add some dynamic content to your form.</p>

		<table class="mc4wp-help">
			<tr>
				<th>{subscriber_count}</th>
				<td>Replaced with the number of subscribers on the selected list(s).</td>
			</tr>
			<tr>
				<th>{ip}</th>
				<td>Replaced with the visitor's IP address.</td>
			</tr>
			<tr>
				<th>{date}</th>
				<td>Replaced with the current date (yyyy/mm/dd eg: <?php echo date("Y/m/d"); ?>)</td>
			</tr>
			<tr>
				<th>{time}</th>
				<td>Replaced with the current time (hh:mm:ss eg: <?php echo date("H:i:s"); ?>)</td>
			</tr>
			<tr>
				<th>{user_email}</th>
				<td>Replaced with the logged in user's email (or nothing, if there is no logged in user).</td>
			</tr>
			<tr>
				<th>{user_name}</th>
				<td>Display name of the current user</td>
			</tr>
			<tr>
				<th>{user_firstname}</th>
				<td>First name of the current user</td>
			</tr>
			<tr>
				<th>{user_lastname}</th>
				<td>Last name of the current user</td>
			</tr>
			<tr>
				<th>{user_id}</th>
				<td>Current user ID</td>
			</tr>
			<tr>
				<th>{current_url}</th>
				<td>Current URL</td>
			</tr>
		</table>
	</div>

		<?php include 'parts/admin-need-support.php'; ?>
	</div>
</div>