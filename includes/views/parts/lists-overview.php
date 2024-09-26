<h3><?php echo esc_html__('Your Mailchimp Account', 'mailchimp-for-wp'); ?></h3>
<p><?php echo esc_html__('The table below shows your Mailchimp audiences and their details. If you just applied changes to your Mailchimp account, please use the following button to renew the cache.', 'mailchimp-for-wp'); ?></p>

<div id="mc4wp-list-fetcher">
	<form method="post">
		<input type="hidden" name="_mc4wp_action" value="empty_lists_cache" />
		<p>
			<input type="submit" value="<?php echo esc_attr__('Renew Mailchimp audiences', 'mailchimp-for-wp'); ?>" class="button">
		</p>
	</form>
</div>

<div class="mc4wp-lists-overview">
	<?php
	if (empty($lists)) {
		?>
		<p><?php echo esc_html__('No audiences were found in your Mailchimp account', 'mailchimp-for-wp'); ?>.</p>
		<?php
	} else {
		echo '<p>', sprintf(esc_html__('A total of %d audiences were found in your Mailchimp account.', 'mailchimp-for-wp'), count($lists)), '</p>';
		echo '<table class="widefat striped" id="mc4wp-mailchimp-lists-overview">';

		$headings = array(
			esc_html__('Audience name', 'mailchimp-for-wp'),
			esc_html__('Audience ID', 'mailchimp-for-wp'),
			esc_html__('# of contacts', 'mailchimp-for-wp'),
		);

		echo '<thead>';
		echo '<tr>';
		foreach ($headings as $heading) {
			echo '<th>', $heading, '</th>';
		}
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';

		foreach ($lists as $list) {
			$attr_data_list_id = esc_attr($list->id);
			$list_name = esc_html($list->name);
			echo '<tr>';
			echo '<td><a href="#" class="mc4wp-mailchimp-list" data-list-id="', $attr_data_list_id, '">', $list_name, '</a><span class="row-actions alignright"></span></td>';
			echo '<td><code>', esc_html($list->id), '</code></td>';
			echo '<td>', esc_html($list->stats->member_count), '</td>';
			echo '</tr>';

			echo '<tr class="list-details list-', $list->id, '-details" style="display: none;">';
			echo '<td colspan="3" style="padding: 0 20px 40px;">';
			echo '<p class="alignright" style="margin: 20px 0;"><a href="https://admin.mailchimp.com/audience/contacts/?id=', $list->web_id, '" target="_blank"><span class="dashicons dashicons-edit"></span> ', esc_html__('Edit this audience in Mailchimp', 'mailchimp-for-wp'), '</a></p>';
			echo '<div><div>', esc_html__('Loading... Please wait.', 'mailchimp-for-wp'), '</div></div>';
			echo '</td>';
			echo '</tr>';
			?>
			<?php
		} // end foreach $lists
		echo '</tbody>';
		echo '</table>';
	} // end if empty
	?>
</div>
