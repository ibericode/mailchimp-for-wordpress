<p>
	<?php _e( 'To get a custom integration to work, include the following HTML in the form you are trying to integrate with.', 'mailchimp-for-wp' ); ?>
</p>

<textarea style="width: 100%;" class="code-sample" rows="5" readonly onfocus="this.select()"><?php ob_start(); ?><p>
	<label>
		<input type="checkbox" name="mc4wp-subscribe" value="1" />
		Subscribe to our newsletter.
	</label>
</p><?php $html = ob_get_clean(); echo esc_textarea( $html ); ?>
</textarea>
