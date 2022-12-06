<?php

/**
 * Filter the order statuses of the order that the plugin will send to MailChimp.
 *
 * Here, we are returning a new array with only the "wc-completed" status in it so that only orders with that status are sent to MailChimp.
 */
add_filter( 'mc4wp_ecommerce_order_statuses', function( $statuses ) {
	return array( 'wc-completed' );
});
