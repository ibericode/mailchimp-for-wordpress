<?php

if( ! defined( 'MC4WP_LITE_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

?>
<div id="mc4wp-admin" class="wrap mc4wp-settings">

	<h2><img src="<?php echo MC4WP_LITE_PLUGIN_URL . 'assets/img/menu-icon.png'; ?>" /> <?php _e( 'MailChimp for WordPress', 'mailchimp-for-wp' ); ?>: <?php _e( 'MailChimp Settings', 'mailchimp-for-wp' ); ?></h2>

	<div id="mc4wp-content">

		<?php settings_errors(); ?>

		<form action="options.php" method="post">
			<?php settings_fields( 'mc4wp_lite_settings' ); ?>
			
			<h3 class="mc4wp-title">
				MailChimp <?php _e( 'API Settings', 'mailchimp-for-wp' ); ?>
				<?php if($connected) { ?>
					<span class="status positive"><?php _e( 'CONNECTED' ,'mailchimp-for-wp' ); ?></span>
				<?php } else { ?>
					<span class="status neutral"><?php _e( 'NOT CONNECTED', 'mailchimp-for-wp' ); ?></span>
				<?php } ?>
			</h3>
			<table class="form-table">

				<tr valign="top">
					<th scope="row"><label for="mailchimp_api_key">MailChimp <?php _e( 'API Key', 'mailchimp-for-wp' ); ?></label></th>
					<td>
						<input type="text" class="widefat" placeholder="<?php _e( 'Your MailChimp API key', 'mailchimp-for-wp' ); ?>" id="mailchimp_api_key" name="mc4wp_lite[api_key]" value="<?php echo esc_attr( $opts['api_key'] ); ?>" />
						<p class="help"><a target="_blank" href="https://admin.mailchimp.com/account/api"><?php _e( 'Get your API key here.', 'mailchimp-for-wp' ); ?></a></p>
					</td>
					
				</tr>

			</table>

			<?php submit_button(); ?>
		</form>

	<?php if($connected) { ?>

		<h3 class="mc4wp-title"><?php _e( 'MailChimp Data' ,'mailchimp-for-wp' ); ?></h3>
		<p><?php _e( 'The table below shows your MailChimp lists data. If you applied changes to your MailChimp lists, please use the following button to renew your cached data.', 'mailchimp-for-wp' ); ?></p>

		<form method="post" action="">
			<input type="hidden" name="mc4wp-renew-cache" value="1" />

			<p>
				<input type="submit" value="<?php _e( 'Renew MailChimp lists', 'mailchimp-for-wp' ); ?>" class="button" />
			</p>
		</form>

		<table class="wp-list-table widefat">
			<thead>
				<tr>
					<th class="mc4wp-hide-smallscreens" scope="col">List ID</th>
					<th scope="col">List Name</th>
					<th scope="col">Merge Fields <code>TAG</code></th>
					<th scope="col">Groupings</th>
					<th class="mc4wp-hide-smallscreens" scope="col">Subscribers</th>
				</tr>
			</thead>
			<tbody>
				<?php
				if( ! empty( $lists ) && is_array( $lists ) ) {
					foreach($lists as $list) { ?>

					<tr valign="top">
						<td class="mc4wp-hide-smallscreens"><?php echo esc_html( $list->id ); ?></td>
						<td><?php echo esc_html( $list->name ); ?></td>
						
						<td>
							<?php if( ! empty( $list->merge_vars ) && is_array( $list->merge_vars ) ) { ?>
								<ul class="ul-square" style="margin-top: 0;">
									<?php foreach( $list->merge_vars as $merge_var ) { ?>
										<li><?php echo esc_html( $merge_var->name ); if( $merge_var->req ) { echo '<span style="color:red;">*</span>'; } ?> <code><?php echo esc_html( $merge_var->tag ); ?></code></li>
									<?php } ?>
								</ul>
							<?php } ?>
						</td>
						<td>
						<?php
						if( ! empty( $list->interest_groupings ) && is_array( $list->interest_groupings ) ) {
							foreach($list->interest_groupings as $grouping) { ?>
								<strong><?php echo esc_html( $grouping->name ); ?></strong>

								<?php if( ! empty( $grouping->groups ) && is_array( $grouping->groups ) ) { ?>
									<ul class="ul-square">
										<?php foreach( $grouping->groups as $group ) { ?>
											<li><?php echo esc_html( $group->name ); ?></li>
										<?php } ?>
									</ul>
								<?php } ?>
							<?php }
						} else {
							?>-<?php
						} ?>

						</td>
						<td class="mc4wp-hide-smallscreens"><?php echo esc_html( $list->subscriber_count ); ?></td>
					</tr>
					<?php
					}
				} else { ?>
					<tr>
						<td colspan="5">
							<p><?php _e( 'No lists were found in your MailChimp account', 'mailchimp-for-wp' ); ?>.</p>
						</td>
					</tr>
				<?php
				}
				?>
			</tbody>
		</table>

		<form method="post" action="">
			<input type="hidden" name="mc4wp-renew-cache" value="1" />

			<p>
				<input type="submit" value="<?php _e( 'Renew MailChimp lists', 'mailchimp-for-wp' ); ?>" class="button" />
			</p>
		</form>
	<?php } ?>

	<?php include 'parts/admin-footer.php'; ?>
</div>



<div id="mc4wp-sidebar">
	<?php include 'parts/admin-upgrade-to-pro.php'; ?>
	<?php include 'parts/admin-need-support.php'; ?>
</div>

</div>

