<p>
	<?php _e( 'To get a custom integration to work, include the following HTML in the form you are trying to integrate with.', 'phplist-for-wp' ); ?>
</p>

<?php ob_start(); ?>
<p>
	<label>
		<input type="checkbox" name="pl4wp-subscribe" value="1" />
		<?php _e( 'Subscribe to our newsletter.', 'phplist-for-wp' ); ?>
	</label>
</p>

<?php $html = ob_get_clean(); ?>

<textarea class="widefat code-sample" rows="<?php echo substr_count( $html, PHP_EOL ); ?>" readonly onfocus="this.select()"><?php echo esc_textarea( $html ); ?></textarea>
