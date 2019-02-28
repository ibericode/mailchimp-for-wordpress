<?php

defined('ABSPATH') or exit;

/**
 * @ignore
 */
function _mc4wp_admin_sidebar_support_notice()
{
    ?>
	<div class="mc4wp-box">
		<h4 class="mc4wp-title"><?php echo esc_html__('Looking for help?', 'mailchimp-for-wp'); ?></h4>
		<p><?php echo __('We have some resources available to help you in the right direction.', 'mailchimp-for-wp'); ?></p>
		<ul class="ul-square">
			<li><a href="https://kb.mc4wp.com/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=sidebar"><?php echo esc_html__('Knowledge Base', 'mailchimp-for-wp'); ?></a></li>
			<li><a href="https://wordpress.org/plugins/mailchimp-for-wp/faq/"><?php echo esc_html__('Frequently Asked Questions', 'mailchimp-for-wp'); ?></a></li>
		</ul>
		<p><?php echo sprintf(__('If your answer can not be found in the resources listed above, please use the <a href="%s">support forums on WordPress.org</a>.'), 'https://wordpress.org/support/plugin/mailchimp-for-wp'); ?></p>
		<p><?php echo sprintf(__('Found a bug? Please <a href="%s">open an issue on GitHub</a>.'), 'https://github.com/ibericode/mailchimp-for-wordpress/issues'); ?></p>
	</div>
	<?php
}

/**
 * @ignore
 */
function _mc4wp_admin_sidebar_other_plugins()
{
    echo '<div class="mc4wp-box">';
    echo '<h4 class="mc4wp-title">' . __('Other plugins by ibericode', 'mailchimp-for-wp') . '</h4>';

    echo '<ul style="margin-bottom: 0;">';

    // Boxzilla
    echo '<li>';
    echo sprintf('<strong><a href="%s">Boxzilla Pop-ups</a></strong><br />', 'https://boxzillaplugin.com/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=sidebar');
    echo  __('Pop-ups or boxes that slide-in with a newsletter sign-up form. A sure-fire way to grow your email lists.', 'mailchimp-for-wp');
    echo '</li>';

    // HTML Forms
    echo '<li>';
    echo sprintf('<strong><a href="%s">HTML Forms</a></strong><br />', 'https://www.htmlforms.io/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=sidebar');
    echo  __('Super flexible forms using native HTML. Just like with Mailchimp for WordPress forms but for other purposes, like a contact form.', 'mailchimp-for-wp');
    echo '</li>';

    echo '</ul>';
    echo '</div>';
}

add_action('mc4wp_admin_sidebar', '_mc4wp_admin_sidebar_other_plugins', 40);
add_action('mc4wp_admin_sidebar', '_mc4wp_admin_sidebar_support_notice', 50);

/**
 * Runs when the sidebar is outputted on Mailchimp for WordPress settings pages.
 *
 * Please note that not all pages have a sidebar.
 *
 * @since 3.0
 */
do_action('mc4wp_admin_sidebar');
