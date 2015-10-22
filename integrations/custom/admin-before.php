<p>
	<?php _e( 'To get a custom integration to work, include the following HTML in the form you are trying to integrate with.', 'mailchimp-for-wp' ); ?>
</p>

<pre style="display: block; background: white; border: 1px solid #333; padding: 10px;"><?php ob_start(); ?><p>
	<label>
		<input type="checkbox" name="mc4wp-subscribe" value="1" />
	</label>
</p><?php $html = ob_get_clean(); echo esc_html( $html ); ?></pre>
