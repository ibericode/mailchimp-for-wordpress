<?php defined('ABSPATH') or exit;

$tabs = array(
    'fields'     => esc_html__('Fields', 'mailchimp-for-wp'),
    'messages'   => esc_html__('Messages', 'mailchimp-for-wp'),
    'settings'   => esc_html__('Settings', 'mailchimp-for-wp'),
    'appearance' => esc_html__('Appearance', 'mailchimp-for-wp'),
);

/**
 * Filters the setting tabs on the "edit form" screen.
 *
 * @param array $tabs
 * @ignore
 */
$tabs = apply_filters('mc4wp_admin_edit_form_tabs', $tabs);

?>
<div id="mc4wp-admin" class="wrap mc4wp-settings">

    <p class="mc4wp-breadcrumbs">
        <span class="prefix"><?php echo esc_html__('You are here: ', 'mailchimp-for-wp'); ?></span>
        <a href="<?php echo esc_url(admin_url('admin.php?page=mailchimp-for-wp')); ?>">Mailchimp for WordPress</a> &rsaquo;
        <a href="<?php echo esc_url(admin_url('admin.php?page=mailchimp-for-wp-forms')); ?>"><?php echo esc_html__('Forms', 'mailchimp-for-wp'); ?></a>
        &rsaquo;
        <span class="current-crumb"><strong><?php echo esc_html__('Form', 'mailchimp-for-wp'); ?> <?php echo esc_html($form_id); ?>
                | <?php echo esc_html($form->name); ?></strong></span>
    </p>

    <div>
        <h1 class="mc4wp-page-title">
            <?php echo esc_html__('Edit Form', 'mailchimp-for-wp'); ?>

            <?php

            /**
             * @ignore
             */

            do_action('mc4wp_admin_edit_form_after_title');
            ?>
        </h1>

        <?php // fake h2 for admin notices ?>
        <h2 style="display: none;"></h2>

        <?php // wrap entire page in <form> element ?>
        <form method="post">
            <?php // default submit button to prevent opening preview ?>
            <input type="submit" style="display: none;" />
            <input type="hidden" name="_mc4wp_action" value="edit_form"/>
            <?php wp_nonce_field('_mc4wp_action', '_wpnonce'); ?>
            <input type="hidden" name="mc4wp_form_id" value="<?php echo esc_attr($form->ID); ?>"/>

            <div id="titlediv" class="mc4wp-margin-s">
                <div id="titlewrap">
                    <label class="screen-reader-text"
                            for="title"><?php echo esc_html__('Enter form title here', 'mailchimp-for-wp'); ?></label>
                    <input type="text" name="mc4wp_form[name]" size="30"
                            value="<?php echo esc_attr($form->name); ?>" id="title" spellcheck="true"
                            autocomplete="off"
                            placeholder="<?php echo esc_html__('Enter the title of your sign-up form', 'mailchimp-for-wp'); ?>"
                            style="line-height: initial;">
                </div>
                <div>
                    <?php echo sprintf(esc_html__('Use the shortcode %s to display this form inside a post, page or text widget.', 'mailchimp-for-wp'), '<input type="text" onfocus="this.select();" readonly="readonly" value="' . esc_attr(sprintf('[mc4wp_form id=%d]', $form->ID)) . '" size="' . ( strlen($form->ID) + 15 ) . '">'); ?>
                </div>
            </div>


            <div>
                <h2 class="nav-tab-wrapper" id="mc4wp-tabs-nav">
                    <?php
                    foreach ($tabs as $tab => $name) {
                        $class = ( $active_tab === $tab ) ? 'nav-tab-active' : '';
                        $href = esc_attr($this->tab_url($tab));
                        echo "<a class=\"nav-tab nav-tab-{$tab} {$class}\" data-tab=\"{$tab}\" href=\"{$href}\">{$name}</a>";
                    }
                    ?>
                </h2>

                <div id="mc4wp-tabs">

                    <?php
                    foreach ($tabs as $tab => $name) {
                        $class = ( $active_tab === $tab ) ? 'mc4wp-tab-active' : '';
                        echo "<div class=\"mc4wp-tab {$class}\" id=\"mc4wp-tab-{$tab}\">";

                        /**
                         * Runs when outputting a tab section on the "edit form" screen
                         *
                         * @param string $tab
                         * @ignore
                         */
                        do_action('mc4wp_admin_edit_form_output_' . $tab . '_tab', $opts, $form);

                        $tab_file = __DIR__ . '/tabs/form-' . $tab . '.php';
                        if (file_exists($tab_file)) {
                            include $tab_file;
                        }

                        // end of .tab
                        echo '</div>';
                    } // foreach tabs
                    ?>

                </div>
            </div>
        </form>

        <?php require MC4WP_PLUGIN_DIR . '/includes/views/parts/admin-footer.php'; ?>
    </div>
</div>
