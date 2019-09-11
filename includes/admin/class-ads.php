<?php

/**
 * Class MC4WP_Admin_Ads
 *
 * @ignore
 * @access private
 */
class MC4WP_Admin_Ads
{

    /**
     * @return bool Adds hooks
     */
    public function add_hooks()
    {

        // don't hook if Premium is activated
        if (defined('MC4WP_PREMIUM_VERSION')) {
            return false;
        }

        add_filter('mc4wp_admin_plugin_meta_links', array( $this, 'plugin_meta_links' ));
        add_action('mc4wp_admin_form_after_behaviour_settings_rows', array( $this, 'after_form_settings_rows' ));
        add_action('mc4wp_admin_form_after_appearance_settings_rows', array( $this, 'after_form_appearance_settings_rows' ));
        add_action('mc4wp_admin_sidebar', array( $this, 'admin_sidebar' ));
        add_action('mc4wp_admin_footer', array( $this, 'admin_footer' ));
        add_action('mc4wp_admin_other_settings', array( $this, 'ecommerce' ), 90);

        add_filter('mc4wp_admin_menu_items', array( $this, 'add_menu_item' ));

        add_action('mc4wp_admin_after_woocommerce_integration_settings', array( $this, 'ecommerce' ));
        return true;
    }

    public function add_menu_item($items)
    {
        $items['extensions'] = array(
            'title' => __('Add-ons', 'mailchimp-for-wp'),
            'text' => __('Add-ons', 'mailchimp-for-wp'),
            'slug' => 'extensions',
            'callback' => array( $this, 'show_extensions_page' ),
            'position' => 100
        );

        return $items;
    }

    /**
     * Add text row to "Form > Appearance" tab.
     */
    public function after_form_appearance_settings_rows()
    {
        echo '<tr valign="top">';
        echo '<td colspan="2">';
        echo '<p class="help">';
        echo sprintf(__('Want to customize the style of your form? <a href="%s">Try our Styles Builder</a> & edit the look of your forms with just a few clicks.', 'mailchimp-for-wp'), 'https://mc4wp.com/premium-features/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=form-settings-link');
        echo '</p>';
        echo '</td>';
        echo '</tr>';
    }

    /**
     * Add text row to "Form > Settings" tab.
     */
    public function after_form_settings_rows()
    {
        echo '<tr valign="top">';
        echo '<td colspan="2">';
        echo '<p class="help">';

        if (rand(1, 2) === 1) {
            echo sprintf(__('Be notified whenever someone subscribes? <a href="%s">Mailchimp for WordPress Premium</a> allows you to set up email notifications for your forms.', 'mailchimp-for-wp'), 'https://mc4wp.com/premium-features/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=footer-link');
        } else {
            echo sprintf(__('Increased conversions? <a href="%s">Mailchimp for WordPress Premium</a> submits forms without reloading the entire page, resulting in a much better experience for your visitors.', 'mailchimp-for-wp'), 'https://mc4wp.com/premium-features/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=form-settings-link');
        }

        echo '</p>';
        echo '</td>';
        echo '</tr>';
    }

    /**
     * @param array $links
     *
     * @return array
     */
    public function plugin_meta_links($links)
    {
        $links[] = '<a href="https://mc4wp.com/premium-features/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=plugins-upgrade-link">' . __('Upgrade to Premium', 'mailchimp-for-wp') . '</a>';
        return $links;
    }

    /**
     * Add several texts to admin footer.
     */
    public function admin_footer()
    {
        if (isset($_GET['view']) && $_GET['view'] === 'edit-form') {

            // WPML & Polylang specific message
            if (defined('ICL_LANGUAGE_CODE')) {
                echo '<p class="help">' . sprintf(__('Do you want translated forms for all of your languages? <a href="%s">Try Mailchimp for WordPress Premium</a>, which does just that plus more.', 'mailchimp-for-wp'), 'https://mc4wp.com/premium-features/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=footer-link') . '</p>';
                return;
            }

            // General "edit form" message
            echo '<p class="help">' . sprintf(__('Do you want to create more than one form? Our Premium add-on does just that! <a href="%s">Have a look at all Premium benefits</a>.', 'mailchimp-for-wp'), 'https://mc4wp.com/premium-features/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=footer-link') . '</p>';
            return;
        }

        // General message
        echo '<p class="help">' . sprintf(__('Are you enjoying this plugin? The Premium add-on unlocks several powerful features. <a href="%s">Find out about all benefits now</a>.', 'mailchimp-for-wp'), 'https://mc4wp.com/premium-features/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=footer-link') . '</p>';
    }

    /**
     * Add email opt-in form to sidebar
     */
    public function admin_sidebar()
    {
        echo '<div class="mc4wp-box">';
        echo '<div style="border: 5px dotted #cc4444; padding: 0 20px; background: white;">';
        echo '<h3>Mailchimp for WordPress Premium</h3>';
        echo '<p>This plugin has a Premium add-on, unlocking several powerful features. <a href="https://mc4wp.com/premium-features/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=upgrade-box">Have a look at its benefits</a>!</p>';
        echo '</div>';
        echo '</div>'; ?>
		<div class="mc4wp-box" id="mc4wp-optin-box">

			<?php $user = wp_get_current_user(); ?>
			<!-- Begin Mailchimp Signup Form -->
			<div id="mc_embed_signup">
				<h4 class="mc4wp-title"><?php _e('More subscribers, better newsletters.', 'mailchimp-for-wp'); ?></h4>
				<p><?php _e('Learn how to best grow your lists & write better emails by subscribing to our monthly tips.', 'mailchimp-for-wp'); ?></p>
				<form action="//mc4wp.us1.list-manage.com/subscribe/post?u=a2d08947dcd3683512ce174c5&amp;id=a940232df9" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" target="_blank">
					<p>
						<label for="mc4wp-email"><?php _e('Email Address', 'mailchimp-for-wp'); ?></label>
						<input type="email" value="<?php echo esc_attr($user->user_email); ?>" name="EMAIL" class="regular-text" id="mc4wp-email" required>
					</p>
					<p>
						<label for="mc4wp-fname"><?php _e('First Name', 'mailchimp-for-wp'); ?></label>
						<input type="text" value="<?php echo esc_attr($user->user_firstname); ?>" name="FNAME" class="regular-text" id="mc4wp-fname">
					</p>
					<div style="position: absolute; left: -5000px;">
						<input type="text" name="b_a2d08947dcd3683512ce174c5_a940232df9" tabindex="-1" value="" autocomplete="off" />
					</div>
					<p>
						<input type="submit" value="<?php esc_attr_e('Subscribe', 'mailchimp-for-wp'); ?>" name="subscribe" class="button">
					</p>

					<input type="hidden" name="SOURCE" value="free-plugin" />
				</form>
			</div>
		</div>
		<?php
    }

    /**
     * Show notice about E-Commerce integration in Premium.
     */
    public function ecommerce()
    {

        // detect whether WooCommerce is installed & activated.
        if (! class_exists('WooCommerce')) {
            return;
        }

        echo '<div class="medium-margin">';
        echo '<h3>Advanced WooCommerce integration for Mailchimp</h3>';
        echo '<p>';
        echo __('Do you want to track all WooCommerce orders in Mailchimp so you can send emails based on the purchase activity of your subscribers?', 'mailchimp-for-wp');
        echo '</p>';
        echo '<p>';
        echo sprintf(__('<a href="%s">Upgrade to Mailchimp for WordPress Premium</a> or <a href="%s">read more about Mailchimp\'s E-Commerce features</a>.', 'mailchimp-for-wp') . '</p>', 'https://mc4wp.com/premium-features/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=other-settings-link', 'https://kb.mc4wp.com/what-is-ecommerce360/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=other-settings-link');
        echo '</p>';
        echo '</div>';
    }

    public function show_extensions_page()
    {
        require MC4WP_PLUGIN_DIR . 'includes/views/extensions.php';
    }
}
