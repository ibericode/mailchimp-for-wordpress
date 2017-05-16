<?php
defined( 'ABSPATH' ) or exit;
?>
<div id="mc4wp-admin" class="wrap mc4wp-settings">

	<p class="breadcrumbs">
		<span class="prefix"><?php echo __( 'You are here: ', 'mailchimp-for-wp' ); ?></span>
		<span class="current-crumb"><strong>MailChimp for WordPress</strong></span>
	</p>


	<div class="row">

		<!-- Main Content -->
		<div class="main-content col col-4">

			<h1 class="page-title">
				<?php _e( 'General Settings', 'mailchimp-for-wp' ); ?>
			</h1>

			<h2 style="display: none;"></h2>
			<?php
			settings_errors();
			$this->messages->show();
			?>

			<form action="<?php echo admin_url( 'options.php' ); ?>" method="post">
				<?php settings_fields( 'mc4wp_settings' ); ?>

				<h3>
					<?php _e( 'MailChimp API Settings', 'mailchimp-for-wp' ); ?>
				</h3>

				<table class="form-table">

					<tr valign="top">
						<th scope="row">
							<?php _e( 'Status', 'mailchimp-for-wp' ); ?>
						</th>
						<td>
							<?php if( $connected ) { ?>
								<span class="status positive"><?php _e( 'CONNECTED' ,'mailchimp-for-wp' ); ?></span>
							<?php } else { ?>
								<span class="status neutral"><?php _e( 'NOT CONNECTED', 'mailchimp-for-wp' ); ?></span>
							<?php } ?>
						</td>
					</tr>


					<tr valign="top">
						<th scope="row"><label for="mailchimp_api_key"><?php _e( 'API Key', 'mailchimp-for-wp' ); ?></label></th>
						<td>
							<input type="text" class="widefat" placeholder="<?php _e( 'Your MailChimp API key', 'mailchimp-for-wp' ); ?>" id="mailchimp_api_key" name="mc4wp[api_key]" value="<?php echo esc_attr( $obfuscated_api_key ); ?>" />
							<p class="help">
								<?php _e( 'The API key for connecting with your MailChimp account.', 'mailchimp-for-wp' ); ?>
								<a target="_blank" href="https://admin.mailchimp.com/account/api"><?php _e( 'Get your API key here.', 'mailchimp-for-wp' ); ?></a>
							</p>
						</td>

					</tr>

				</table>

				<?php submit_button(); ?>

			</form>

			<?php

			/**
			 * Runs right after general settings are outputted in admin.
			 *
			 * @since 3.0
			 * @ignore
			 */
			do_action( 'mc4wp_admin_after_general_settings' );

			if( ! empty( $opts['api_key'] ) ) {
				echo '<hr />';
				include dirname( __FILE__ ) . '/parts/lists-overview.php';
			}

			include dirname( __FILE__ ) . '/parts/admin-footer.php';

			?>
		</div>

		<!-- Sidebar -->
		<div class="sidebar col col-2">
			<?php include dirname( __FILE__ ) . '/parts/admin-sidebar.php'; ?>
		</div>


	</div>

</div>

