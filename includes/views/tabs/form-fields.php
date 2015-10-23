<?php add_thickbox(); ?>

<a href="#TB_inline?width=600&height=550&inlineId=mc4wp-form-variables" class="alignright thickbox button-secondary">
	<span class="dashicons dashicons-info"></span>
	<?php _e( 'Form variables', 'mailchimp-for-wp' ); ?>
</a>
<h2><?php _e( "Form Fields", 'mailchimp-for-wp' ); ?></h2>

<div id="mc4wp-field-wizard"></div>

<textarea class="widefat" cols="160" rows="20" id="mc4wp-form-content" name="mc4wp_form[content]"><?php echo esc_textarea( $form->content ); ?></textarea>


<div id="missing-fields-notice" class="mc4wp-notice" style="display: none;">
	<p>
		<?php echo __( 'Your form is missing the following (required) form fields:', 'mailchimp-for-wp' ); ?>
	</p>
	<ul id="missing-fields-list" class="ul-square"></ul>
</div>

<?php submit_button(); ?>

<p class="mc4wp-form-usage"><?php printf( __( 'Use the shortcode %s to display this form inside a post, page or text widget.' ,'mailchimp-for-wp' ), '<input type="text" onfocus="this.select();" readonly="readonly" value="[mc4wp_form id='. $form->ID .']" size="'. ( strlen( $form->ID ) + 18 ) .'">' ); ?></p>

<div id="mc4wp-form-variables" style="display: none;">
	<?php include dirname( __FILE__ ) . '/../parts/admin-form-variables.php'; ?>
</div>