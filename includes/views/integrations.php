<?php defined( 'ABSPATH' ) or exit;
/** @var MC4WP_Integration[] $integrations */
?>
<div id="mc4wp-admin" class="wrap mc4wp-settings">

	<div class="main-content row">

		<!-- Main Content -->
		<div class="col col-4">

			<h1 class="page-title"><?php _e( 'Checkbox Integrations', 'mailchimp-for-wp' ); ?></h1>

			<form action="<?php echo admin_url( 'options.php' ); ?>" method="post">

				<?php settings_fields( 'mc4wp_integrations_settings' ); ?>

				<table class="mc4wp-table widefat striped">

					<thead>
						<tr>
							<th><?php _e( 'Enabled', 'mailchimp-for-wp' ); ?></th>
							<th><?php _e( 'Name', 'mailchimp-for-wp' ); ?></th>
							<th><?php _e( 'Description', 'mailchimp-for-wp' ); ?></th>
						</tr>
					</thead>

					<tbody>

					<?php foreach( $integrations as $integration ) {

						$installed = $integration->is_installed();
						?>
						<tr style="<?php if( ! $installed ) { echo 'opacity: 0.5;'; } ?>">
							<td>
								<!-- hidden field to make sure a value is sent to the server -->
								<input type="hidden" name="mc4wp_integrations[<?php echo $integration->slug; ?>][enabled]" value="0" />
								<input type="checkbox" name="mc4wp_integrations[<?php echo $integration->slug; ?>][enabled]" value="1" <?php if( $installed ) { checked( $integration->enabled, true ); disabled( $integration->enabled_by_default, true ); } else { disabled( true, true ); } ?> />
							</td>
							<td class="row-title">
								<?php

								if( $installed ) {
									printf( '<a href="%s">%s</a>', add_query_arg( array( 'integration' => $integration->slug ) ), $integration->name );
								} else {
									echo $integration->name ;
								} ?>
							</td>
							<td class="desc">
								<?php echo $integration->description; ?>
							</td>
						</tr>
					<?php } ?>

					</tbody>
				</table>

				<?php submit_button(); ?>
			</form>

		</div>

		<!-- Sidebar -->
		<div class="sidebar col col-2">
			<?php include dirname( __FILE__ ) . '/parts/admin-sidebar.php'; ?>
		</div>

	</div>

</div>
