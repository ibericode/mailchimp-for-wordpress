<?php

/**
 * Class MC4WP_Admin_Texts
 *
 * @ignore
 * @since 3.0
 */
class MC4WP_Admin_Texts
{
    /**
     * @var string
     */
    protected $plugin_file;

    /**
     * @param string $plugin_file
     */
    public function __construct($plugin_file)
    {
        $this->plugin_file = $plugin_file;
    }

    /**
     * Add hooks
     */
    public function add_hooks()
    {
        global $pagenow;

        add_filter('admin_footer_text', array( $this, 'footer_text' ));

        // Hooks for Plugins overview page
        if ($pagenow === 'plugins.php') {
            add_filter('plugin_action_links_' . $this->plugin_file, array( $this, 'add_plugin_settings_link' ), 10, 2);
            add_filter('plugin_row_meta', array( $this, 'add_plugin_meta_links' ), 10, 2);
        }
    }

    /**
     * Ask for a plugin review in the WP Admin footer, if this is one of the plugin pages.
     *
     * @param string $text
     *
     * @return string
     */
    public function footer_text($text)
    {
        if (! empty($_GET['page']) && strpos($_GET['page'], 'mailchimp-for-wp') === 0) {
            $text = sprintf('If you enjoy using <strong>Mailchimp for WordPress</strong>, please <a href="%s" target="_blank">leave us a ★★★★★ plugin review on WordPress.org</a>.', 'https://wordpress.org/support/plugin/mailchimp-for-wp/reviews/#new-post');
        }

        return $text;
    }

    /**
     * Add the settings link to the Plugins overview
     *
     * @param array $links
     * @param       $file
     *
     * @return array
     */
    public function add_plugin_settings_link($links, $file)
    {
        if ($file !== $this->plugin_file) {
            return $links;
        }

        $settings_link = sprintf('<a href="%s">%s</a>', admin_url('admin.php?page=mailchimp-for-wp'), esc_html__('Settings', 'mailchimp-for-wp'));
        array_unshift($links, $settings_link);
        return $links;
    }

    /**
     * Adds meta links to the plugin in the WP Admin > Plugins screen
     *
     * @param array $links
     * @param string $file
     *
     * @return array
     */
    public function add_plugin_meta_links($links, $file)
    {
        if ($file !== $this->plugin_file) {
            return $links;
        }

        $links[] = '<a href="https://www.mc4wp.com/kb/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=plugins-page">' . esc_html__('Documentation', 'mailchimp-for-wp') . '</a>';

        /**
         * Filters meta links shown on the Plugins overview page
         *
         * This takes an array of strings
         *
         * @since 3.0
         * @param array $links
         * @ignore
         */
        $links = apply_filters('mc4wp_admin_plugin_meta_links', $links);

        return $links;
    }
}
