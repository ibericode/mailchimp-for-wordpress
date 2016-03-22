<?php

$position_options = array(
	'billing' => __( "After billing details", 'mailchimp-for-wp' ),
	'shipping' => __( 'After shipping details', 'mailchimp-for-wp' ),
	'after_customer_details' => __( 'After customer details', 'mailchimp-for-wp' ),
);

?>
<table class="form-table">
	<tr valign="top">
		<th scope="row">
			<?php _e( 'Position', 'mailchimp-for-wp' ); ?>
		</th>
		<td>
			<select name="mc4wp_integrations[<?php echo $integration->slug; ?>][position]">
				<?php

				foreach( $position_options as $value => $label ) {
					printf( '<option value="%s" %s>%s</option>', esc_attr( $value ), selected( $value, $opts['position'], false ), esc_html( $label ) );
				}
				?>

			</select>
		</td>
	</tr>
</table>
