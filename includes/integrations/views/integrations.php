<?php defined( 'ABSPATH' ) or exit;
/** @var MC4WP_Integration_Fixture[] $integrations */
?>
<div id="mc4wp-admin" class="wrap mc4wp-settings">

	<p class="breadcrumbs">
		<span class="prefix"><?php echo __( 'You are here: ', 'mailchimp-for-wp' ); ?></span>
		<a href="<?php echo admin_url( 'admin.php?page=mailchimp-for-wp' ); ?>">MailChimp for WordPress</a> &rsaquo;
		<span class="current-crumb"><strong><?php _e( 'Integrations', 'mailchimp-for-wp' ); ?></strong></span>
	</p>

	<div class="main-content row">

		<!-- Main Content -->
		<div class="col col-4">

			<h1 class="page-title"><?php _e( 'Integrations', 'mailchimp-for-wp' ); ?></h1>

			<h2 style="display: none;"></h2>
			<?php settings_errors(); ?>

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

								<?php if( ! $integration->enabled_by_default ) { ?>
									<input type="checkbox" name="mc4wp_integrations[<?php echo $integration->slug; ?>][enabled]" value="1" <?php if( $installed ) { checked( $integration->enabled, true ); } else { disabled( true, true );  } ?> />
								<?php } else { ?>
									<input type="checkbox" name="mc4wp_integrations[<?php echo $integration->slug; ?>][enabled]" value="1" <?php checked( $installed, true );  ?>  title="<?php esc_attr_e( 'This integration is enabled by default as it requires manual actions to work.', 'mailchimp-for-wp' ); ?>" disabled="disabled" />
								<?php } ?>
							</td>
							<td class="row-title">
								<?php

								if( $installed ) {
									printf( '<a href="%s" title="%s">%s</a>', add_query_arg( array( 'integration' => $integration->slug ) ), __( 'Configure this integration', 'mailchimp-for-wp' ), $integration->name );
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
			<?php include MC4WP_PLUGIN_DIR . '/includes/views/parts/admin-sidebar.php'; ?>
		</div>

	</div>

</div>
