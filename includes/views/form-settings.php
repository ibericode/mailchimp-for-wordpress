<div id="mc4wp-<?php echo $tab; ?>" class="wrap mc4wp-settings">

	<h2>Form settings</h2>

	<div id="mc4wp-content">

		<?php settings_errors(); ?>

		<p>To use the MailChimp for WP sign-up form, configure the form below and then paste <input size="10" type="text" onfocus="this.select();" readonly="readonly" value="[mc4wp-form]" class="mc4wp-shortcode-example"> in a post, page or text widget.</p>

			<form action="options.php" method="post">
				<?php settings_fields( 'mc4wp_lite_form_settings' ); ?>
				
				<h3>Required form settings</h3>
				<table class="form-table">

					<tr valign="top">
						<th scope="row">MailChimp list(s)</th>
					<?php // loop through lists
					if(empty($lists)) { 
						?><td colspan="2">No lists found, are you connected to MailChimp?</td><?php
					} else { ?>
					<td>
						<ul id="mc4wp-lists">
						<?php foreach($lists as $list) { ?>
							<li><input type="checkbox" id="mc4wp_form_list_<?php echo $list->id; ?>_cb" name="mc4wp_lite_form[lists][<?php echo $list->id; ?>]" value="<?php echo $list->id; ?>" data-groupings="<?php echo esc_attr(json_encode($list->interest_groupings)); ?>" data-fields="<?php echo esc_attr(json_encode($list->merge_vars)); ?>" <?php if(array_key_exists($list->id, $opts['lists'])) echo 'checked="checked"'; ?>> <label for="mc4wp_form_list_<?php echo $list->id; ?>_cb"><?php echo $list->name; ?></label></li>
						<?php } ?>
						</ul>
				</td>
				<td class="desc" <?php if(empty($opts['lists'])) { ?>style="color:red;"<?php } ?>>Select at least one MailChimp list for this form</td>
				<?php
			} ?>

		</tr>
		<tr valign="top">
			<td colspan="3">
				<h4>Form mark-up</h4>
				<div class="mc4wp-wrapper">
					<div class="mc4wp-col mc4wp-col-2-3 mc4wp-first">
						<?php 
						if(function_exists('wp_editor')) {
							wp_editor( esc_textarea($opts['markup']), 'mc4wpformmarkup', array('tinymce' => false, 'media_buttons' => false, 'textarea_name' => 'mc4wp_lite_form[markup]'));
						} else {
							?><textarea class="widefat" cols="160" rows="20" id="mc4wpformmarkup" name="mc4wp_lite_form[markup]"><?php echo esc_textarea($opts['markup']); ?></textarea><?php
						} ?>
						<p><small>Use <input type="text" onfocus="this.select();" readonly="readonly" value="[mc4wp-form]" size="10" class="mc4wp-shortcode-example"> to render this form inside a widget, post or page. <u>Do not just copy the form mark-up as that will not work.</u> </small></p>
						<p class="submit">
							<input type="submit" class="button-primary" value="<?php _e('Save All Changes') ?>" id="mc4wp-submit-form-settings" />
						</p>
					</div>

					<div class="mc4wp-col mc4wp-col-1-3 mc4wp-last">
						<?php include('parts/admin-field-wizard.php'); ?>
					</div>
				</div>
			</td>
		</tr>
	</table>

		<h3>MailChimp Settings</h3>
		<table class="form-table">
			<tr valign="top">
				<th scope="row">Double opt-in?</th>
				<td class="nowrap"><input type="radio" id="mc4wp_form_double_optin_1" name="mc4wp_lite_form[double_optin]" value="1" <?php if($opts['double_optin'] == 1) echo 'checked="checked"'; ?> /> <label for="mc4wp_form_double_optin_1">Yes</label> &nbsp; <input type="radio" id="mc4wp_form_double_optin_0" name="mc4wp_lite_form[double_optin]" value="0" <?php if($opts['double_optin'] == 0) echo 'checked="checked"'; ?> /> <label for="mc4wp_form_double_optin_0">No</label></td>
				<td class="desc">Tick "yes" if you want subscribers to confirm their email address (recommended)</td>
			</tr>
			<tr class="pro-feature" valign="top">
				<th scope="row">Send Welcome Email?</th>
				<td class="nowrap">
					<input type="radio" readonly /> 
					<label>Yes</label> &nbsp; 
					<input type="radio" checked readonly /> 
					<label>No</label> &nbsp; 
				</td>
				<td class="desc">Tick "yes" if you want to send your lists Welcome Email if a subscribe succeeds. Only when double opt-in is disabled.</td>
			</tr>
			<tr class="pro-feature" valign="top">
				<th scope="row">Update existing subscribers?</th>
				<td class="nowrap">
					<input type="radio" readonly /> 
					<label>Yes</label> &nbsp; 
					<input type="radio" checked readonly /> 
					<label>No</label> &nbsp; 
				</td>
				<td class="desc">Tick "yes" if you want to update existing subscribers instead of showing the "already subscribed" message.</td>
			</tr>
			<tr class="pro-feature" valign="top">
				<th scope="row">Replace interest groups?</th>
				<td class="nowrap">
					<input type="radio" checked readonly /> 
					<label>Yes</label> &nbsp; 
					<input type="radio" readonly /> 
					<label>No</label> &nbsp; 
				</td>
				<td class="desc">Tick "yes" if you want to replace the interest groups with the groups provided instead of adding the provided groups to the member's interest groups. Only when updating a subscriber.</td>
			</tr>
		</table>

		<h3>Form Settings & Messages</h3>

		<table class="form-table mc4wp-form-messages">
			<tr valign="top" class="pro-feature">
				<th scope="row">Enable AJAX?</th>
				<td class="nowrap">
					<input type="radio" readonly /> <label>Yes</label> &nbsp; 
					<input type="radio" checked readonly /> <label>No</label>
				</td>
				<td class="desc">Tick "yes" if you want to use AJAX to submit forms, meaning the page doesn't need to reload so everything happens inline. <a href="http://dannyvankooten.com/wordpress-plugins/mailchimp-for-wordpress/demo-sign-up-forms/?utm_source=lite-plugin&utm_medium=link&utm_campaign=settings-demo-link">(demo)</a></td>
			</tr>
			<tr valign="top">
				<th scope="row">Load some default CSS?</th>
				<td class="nowrap"><input type="radio" id="mc4wp_form_css_1" name="mc4wp_lite_form[css]" value="1" <?php if($opts['css'] == 1) echo 'checked="checked"'; ?> /> <label for="mc4wp_form_css_1">Yes</label> &nbsp; <input type="radio" id="mc4wp_form_css_0" name="mc4wp_lite_form[css]" value="0" <?php if($opts['css'] == 0) echo 'checked="checked"'; ?> /> <label for="mc4wp_form_css_0">No</label></td>
				<td class="desc">Tick "yes" to load some basic form styles.</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="mc4wp_form_hide_after_success">Hide form after a successful sign-up?</label></th>
				<td class="nowrap"><input type="radio" id="mc4wp_form_hide_after_success_1" name="mc4wp_lite_form[hide_after_success]" value="1" <?php if($opts['hide_after_success'] == 1) echo 'checked="checked"'; ?> /> <label for="mc4wp_form_hide_after_success_1">Yes</label> &nbsp; <input type="radio" id="mc4wp_form_hide_after_success_0" name="mc4wp_lite_form[hide_after_success]" value="0" <?php if($opts['hide_after_success'] == 0) echo 'checked="checked"'; ?> /> <label for="mc4wp_form_hide_after_success_0">No</label></td>
				<td class="desc">Tick "yes" to hide the form fields after a successful sign-up.</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="mc4wp_form_redirect">Redirect to this URL after a successful sign-up</label></th>
				<td colspan="2">
					<input type="text" class="widefat" name="mc4wp_lite_form[redirect]" id="mc4wp_form_redirect" placeholder="Example: <?php echo esc_attr(site_url('/thank-you/')); ?>"value="<?php echo $opts['redirect']; ?>" />
					<small>Leave empty or enter <strong>0</strong> (zero) for no redirection. Use complete URL's, including http://</small>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="mc4wp_form_text_success">Success message</label></th>
				<td colspan="2" ><input type="text" class="widefat" id="mc4wp_form_text_success" name="mc4wp_lite_form[text_success]" value="<?php echo esc_attr($opts['text_success']); ?>" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="mc4wp_form_text_error">General error message</label></th>
				<td colspan="2" ><input type="text" class="widefat" id="mc4wp_form_text_error" name="mc4wp_lite_form[text_error]" value="<?php echo esc_attr($opts['text_error']); ?>" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="mc4wp_form_text_invalid_email">Invalid email address message</label></th>
				<td colspan="2" ><input type="text" class="widefat" id="mc4wp_form_text_invalid_email" name="mc4wp_lite_form[text_invalid_email]" value="<?php echo esc_attr($opts['text_invalid_email']); ?>" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="mc4wp_form_text_already_subscribed">Email address is already on list message</label></th>
				<td colspan="2" ><input type="text" class="widefat" id="mc4wp_form_text_already_subscribed" name="mc4wp_lite_form[text_already_subscribed]" value="<?php echo esc_attr($opts['text_already_subscribed']); ?>" /></td>
			</tr>
			<tr>
				<th></th>
				<td colspan="2"><p><small>HTML tags like &lt;a&gt; and &lt;strong&gt; etc. are allowed in the message fields.</small></p></td>
			</tr>
		</table>

	<?php submit_button("Save All Changes"); ?>
	</form>

	<?php include 'parts/admin-footer.php'; ?>
</div>
<div id="mc4wp-sidebar">
	<?php include 'parts/admin-upgrade-to-pro.php'; ?>

	<div class="mc4wp-box" id="mc4wp-info-tabs">
		<h3>Building your sign-up form</h3>
		<p>At a minimum, your form should include just an <strong>EMAIL</strong> field and a submit button. If your list requires more fields, add those too. 
		Field names should be uppercased and match your MailChimp list fields merge tags. The field wizard tool does this automatically.</p>

		<p><strong>Styling</strong><br />
		Alter the visual appearance of the form by applying CSS rules to <b>.mc4wp-form</b> and its child elements.</p>
		<p>You should add the CSS rules to your theme stylesheet using the <a href="<?php echo admin_url('theme-editor.php?file=style.css'); ?>">Theme Editor</a> or by editing <em><?php echo get_stylesheet_directory(); ?>/style.css</em> over FTP.</p>

			
		<p>The <a href="http://wordpress.org/plugins/mailchimp-for-wp/faq/" target="_blank">MailChimp for WP FAQ</a> lists the various CSS selectors you can use to target the different elements.</p>

		<p><em>PS: The premium version has a neat CSS builder. <a href="http://dannyvankooten.com/wp-content/uploads/2013/06/form-css-designer.png">Here's a screenshot</a>. Absolutely zero CSS knowledge required to create beautiful forms!</em></p>
			
			<h3>Form variables</h3>
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