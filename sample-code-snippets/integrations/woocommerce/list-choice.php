<?php

/**
 * This snippet adds the HTML for a MailChimp list-choice to your WooCommerce checkout.
 */
add_action( 'woocommerce_after_order_notes', function() {
	?>

	<!-- List choice -->
	<p class="form-row form-row " id="_mc4wp_subscribe_woocommerce_checkout_field">
		<label class="checkbox ">
			<input type="checkbox" name="_mc4wp_lists[]" value="24c681e3c0" checked="checked" /> Send me the annually printed catalogue.
			<input type="checkbox" name="_mc4wp_lists[]" value="6196961cca" checked="checked" /> Sign me up for the newsletter.
		</label>
	</p>

	<!-- This tells MailChimp for WordPress to susbcribe this customer -->
	<input type="hidden" name="mc4wp-subscribe" value="1" />
<?php
} );