# MailChimp for WordPress library: [ibericode/mc4wp-library](https://github.com/ibericode/mc4wp-library)

A set of classes that connect WordPress to MailChimp. 

Classes in this library are shared by [ibericode/mailchimp-for-wordpress](https://github.com/ibericode/mailchimp-for-wordpress) and [ibericode/mailchimp-for-wordpress-pro](https://github.com/ibericode/mailchimp-for-wordpress-pro/).

Run the following command in either of the two above repositories to update to the latest version of this library.

```
composer update --no-dev
```


### Usage

You can of course include this repository in your own code or plugin the same way, but we recommend making it an add-on of MailChimp for WordPress as you benefit from the plugin interface that way.

**Example (add-on of [MailChimp for WordPress](https://mc4wp.com/))**

```php
$api = mc4wp_get_api(); // get instance of API class with API key entered in the plugin settings
$result = $api->subscribe( $mailchimp_list, 'johndoe@email.com' );
```

**Example (dependency)**

Include the library in your project using [Composer](https://getcomposer.org/).

```sh
composer require "ibericode/mc4wp-library"
```

Use library classes where you need them.

```php
require __DIR__ . '/vendor/autoload.php';
$api = new MC4WP_API( $api_key );
$result = $api->subscribe( $mailchimp_list, 'johndoe@email.com' );
```

### License

GNU GENERAL PUBLIC LICENSE, Version 2