<?php 

if( ! defined("MC4WP_LITE_VERSION") ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

?>
<div id="mc4wp-<?php echo $tab; ?>" class="wrap mc4wp-settings">

	<h2><img src="<?php echo MC4WP_LITE_PLUGIN_URL . 'assets/img/menu-icon.png'; ?>" /> MailChimp for WordPress: MailChimp Settings</h2>

	<div id="mc4wp-content">

		<?php settings_errors(); ?>

		<form action="options.php" method="post">
			<?php settings_fields( 'mc4wp_lite_settings' ); ?>
			
			<h3 class="mc4wp-title">MailChimp API Settings <?php if($connected) { ?><span class="status positive">CONNECTED</span> <?php } else { ?><span class="status negative">NOT CONNECTED</span><?php } ?></h3>
			<table class="form-table">

				<tr valign="top">
					<th scope="row"><label for="mailchimp_api_key">MailChimp API Key</label></th>
					<td>
						<input type="text" class="widefat" placeholder="Your MailChimp API key" id="mailchimp_api_key" name="mc4wp_lite[api_key]" value="<?php echo $opts['api_key']; ?>" />
						<p class="help"><a target="_blank" href="http://admin.mailchimp.com/account/api">Click here to get your MailChimp API Key.</a></p>
					</td>
					
				</tr>

			</table>

			<?php submit_button(); ?>
		</form>

	<?php if($connected) { ?>
	
		<h3 class="mc4wp-title">MailChimp Data</h3>
		<p>The table below shows your MailChimp lists data. If you applied changes to your MailChimp lists, please use the following button to renew your cached data.</p>
		
		<form method="post">
			<p>
				<input type="submit" name="renew-cached-data" value="Renew cached data" class="button" />
			</p>
		</form>

		<table class="wp-list-table widefat">
			<thead>
				<tr>
					<th class="mc4wp-hide-smallscreens" scope="col">List ID</th>
					<th scope="col">List Name</th>
					<th scope="col">Merge fields</th>
					<th scope="col">Groupings</th>
					<th scope="col">Subscriber Count</th>
				</tr>
			</thead>
			<tbody>
				<?php 
				if($lists) { 
					foreach($lists as $list) { ?>

					<tr valign="top">
						<td class="mc4wp-hide-smallscreens"><?php echo $list->id; ?></td>
						<td><?php echo $list->name; ?></td>
						
						<td>
							<?php if( ! empty( $list->merge_vars ) ) { ?>
								<ul class="ul-square" style="margin-top: 0;">
									<?php foreach( $list->merge_vars as $merge_var ) { ?>
										<li><?php echo $merge_var->name; ?></li>
									<?php } ?>
								</ul>
							<?php } ?>
						</td>
						<td>
						<?php 
						if( ! empty( $list->interest_groupings ) ) {
							foreach($list->interest_groupings as $grouping) { ?>
								<strong><?php echo $grouping->name; ?></strong>

								<?php if( ! empty( $grouping->groups ) ) { ?>
									<ul class="ul-square">
										<?php foreach( $grouping->groups as $group ) { ?>
											<li><?php echo $group->name; ?></li>
										<?php } ?>
									</ul>
								<?php } ?>
							<?php }
							} else {
								?>-<?php
							} ?>

						</td>
						<td><?php echo $list->subscriber_count; ?></td>
					</tr>
					<?php 
					}  
				} else { ?>
					<tr>
						<td colspan="3">
							<p>No lists were found in your MailChimp account.</p>
						</td>
					</tr>
				<?php 
				} 
				?>
			</tbody>
		</table>

		<form method="post">
			<p>
				<input type="submit" name="renew-cached-data" value="Renew cached data" class="button" />
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

