<?php

/**
 * This snippet adds the HTML for a MailChimp interest groups to your WooCommerce Registration.
 */

add_action('woocommerce_register_form_start', 'mc4wp_show_interest_group_in_registration');
function mc4wp_show_interest_group_in_registration()
{
    ?>

    <!-- Subscription Checkbox -->
    <p class="mc4wp-checkbox mc4wp-checkbox-woocommerce">
        <label>
            <?php //<input type="checkbox" name="_mc4wp_subscribe_woocommerce" value="1"> ?>
            <input type="checkbox" name="mc4wp-subscribe" value="1">
            <span>Sign me up for the newsletter YAY!</span>
        </label>
    </p>

    <!-- Interest Groups -->
    <p class="form-row form-row " id="_mc4wp_subscribe_woocommerce_checkout_field">
        <span>Choose the topic of your interest</span><br />
        <label class="checkbox ">
            <input name="mc4wp-INTERESTS[XXXX][]" type="checkbox" value="ENTER-VALUE"> <span>Theme</span>
            <input name="mc4wp-INTERESTS[XXXX][]" type="checkbox" value="ENTER-VALUE"> <span>Plugin</span>
        </label>
    </p>

    <?php
}
