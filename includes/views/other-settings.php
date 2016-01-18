<?php
defined( 'ABSPATH' ) or exit;

/** @var MC4WP_Debug_Log_Reader $log_reader */

/**
 * @ignore
 *
 * @param array $opts
 */
function __usage_tracking_setting( $opts ) {
	?>
	<div class="medium-margin">
	<h3><?php _e( 'Usage Tracking', 'mailchimp-for-wp' ); ?></h3>
	<p>
		<label>
			<?php /* hidden input field to send `0` when checkbox is not checked */ ?>
			<input type="hidden" name="mc4wp[allow_usage_tracking]" value="0" />
			<input type="checkbox" name="mc4wp[allow_usage_tracking]" value="1" <?php checked( $opts['allow_usage_tracking'], 1 ); ?>>
			<?php echo __( 'Allow us to anonymously track how this plugin is used to help us make it better fit your needs.', 'mailchimp-for-wp' ); ?>
			<a href="https://mc4wp.com/kb/what-is-usage-tracking/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=settings-page" target="_blank">
				<?php _e( 'This is what we track.', 'mailchimp-for-wp' ); ?>
			</a>
		</label>
	</p>
	</div>
	<?php
}

add_action( 'mc4wp_admin_other_settings', '__usage_tracking_setting', 70 );
?>
<div id="mc4wp-admin" class="wrap mc4wp-settings">

	<p class="breadcrumbs">
		<span class="prefix"><?php echo __( 'You are here: ', 'mailchimp-for-wp' ); ?></span>
		<a href="<?php echo admin_url( 'admin.php?page=mailchimp-for-wp' ); ?>">MailChimp for WordPress</a> &rsaquo;
		<span class="current-crumb"><strong><?php _e( 'Other Settings', 'mailchimp-for-wp' ); ?></strong></span>
	</p>


	<div class="row">

		<!-- Main Content -->
		<div class="main-content col col-4">

			<h1 class="page-title">
				<?php _e( 'Other Settings', 'mailchimp-for-wp' ); ?>
			</h1>

			<h2 style="display: none;"></h2>
			<?php settings_errors(); ?>

			<?php
			/**
			 * @ignore
			 */
			do_action( 'mc4wp_admin_before_other_settings', $opts );
			?>

			<!-- Settings -->
			<form action="<?php echo admin_url( 'options.php' ); ?>" method="post">
				<?php settings_fields( 'mc4wp_settings' ); ?>

				<?php
				/**
				 * @ignore
				 */
				do_action( 'mc4wp_admin_other_settings', $opts );
				?>

				<?php submit_button(); ?>
			</form>

			<!-- Debug Log -->
			<h3><?php _e( 'Debug Log', 'mailchimp-for-wp' ); ?> <input type="text" id="debug-log-filter" class="regular-text" placeholder="<?php esc_attr_e( 'Filter..', 'mailchimp-for-wp' ); ?>" style="float: right;"/></h3>


			<style type="text/css">
				#debug-log { height: 400px; padding: 6px; border:1px solid #ccc; background: #262626; color: white; overflow-y: scroll; }
				#debug-log .line { line-height: 22px; }
				#debug-log .time { color: rgb(181, 137, 0); }
				#debug-log .level { color: rgb(37, 140, 205); }
			</style>

			<div id="debug-log" class="widefat">
				<?php while( ( $line = $log_reader->read_as_html() ) ) {
					echo '<div class="line">' . $line . '</div>';
				} ?>
			</div>

			<form method="post">
				<input type="hidden" name="_mc4wp_action" value="empty_debug_log">
				<p>
					<input type="submit" class="button" value="<?php esc_attr_e( 'Empty Log', 'mailchimp-for-wp' ); ?>" />
				</p>
			</form>

			<script type="text/javascript">
				(function() {
					'use strict';
					// scroll to bottom of log
					var log = document.getElementById("debug-log");
					log.scrollTop = log.scrollHeight;

					// add filter
					var logFilter = document.getElementById('debug-log-filter');
					logFilter.addEventListener('keydown', function(e) {
						if(e.keyCode == 13 ) {
							var search = e.target.value.toLowerCase();

							// go go go
							[].forEach.call(log.childNodes, function(child) {
								if( child.innerText == undefined ) { return; }
								child.style.display = ( child.innerText.toLowerCase().indexOf(search) > -1 ) ? 'block' : 'none';
							});

							log.scrollTop = log.scrollHeight;
						}
					})
				})();

			</script>



			<?php include dirname( __FILE__ ) . '/parts/admin-footer.php'; ?>
		</div>

		<!-- Sidebar -->
		<div class="sidebar col col-2">
			<?php include dirname( __FILE__ ) . '/parts/admin-sidebar.php'; ?>
		</div>


	</div>

</div>

