<?php defined('ABSPATH') or exit; ?>
<div id="mc4wp-admin" class="wrap mc4wp-settings">
	<div class="mc4wp-row">
		<div class="main-content mc4wp-col">
			<h1 class="mc4wp-page-title">
				<?php echo esc_html__('Add new form', 'mailchimp-for-wp'); ?>
			</h1>
			<h2 style="display: none;"></h2><?php // fake h2 for admin notices ?>
			<div style="max-width: 480px;">
				<form method="post">
					<input type="hidden" name="_mc4wp_action" value="add_form" />
					<?php wp_nonce_field('_mc4wp_action', '_wpnonce'); ?>
					<div class="mc4wp-margin-s">
						<h3>
							<label>
								<?php echo esc_html__('What is the name of this form?', 'mailchimp-for-wp'); ?>
							</label>
						</h3>
						<input type="text" name="mc4wp_form[name]" class="widefat" value="" spellcheck="true" autocomplete="off" placeholder="<?php echo esc_attr__('Enter your form title..', 'mailchimp-for-wp'); ?>">
					</div>
					<div class="mc4wp-margin-s">
						<h3>
							<label>
								<?php echo esc_html__('To which Mailchimp audience should this form subscribe?', 'mailchimp-for-wp'); ?>
							</label>
						</h3>

						<?php
						if (! empty($lists)) {
							?>
						<ul id="mc4wp-lists">
							<?php
							foreach ($lists as $list) {
								?>
								<li>
									<label>
										<input type="checkbox" name="mc4wp_form[settings][lists][<?php echo esc_attr($list->id); ?>]" value="<?php echo esc_attr($list->id); ?>" <?php checked($number_of_lists, 1); ?> >
										<?php echo esc_html($list->name); ?>
									</label>
								</li>
								<?php
							}
							?>
						</ul>
							<?php
						} else {
							?>
						<p class="mc4wp-notice">
							<?php echo sprintf(wp_kses(__('No Mailchimp audiences found. Did you <a href="%s">connect with Mailchimp</a>?', 'mailchimp-for-wp'), array( 'a' => array( 'href' => array() ) )), admin_url('admin.php?page=mailchimp-for-wp')); ?>
						</p>
							<?php
						}
						?>
					</div>
					<?php submit_button(esc_html__('Add new form', 'mailchimp-for-wp')); ?>
				</form>
			</div>
			<?php require MC4WP_PLUGIN_DIR . '/includes/views/parts/admin-footer.php'; ?>
		</div>
		<div class="mc4wp-sidebar mc4wp-col">
			<?php require MC4WP_PLUGIN_DIR . '/includes/views/parts/admin-sidebar.php'; ?>
		</div>
	</div>
</div>
