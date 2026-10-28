=== MC4WP: Mailchimp for WordPress ===
Contributors: Ibericode, DvanKooten, hchouhan, lapzor
Donate link: https://www.mc4wp.com/contribute/#utm_source=wp-plugin-repo&utm_medium=mailchimp-for-wp&utm_campaign=donate-link
Tags: mailchimp, subscribe, email, newsletter, form
Requires at least: 5.3
Tested up to: 7.1
Stable tag: 4.15.0-rc1
License: GPL-3.0-or-later
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Requires PHP: 7.4

The #1 Mailchimp plugin for WordPress. Allows you to add a multitude of newsletter sign-up methods to your site.

== Description ==

*Allowing your visitors to subscribe to your newsletter should be easy. With this plugin, it finally is.*

This plugins helps you grow your email list in Mailchimp. You can use it to create good looking and accessible sign-up forms or integrate with any other existing form on your WordPress site, like your contact, comment or checkout form.

[youtube https://www.youtube.com/watch?v=fZCYPnFybqU]

#### Some (but not all) features

- Connect with your Mailchimp account in seconds.

- Sign-up forms which are good looking, user-friendly and mobile optimized. You have complete control over the form fields and can build your forms using native HTML.

- Seamless integration with the following plugins:
  - WordPress Comment Form
  - WordPress Registration Form
  - Contact Form 7
  - WooCommerce
  - Gravity Forms
  - Ninja Forms 3
  - WPForms
  - BuddyPress
  - MemberPress
  - Events Manager
  - Easy Digital Downloads
  - Give
  - UltimateMember
  - HTML Forms
  - AffiliateWP

- Is the plugin you want to integrate with not listed above? You can probably still use our [custom integration](https://www.mc4wp.com/kb/subscribe-mailchimp-custom-html-form/) feature. Alternatively, the plugin comes with a PHP API to programmatically add a new subscriber to Mailchimp.

- [Mailchimp for WordPress Premium](https://www.mc4wp.com/): Send your WooCommerce orders to Mailchimp so you can see exactly what each subscriber purchased and how much revenue your email campaigns are generating.

- A multitude of available add-on plugins and integrations:
  - [Mailchimp for WordPress Premium](https://www.mc4wp.com/)
  - [Mailchimp Top Bar](https://wordpress.org/plugins/mailchimp-top-bar/)
  - [Boxzilla Pop-ups](https://wordpress.org/plugins/boxzilla/)

- Well documented through our [knowledge base](https://www.mc4wp.com/kb/).

- Developer friendly. For some inspiration, check out our [repository of example code snippets](https://github.com/ibericode/mailchimp-for-wordpress/tree/main/sample-code-snippets).

- Ready for PHP 8.5, but backwards-compatible all the way down to PHP 7.4.

#### What is Mailchimp?

Mailchimp is a newsletter service that allows you to send out email campaigns to a list of email subscribers. It is free for lists with up to 500 email subscribers, which is why it is the newsletter-service of choice for thousands of small businesses across the globe.

If you are not yet using Mailchimp, [creating an account is 100% free and only takes you about 30 seconds](http://eepurl.com/igOGeX).

== Installation ==

#### Installing the plugin
1. In your WordPress admin panel, go to *Plugins > New Plugin*, search for **Mailchimp for WordPress** and click "*Install now*"
1. Alternatively, download the plugin and upload the contents of `mailchimp-for-wp.zip` to your plugins directory, which usually is `/wp-content/plugins/`.
1. Activate the plugin
1. Set [your API key](https://admin.mailchimp.com/account/api) in the plugin settings.

#### Configuring Sign-Up Form(s)
1. Go to *Mailchimp for WP > Forms*
2. Select at least one list to subscribe people to.
3. *(Optional)* Add more fields to your form.
4. Embed a sign-up form in pages or posts using the `[mc4wp_form]` shortcode or Gutenberg block.
5. Show a sign-up form in your widget areas using the "Mailchimp Sign-Up Form" widget.
6. Show a sign-up form from your theme files by using the `mc4wp_show_form()` PHP function.

#### Need help?
Please take a look at the [MC4WP knowledge base](https://www.mc4wp.com/kb/#utm_source=wp-plugin-repo&utm_medium=mailchimp-for-wp&utm_campaign=installation-instructions-link) first.

If you can't find an answer there, please look through the [plugin support forums](https://wordpress.org/support/plugin/mailchimp-for-wp) or start your own topic.

== Frequently Asked Questions ==

= Where can I find my Mailchimp API key? =

You can [find your Mailchimp API key here](http://kb.mailchimp.com/accounts/management/about-api-keys#Find-or-Generate-Your-API-Key)

= How to display a form in posts or pages? =

Use the `[mc4wp_form]` shortcode or the Gutenberg block.

= How to display a form in widget areas like the sidebar or footer? =

Go to **Appearance > Widgets** and use the **Mailchimp for WP Form** widget that comes with the plugin.

= How to add a sign-up checkbox to my Contact Form 7 form? =

Use the following shortcode in your CF7 form to display a newsletter sign-up checkbox.

`
[mc4wp_checkbox "Subscribe to our newsletter?"]
`

Our knowledge base has more information on [connecting Contact Form 7 and Mailchimp](https://www.mc4wp.com/kb/subscribe-mailchimp-contact-form-7/).

=  The form shows a success message but subscribers are not added to my list(s)? =

If the form shows a success message, there is no doubt that the sign-up request succeeded. Mailchimp could have a slight delay sending the confirmation email though. Please check again in a few minutes (sometimes hours) and don't forget to check your junk folder too.

When you have double opt-in disabled, new subscribers will be seen as *imports* by Mailchimp. They will not show up in your daily digest emails or statistics. [We always recommend leaving double opt-in enabled](http://blog.mailchimp.com/double-opt-in-vs-single-opt-in-stats/).

= How can I style the sign-up form? =

You can use custom CSS to style the sign-up form if you do not like the themes that come with the plugin. The following selectors can be used to target the various form elements.

`
.mc4wp-form { ... } /* the form element */
.mc4wp-form p { ... } /* form paragraphs */
.mc4wp-form label { ... } /* labels */
.mc4wp-form input { ... } /* input fields */
.mc4wp-form input[type="checkbox"] { ... } /* checkboxes */
.mc4wp-form input[type="submit"] { ... } /* submit button */
.mc4wp-alert { ... } /* success & error messages */
.mc4wp-success { ... } /* success message */
.mc4wp-error { ... } /* error messages */
`

You can add your custom CSS to your theme stylesheet or (easier) by using a plugin like [Simple Custom CSS](https://wordpress.org/plugins/simple-custom-css/#utm_source=wp-plugin-repo&utm_medium=mailchimp-for-wp&utm_campaign=after-css-link)

= How do I show a sign-up form in a pop-up? =

We recommend the [Boxzilla pop-up plugin](https://wordpress.org/plugins/boxzilla/) for this. You can use the form shortcode in your pop-up box to show a sign-up form.

= How do I subscribe from my WooCommerce checkout form? =

You can use our WooCommerce integration for that. [How to subscribe to Mailchimp from the WooCommerce checkout form](https://www.mc4wp.com/kb/connect-woocommerce-store-mailchimp/).

= How to connect my WooCommerce store with Mailchimp? =

You can find instructions for [connecting your WooCommerce store with Mailchimp](https://www.mc4wp.com/kb/connect-woocommerce-store-mailchimp/) on our website.

= I'm getting an "HTTP Error" when trying to connect to Mailchimp. =

the "HTTP Error" type is usually because of a firewall configuration issue or outdated software on your web server.

Please contact your webhost and ask them to check the following:

- Whether remote HTTP requests to `https://api.mailchimp.com` are allowed.
- Whether cURL and the PHP-cURL extension are installed and updated to a recent version.

= Where do I report security bugs found in this plugin? =

Please report security bugs found in the source code of the Mailchimp for WordPress plugin through the [Patchstack Vulnerability Disclosure Program](https://patchstack.com/database/vdp/8c215d34-dc57-4167-8af8-a9863cb63668). The Patchstack team will assist you with verification, CVE assignment, and notify the developers of this plugin.

= My question is not listed here. =

Please search through our [knowledge base](https://www.mc4wp.com/kb/#utm_source=wp-plugin-repo&utm_medium=mailchimp-for-wp&utm_campaign=faq).


== Other Notes ==

#### Support

If you need some help in setting up the plugin, you have various options:

- Search through our [knowledge base](https://www.mc4wp.com/kb/#utm_source=wp-plugin-repo&utm_medium=mailchimp-for-wp&utm_campaign=description).
- Open a topic in the [WordPress.org plugin support forums](https://wordpress.org/support/plugin/mailchimp-for-wp)
- If you're a premium user, send an email to the email address listed inside the plugin.

#### Translations

You can [help translate this plugin into your language](https://translate.wordpress.org/projects/wp-plugins/mailchimp-for-wp/stable/) using your WordPress.org account.

#### Development

This plugin is being developed on GitHub. If you want to collaborate, please look at [ibericode/mailchimp-for-wordpress](https://github.com/ibericode/mailchimp-for-wordpress).

#### Customizing the plugin

The plugin provides various filter and action hooks that allow you to modify or extend the default behavior. We're also maintaining a [collection of sample code snippets](https://github.com/ibericode/mailchimp-for-wordpress/tree/main/sample-code-snippets).

== Screenshots ==

1. Example sign-up form in the TwentyTwenty theme.
2. Example sign-up integration with a contact form.
3. Settings page to connect with your Mailchimp account.
4. Overview of sign-up integrations.
5. Overview of sign-up forms.
6. Settings page to configure an integration.
7. Page where you edit your sign-up forms.
8. Page where you modify your form messages.
9. Settings page for e-commerce integration with Mailchimp. Requires [Mailchimp for WordPress Premium](https://www.mc4wp.com/).
== Upgrade Notice ==

= 3.0.3 =

Minor improvements and re-added support for Goodbye Captcha integration.

== Changelog ==

= 4.15.0 =

_Release date: TBD_

- Security: Only accept lists from the `_mc4wp_lists` request parameter when the site owner chose them for that form or integration. A visitor could otherwise subscribe an email address to any audience in the connected Mailchimp account. Thanks to Harry Nguyen and Karthik Ramakrishnan for reporting this.
- Security: Encode square brackets in the values of dynamic content tags, so that a value like `{data key="..."}` can not run a shortcode when the form is shown with the form block. Thanks to Jakub Herman for reporting this.
- Security: URL-encode the IDs in all Mailchimp API resource paths.
- Security: Ignore submissions to sign-up forms that are not published.
- Security: Escape the Contact Form 7 form title in the admin.
- Compatibility: Custom HTML that sends `_mc4wp_lists` to an integration now only works for lists that are selected in the settings of that integration. List choice fields can no longer be added to a form through the `mc4wp_form_content` filter; add them to the form content instead. Pages that were cached before this update and use a `lists` attribute on the `[mc4wp_form]` shortcode subscribe to the lists in the form settings until the cache is cleared.
- Forms & Integrations: New "Update only empty fields?" setting. When updating an existing subscriber, it keeps the field values that are already set in Mailchimp and only fills in empty fields. Thanks [arnelap](https://github.com/arnelap)!
- Misc: Fix a fatal error when the data for a Mailchimp API request can not be encoded as JSON. The request now fails with an error instead. Thanks [Robert Staddon](https://github.com/robertstaddon)!
- Misc: The WPML code snippets now also work with language codes that include a country, like `en-us`. Thanks [arnelap](https://github.com/arnelap)!
- Misc: Bump tested WordPress version to 7.1.


= 4.14.1 =

_Release date: Sep 10, 2026_

- Security: Escape dynamic content tags based on where they appear in the HTML. Values in URL attributes can no longer use a protocol like `javascript:`, and values in event handler attributes are removed.
- Integrations: Prosopo Procaptcha now follows the alignment of the surrounding form. Thanks [Chris Taylor](https://github.com/forgetso)!
- Integrations: Fix Prosopo Procaptcha breaking other scripts on the page when the theme wraps the form in paragraph tags. Thanks [Chris Taylor](https://github.com/forgetso)!
- Misc: Fix the Mailchimp Site Tracking Pixel not finding an already connected site for the same domain, for example when it was registered with a `www.` prefix or on a multisite subdirectory install.
- Misc: Log the full Mailchimp error message when the Site Tracking Pixel fails to connect.


= 4.14.0 =

_Release date: Aug 4, 2026_

- Integrations: Show the sign-up checkbox on the admin "Add New User" form. Thanks [arnelap](https://github.com/arnelap)!
- Misc: Add a code snippet for sending the WordPress username to Mailchimp with User Sync.
- Misc: Update WordPress Coding Standards to 3.4 and fix a few incorrect type annotations.


= 4.13.1 =

_Release date: Jun 22, 2026_

- Forms: Improve handling of pasted `<form>` wrapper tags in form content.
- Forms: Remove `mc4wp_default_form_id` option. We now default to first available form.
- Forms: Improve the `{email}` dynamic content tag by detecting common lowercase email field names.
- Misc: Adhere to DB, Security, I18n sniffs from WordPress Coding Standards.
- Misc: Prevent direct access to plugin configuration files.
- Misc: Remove the plugin review request from the footer of plugin admin pages.
- Security: Add Patchstack vulnerability disclosure instructions to the plugin FAQ and security policy.


= 4.13.0 =

_Release date: Jun 1, 2026_

- Improve performance by preloading core plugin classes and skipping dynamic content tag parsing when no tags are present.
- Improve security for admin AJAX requests by adding a nonce check and stricter audience ID handling.
- Improve dynamic content tag replacement by removing broken regular expressions.
- Improve form preview output by disabling PHP error display during preview rendering.
- Update JavaScript dependencies and pass admin script data using `wp_add_inline_script()`.

[View the full changelog on GitHub](https://github.com/ibericode/mailchimp-for-wordpress/blob/main/CHANGELOG.md)
