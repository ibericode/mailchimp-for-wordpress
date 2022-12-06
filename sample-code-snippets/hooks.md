MailChimp for WordPress comes with a range of action and filter hooks, which allow you to modify the default behavior of
the plugin.

All of these hooks are thoroughly documented in the plugin code itself, so the best way to learn about these hooks is by
taking a look at the [source code of the plugin](https://github.com/ibericode/mailchimp-for-wordpress/tree/master/includes).

# Action hooks

```
mc4wp_form_api_error
mc4wp_form_error
mc4wp_form_respond
mc4wp_form_subscribed
mc4wp_form_success
mc4wp_form_unsubscribed
mc4wp_integration_before_checkbox_wrapper
mc4wp_integration_after_checkbox_wrapper
mc4wp_integration_subscribed
mc4wp_load_form_stylesheets
mc4wp_load_form_scripts
mc4wp_output_form
mc4wp_save_form
```

**Example action usage**

```php
add_action( 'mc4wp_form_subscribed', function() {
   // do something
});
```

# Filter hooks

```
mc4wp_admin_required_capability
mc4wp_debug_log_file
mc4wp_debug_log_level
mc4wp_email_type
mc4wp_form_action
mc4wp_form_auto_scroll
mc4wp_form_after_fields
mc4wp_form_before_fields
mc4wp_form_content
mc4wp_form_css_classes
mc4wp_form_data
mc4wp_form_element_attributes
mc4wp_form_errors
mc4wp_form_ignored_field_names
mc4wp_form_lists
mc4wp_form_merge_vars
mc4wp_form_messages
mc4wp_form_redirect_url
mc4wp_form_required_fields
mc4wp_form_sanitized_data
mc4wp_form_subscriber_data
mc4wp_format_field_value
mc4wp_integration_checkbox_label
mc4wp_integration_checkbox_attributes
mc4wp_integration_lists
mc4wp_integration_data
mc4wp_integration_merge_vars
mc4wp_integration_subscriber_data
mc4wp_lists
mc4wp_load_form_scripts
mc4wp_merge_vars
mc4wp_subscriber_count
mc4wp_subscriber_data
mc4wp_user_merge_vars
mc4wp_use_sslverify
```

**Example filter usage**

```php
add_filter( 'mc4wp_form_content', function( $content ) {
    $addition = 'Some text at the end of every form.';
   return $content . $addition;
});
```
