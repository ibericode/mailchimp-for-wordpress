Integration slugs
=================

The following integration slugs are used by the MailChimp for WordPress plugin. 

- affiliatewp
- buddypress
- contact-form-7
- custom
- easy-digital-downloads
- events-manager
- gravity-forms
- memberpress
- ninja-forms
- woocommerce
- wp-comment-form
- wp-registration-form
- wpforms

[View source](https://github.com/ibericode/mailchimp-for-wordpress/tree/master/integrations)

You can use these slugs in the following filter or action hooks.

```php
mc4wp_integration_{slug}_data
mc4wp_integration_{slug}_subscriber_data
mc4wp_integration_{slug}_lists
mc4wp_integration_{slug}_checkbox_attributes
mc4wp_integration_{slug}_before_checkbox_wrapper
mc4wp_integration_{slug}_after_checkbox_wrapper
````

_Example_

```php
add_filter( 'mc4wp_integration_woocommerce_subscriber_data', function( MC4WP_MailChimp_Subscriber $subscriber ) {
    $subscriber->merge_fields[ "COUNTRY" ] = sanitize_text_field( $_POST['billing_country'] );
    return $subscriber;
});
```

_Example_

```php
//Replace the IDs with your own list ids from MailChimp for WP > MailChimp
add_filter( 'mc4wp_integration_ninja-forms_lists', function() {
	return array("f2415574a4","bd0c7cefa9","a53c0bf8e5");
});
```

_Example_

```php
add_filter( 'mc4wp_integration_woocommerce_checkbox_attributes', function() {
	return array("data-example" => "value-example","data-example2" => "value2");
});
```

_Example_

```php
add_filter( 'mc4wp_integration_contact-form-7_before_checkbox_wrapper', function() {
	echo "<p>Some HTML before the checkbox.</p>";
});
```

_Example_

```php
add_filter( 'mc4wp_integration_contact-form-7_after_checkbox_wrapper', function() {
	echo "<p>Some HTML code after the checkbox</p>";
});
```
