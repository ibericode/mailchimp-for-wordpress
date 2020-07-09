<?php
defined( 'ABSPATH' ) or exit;
?>
<div id="mc4wp-admin" class="wrap mc4wp-settings">

	<p class="breadcrumbs">
		<span class="prefix"><?php echo esc_html__( 'You are here: ', 'mailchimp-for-wp' ); ?></span>
		<span class="current-crumb"><strong>Mailchimp for WordPress</strong></span>
	</p>


	<div class="row">

		<!-- Main Content -->
		<div class="main-content col col-4">

			<h1 class="page-title">
				Mailchimp for WordPress: <?php echo esc_html__( 'API Settings', 'mailchimp-for-wp' ); ?>
			</h1>

			<h2 style="display: none;"></h2>
			<?php
			settings_errors();
			$this->messages->show();
			?>

			<form action="<?php echo admin_url( 'options.php' ); ?>" method="post">
				<?php settings_fields( 'mc4wp_settings' ); ?>

				<table class="form-table">

					<tr valign="top">
						<th scope="row">
							<?php echo esc_html__( 'Status', 'mailchimp-for-wp' ); ?>
						</th>
						<td>
							<?php
							if ( $connected ) {
								?>
								<span class="status positive"><?php echo esc_html__( 'CONNECTED', 'mailchimp-for-wp' ); ?></span>
								<?php
							} else {
								?>
								<span class="status neutral"><?php echo esc_html__( 'NOT CONNECTED', 'mailchimp-for-wp' ); ?></span>
								<?php
							}
							?>
						</td>
					</tr>


					<tr valign="top">
						<th scope="row"><label for="mailchimp_api_key"><?php echo esc_html__( 'API Key', 'mailchimp-for-wp' ); ?></label></th>
						<td>
							<input type="text" class="widefat" placeholder="<?php echo esc_html__( 'Your Mailchimp API key', 'mailchimp-for-wp' ); ?>" id="mailchimp_api_key" name="mc4wp[api_key]" value="<?php echo esc_attr( $obfuscated_api_key ); ?>" <?php echo defined( 'MC4WP_API_KEY' ) ? 'readonly="readonly"' : ''; ?> />
							<p class="help">
								<?php echo esc_html__( 'The API key for connecting with your Mailchimp account.', 'mailchimp-for-wp' ); ?>
								<a target="_blank" href="https://admin.mailchimp.com/account/api"><?php echo esc_html__( 'Get your API key here.', 'mailchimp-for-wp' ); ?></a>
							</p>

							<?php
							if ( defined( 'MC4WP_API_KEY' ) ) {
								echo '<p class="help">', wp_kses( __( 'You defined your Mailchimp API key using the <code>MC4WP_API_KEY</code> constant.', 'mailchimp-for-wp' ), array( 'code' => array() ) ), '</p>';
							}
							?>
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

			if ( ! empty( $opts['api_key'] ) ) {
				echo '<hr />';
				include __DIR__ . '/parts/lists-overview.php';
			}

			include __DIR__ . '/parts/admin-footer.php';

			?>
		</div>

		<!-- Sidebar -->
		<div class="sidebar col col-2">
			<?php include __DIR__ . '/parts/admin-sidebar.php'; ?>
		</div>


	</div>

</div>

