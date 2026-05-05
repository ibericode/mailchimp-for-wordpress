<?php

defined('ABSPATH') or exit;


use Automattic\WooCommerce\Blocks\Utils\CartCheckoutUtils;

// if Checkout Block is used, we have less available options for where to show the sign-up checkbox
if (class_exists(CartCheckoutUtils::class) && method_exists(CartCheckoutUtils::class, 'is_checkout_block_default') && CartCheckoutUtils::is_checkout_block_default()) {
    $position_options = [
        'after_email_field'               => __('After email field', 'mailchimp-for-wp'),
        'after_order_notes'               => __('After order notes', 'mailchimp-for-wp'),
    ];
} else {
    $position_options = [
        'after_email_field'               => __('After email field', 'mailchimp-for-wp'),
        'checkout_billing'                => __('After billing details', 'mailchimp-for-wp'),
        'checkout_shipping'               => __('After shipping details', 'mailchimp-for-wp'),
        'checkout_after_customer_details' => __('After customer details', 'mailchimp-for-wp'),
        'review_order_before_submit'      => __('Before submit button', 'mailchimp-for-wp'),
        'after_order_notes'               => __('After order notes', 'mailchimp-for-wp'),
    ];
}

if (defined('CFW_NAME')) {
    $position_options['cfw_checkout_before_payment_method_tab_nav'] = __('Checkout for WooCommerce: Before complete order button', 'mailchimp-for-wp');
    $position_options['cfw_after_customer_info_account_details']    = __('Checkout for WooCommerce: After account info', 'mailchimp-for-wp');
    $position_options['cfw_checkout_after_customer_info_address']   = __('Checkout for WooCommerce: After customer info', 'mailchimp-for-wp');
}

/** @var MC4WP_Integration $integration */

$body_config = [
    'element' => 'mc4wp_integrations[' . $integration->slug . '][enabled]',
    'value'   => '1',
    'hide'    => false,
];

$config = [
    'element' => 'mc4wp_integrations[' . $integration->slug . '][implicit]',
    'value'   => '0',
];

?>
<table class="form-table">
    <tbody class="integration-toggled-settings" data-showif="<?php echo esc_attr(json_encode($body_config)); ?>">
        <tr valign="top" data-showif="<?php echo esc_attr(json_encode($config)); ?>">
            <th scope="row">
                <?php esc_html_e('Position', 'mailchimp-for-wp'); ?>
            </th>
            <td>
                <select name="mc4wp_integrations[<?php echo esc_attr($integration->slug); ?>][position]">
                    <?php

                    foreach ($position_options as $value => $label) {
                        printf('<option value="%s" %s>%s</option>', esc_attr($value), selected($value, $opts['position'], false), esc_html($label));
                    }
                    ?>

                </select>
                <p class="description"><?php esc_html_e('Select the location where you would like to show the sign-up checkbox.', 'mailchimp-for-wp'); ?></p>
            </td>
        </tr>
    </tbody>
</table>
