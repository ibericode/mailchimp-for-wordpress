<?php defined('ABSPATH') or exit;
/** @var MC4WP_Integration $integration */
/** @var array $opts */
?>
<div id="mc4wp-admin" class="wrap mc4wp-settings">
    <p class="mc4wp-breadcrumbs">
        <span class="prefix"><?php echo esc_html__('You are here: ', 'mailchimp-for-wp'); ?></span>
        <a href="<?php echo esc_url(admin_url('admin.php?page=mailchimp-for-wp')); ?>">Mailchimp for WordPress</a> &rsaquo;
        <a href="<?php echo esc_url(admin_url('admin.php?page=mailchimp-for-wp-integrations')); ?>"><?php echo esc_html__('Integrations', 'mailchimp-for-wp'); ?></a> &rsaquo;
        <span class="current-crumb"><strong><?php echo esc_html($integration->name); ?></strong></span>
    </p>
    <div class="mc4wp-row">
        <div class="main-content mc4wp-col">
            <h1 class="mc4wp-page-title">
                <?php printf(esc_html__('%s integration', 'mailchimp-for-wp'), esc_html($integration->name)); ?>
            </h1>

            <h2 style="display: none;"></h2>
            <?php settings_errors(); ?>

            <div id="notice-additional-fields" class="notice notice-warning" style="display: none;">
                <p><?php echo esc_html__('The selected Mailchimp lists require non-default fields, which may prevent this integration from working.', 'mailchimp-for-wp'); ?></p>
                <p><?php echo sprintf(wp_kses(__('Please ensure you <a href="%1$s">configure the plugin to send all required fields</a> or <a href="%2$s">log into your Mailchimp account</a> and make sure only the email & name fields are marked as required fields for the selected list(s).', 'mailchimp-for-wp'), array( 'a' => array( 'href' => array() ) )), 'https://www.mc4wp.com/kb/send-additional-fields-from-integrations/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=integrations-page', 'https://admin.mailchimp.com/lists/'); ?></p>
            </div>

            <p>
                <?php echo esc_html($integration->description); ?>
            </p>

            <form method="post" action="<?php echo admin_url('options.php'); ?>">
                <?php settings_fields('mc4wp_integrations_settings'); ?>

                <?php

                /**
                 * Runs just before integration settings are outputted in admin.
                 *
                 * @since 3.0
                 *
                 * @param MC4WP_Integration $integration
                 * @param array $opts
                 * @ignore
                 */

                do_action('mc4wp_admin_before_integration_settings', $integration, $opts);

                /**
                 * @ignore
                 */
                do_action('mc4wp_admin_before_' . $integration->slug . '_integration_settings', $integration, $opts);
                ?>

                <table class="form-table">
                    <?php
                    if ($integration->has_ui_element('enabled')) {
                        ?>
                        <tbody>
	                    <tr valign="top">
	                        <th scope="row"><?php echo esc_html__('Enabled?', 'mailchimp-for-wp'); ?></th>
	                        <td class="nowrap integration-toggles-wrap">
	                            <label><input type="radio" name="mc4wp_integrations[<?php echo $integration->slug; ?>][enabled]" value="1" <?php checked($opts['enabled'], 1); ?> /> <?php echo esc_html__('Yes', 'mailchimp-for-wp'); ?></label> &nbsp;
	                            <label><input type="radio" name="mc4wp_integrations[<?php echo $integration->slug; ?>][enabled]" value="0" <?php checked($opts['enabled'], 0); ?> /> <?php echo esc_html__('No', 'mailchimp-for-wp'); ?></label>
	                            <p class="description"><?php echo sprintf(esc_html__('Enable the %s integration? This will add a sign-up checkbox to the form.', 'mailchimp-for-wp'), $integration->name); ?></p>
	                        </td>
	                    </tr>
	                	</tbody>
                        <?php
                    }
                    ?>

                    <?php
                    $config = array(
                        'element' => 'mc4wp_integrations[' . $integration->slug . '][enabled]',
                        'value'   => '1',
                        'hide'    => false,
                    );
                    ?>
                    <tbody class="integration-toggled-settings" data-showif="<?php echo esc_attr(json_encode($config)); ?>">

                    <?php
                    if ($integration->has_ui_element('implicit')) {
                        ?>
                        <tr valign="top">
                            <th scope="row"><?php echo esc_html__('Implicit?', 'mailchimp-for-wp'); ?></th>
                            <td class="nowrap">
                                <label><input type="radio" name="mc4wp_integrations[<?php echo $integration->slug; ?>][implicit]" value="1" <?php checked($opts['implicit'], 1); ?> /> <?php echo esc_html__('Yes', 'mailchimp-for-wp'); ?></label> &nbsp;
                                <label><input type="radio" name="mc4wp_integrations[<?php echo $integration->slug; ?>][implicit]" value="0" <?php checked($opts['implicit'], 0); ?> /> <?php echo esc_html__('No', 'mailchimp-for-wp'); ?> <?php echo '<em>', esc_html__('(recommended)', 'mailchimp-for-wp'), '</em>'; ?>
                            </label>
                                <p class="description">
                                    <?php
                                    echo esc_html__('Select "yes" if you want to subscribe people without asking them explicitly.', 'mailchimp-for-wp');
                                    echo '<br />';

                                    echo sprintf(
                                        wp_kses(
                                            __('<strong>Warning: </strong> enabling this may affect your <a href="%s">GDPR compliance</a>.', 'mailchimp-for-wp'),
                                            array(
                                            'a' => array( 'href' => array() ),
                                            'strong' => array(),
                                            )
                                        ),
                                        'https://www.mc4wp.com/kb/gdpr-compliance/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=integrations-page'
                                    );
                                    ?>
                                    </p>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>

                    <?php
                    if ($integration->has_ui_element('lists')) {
                        ?>
                        <?php // hidden input to make sure a value is sent to the server when no checkboxes were selected ?>
                        <input type="hidden" name="mc4wp_integrations[<?php echo $integration->slug; ?>][lists][]" value="" />
                        <tr valign="top">
                            <th scope="row"><?php echo esc_html__('Mailchimp Lists', 'mailchimp-for-wp'); ?></th>
                            <?php
                            if (! empty($lists)) {
                                echo '<td>';
                                echo '<ul style="margin-bottom: 20px; max-height: 300px; overflow-y: auto;">';
                                foreach ($lists as $list) {
                                	$checked = checked(in_array($list->id, $opts['lists'], true), true, false);
                                	$value = esc_attr($list->id);
                                    echo '<li><label>';
                                    echo "<input type=\"checkbox\" name=\"mc4wp_integrations[{$integration->slug}][lists][]\" value=\"{$value}\" class=\"mc4wp-list-input\" {$checked}> ";
                                    echo esc_html($list->name);
                                    echo '</label></li>';
                                }
                                echo '</ul>';

                                echo '<p class="description">';
                                echo esc_html__('Select the list(s) to which people who check the checkbox should be subscribed.', 'mailchimp-for-wp');
                                echo '</p>';
                                echo '</td>';
                            } else {
                                echo '<td>', sprintf(wp_kses(__('No lists found, <a href="%s">are you connected to Mailchimp</a>?', 'mailchimp-for-wp'), array( 'a' => array( 'href' => array() ) )), esc_url(admin_url('admin.php?page=mailchimp-for-wp'))), '</td>';
                            }
                            ?>
                        </tr>
                        <?php
                    } // end if UI has lists
                    ?>

                    <?php
                    if ($integration->has_ui_element('label')) {
                        $config = array(
                            'element' => 'mc4wp_integrations[' . $integration->slug . '][implicit]',
                            'value'   => 0,
                        );
                        ?>
                        <tr valign="top" data-showif="<?php echo esc_attr(json_encode($config)); ?>">
                            <th scope="row"><label for="mc4wp_checkbox_label"><?php echo esc_html__('Checkbox label text', 'mailchimp-for-wp'); ?></label></th>
                            <td>
                                <input type="text"  class="widefat" id="mc4wp_checkbox_label" name="mc4wp_integrations[<?php echo $integration->slug; ?>][label]" value="<?php echo esc_attr($opts['label']); ?>" required />
                                <p class="description"><?php printf(esc_html__('HTML tags like %s are allowed in the label text.', 'mailchimp-for-wp'), '<code>' . esc_html('<strong><em><a>') . '</code>'); ?></p>
                            </td>
                        </tr>
                        <?php
                    } // end if UI label
                    ?>


                    <?php
                    if ($integration->has_ui_element('precheck')) {
                        $config = array(
                            'element' => 'mc4wp_integrations[' . $integration->slug . '][implicit]',
                            'value'   => 0,
                        );
                        ?>
                        <tr valign="top" data-showif="<?php echo esc_attr(json_encode($config)); ?>">
                            <th scope="row"><?php echo esc_html__('Pre-check the checkbox?', 'mailchimp-for-wp'); ?></th>
                            <td class="nowrap">
                                <label><input type="radio" name="mc4wp_integrations[<?php echo $integration->slug; ?>][precheck]" value="1" <?php checked($opts['precheck'], 1); ?> /> <?php echo esc_html__('Yes', 'mailchimp-for-wp'); ?></label> &nbsp;
                                <label><input type="radio" name="mc4wp_integrations[<?php echo $integration->slug; ?>][precheck]" value="0" <?php checked($opts['precheck'], 0); ?> /> <?php echo esc_html__('No', 'mailchimp-for-wp'); ?> <?php echo '<em>' . __('(recommended)', 'mailchimp-for-wp') . '</em>'; ?></label>
                                <p class="description">
                                    <?php
                                    echo esc_html__('Select "yes" if the checkbox should be pre-checked.', 'mailchimp-for-wp');
                                    echo '<br />';
                                    echo sprintf(
                                        wp_kses(
                                            __('<strong>Warning: </strong> enabling this may affect your <a href="%s">GDPR compliance</a>.', 'mailchimp-for-wp'),
                                            array(
                                                    'a'      => array( 'href' => array() ),
                                                    'strong' => array(),
                                            )
                                        ),
                                        'https://www.mc4wp.com/kb/gdpr-compliance/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=integrations-page'
                                    );
                                    ?>
                                </p>
                            </td>
                        <?php
                    } // end if UI precheck
                    ?>

                    <?php
                    if ($integration->has_ui_element('css')) {
                        $config = array(
                            'element' => 'mc4wp_integrations[' . $integration->slug . '][implicit]',
                            'value'   => 0,
                        );
                        ?>
                        <tr valign="top" data-showif="<?php echo esc_attr(json_encode($config)); ?>">
                            <th scope="row"><?php echo esc_html__('Load some default CSS?', 'mailchimp-for-wp'); ?></th>
                            <td class="nowrap">
                                <label><input type="radio" name="mc4wp_integrations[<?php echo $integration->slug; ?>][css]" value="1" <?php checked($opts['css'], 1); ?> />&rlm; <?php echo esc_html__('Yes', 'mailchimp-for-wp'); ?></label> &nbsp;
                                <label><input type="radio" name="mc4wp_integrations[<?php echo $integration->slug; ?>][css]" value="0" <?php checked($opts['css'], 0); ?> />&rlm; <?php echo esc_html__('No', 'mailchimp-for-wp'); ?></label>
                                <p class="description"><?php echo esc_html__('Select "yes" if the checkbox appears in a weird place.', 'mailchimp-for-wp'); ?></p>
                            </td>
                        </tr>
                        <?php
                    } // end if UI css
                    ?>

                    <?php
                    if ($integration->has_ui_element('double_optin')) {
                        ?>
                        <tr valign="top">
                            <th scope="row"><?php echo esc_html__('Double opt-in?', 'mailchimp-for-wp'); ?></th>
                            <td class="nowrap">
                                <label>
                                    <input type="radio" name="mc4wp_integrations[<?php echo $integration->slug; ?>][double_optin]" value="1" <?php checked($opts['double_optin'], 1); ?> />&rlm;
                                    <?php echo esc_html__('Yes', 'mailchimp-for-wp'); ?>
                                </label> &nbsp;
                                <label>
                                    <input type="radio" id="mc4wp_checkbox_double_optin_0" name="mc4wp_integrations[<?php echo $integration->slug; ?>][double_optin]" value="0" <?php checked($opts['double_optin'], 0); ?> />&rlm;
                                    <?php echo esc_html__('No', 'mailchimp-for-wp'); ?>
                                </label>
                                <p class="description">
                                    <?php echo esc_html__('Select "yes" if you want people to confirm their email address before being subscribed (recommended)', 'mailchimp-for-wp'); ?>
                                </p>
                            </td>
                        </tr>
                        <?php
                    } // end if UI double_optin
                    ?>

                    <?php
                    if ($integration->has_ui_element('update_existing')) {
                        ?>
	                    <tr valign="top">
	                        <th scope="row"><?php echo esc_html__('Update existing subscribers?', 'mailchimp-for-wp'); ?></th>
	                        <td class="nowrap">
	                            <label>
	                                <input type="radio" name="mc4wp_integrations[<?php echo $integration->slug; ?>][update_existing]" value="1" <?php checked($opts['update_existing'], 1); ?> />&rlm;
	                                <?php echo esc_html__('Yes', 'mailchimp-for-wp'); ?>
	                            </label> &nbsp;
	                            <label>
	                                <input type="radio" name="mc4wp_integrations[<?php echo $integration->slug; ?>][update_existing]" value="0" <?php checked($opts['update_existing'], 0); ?> />&rlm;
	                                <?php echo esc_html__('No', 'mailchimp-for-wp'); ?>
	                            </label>
	                            <p class="description"><?php echo esc_html__('Select "yes" if you want to update existing subscribers with the data that is sent.', 'mailchimp-for-wp'); ?></p>
	                        </td>
	                    </tr>
                        <?php
                    } // end if UI update_existing
                    ?>

                    <?php
                    if ($integration->has_ui_element('replace_interests')) {
                        $config = array(
                            'element' => 'mc4wp_integrations[' . $integration->slug . '][update_existing]',
                            'value'   => 1,
                        );
                        ?>
                        <tr valign="top" data-showif="<?php echo esc_attr(json_encode($config)); ?>">
                            <th scope="row"><?php echo esc_html__('Replace interest groups?', 'mailchimp-for-wp'); ?></th>
                            <td class="nowrap">
                                <label>
                                    <input type="radio" name="mc4wp_integrations[<?php echo $integration->slug; ?>][replace_interests]" value="1" <?php checked($opts['replace_interests'], 1); ?> />&rlm;
                                    <?php echo esc_html__('Yes', 'mailchimp-for-wp'); ?>
                                </label> &nbsp;
                                <label>
                                    <input type="radio" name="mc4wp_integrations[<?php echo $integration->slug; ?>][replace_interests]" value="0" <?php checked($opts['replace_interests'], 0); ?> />&rlm;
                                    <?php echo esc_html__('No', 'mailchimp-for-wp'); ?>
                                </label>
                                <p class="description">
                                    <?php echo esc_html__('Select "no" if you want to add the selected interests to any previously selected interests when updating a subscriber.', 'mailchimp-for-wp'); ?>
                                    <?php echo sprintf('<a href="%s" target="_blank">' . esc_html__('What does this do?', 'mailchimp-for-wp') . '</a>', 'https://www.mc4wp.com/kb/what-does-replace-groupings-mean/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=integrations-page'); ?>
                                </p>
                            </td>
                        </tr>
                        <?php
                    } // end if UI replace_interests
                    ?>

                    </tbody>
                </table>

                <?php

                /**
                 * Runs right after integration settings are outputted (before the submit button).
                 *
                 * @param MC4WP_Integration $integration
                 * @param array $opts
                 * @ignore
                 */
                do_action('mc4wp_admin_after_integration_settings', $integration, $opts);

                /**
                 * @ignore
                 */
                do_action('mc4wp_admin_after_' . $integration->slug . '_integration_settings', $integration, $opts);
                ?>

                <?php
                if (count($integration->get_ui_elements()) > 0) {
                    submit_button();
                }
                ?>

            </form>
        </div>

        <div class="mc4wp-sidebar mc4wp-col">
            <?php require MC4WP_PLUGIN_DIR . '/includes/views/parts/admin-sidebar.php'; ?>
        </div>
    </div>
</div>
