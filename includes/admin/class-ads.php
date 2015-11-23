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
		add_action( 'mc4wp_admin_sidebar', array( $this, 'admin_sidebar' ) );
		add_action( 'mc4wp_admin_footer', array( $this, 'admin_footer' ), 10 );
		return true;
	}

	/**
	 * @param array $links
	 *
	 * @return array
	 */
	public function plugin_meta_links( $links ) {
		$links[] = '<a href="https://mc4wp.com/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=plugins-upgrade-link">' . __( 'Upgrade to MailChimp for WordPress Pro', 'mailchimp-for-wp' ) . '</a>';
		return $links;
	}

	/**
	 * Add upgrade text to admin footer.
	 */
	public function admin_footer() {
		echo '<p class="help">' . sprintf( __( 'Enjoying this plugin? <a href="%s">Purchase our bundle of premium features</a> for an even better plugin.', 'mailchimp-for-wp' ), 'https://mc4wp.com/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=footer-link' ) . '</p>';
	}

	/**
	 * Add upgrade block to sidebar
	 */
	public function admin_sidebar() {
		?>
		<div class="mc4wp-box">
			<?php
			// upgrade block
			$block = new MC4WP_Remote_Content_Block( 'https://mc4wp.com/api/content-blocks?id=103927' );
			echo $block;
			?>
		</div>

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