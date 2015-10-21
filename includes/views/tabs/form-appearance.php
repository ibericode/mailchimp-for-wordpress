<?php $theme = wp_get_theme(); ?>

<h2><?php _e( 'Form Appearance', 'mailchimp-for-wp' ); ?></h2>

<table class="form-table">
	<tr valign="top">
		<th scope="row"><label for="mc4wp_load_stylesheet_select"><?php _e( 'Form Style' ,'mailchimp-for-wp' ); ?></label></th>
		<td class="nowrap valigntop">
			<select name="mc4wp_form[settings][css]" id="mc4wp_load_stylesheet_select">
				<option value="0" <?php selected( $opts['css'], 0 ); ?>><?php printf( __( 'Inherit from %s theme', 'mailchimp-for-wp' ), $theme->Name ); ?></option>
				<option value="form-basic" <?php selected( $opts['css'], 'form-basic' ); ?><?php selected( $opts['css'], 1 ); ?>><?php _e( 'Basic', 'mailchimp-for-wp' ); ?></option>
				<optgroup label="<?php _e( 'Form Themes', 'mailchimp-for-wp' ); ?>">
					<option value="form-theme-light" <?php selected( $opts['css'], 'form-theme-light' ); ?>><?php _e( 'Light Theme', 'mailchimp-for-wp' ); ?></option>
					<option value="form-theme-dark" <?php selected( $opts['css'], 'form-theme-dark' ); ?>><?php _e( 'Dark Theme', 'mailchimp-for-wp' ); ?></option>
					<option value="form-theme-red" <?php selected( $opts['css'], 'form-theme-red' ); ?>><?php _e( 'Red Theme', 'mailchimp-for-wp' ); ?></option>
					<option value="form-theme-green" <?php selected( $opts['css'], 'form-theme-green' ); ?>><?php _e( 'Green Theme', 'mailchimp-for-wp' ); ?></option>
					<option value="form-theme-blue" <?php selected( $opts['css'], 'form-theme-blue' ); ?>><?php _e( 'Blue Theme', 'mailchimp-for-wp' ); ?></option>
					<option disabled><?php _e( '(PRO ONLY)', 'mailchimp-for-wp' ); ?> <?php _e( 'Custom Color Theme', 'mailchimp-for-wp' ); ?></option>
				</optgroup>
			</select>
			<p class="help">
				<?php _e( 'If you want to load some default CSS styles, select "basic formatting styles" or choose one of the color themes' , 'mailchimp-for-wp' ); ?>
			</p>
		</td>

	</tr>
</table>

<?php submit_button(); ?>