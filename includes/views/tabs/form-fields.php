<p><?php printf( __( 'To use the MailChimp sign-up form, configure the form below and then either paste %s in the content of a post or page or use the  widget.', 'mailchimp-for-wp' ), '<input size="'. ( strlen( $form->ID ) + 18 ) .'" type="text" onfocus="this.select();" readonly="readonly" value="[mc4wp_form id=\''. $form->ID .'\']" class="mc4wp-shortcode-example">' ); ?></p>

<p>
<a class="button-secondary">
	<span class="dashicons dashicons-plus-alt"></span>
	<?php _e( 'Add MailChimp field', 'mailchimp-for-wp' ); ?>
</a>
</p>
<?php
if( function_exists( 'wp_editor' ) ) {
	wp_editor( esc_textarea( $form->content ), 'mc4wpformmarkup', array( 'tinymce' => false, 'media_buttons' => false, 'textarea_name' => 'mc4wp_form[content]') );
} else {
	?><textarea class="widefat" cols="160" rows="20" id="mc4wpformmarkup" name="mc4wp_lite_form[markup]"><?php echo esc_textarea( $form->content ); ?></textarea><?php
} ?>
<p class="mc4wp-form-usage"><?php printf( __( 'Use the shortcode %s to display this form inside a post, page or text widget.' ,'mailchimp-for-wp' ), '<input type="text" onfocus="this.select();" readonly="readonly" value="[mc4wp_form]" class="mc4wp-shortcode-example">' ); ?></p>

<div id="missing-fields-notice" class="mc4wp-notice" style="display: none;">
	<p>
		<?php echo __( 'Your form is missing the following (required) form fields:', 'mailchimp-for-wp' ); ?>
	</p>
	<ul id="missing-fields-list" class="ul-square"></ul>
</div>

<?php submit_button(); ?>