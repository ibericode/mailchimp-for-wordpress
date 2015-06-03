<?php

class MC4WP_Promotional_Elements {

	public function add_hooks() {
		add_action( 'admin_init', array( $this, 'listen' ) );
		add_action( 'mc4wp_admin_footer', array( $this, 'footer_link' ) );
		add_action( 'mc4wp_admin_before_sidebar', array( $this, 'sidebar_box' ) );
		add_filter( 'mc4wp_menu_items', array( $this, 'menu_item' ) );
	}

	public function menu_item( $items ) {
		$items['upgrade'] = array(
			'title' => __( 'Upgrade to Pro', 'mailchimp-for-wp' ),
			'text' => '<span style="line-height: 20px;"><span class="dashicons dashicons-external"></span> ' .__( 'Upgrade to Pro', 'mailchimp-for-wp' ),
			'slug' => 'upgrade',
			'callback' => array( $this, 'redirect_to_pro' ),
		);
		return $items;
	}

	public function footer_link() {
		?>
		<p class="help"><?php printf( __( 'Enjoying this plugin? <a href="%s">Upgrade to MailChimp for WordPress Pro</a> for an even better plugin, you will love it.', 'mailchimp-for-wp' ), 'https://mc4wp.com/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=footer-link' ); ?></p>
	<?php
	}

	/**
	 * Redirects to the premium version of MailChimp for WordPress (uses JS)
	 */
	public function redirect_to_pro()
	{
		?><script type="text/javascript">window.location.replace('https://mc4wp.com/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=menu-upgrade-link'); </script><?php
	}

	/**
	 * Listen to various actions
	 */
	public function listen() {
		// did the user click on upgrade to pro link?
		$page = isset( $_GET['page'] ) ? $_GET['page'] : '';
		if( $page === 'mailchimp-for-wp-upgrade' && ! headers_sent() ) {
			wp_redirect( 'https://mc4wp.com/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=menu-upgrade-link' );
			exit;
		}
	}

	public function sidebar_box() {
		?>
		<div class="mc4wp-box" id="mc4wp-upgrade-box">
			<h3>Upgrade to MailChimp for WordPress Pro</h3>
			<p><em><?php _e( 'This plugin has an even better premium version, you will absolutely love it.', 'mailchimp-for-wp' ); ?></em></p>
			<p><?php _e( 'Some differences with this free version of the plugin:', 'mailchimp-for-wp' ); ?></p>
			<ul class="ul-square">
				<li>
					<strong><?php _e( 'Multiple forms', 'mailchimp-for-wp' ); ?></strong><br />
					<?php _e( 'Each subscribing to one or multiple MailChimp lists.', 'mailchimp-for-wp' ); ?>
				</li>
				<li>
					<strong><?php _e( 'AJAX forms', 'mailchimp-for-wp' ); ?></strong><br />
					<?php _e( 'Forms do not require a full page reload.', 'mailchimp-for-wp' ); ?>
				</li>
				<li>
					<strong><?php _e( 'Statistics', 'mailchimp-for-wp' ); ?></strong><br />
					<?php _e( 'Every form interaction is logged and visualised in insightful charts.', 'mailchimp-for-wp' ); ?>
				</li>
				<li>
					<strong><?php _e( 'Styles Builder', 'mailchimp-for-wp' ); ?></strong><br />
					<?php _e( 'Create beautiful form themes with ease.', 'mailchimp-for-wp' ); ?>
				</li>
			</ul>
			<p>
				<a class="button button-primary button-large" href="https://mc4wp.com/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=upgrade-box"><?php _e( 'Upgrade Now', 'mailchimp-for-wp' ); ?></a>
				<a class="button" href="https://mc4wp.com/demo/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=upgrade-box"><?php _e( 'View Demo', 'mailchimp-for-wp' ); ?></a></p>
			</p>
		</div>
		<?php
	}
}