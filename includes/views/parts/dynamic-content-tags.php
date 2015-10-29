<?php
defined( 'ABSPATH' ) or exit;

$tags = MC4WP_Dynamic_Content_Tags::instance()->get_tags();
?>
<h2><?php _e( 'Add dynamic form variable', 'mailchimp-for-wp' ); ?></h2>
<p>
	<?php echo sprintf( __( 'The following list of variables can be used to <a href="%s">add some dynamic content to your form or success and error messages</a>.', 'mailchimp-for-wp' ), 'https://mc4wp.com/kb/using-variables-in-your-form-or-messages/' ) . ' ' . __( 'This allows you to personalise your form or response messages.', 'mailchimp-for-wp' ); ?>
</p>
<table class="widefat striped">
	<?php foreach( $tags as $tag => $config ) {
		$tag = ! empty( $config['example'] ) ? $config['example'] : $tag;
		?>
		<tr>
			<th><?php echo sprintf( '{%s}', $tag ); ?></th>
			<td><?php echo $config['description']; ?></td>
		</tr>
	<?php } ?>
</table>
