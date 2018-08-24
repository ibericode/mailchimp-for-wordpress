<?php defined( 'ABSPATH' ) or exit;
/** @var PL4WP_Integration_Fixture[] $enabled_integrations */
/** @var PL4WP_Integration_Fixture[] $available_integrations */

/**
 * Render a table with integrations
 *
 * @param $integrations
 * @ignore
 */
function _pl4wp_integrations_table( $integrations ) {
	?>
	<table class="pl4wp-table widefat striped">

		<thead>
		<tr>
			<th><?php _e( 'Name', 'phplist-for-wp' ); ?></th>
			<th><?php _e( 'Description', 'phplist-for-wp' ); ?></th>
		</tr>
		</thead>

		<tbody>

		<?php foreach( $integrations as $integration ) {

			$installed = $integration->is_installed();
			?>
			<tr style="<?php if( ! $installed ) { echo 'opacity: 0.4;'; } ?>">

				<!-- Integration Name -->
				<td>

					<?php
					if( $installed ) {
						printf( '<strong><a href="%s" title="%s">%s</a></strong>', esc_attr( add_query_arg( array( 'integration' => $integration->slug ) ) ), __( 'Configure this integration', 'phplist-for-wp' ), $integration->name );
					} else {
						echo $integration->name;
					} ?>


				</td>
				<td class="desc">
					<?php
                   _e($integration->description, 'phplist-for-wp');
                    ?>
				</td>
			</tr>
		<?php } ?>

		</tbody>
	</table><?php
}
?>
<div id="pl4wp-admin" class="wrap pl4wp-settings">

	<p class="breadcrumbs">
		<span class="prefix"><?php echo __( 'You are here: ', 'phplist-for-wp' ); ?></span>
		<a href="<?php echo admin_url( 'admin.php?page=phplist-for-wp' ); ?>">PhpList for WordPress</a> &rsaquo;
		<span class="current-crumb"><strong><?php _e( 'Integrations', 'phplist-for-wp' ); ?></strong></span>
	</p>

	<div class="main-content row">

		<!-- Main Content -->
		<div class="col col-4">

			<h1 class="page-title"><?php _e( 'Integrations', 'phplist-for-wp' ); ?></h1>

			<h2 style="display: none;"></h2>
			<?php settings_errors(); ?>

			<p>
				<?php _e( 'The table below shows all available integrations.', 'phplist-for-wp' ); ?>
				<?php _e( 'Click on the name of an integration to edit all settings specific to that integration.', 'phplist-for-wp' ); ?>
			</p>

			<form action="<?php echo admin_url( 'options.php' ); ?>" method="post">

				<?php settings_fields( 'pl4wp_integrations_settings' ); ?>

				<h3><?php _e( 'Enabled integrations', 'phplist-for-wp' ); ?></h3>
				<?php _pl4wp_integrations_table( $enabled_integrations ); ?>

				<div class="medium-margin"></div>

				<h3><?php _e( 'Available integrations', 'phplist-for-wp' ); ?></h3>
				<?php _pl4wp_integrations_table( $available_integrations ); ?>
                <p><?php echo __( "Greyed out integrations will become available after installing & activating the corresponding plugin.", 'phplist-for-wp' ); ?></p>


            </form>

		</div>

		<!-- Sidebar -->
		<div class="sidebar col col-2">
			<?php include PL4WP_PLUGIN_DIR . '/includes/views/parts/admin-sidebar.php'; ?>
		</div>

	</div>

</div>
