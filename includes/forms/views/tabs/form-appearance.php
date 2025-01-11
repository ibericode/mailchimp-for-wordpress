<?php

$theme       = wp_get_theme();
$css_options = [
    '0'                                     => sprintf(esc_html__('Inherit from %s theme', 'mailchimp-for-wp'), $theme->Name),
    'basic'                                 => esc_html__('Basic', 'mailchimp-for-wp'),
    esc_html__('Form Themes', 'mailchimp-for-wp') => [
        'theme-light' => esc_html__('Light Theme', 'mailchimp-for-wp'),
        'theme-dark'  => esc_html__('Dark Theme', 'mailchimp-for-wp'),
        'theme-red'   => esc_html__('Red Theme', 'mailchimp-for-wp'),
        'theme-green' => esc_html__('Green Theme', 'mailchimp-for-wp'),
        'theme-blue'  => esc_html__('Blue Theme', 'mailchimp-for-wp'),
    ],
];

/**
 * Filters the <option>'s in the "CSS Stylesheet" <select> box.
 */
$css_options = apply_filters('mc4wp_admin_form_css_options', $css_options);

?>

<h2><?php echo esc_html__('Form Appearance', 'mailchimp-for-wp'); ?></h2>

<table class="form-table">
    <tr valign="top">
        <th scope="row"><label for="mc4wp_load_stylesheet_select"><?php echo esc_html__('Form Style', 'mailchimp-for-wp'); ?></label></th>
        <td class="nowrap valigntop">
            <select name="mc4wp_form[settings][css]" id="mc4wp_load_stylesheet_select">

                <?php
                foreach ($css_options as $key => $option) {
                    if (is_array($option)) {
                        $label   = $key;
                        $options = $option;
                        printf('<optgroup label="%s">', $label);
                        foreach ($options as $key => $option) {
                            printf('<option value="%s" %s>%s</option>', $key, selected($opts['css'], $key, false), $option);
                        }
                        print( '</optgroup>' );
                    } else {
                        printf('<option value="%s" %s>%s</option>', $key, selected($opts['css'], $key, false), $option);
                    }
                }
                ?>
            </select>
            <p class="description">
                <?php echo esc_html__('If you want to load some default CSS styles, select "basic formatting styles" or choose one of the color themes', 'mailchimp-for-wp'); ?>
            </p>
        </td>
    </tr>

    <?php do_action('mc4wp_admin_form_after_appearance_settings_rows', $opts, $form); ?>

</table>

<?php submit_button(); ?>
