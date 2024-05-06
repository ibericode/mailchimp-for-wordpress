<?php add_thickbox(); ?>

<div class="alignright">
    <a href="#TB_inline?width=0&height=550&inlineId=mc4wp-form-variables" class="thickbox button-secondary">
        <span class="dashicons dashicons-info"></span>
        <?php echo esc_html__('Form variables', 'mailchimp-for-wp'); ?>
    </a>
    <a href="#TB_inline?width=600&height=400&inlineId=mc4wp-add-field-help" class="thickbox button-secondary">
        <span class="dashicons dashicons-editor-help"></span>
        <?php echo esc_html__('Add more fields', 'mailchimp-for-wp'); ?>
    </a>
</div>
<h2><?php echo esc_html__('Form Fields', 'mailchimp-for-wp'); ?></h2>

<!-- Placeholder for the field wizard -->
<div id="mc4wp-field-wizard"></div>

<div class="mc4wp-form-markup-wrap">
    <div class="mc4wp-form-editor-wrap">
        <h4 style="margin: 0"><?php echo esc_html__('Form code', 'mailchimp-for-wp'); ?> <span style="visibility: hidden;" class="dashicons dashicons-editor-help"></span></h4>
        <!-- Textarea for the actual form content HTML -->
        <textarea class="widefat" cols="160" rows="20" id="mc4wp-form-content" name="mc4wp_form[content]" placeholder="<?php echo esc_attr__('Enter the HTML code for your form fields..', 'mailchimp-for-wp'); ?>" autocomplete="false" autocorrect="false" autocapitalize="false" spellcheck="false"><?php echo htmlspecialchars($form->content, ENT_QUOTES, get_option('blog_charset')); ?></textarea>
    </div>
    <div class="mc4wp-form-preview-wrap">
        <h4 style="margin: 0;">
            <?php echo esc_html__('Form preview', 'mailchimp-for-wp'); ?>
            <span class="dashicons dashicons-editor-help" title="<?php echo esc_attr__('The form may look slightly different than this when shown in a post, page or widget area.', 'mailchimp-for-wp'); ?>"></span>
        </h4>
        <iframe id="mc4wp-form-preview" src="<?php echo esc_attr($form_preview_url); ?>"></iframe>
    </div>
</div>


<!-- This field is updated by JavaScript as the form content changes -->
<input type="hidden" id="required-fields" name="mc4wp_form[settings][required_fields]" value="<?php echo esc_attr($form->settings['required_fields']); ?>" />

<?php submit_button(); ?>

<p class="mc4wp-form-usage"><?php printf(esc_html__('Use the shortcode %s to display this form inside a post, page or text widget.', 'mailchimp-for-wp'), '<input type="text" onfocus="this.select();" readonly="readonly" value="' . esc_attr(sprintf('[mc4wp_form id=%d]', $form->ID)) . '" size="' . ( strlen($form->ID) + 15 ) . '">'); ?></p>


<?php // Content for Thickboxes ?>
<div id="mc4wp-form-variables" style="display: none;">
    <?php require __DIR__ . '/../parts/dynamic-content-tags.php'; ?>
</div>

<div id="mc4wp-add-field-help" style="display: none;">
    <?php require __DIR__ . '/../parts/add-fields-help.php'; ?>
</div>
