# Disable HTML5 form validation

If you want to disable all HTML5 validation and only rely on the plugin's server side validation,
you can use the following snippet.

This adds the `novalidate` attribute to all `<form>` elements outputted by the plugin.

```php
add_filter('mc4wp_form_element_attributes', function($attrs) {
	$attrs['novalidate'] = 'novalidate';
	return $attrs;
});
```
