<?php 
if( ! defined("MC4WP_LITE_VERSION") ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
} ?>
<div id="mc4wp" class="wrap mc4wp-settings">

	<h2><img src="<?php echo MC4WP_LITE_PLUGIN_URL . 'assets/img/menu-icon.png'; ?>" /> <?php _e( 'MailChimp for WordPress', 'mailchimp-for-wp' ); ?>: <?php _e( 'Form Settings', 'mailchimp-for-wp' ); ?></h2>

	<div id="mc4wp-content">

		<?php settings_errors(); ?>

		<p><?php printf( __( 'To use the MailChimp sign-up form, configure the form below and then either paste %s in the content of a post or page or use the  widget.', 'mailchimp-for-wp' ), '<input size="10" type="text" onfocus="this.select();" readonly="readonly" value="[mc4wp_form]" class="mc4wp-shortcode-example">' ); ?></p>

			<form action="options.php" method="post">
				<?php settings_fields( 'mc4wp_lite_form_settings' ); ?>
				
				<h3 class="mc4wp-title"><?php _e( 'Required form settings', 'mailchimp-for-wp' ); ?></h3>
				<table class="form-table">

					<tr valign="top">
						<th scope="row"><label for="mc4wp_load_stylesheet_select"><?php _e( 'Load form styles (CSS)?' ,'mailchimp-for-wp' ); ?></label></th>
						<td class="nowrap valigntop">
							<select name="mc4wp_lite_form[css]" id="mc4wp_load_stylesheet_select">
								<option value="0" <?php selected($opts['css'], 0); ?>><?php _e( 'No' ); ?></option>
								<option value="default" <?php selected($opts['css'], 'default'); ?><?php selected($opts['css'], 1); ?>><?php _e( 'Yes, load basic form styles', 'mailchimp-for-wp' ); ?></option>
								<option disabled>(PRO ONLY) <?php _e( 'Yes, load my custom form styles', 'mailchimp-for-wp' ); ?></option>
								<optgroup label="<?php _e( 'Yes, load default form theme', 'mailchimp-for-wp' ); ?>">
									<option value="light" <?php selected($opts['css'], 'light'); ?>><?php _e( 'Light Theme', 'mailchimp-for-wp' ); ?></option>
									<option value="red" <?php selected($opts['css'], 'red'); ?>><?php _e( 'Red Theme', 'mailchimp-for-wp' ); ?></option>
									<option value="green" <?php selected($opts['css'], 'green'); ?>><?php _e( 'Green Theme', 'mailchimp-for-wp' ); ?></option>
									<option value="blue" <?php selected($opts['css'], 'blue'); ?>><?php _e( 'Blue Theme', 'mailchimp-for-wp' ); ?></option>
									<option value="dark" <?php selected($opts['css'], 'dark'); ?>><?php _e( 'Dark Theme', 'mailchimp-for-wp' ); ?></option>
									<option disabled>(PRO ONLY) <?php _e( 'Custom Color Theme', 'mailchimp-for-wp' ); ?></option>
								</optgroup>
							</select>
						</td>
						<td class="desc">
							<?php _e( 'If you want to load some default CSS styles, select "basic formatting styles" or choose one of the color themes' , 'mailchimp-for-wp' ); ?>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'Lists this form subscribes to', 'mailchimp-for-wp' ); ?></th>
					<?php // loop through lists
					if( empty( $lists ) ) {
						?><td colspan="2"><?php printf( __( 'No lists found, %sare you connected to MailChimp?%s', 'mailchimp-for-wp' ), '<a href="'. admin_url( 'admin.php?page=mc4wp-lite' ) .'">', '</a>' ); ?></td><?php
					} else { ?>
					<td>

						<ul id="mc4wp-lists">
						<?php foreach($lists as $list) { ?>
							<li>
								<label>
									<input type="checkbox" name="mc4wp_lite_form[lists][<?php echo esc_attr( $list->id ); ?>]" value="<?php echo esc_attr( $list->id ); ?>" data-list-groupings="<?php echo esc_attr(json_encode($list->interest_groupings)); ?>" data-list-fields="<?php echo esc_attr(json_encode($list->merge_vars)); ?>" <?php if(array_key_exists($list->id, $opts['lists'])) echo 'checked="checked"'; ?>> <?php echo esc_html( $list->name ); ?>
								</label>
							</li>
						<?php } ?>
						</ul>

					</td>
					<td class="desc"><?php _e( 'Select the list(s) to which people who submit this form should be subscribed.' ,'mailchimp-for-wp' ); ?></td>
					<?php } ?>

					</tr>
					<tr valign="top">
						<td colspan="3">
							<h4><?php _e( 'Form mark-up', 'mailchimp-for-wp' ); ?></h4>

							<?php if ( ! empty( $missing_form_fields ) ) {

								?><p class="mc4wp-notice missing-form-fields"><?php

								echo __( 'Your form is missing the following (required) form fields:', 'mailchimp-for-wp') . ' <br /><br />';

								foreach( $missing_form_fields as $missing_field ) {
									echo '- ' . $missing_field . '<br />';
								}
								?></p><?php

							} ?>

							<div class="mc4wp-wrapper">
								<div class="mc4wp-col mc4wp-first">
									<?php 
									if( function_exists( 'wp_editor' ) ) {
										wp_editor( esc_textarea( $opts['markup'] ), 'mc4wpformmarkup', array( 'tinymce' => false, 'media_buttons' => true, 'textarea_name' => 'mc4wp_lite_form[markup]'));
									} else {
										?><textarea class="widefat" cols="160" rows="20" id="mc4wpformmarkup" name="mc4wp_lite_form[markup]"><?php echo esc_textarea( $opts['markup'] ); ?></textarea><?php
									} ?>
									<p class="mc4wp-form-usage"><?php printf( __( 'Use the shortcode %s to display this form inside a post, page or text widget.' ,'mailchimp-for-wp' ), '<input type="text" onfocus="this.select();" readonly="readonly" value="[mc4wp_form]" class="mc4wp-shortcode-example">' ); ?></p>
								</div>

								<div class="mc4wp-col mc4wp-last">
									<?php include('parts/admin-field-wizard.php'); ?>
								</div>
							</div>
						</td>
					</tr>

			</table>

	<?php submit_button(); ?>

		<h3 class="mc4wp-title"><?php _e( 'MailChimp Settings', 'mailchimp-for-wp' ); ?></h3>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php _e( 'Double opt-in?', 'mailchimp-for-wp' ); ?></th>
				<td class="nowrap"><input type="radio" id="mc4wp_form_double_optin_1" name="mc4wp_lite_form[double_optin]" value="1" <?php checked( $opts['double_optin'], 1 ); ?> /> <label for="mc4wp_form_double_optin_1"><?php _e( 'Yes' ); ?></label> &nbsp; <input type="radio" id="mc4wp_form_double_optin_0" name="mc4wp_lite_form[double_optin]" value="0" <?php checked( $opts['double_optin'], 0); ?> /> <label for="mc4wp_form_double_optin_0"><?php _e( 'No' ); ?></label></td>
				<td class="desc"><?php _e( 'Select "yes" if you want people to confirm their email address before being subscribed (recommended)', 'mailchimp-for-wp' ); ?></td>
			</tr>
			<tr class="pro-feature" valign="top">
				<th scope="row"><?php _e( 'Send Welcome Email?', 'mailchimp-for-wp' ); ?></th>
				<td class="nowrap">
					<input type="radio" readonly /> 
					<label><?php _e( "Yes" ); ?></label> &nbsp;
					<input type="radio" checked readonly /> 
					<label><?php _e( "No" ); ?></label> &nbsp;
				</td>
				<td class="desc"><?php _e( 'Select "yes" if you want to send your lists Welcome Email if a subscribe succeeds (only when double opt-in is disabled).' ,'mailchimp-for-wp' ); ?></td>
			</tr>
			<tr class="pro-feature" valign="top">
				<th scope="row"><?php _e( 'Update existing subscribers?', 'mailchimp-for-wp' ); ?></th>
				<td class="nowrap">
					<input type="radio" readonly /> 
					<label><?php _e("Yes"); ?></label> &nbsp; 
					<input type="radio" checked readonly /> 
					<label><?php _e( 'No' ); ?></label> &nbsp;
				</td>
				<td class="desc"><?php _e( 'Select "yes" if you want to update existing subscribers (instead of showing the "already subscribed" message).', 'mailchimp-for-wp' ); ?></td>
			</tr>
			<tr class="pro-feature" valign="top">
				<th scope="row"><?php _e( 'Replace interest groups?', 'mailchimp-for-wp' ); ?></th>
				<td class="nowrap">
					<input type="radio" checked readonly /> 
					<label><?php _e("Yes"); ?></label> &nbsp; 
					<input type="radio" readonly /> 
					<label><?php _e("No"); ?></label> &nbsp; 
				</td>
				<td class="desc"><?php _e( 'Select "yes" if you want to replace the interest groups with the groups provided instead of adding the provided groups to the member\'s interest groups (only when updating a subscriber).', 'mailchimp-for-wp'); ?></td>
			</tr>
		</table>

		<h3 class="mc4wp-title"><?php _e( 'Form Settings & Messages', 'mailchimp-for-wp' ); ?></h3>

		<table class="form-table mc4wp-form-messages">
			<tr valign="top" class="pro-feature">
				<th scope="row"><?php _e( 'Enable AJAX form submission?', 'mailchimp-for-wp' ); ?></th>
				<td class="nowrap">
					<input type="radio" readonly /> <label><?php _e("Yes"); ?></label> &nbsp; 
					<input type="radio" checked readonly /> <label><?php _e("No"); ?></label>
				</td>
				<td class="desc"><?php _e( 'Select "yes" if you want to use AJAX (JavaScript) to submit forms.', 'mailchimp-for-wp' ); ?> <a href="https://dannyvankooten.com/mailchimp-for-wordpress/demo/#utm_source=lite-plugin&utm_medium=link&utm_campaign=settings-demo-link">(demo)</a></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="mc4wp_form_hide_after_success"><?php _e( 'Hide form after a successful sign-up?', 'mailchimp-for-wp' ); ?></label></th>
				<td class="nowrap"><input type="radio" id="mc4wp_form_hide_after_success_1" name="mc4wp_lite_form[hide_after_success]" value="1" <?php if($opts['hide_after_success'] == 1) echo 'checked="checked"'; ?> /> <label for="mc4wp_form_hide_after_success_1"><?php _e( 'Yes'); ?></label> &nbsp; <input type="radio" id="mc4wp_form_hide_after_success_0" name="mc4wp_lite_form[hide_after_success]" value="0" <?php if($opts['hide_after_success'] == 0) echo 'checked="checked"'; ?> /> <label for="mc4wp_form_hide_after_success_0"><?php _e( 'No' ); ?></label></td>
				<td class="desc"><?php _e( 'Select "yes" to hide the form fields after a successful sign-up.', 'mailchimp-for-wp' ); ?></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="mc4wp_form_redirect"><?php _e( 'Redirect to URL after successful sign-ups', 'mailchimp-for-wp' ); ?></label></th>
				<td colspan="2">
					<input type="text" class="widefat" name="mc4wp_lite_form[redirect]" id="mc4wp_form_redirect" placeholder="Example: <?php echo esc_attr( site_url('/thank-you/' ) ); ?>"value="<?php echo $opts['redirect']; ?>" />
					<p class="help"><?php _e( 'Leave empty or enter 0 for no redirection. Use complete (absolute) URLs, including <code>http://</code>', 'mailchimp-for-wp' ); ?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="mc4wp_form_text_success"><?php _e( 'Success message', 'mailchimp-for-wp' ); ?></label></th>
				<td colspan="2" ><input type="text" class="widefat" id="mc4wp_form_text_success" name="mc4wp_lite_form[text_success]" value="<?php echo esc_attr( $opts['text_success'] ); ?>" required /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="mc4wp_form_text_invalid_email"><?php _e( 'Invalid email address message', 'mailchimp-for-wp' ); ?></label></th>
				<td colspan="2" ><input type="text" class="widefat" id="mc4wp_form_text_invalid_email" name="mc4wp_lite_form[text_invalid_email]" value="<?php echo esc_attr( $opts['text_invalid_email'] ); ?>" required /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="mc4wp_form_text_required_field_missing"><?php _e( 'Required field missing message', 'mailchimp-for-wp' ); ?></label></th>
				<td colspan="2" ><input type="text" class="widefat" id="mc4wp_form_text_required_field_missing" name="mc4wp_lite_form[text_required_field_missing]" value="<?php echo esc_attr( $opts['text_required_field_missing'] ); ?>" required /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="mc4wp_form_text_already_subscribed"><?php _e( 'Already subscribed message', 'mailchimp-for-wp' ); ?></label></th>
				<td colspan="2" ><input type="text" class="widefat" id="mc4wp_form_text_already_subscribed" name="mc4wp_lite_form[text_already_subscribed]" value="<?php echo esc_attr( $opts['text_already_subscribed'] ); ?>" required /></td>
			</tr>
			<?php if( true === $this->has_captcha_plugin ) { ?>
				<tr valign="top">
					<th scope="row"><label for="mc4wp_form_text_invalid_captcha"><?php _e( 'Invalid CAPTCHA message', 'mailchimp-for-wp' ); ?></label></th>
					<td colspan="2" ><input type="text" class="widefat" id="mc4wp_form_text_invalid_captcha" name="mc4wp_lite_form[text_invalid_captcha]" value="<?php echo esc_attr( $opts['text_invalid_captcha'] ); ?>" required /></td>
				</tr>
			<?php } ?>
			<tr valign="top">
				<th scope="row"><label for="mc4wp_form_text_error"><?php _e( 'General error message' ,'mailchimp-for-wp' ); ?></label></th>
				<td colspan="2" ><input type="text" class="widefat" id="mc4wp_form_text_error" name="mc4wp_lite_form[text_error]" value="<?php echo esc_attr( $opts['text_error'] ); ?>" required /></td>
			</tr>
			<tr>
				<th></th>
				<td colspan="2">
					<p class="help"><?php printf( __( 'HTML tags like %s are allowed in the message fields.', 'mailchimp-for-wp' ), '<code>' . esc_html( '<strong><em><a>' ) . '</code>' ); ?></p>
				</td>
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
		
		<h3 class="mc4wp-title"><?php _e( 'Form variables', 'mailchimp-for-wp' ); ?></h3>
		<p><?php _e( 'Use the following variables to add some dynamic content to your form.', 'mailchimp-for-wp' ); ?></p>

		<?php $language = defined( 'ICL_LANGUAGE_CODE' ) ? ICL_LANGUAGE_CODE : get_locale(); ?>

		<table class="mc4wp-help">
			<tr>
				<th>{subscriber_count}</th>
				<td><?php _e( 'Replaced with the number of subscribers on the selected list(s)', 'mailchimp-for-wp' ); ?></td>
			</tr>
			<tr>
				<th>{language}</th>
				<td><?php printf( __( 'Replaced with the current site language, eg: %s', 'mailchimp-for-wp' ), '<em>' . $language . '</em>' ); ?></td>
			</tr>
			<tr>
				<th>{ip}</th>
				<td><?php _e( 'Replaced with the visitor\'s IP address', 'mailchimp-for-wp' ); ?></td>
			</tr>
			<tr>
				<th>{date}</th>
				<td><?php printf( __( 'Replaced with the current date (yyyy/mm/dd eg: %s)', 'mailchimp-for-wp' ), '<em>' . date("Y/m/d") . '</em>' ); ?></td>
			</tr>
			<tr>
				<th>{time}</th>
				<td><?php printf( __( 'Replaced with the current time (hh:mm:ss eg: %s)', 'mailchimp-for-wp' ), '<em>' . date("H:i:s") . '</em>' ); ?></td>
			</tr>
			<tr>
				<th>{user_email}</th>
				<td><?php _e( 'Replaced with the logged in user\'s email (or nothing, if there is no logged in user)', 'mailchimp-for-wp' ); ?></td>
			</tr>
			<tr>
				<th>{user_firstname}</th>
				<td><?php _e( 'First name of the current user', 'mailchimp-for-wp' ); ?></td>
			</tr>
			<tr>
				<th>{user_lastname}</th>
				<td><?php _e( 'Last name of the current user', 'mailchimp-for-wp' ); ?></td>
			</tr>
			<tr>
				<th>{user_id}</th>
				<td><?php _e( 'Current user ID', 'mailchimp-for-wp' ); ?></td>
			</tr>
			<tr>
				<th>{current_url}</th>
				<td><?php _e( 'Current URL', 'mailchimp-for-wp' ); ?></td>
			</tr>
		</table>
	</div>

		<?php include 'parts/admin-need-support.php'; ?>
	</div>
</div>