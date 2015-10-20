<table class="form-table">
	<tr valign="top">
		<th scope="row"><label for="mc4wp_load_stylesheet_select"><?php _e( 'Load form styles (CSS)?' ,'mailchimp-for-wp' ); ?></label></th>
		<td class="nowrap valigntop">
			<select name="mc4wp_form[settings][css]" id="mc4wp_load_stylesheet_select">
				<option value="0" <?php selected( $opts['css'], 0 ); ?>><?php _e( 'No', 'mailchimp-for-wp' ); ?></option>
				<option value="default" <?php selected( $opts['css'], 'default' ); ?><?php selected( $opts['css'], 1 ); ?>><?php _e( 'Yes, load basic form styles', 'mailchimp-for-wp' ); ?></option>
				<option disabled><?php _e( '(PRO ONLY)', 'mailchimp-for-wp' ); ?> <?php _e( 'Yes, load my custom form styles', 'mailchimp-for-wp' ); ?></option>
				<optgroup label="<?php _e( 'Yes, load default form theme', 'mailchimp-for-wp' ); ?>">
					<option value="light" <?php selected( $opts['css'], 'light' ); ?>><?php _e( 'Light Theme', 'mailchimp-for-wp' ); ?></option>
					<option value="red" <?php selected( $opts['css'], 'red' ); ?>><?php _e( 'Red Theme', 'mailchimp-for-wp' ); ?></option>
					<option value="green" <?php selected( $opts['css'], 'green' ); ?>><?php _e( 'Green Theme', 'mailchimp-for-wp' ); ?></option>
					<option value="blue" <?php selected( $opts['css'], 'blue' ); ?>><?php _e( 'Blue Theme', 'mailchimp-for-wp' ); ?></option>
					<option value="dark" <?php selected( $opts['css'], 'dark' ); ?>><?php _e( 'Dark Theme', 'mailchimp-for-wp' ); ?></option>
					<option disabled><?php _e( '(PRO ONLY)', 'mailchimp-for-wp' ); ?> <?php _e( 'Custom Color Theme', 'mailchimp-for-wp' ); ?></option>
				</optgroup>
			</select>
		</td>
		<td class="desc">
			<?php _e( 'If you want to load some default CSS styles, select "basic formatting styles" or choose one of the color themes' , 'mailchimp-for-wp' ); ?>
		</td>
	</tr>
</table>

<?php submit_button(); ?>