<?php 

if( ! defined("MC4WP_LITE_VERSION") ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

?>
<div id="mc4wp-<?php echo $tab; ?>" class="wrap mc4wp-settings">

	<h2><img src="<?php echo plugins_url('mailchimp-for-wp/assets/img/menu-icon.png'); ?>" /> MailChimp for WordPress: MailChimp Settings</h2>

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
	<h3 class="mc4wp-title">Cached MailChimp Settings</h3>
	<p>The table below shows your cached MailChimp lists configuration.</p>
	<p>Made changes to your lists? Please renew the cache manually by hitting the "renew cached data" button.</p>

	<table class="wp-list-table widefat">
		<thead>
			<tr>
				<th scope="col">List Name</th>
				<th scope="col">Merge fields</th>
				<th scope="col">Interest groupings</th>
			</tr>
		</thead>
		<tbody>
			<?php 
			if($lists && is_array($lists)) { ?>
				<?php foreach($lists as $list) {  ?>
					<tr valign="top">
						<td><?php echo $list->name; ?></td>
						<td><?php 
						$first = true;
						foreach($list->merge_vars as $merge_var) { 
							echo ($first) ? $merge_var->name : ', '. $merge_var->name;
							$first = false;
						} 
						?>
						</td>
						<td class="pro-feature">Pro Only</td>
					</tr>
				<?php } // endforeach ?>
			<?php } else { ?>
				<tr><td colspan="4"><p>No lists found, are you connected to MailChimp? If so, try renewing the cache.</p></td></tr>
			<?php } ?>
		</tbody>
	</table>

	<p><form method="post"><input type="submit" name="renew-cached-data" value="Renew cached data" class="button" /></form></p>
	<?php } ?>

	<?php include 'parts/admin-footer.php'; ?>
</div>



<div id="mc4wp-sidebar">
	<?php include 'parts/admin-upgrade-to-pro.php'; ?>
	<?php include 'parts/admin-need-support.php'; ?>
</div>

</div>

