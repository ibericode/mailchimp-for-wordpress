<?php

/**
 * Class MC4WP_Admin_Ads
 *
 * @ignore
 * @access private
 */
class MC4WP_Admin_Ads {

	/**
	 * @return bool Adds hooks
	 */
	public function add_hooks() {

		// don't hook if Premium is activated
		if( defined( 'MC4WP_PREMIUM_VERSION' ) ) {
			return false;
		}

		add_filter( 'mc4wp_admin_plugin_meta_links', array( $this, 'plugin_meta_links' ) );
		add_action( 'mc4wp_admin_form_after_behaviour_settings_rows', array( $this, 'after_form_settings_rows' ) );
		add_action( 'mc4wp_admin_form_after_appearance_settings_rows', array( $this, 'after_form_appearance_settings_rows' ) );
		add_action( 'mc4wp_admin_sidebar', array( $this, 'admin_sidebar' ) );
		add_action( 'mc4wp_admin_footer', array( $this, 'admin_footer' ) );
		return true;
	}

	/**
	 * Add text row to "Form > Appearance" tab.
	 */
	public function after_form_appearance_settings_rows() {
		echo '<tr valign="top">';
		echo '<td colspan="2">';
		echo '<p class="help">';
		echo sprintf( __( 'Want to customize the style of your form? <a href="%s">Try our Styles Builder</a> & edit the look of your forms with just a few clicks.', 'mailchimp-for-wp' ), 'https://mc4wp.com/features/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=form-settings-link' );
		echo '</p>';
		echo '</td>';
		echo '</tr>';
	}

	/**
	 * Add text row to "Form > Settings" tab.
	 */
	public function after_form_settings_rows() {
		echo '<tr valign="top">';
		echo '<td colspan="2">';
		echo '<p class="help">';

		if( rand( 1, 2 ) === 1 ) {
			echo sprintf( __( 'Be notified whenever someone subscribes? <a href="%s">MailChimp for WordPress Premium</a> allows you to set up email notifications for your forms.', 'mailchimp-for-wp' ), 'https://mc4wp.com/features/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=footer-link' );
		} else {
			echo sprintf( __( 'Increased conversions? <a href="%s">MailChimp for WordPress Premium</a> submits forms without reloading the entire page, resulting in a much better experience for your visitors.', 'mailchimp-for-wp' ), 'https://mc4wp.com/features/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=form-settings-link' );
		}

		echo '</p>';
		echo '</td>';
		echo '</tr>';
	}

	/**
	 * @param array $links
	 *
	 * @return array
	 */
	public function plugin_meta_links( $links ) {
		$links[] = '<a href="https://mc4wp.com/features/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=plugins-upgrade-link">' . __( 'Upgrade to Premium', 'mailchimp-for-wp' ) . '</a>';
		return $links;
	}

	/**
	 * Add several texts to admin footer.
	 */
	public function admin_footer() {

		if( isset( $_GET['view'] ) && $_GET['view'] === 'edit-form' ) {

			// WPML & Polylang specific message
			if( defined( 'ICL_LANGUAGE_CODE' ) ) {
				echo '<p class="help">' . sprintf( __( 'Do you want translated forms for all of your languages? <a href="%s">Try MailChimp for WordPress Premium</a>, which does just that plus more.', 'mailchimp-for-wp' ), 'https://mc4wp.com/features/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=footer-link' ) . '</p>';
				return;
			}

			// General "edit form" message
			echo '<p class="help">' . sprintf( __( 'Do you want to create more than one form? Our Premium add-on does just that! <a href="%s">Have a look at all Premium benefits</a>.', 'mailchimp-for-wp' ), 'https://mc4wp.com/features/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=footer-link' ) . '</p>';
			return;
		}

		// General message
		echo '<p class="help">' . sprintf( __( 'Are you enjoying this plugin? The Premium add-on unlocks several powerful features. <a href="%s">Find out about all benefits now</a>.', 'mailchimp-for-wp' ), 'https://mc4wp.com/features/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=footer-link' ) . '</p>';
	}

	/**
	 * Add email opt-in form to sidebar
	 */
	public function admin_sidebar() {

		echo '<div class="mc4wp-box">';
		$block = new MC4WP_Remote_Content_Block( 'https://mc4wp.com/api/content-blocks?id=106689' );
		$block->refresh();
		echo $block;
		echo '</div>';

		?>
		<div class="mc4wp-box" id="mc4wp-optin-box">

			<?php $user = wp_get_current_user(); ?>
			<!-- Begin MailChimp Signup Form -->
			<div id="mc_embed_signup">
				<h4 class="mc4wp-title"><?php _e( 'More subscribers, better newsletters.', 'mailchimp-for-wp' ); ?></h4>
				<p><?php _e( 'Learn how to best grow your lists & write better emails by subscribing to our monthly tips.', 'mailchimp-for-wp' ); ?></p>
				<form action="//mc4wp.us1.list-manage.com/subscribe/post?u=a2d08947dcd3683512ce174c5&amp;id=a940232df9" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" target="_blank">
					<p>
						<label for="mc4wp-email"><?php _e( 'Email Address', 'mailchimp-for-wp' ); ?></label>
						<input type="email" value="<?php echo esc_attr( $user->user_email ); ?>" name="EMAIL" class="regular-text" id="mc4wp-email" required>
					</p>
					<p>
						<label for="mc4wp-fname"><?php _e( 'First Name', 'mailchimp-for-wp' ); ?></label>
						<input type="text" value="<?php echo esc_attr( $user->user_firstname ); ?>" name="FNAME" class="regular-text" id="mc4wp-fname">
					</p>
					<div style="position: absolute; left: -5000px;">
						<input type="text" name="b_a2d08947dcd3683512ce174c5_a940232df9" tabindex="-1" value="" autocomplete="off" />
					</div>
					<p>
						<input type="submit" value="<?php esc_attr_e( 'Subscribe', 'mailchimp-for-wp' ); ?>" name="subscribe" class="button">
					</p>

					<input type="hidden" name="SOURCE" value="free-plugin" />
				</form>
			</div>
		</div>
		<?php
	}

}