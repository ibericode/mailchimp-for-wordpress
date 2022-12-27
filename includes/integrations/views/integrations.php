<?php defined( 'ABSPATH' ) or exit;
/** @var MC4WP_Integration_Fixture[] $enabled_integrations */
/** @var MC4WP_Integration_Fixture[] $available_integrations */
/** @var MC4WP_Integration_Fixture $integration */
function _mc4wp_integrations_table_row( $integration ) {
	?>
	<tr style="
	<?php
	if ( ! $integration->is_installed() ) {
		echo 'opacity: 0.6;';
	}
	?>
	">

		<!-- Integration Name -->
		<td>

			<?php
			if ( $integration->is_installed() ) {
				echo sprintf( '<strong><a href="%s" title="%s">%s</a></strong>', esc_attr( add_query_arg( array( 'integration' => $integration->slug ) ) ), esc_html__( 'Configure this integration', 'mailchimp-for-wp' ), $integration->name );
			} else {
				echo $integration->name;
			}
			?>


		</td>
		<td class="desc">
			<?php
			echo esc_html( $integration->description );
			?>
		</td>
		<td>
			<?php
			if ( $integration->enabled && $integration->is_installed() ) {
				echo '<span class="mc4wp-status positive">', esc_html__( 'Active', 'mailchimp-for-wp' ), '</span>';
			} elseif ( $integration->is_installed() ) {
				echo '<span class="mc4wp-status neutral">', esc_html__( 'Inactive', 'mailchimp-for-wp' ), '</span>';
			} else {
				echo '<span>', esc_html__( 'Not installed', 'mailchimp-for-wp' ), '</span>';
			}
			?>
		</td>
	</tr>
	<?php
}

/**
 * Render a table with integrations
 *
 * @param $integrations
 * @ignore
 */
function _mc4wp_integrations_table( $integrations ) {
	?>
	<table class="mc4wp-table widefat striped">

		<thead>
		<tr>
			<th><?php echo esc_html__( 'Name', 'mailchimp-for-wp' ); ?></th>
			<th><?php echo esc_html__( 'Description', 'mailchimp-for-wp' ); ?></th>
			<th><?php echo esc_html__( 'Status', 'mailchimp-for-wp' ); ?></th>
		</tr>
		</thead>

		<tbody>

		<?php
		// active & enabled integrations first
		foreach ( $integrations as $integration ) {
			if ( $integration->is_installed() && $integration->enabled ) {
				_mc4wp_integrations_table_row( $integration );
			}
		}

		// active & disabled integrations next
		foreach ( $integrations as $integration ) {
			if ( $integration->is_installed() && ! $integration->enabled ) {
				_mc4wp_integrations_table_row( $integration );
			}
		}

		// rest
		foreach ( $integrations as $integration ) {
			if ( ! $integration->is_installed() ) {
				_mc4wp_integrations_table_row( $integration );
			}
		}
		?>

		</tbody>
	</table>
	<?php
}
?>
<div id="mc4wp-admin" class="wrap mc4wp-settings">

	<p class="mc4wp-breadcrumbs">
		<span class="prefix"><?php echo esc_html__( 'You are here: ', 'mailchimp-for-wp' ); ?></span>
		<a href="<?php echo admin_url( 'admin.php?page=mailchimp-for-wp' ); ?>">Mailchimp for WordPress</a> &rsaquo;
		<span class="current-crumb"><strong><?php echo esc_html__( 'Integrations', 'mailchimp-for-wp' ); ?></strong></span>
	</p>

	<div class="mc4wp-row">

		<!-- Main Content -->
		<div class="mc4wp-col mc4wp-col-4">

			<h1 class="mc4wp-page-title">Mailchimp for WordPress: <?php echo esc_html__( 'Integrations', 'mailchimp-for-wp' ); ?></h1>

			<h2 style="display: none;"></h2>
			<?php settings_errors(); ?>

			<p>
				<?php echo esc_html__( 'The table below shows all available integrations.', 'mailchimp-for-wp' ); ?>
				<?php echo esc_html__( 'Click on the name of an integration to edit all settings specific to that integration.', 'mailchimp-for-wp' ); ?>
			</p>

			<form action="<?php echo admin_url( 'options.php' ); ?>" method="post">

				<?php settings_fields( 'mc4wp_integrations_settings' ); ?>

				<h3><?php echo esc_html__( 'Integrations', 'mailchimp-for-wp' ); ?></h3>
				<?php _mc4wp_integrations_table( $integrations ); ?>

				<p><?php echo esc_html__( 'Greyed out integrations will become available after installing & activating the corresponding plugin.', 'mailchimp-for-wp' ); ?></p>


			</form>

		</div>

		<!-- Sidebar -->
		<div class="mc4wp-sidebar mc4wp-col">
			<?php require MC4WP_PLUGIN_DIR . '/includes/views/parts/admin-sidebar.php'; ?>
		</div>

	</div>

</div>
