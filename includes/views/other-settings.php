<?php
defined( 'ABSPATH' ) or exit;

/** @var MC4WP_Debug_Log $log */
/** @var MC4WP_Debug_Log_Reader $log_reader */

/**
 * @ignore
 * @param array $opts
 */
function _mc4wp_usage_tracking_setting( $opts ) {
	?>
	<div class="mc4wp-margin-m" >
		<h3><?php echo esc_html__( 'Miscellaneous settings', 'mailchimp-for-wp' ); ?></h3>
		<table class="form-table">
			<tr>
				<th><?php echo esc_html__( 'Usage Tracking', 'mailchimp-for-wp' ); ?></th>
				<td>
					<label>
						<input type="radio" name="mc4wp[allow_usage_tracking]" value="1" <?php checked( $opts['allow_usage_tracking'], 1 ); ?> />
						<?php echo esc_html__( 'Yes', 'mailchimp-for-wp' ); ?>
					</label> &nbsp;
					<label>
						<input type="radio" name="mc4wp[allow_usage_tracking]" value="0" <?php checked( $opts['allow_usage_tracking'], 0 ); ?>  />
						<?php echo esc_html__( 'No', 'mailchimp-for-wp' ); ?>
					</label>

					<p class="description">
						<?php echo esc_html__( 'Allow us to anonymously track how this plugin is used to help us make it better fit your needs.', 'mailchimp-for-wp' ); ?>
						<a href="https://www.mc4wp.com/kb/what-is-usage-tracking/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=settings-page" target="_blank">
							<?php echo esc_html__( 'This is what we track.', 'mailchimp-for-wp' ); ?>
						</a>
					</p>
				</td>
			</tr>
			<tr>
				<th><?php echo esc_html__( 'Logging', 'mailchimp-for-wp' ); ?></th>
				<td>
					<select name="mc4wp[debug_log_level]">
						<option value="warning" <?php selected( 'warning', $opts['debug_log_level'] ); ?>><?php echo esc_html__( 'Errors & warnings only', 'mailchimp-for-wp' ); ?></option>
						<option value="debug" <?php selected( 'debug', $opts['debug_log_level'] ); ?>><?php echo esc_html__( 'Everything', 'mailchimp-for-wp' ); ?></option>
					</select>
					<p class="description">
						<?php echo sprintf( wp_kses( __( 'Determines what events should be written to <a href="%s">the debug log</a> (see below).', 'mailchimp-for-wp' ), array( 'a' => array( 'href' => array() ) ) ), 'https://www.mc4wp.com/kb/how-to-enable-log-debugging/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=settings-page' ); ?>
					</p>
				</td>
			</tr>
		</table>
	</div>
	<?php
}

add_action( 'mc4wp_admin_other_settings', '_mc4wp_usage_tracking_setting', 70 );
?>
<div id="mc4wp-admin" class="wrap mc4wp-settings">

	<p class="mc4wp-breadcrumbs">
		<span class="prefix"><?php echo esc_html__( 'You are here: ', 'mailchimp-for-wp' ); ?></span>
		<a href="<?php echo admin_url( 'admin.php?page=mailchimp-for-wp' ); ?>">Mailchimp for WordPress</a> &rsaquo;
		<span class="current-crumb"><strong><?php echo esc_html__( 'Other Settings', 'mailchimp-for-wp' ); ?></strong></span>
	</p>


	<div class="mc4wp-row">

		<!-- Main Content -->
		<div class="main-content mc4wp-col mc4wp-col-4">

			<h1 class="mc4wp-page-title">
				<?php echo esc_html__( 'Other Settings', 'mailchimp-for-wp' ); ?>
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

				<div style="margin-top: -20px;"><?php submit_button(); ?></div>
			</form>

			<!-- Debug Log -->
			<div class="mc4wp-margin-m">
				<h3><?php echo esc_html__( 'Debug Log', 'mailchimp-for-wp' ); ?> <input type="text" id="debug-log-filter" class="alignright regular-text" placeholder="<?php echo esc_attr__( 'Filter..', 'mailchimp-for-wp' ); ?>" /></h3>

				<?php
				if ( ! $log->test() ) {
					echo '<p>';
					echo esc_html__( 'Log file is not writable.', 'mailchimp-for-wp' ) . ' ';
					echo sprintf( wp_kses( __( 'Please ensure %1$s has the proper <a href="%2$s">file permissions</a>.', 'mailchimp-for-wp' ), array( 'a' => array( 'href' => array() ) ) ), '<code>' . $log->file . '</code>', 'https://codex.wordpress.org/Changing_File_Permissions' );
					echo '</p>';

					// hack to hide filter input
					echo '<style type="text/css">#debug-log-filter { display: none; }</style>';
				} else {
					?>
					<div id="debug-log" class="mc4wp-log widefat">
						<?php
						$line = $log_reader->read_as_html();

						if ( ! empty( $line ) ) {
							while ( is_string( $line ) ) {
								if ( ! empty( $line ) ) {
									echo '<div class="debug-log-line">' . $line . '</div>';
								}

								$line = $log_reader->read_as_html();
							}
						} else {
							echo '<div class="debug-log-empty">';
							echo '-- ', esc_html__( 'Nothing here. Which means there are no errors!', 'mailchimp-for-wp' );
							echo '</div>';
						}
						?>
					</div>

					<form method="post">
						<input type="hidden" name="_mc4wp_action" value="empty_debug_log">
						<p>
							<input type="submit" class="button" value="<?php echo esc_attr__( 'Empty Log', 'mailchimp-for-wp' ); ?>"/>
						</p>
					</form>
					<?php
				} // end if is writable

				if ( $log->level >= 300 ) {
					echo '<p>';
					echo esc_html__( 'Right now, the plugin is configured to only log errors and warnings.', 'mailchimp-for-wp' );
					echo '</p>';
				}
				?>

				<script>
					(function() {
						'use strict';
						// scroll to bottom of log
						var log = document.getElementById("debug-log"),
							logItems;
						log.scrollTop = log.scrollHeight;
						log.style.minHeight = '';
						log.style.maxHeight = '';
						log.style.height = log.clientHeight + "px";

						// add filter
						var logFilter = document.getElementById('debug-log-filter');
						logFilter.addEventListener('keydown', function(evt) {
							if(evt.keyCode === 13 ) {
								searchLog(evt.target.value.trim());
							}
						});

						// search log for query
						function searchLog(query) {
							if( ! logItems ) {
								logItems = [].map.call(log.children, function(node) {
									return node.cloneNode(true);
								})
							}

							var ri = new RegExp(query.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&"), 'i');
							var newLog = log.cloneNode();
							logItems.forEach(function(node) {
								if( ! node.textContent ) { return ; }
								if( ! query.length || ri.test(node.textContent) ) {
									newLog.appendChild(node);
								}
							});

							log.parentNode.replaceChild(newLog,log);
							log = newLog;
							log.scrollTop = log.scrollHeight;
						}
					})();
				</script>
			</div>
			<!-- / Debug Log -->
			<?php include __DIR__ . '/parts/admin-footer.php'; ?>
		</div>

		<!-- Sidebar -->
		<div class="mc4wp-sidebar mc4wp-col mc4wp-col-2">
			<?php include __DIR__ . '/parts/admin-sidebar.php'; ?>
		</div>


	</div>

</div>

