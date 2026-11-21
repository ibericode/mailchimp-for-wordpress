<?php

defined('ABSPATH') || exit;


$opts = $opts ?? [];
$opts = true === is_array($opts) ?
    $opts :
    [];

$site_key               = $opts['site_key'] ?? '';
$secret_key             = $opts['secret_key'] ?? '';
$enabled                = $opts['enabled'] ?? '0';
$display_for_authorized = $opts['display_for_authorized'] ?? '0';

?>

<?php
if ('1' === $enabled) {
    ?>
<p>
    <?php echo esc_html__('Preview: if the site key is valid, you should be able to complete the captcha below:', 'mailchimp-for-wp'); ?>
</p>
    <?php
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in print_captcha_element().
    echo MC4WP_InfoPeak_Captcha::get_instance()->print_captcha_element(true);
}
?>

<input class="infopeak-captcha__enabled-setting" type="hidden" name="mc4wp_integrations[infopeak-captcha][enabled]" value="<?php echo esc_attr($enabled); ?>">

<table class="form-table">
<tbody>
<tr valign="top">
    <th scope="row"><?php echo esc_html__('Site Key', 'mailchimp-for-wp'); ?></th>
    <td class="nowrap integration-toggles-wrap">
        <label>
            <input class="widefat infopeak-captcha__site-key" type="text" name="mc4wp_integrations[infopeak-captcha][site_key]"
                    placeholder="<?php echo esc_attr__('Enter your site key', 'mailchimp-for-wp'); ?>"
                    value="<?php echo esc_attr($site_key); ?>">
        </label>
        <p class="description">
        <?php
            printf(
                // translators: %1$s: opening anchor tag, %2$s: closing anchor tag
                esc_html__('The site key and secret key of your InfoPeak Captcha site. %1$s Create a free account to get them here %2$s', 'mailchimp-for-wp'),
                '<a href="https://accounts.infopeak.io/signup?plan=captcha" target="_blank">',
                '</a>'
            );
            ?>
        </p>
    </td>
</tr>
<tr valign="top">
    <th scope="row"><?php echo esc_html__('Secret Key', 'mailchimp-for-wp'); ?></th>
    <td class="nowrap integration-toggles-wrap">
        <label>
            <input class="widefat infopeak-captcha__secret-key" type="password" name="mc4wp_integrations[infopeak-captcha][secret_key]"
                    placeholder="<?php echo esc_attr__('Enter your secret key', 'mailchimp-for-wp'); ?>"
                    value="<?php echo esc_attr($secret_key); ?>">
        </label>
    </td>
</tr>
<tr valign="top">
    <th scope="row"><?php echo esc_html__('Display for authorized users', 'mailchimp-for-wp'); ?></th>
    <td class="nowrap integration-toggles-wrap">
        <label>
            <input type="radio" name="mc4wp_integrations[infopeak-captcha][display_for_authorized]" value="1" <?php checked($display_for_authorized, '1'); ?> />&rlm;
            <?php echo esc_html__('Yes', 'mailchimp-for-wp'); ?>
        </label> &nbsp;
        <label>
            <input type="radio" name="mc4wp_integrations[infopeak-captcha][display_for_authorized]" value="0" <?php checked($display_for_authorized, '0'); ?> />&rlm;
            <?php echo esc_html__('No', 'mailchimp-for-wp'); ?>
        </label>
        <p class="description"><?php echo esc_html__('Select "yes" to require the captcha even from authorized users.', 'mailchimp-for-wp'); ?></p>
    </td>
</tr>
</tbody>
</table>

<script>
    (function () {
        var enabledInput = document.querySelector('.infopeak-captcha__enabled-setting');
        enabledInput.form.addEventListener('submit', function () {
            var siteKey = enabledInput.form.querySelector('.infopeak-captcha__site-key').value.trim();
            var secretKey = enabledInput.form.querySelector('.infopeak-captcha__secret-key').value.trim();

            enabledInput.value = '' !== siteKey && '' !== secretKey ? '1' : '0';
        });
    })();
</script>
