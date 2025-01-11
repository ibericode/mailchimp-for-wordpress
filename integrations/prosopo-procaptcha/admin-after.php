<?php

$opts = $opts ?? [];
$opts = true === is_array($opts) ?
    $opts :
    [];

$site_key               = $opts['site_key'] ?? '';
$secret_key             = $opts['secret_key'] ?? '';
$enabled                = $opts['enabled'] ?? '0';
$display_for_authorized = $opts['display_for_authorized'] ?? '0';
$theme                  = $opts['theme'] ?? '';
$type                   = $opts['type'] ?? '';

$theme_options = [
    'light' => esc_html__('Light', 'mailchimp-for-wp'),
    'dark' => esc_html__('Dark', 'mailchimp-for-wp'),
];
$type_options  = [
    'frictionless' => esc_html__('Frictionless', 'mailchimp-for-wp'),
    'pow' => esc_html__('Proof of Work', 'mailchimp-for-wp'),
    'image' => esc_html__('Image Captcha', 'mailchimp-for-wp'),
];

?>

<?php
if ('1' === $enabled) {
    ?>
<p>
    <?php echo esc_html__('Preview: if the credentials are valid, you should be able to complete the captcha below:', 'mailchimp-for-wp'); ?>
</p>
    <?php
}
$procaptcha_api = MC4WP_Procaptcha::get_instance();
echo $procaptcha_api->print_captcha_element(true, true);
?>

<input class="prosopo-procaptcha__enabled-setting" type="hidden" name="mc4wp_integrations[prosopo-procaptcha][enabled]" value="<?php echo esc_attr($enabled); ?>">

<table class="form-table">
<tbody>
<tr valign="top">
    <th scope="row"><?php echo esc_html__('Site Key', 'mailchimp-for-wp'); ?></th>
    <td class="nowrap integration-toggles-wrap">
        <label>
            <input class="widefat prosopo-procaptcha__site-key" type="text" name="mc4wp_integrations[prosopo-procaptcha][site_key]"
                    placeholder="<?php echo esc_attr__('Enter your site key', 'mailchimp-for-wp'); ?>"
                    value="<?php echo esc_attr($site_key); ?>">
        </label>
        <p class="description">
        <?php
            echo
            sprintf(
                // translators: %1$s: opening anchor tag, %2$s: closing anchor tag
                esc_html__('The API key for connecting with your Procaptcha account. %1$s Get your Site key here %2$s', 'mailchimp-for-wp'),
                '<a href="https://portal.prosopo.io/" target="_blank">',
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
            <input class="widefat prosopo-procaptcha__secret-key" type="password" name="mc4wp_integrations[prosopo-procaptcha][secret_key]"
                    placeholder="<?php echo esc_attr__('Enter your secret key', 'mailchimp-for-wp'); ?>"
                    value="<?php echo esc_attr($secret_key); ?>">
        </label>
    </td>
</tr>
<tr valign="top">
    <th scope="row"><?php echo esc_html__('Theme', 'mailchimp-for-wp'); ?></th>
    <td class="nowrap integration-toggles-wrap">
        <label>
            <select name="mc4wp_integrations[prosopo-procaptcha][theme]" style="width:250px;">
            <?php
            foreach ($theme_options as $value => $label) {
                $selected = $theme === $value ? ' selected' : '';
                printf('<option value="%s"%s>%s</option>', esc_attr($value), esc_attr($selected), esc_html($label));
            }
            ?>
            </select>
        </label>
    </td>
</tr>
<tr valign="top">
    <th scope="row"><?php echo esc_html__('Type', 'mailchimp-for-wp'); ?></th>
    <td class="nowrap integration-toggles-wrap">
        <label>
            <select name="mc4wp_integrations[prosopo-procaptcha][type]" style="width:250px;">
            <?php
            foreach ($type_options as $value => $label) {
                $selected = $type === $value ? ' selected' : '';
                printf('<option value="%s"%s>%s</option>', esc_attr($value), esc_attr($selected), esc_html($label));
            }
            ?>
            </select>
        </label>
    </td>
</tr>
<tr valign="top">
    <th scope="row"><?php echo esc_html__('Display for authorized users', 'mailchimp-for-wp'); ?></th>
    <td class="nowrap integration-toggles-wrap">
        <label>
            <input type="radio" name="mc4wp_integrations[prosopo-procaptcha][display_for_authorized]" value="1" <?php checked($display_for_authorized, '1'); ?> />&rlm;
            <?php echo esc_html__('Yes', 'mailchimp-for-wp'); ?>
        </label> &nbsp;
        <label>
            <input type="radio" name="mc4wp_integrations[prosopo-procaptcha][display_for_authorized]" value="0" <?php checked($display_for_authorized, '0'); ?> />&rlm;
            <?php echo esc_html__('No', 'mailchimp-for-wp'); ?>
        </label>
        <p class="description"><?php echo esc_html__('Select "yes" to require the captcha even from authorized users.', 'mailchimp-for-wp'); ?></p>
    </td>
</tr>
</tbody>
</table>

<prosopo-procaptcha-settings></prosopo-procaptcha-settings>

<script type="module">
    class ProsopoProcaptchaSettings extends HTMLElement {
        connectedCallback(){
            "loading" === document.readyState ?
                document.addEventListener("DOMContentLoaded", this.setup.bind(this)) :
                this.setup()
        }

        updateEnabledSetting(event){
            let form = event.target;
            let enabledInput = form.querySelector('.prosopo-procaptcha__enabled-setting');
            let siteKey= form.querySelector('.prosopo-procaptcha__site-key').value.trim();
            let secretKey = form.querySelector('.prosopo-procaptcha__secret-key').value.trim();

            enabledInput.value = '' !== siteKey &&
                '' !== secretKey?
                1:
                0;
        }

        setup(){
            this.closest('form').addEventListener('submit', this.updateEnabledSetting.bind(this))
        }
    }
    customElements.define('prosopo-procaptcha-settings', ProsopoProcaptchaSettings);
</script>
