<div id="mc4wp-<?php echo $tab; ?>" class="wrap mc4wp-settings">

	<h2>MailChimp API Settings</h2>

	<div id="mc4wp-content">

		<?php settings_errors(); ?>

		<form action="options.php" method="post">
			<?php settings_fields( 'mc4wp_lite_settings' ); ?>
			
			<h3>MailChimp API Settings <?php if($connected) { ?><span class="status positive">CONNECTED</span> <?php } else { ?><span class="status negative">NOT CONNECTED</span><?php } ?></h3>
			<table class="form-table">

				<tr valign="top">
					<th scope="row"><label for="mailchimp_api_key">MailChimp API Key</label></th>
					<td>
						<input type="text" class="widefat" placeholder="Your MailChimp API key" id="mailchimp_api_key" name="mc4wp_lite[api_key]" value="<?php echo $opts['api_key']; ?>" />
						<small><a target="_blank" href="http://admin.mailchimp.com/account/api">Get your MailChimp API key here.</a></small>
					</td>
					
				</tr>

			</table>

			<?php submit_button(); ?>
		</form>

	<?php if($connected) { ?>
	<h3>Cache</h3>
	<p>The table below shows your cached MailChimp lists configuration. If you made any changes in your MailChimp configuration that is not yet represented in the table below, please renew the cache manually by hitting the "renew cached data" button.</p>

	<h4>Lists</h4>
	<table class="wp-list-table widefat">
		<thead>
			<tr>
				<th scope="col">ID</th><th scope="col">Name</th><th>Merge fields & Interest groupings</th>
			</tr>
		</thead>
		<tbody>
			<?php 
			if($lists && is_array($lists)) { ?>
				<?php foreach($lists as $list) {  ?>
					<tr valign="top">
						<td><?php echo $list->id; ?></td>
						<td><?php echo $list->name; ?></td>
						<td><em>Only available in the premium version. <a href="http://dannyvankooten.com/wordpress-plugins/mailchimp-for-wordpress/">Upgrade now.</a></em></td>
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

