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
            'title'    => __('Add-ons', 'mailchimp-for-wp'),
            'text'     => __('Add-ons', 'mailchimp-for-wp'),
            'slug'     => 'extensions',
            'callback' => array( $this, 'show_extensions_page' ),
            'position' => 100,
        );

        return $items;
    }

    /**
     * Add text row to "Form > Appearance" tab.
     */
    public function after_form_appearance_settings_rows()
    {
        echo '<tr>';
        echo '<td colspan="2">';
        echo '<p class="description">';
        echo sprintf(__('Want to customize the style of your form? <a href="%s">Try our Styles Builder</a> & edit the look of your forms with just a few clicks.', 'mailchimp-for-wp'), 'https://www.mc4wp.com/premium-features/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=form-settings-link');
        echo '</p>';
        echo '</td>';
        echo '</tr>';
    }

    /**
     * Add text row to "Form > Settings" tab.
     */
    public function after_form_settings_rows()
    {
        echo '<tr>';
        echo '<td colspan="2">';
        echo '<p class="description">';

        if (rand(1, 2) === 1) {
            echo sprintf(__('Be notified whenever someone subscribes? <a href="%s">Mailchimp for WordPress Premium</a> allows you to set up email notifications for your forms.', 'mailchimp-for-wp'), 'https://www.mc4wp.com/premium-features/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=footer-link');
        } else {
            echo sprintf(__('Increased conversions? <a href="%s">Mailchimp for WordPress Premium</a> submits forms without reloading the entire page, resulting in a much better experience for your visitors.', 'mailchimp-for-wp'), 'https://www.mc4wp.com/premium-features/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=form-settings-link');
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
        $links[] = '<a href="https://www.mc4wp.com/premium-features/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=plugins-upgrade-link">' . __('Upgrade to Premium', 'mailchimp-for-wp') . '</a>';
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
                echo '<p class="description">' . sprintf(__('Do you want translated forms for all of your languages? <a href="%s">Try Mailchimp for WordPress Premium</a>, which does just that plus more.', 'mailchimp-for-wp'), 'https://www.mc4wp.com/premium-features/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=footer-link') . '</p>';
                return;
            }

            // General "edit form" message
            echo '<p class="description">' . sprintf(__('Do you want to create more than one form? Our Premium add-on does just that! <a href="%s">Have a look at all Premium benefits</a>.', 'mailchimp-for-wp'), 'https://www.mc4wp.com/premium-features/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=footer-link') . '</p>';
            return;
        }

        // General message
        echo '<p class="description">' . sprintf(__('Are you enjoying this plugin? The Premium add-on unlocks several powerful features. <a href="%s">Find out about all benefits now</a>.', 'mailchimp-for-wp'), 'https://www.mc4wp.com/premium-features/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=footer-link') . '</p>';
    }

    /**
     * Add email opt-in form to sidebar
     */
    public function admin_sidebar()
    {
        echo '<style>.mc4wp-premium-box {
  background: #fff8c5;
  border: 1px solid #d4a72c66;
  padding: 1em;
}</style>';
        echo '<div class="mc4wp-box">';
        echo '<div class="mc4wp-premium-box">';
        echo '<h3>Mailchimp for WordPress Premium</h3>';
        echo '<p>';
        echo 'You are currently using the free version of Mailchimp for WordPress. ';
        echo '</p>';
        echo '<p>';
        echo 'There is a Premium version of this plugin that adds several powerful features. Like multiple and improved sign-up forms, an easier way to visually enhance those forms, advanced e-commerce integration and keeping track of all sign-up attempts in your local WordPress database.';
        echo '</p>';
        echo '<p>You can have all those benefits for a small yearly fee. <a href="https://www.mc4wp.com/premium-features/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=upgrade-box">Take a look at Mailchimp for WordPress Premium here</a>.</p>';
        echo '</div>';
        echo '</div>';
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

        echo '<div class="mc4wp-margin-m">';
        echo '<h3>Advanced WooCommerce integration for Mailchimp</h3>';
        echo '<p>';
        echo __('Do you want to track all WooCommerce orders in Mailchimp so you can send emails based on the purchase activity of your subscribers?', 'mailchimp-for-wp');
        echo '</p>';
        echo '<p>';
        echo sprintf(__('<a href="%1$s">Upgrade to Mailchimp for WordPress Premium</a> or <a href="%2$s">read more about Mailchimp\'s E-Commerce features</a>.', 'mailchimp-for-wp') . '</p>', 'https://www.mc4wp.com/premium-features/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=other-settings-link', 'https://www.mc4wp.com/kb/what-is-ecommerce360/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=other-settings-link');
        echo '</p>';
        echo '</div>';
    }

    public function show_extensions_page()
    {
        require MC4WP_PLUGIN_DIR . '/includes/views/extensions.php';
    }
}
