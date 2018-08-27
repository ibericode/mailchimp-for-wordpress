<?php
defined( 'ABSPATH' ) or exit;
?>
<div id="pl4wp-admin" class="wrap pl4wp-settings">

	<p class="breadcrumbs">
		<span class="prefix"><?php echo __( 'You are here: ', 'phplist-for-wp' ); ?></span>
		<span class="current-crumb"><strong>PhpList for WordPress</strong></span>
	</p>


	<div class="row">

		<!-- Main Content -->
		<div class="main-content col col-4">

			<h1 class="page-title">
				<?php _e( 'General Settings', 'phplist-for-wp' ); ?>
			</h1>

			<h2 style="display: none;"></h2>
			<?php
			settings_errors();
			$this->messages->show();
			?>

			<form action="<?php echo admin_url( 'options.php' ); ?>" method="post">
				<?php settings_fields( 'pl4wp_settings' ); ?>

				<h3>
					<?php _e( 'PhpList API Settings', 'phplist-for-wp' ); ?>
				</h3>

				<table class="form-table">

					<tr valign="top">
						<th scope="row">
							<?php _e( 'Status', 'phplist-for-wp' ); ?>
						</th>
						<td>
							<?php if( $connected ) { ?>
								<span class="status positive"><?php _e( 'CONNECTED' ,'phplist-for-wp' ); ?></span>
							<?php } else { ?>
								<span class="status neutral"><?php _e( 'NOT CONNECTED', 'phplist-for-wp' ); ?></span>
							<?php } ?>
						</td>
					</tr>


					<tr valign="top">
						<th scope="row"><label for="phplist_installation_url"><?php _e( 'PhpList URL', 'phplist-for-wp' ); ?></label></th>
						<td>
							<input type="text" class="widefat" placeholder="<?php _e( 'Your PhpList URL', 'phplist-for-wp' ); ?>" id="phplist_installation_url" name="pl4wp[installation_url]" value="<?=$installation_url?>" />
						</td>

					</tr>

					<tr valign="top">
						<th scope="row"><label for="phplist_api_key"><?php _e( 'API Key', 'phplist-for-wp' ); ?></label></th>
						<td>
							<input type="text" class="widefat" placeholder="<?php _e( 'Your PhpList API key', 'phplist-for-wp' ); ?>" id="phplist_api_key" name="pl4wp[api_key]" value="<?php echo esc_attr( $obfuscated_api_key ); ?>" />
						</td>

					</tr>

					<tr valign="top">
						<th scope="row"><label for="phplist_api_username"><?php _e( 'API Username', 'phplist-for-wp' ); ?></label></th>
						<td>
							<input type="text" class="widefat" placeholder="<?php _e( 'Your PhpList API username', 'phplist-for-wp' ); ?>" id="phplist_api_username" name="pl4wp[api_username]" value="<?php echo esc_attr( $api_username ); ?>" />
						</td>

					</tr>

					<tr valign="top">
						<th scope="row"><label for="phplist_api_password"><?php _e( 'API Password', 'phplist-for-wp' ); ?></label></th>
						<td>
							<input type="text" class="widefat" placeholder="<?php _e( 'Your PhpList API password', 'phplist-for-wp' ); ?>" id="phplist_api_password" name="pl4wp[api_password]" value="<?php echo esc_attr( $obfuscated_api_password ); ?>" />
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
			do_action( 'pl4wp_admin_after_general_settings' );

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

