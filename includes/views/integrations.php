<?php defined( 'ABSPATH' ) or exit;

$integrations = MC4WP_Integration_Manager::instance();
?>
<div id="mc4wp-admin" class="wrap mc4wp-settings">

	<div class="main-content row">

		<!-- Main Content -->
		<div class="col col-4">

			<h1 class="page-title"><?php _e( 'Checkbox Integrations', 'mailchimp-for-wp' ); ?></h1>

			<table class="widefat striped">

				<tbody>

				<?php foreach( array_keys( $integrations->registered_integrations ) as $slug ) {

					$integration = $integrations->integration( $slug );
					$installed = $integration->is_installed();
					?>
					<tr style="<?php if( ! $installed ) { echo 'opacity: 0.5;'; } ?>">
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

		</div>

		<!-- Sidebar -->
		<div class="sidebar col col-2">
			<?php include dirname( __FILE__ ) . '/parts/admin-sidebar.php'; ?>
		</div>

	</div>

</div>