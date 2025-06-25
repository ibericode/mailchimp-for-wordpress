=== MC4WP: Mailchimp for WordPress ===
Contributors: Ibericode, DvanKooten, hchouhan, lapzor
Donate link: https://www.mc4wp.com/contribute/#utm_source=wp-plugin-repo&utm_medium=mailchimp-for-wp&utm_campaign=donate-link
Tags: mailchimp, subscribe, email, newsletter, form
Requires at least: 4.6
Tested up to: 6.8
Stable tag: 4.10.5
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

- Ready for PHP 8.4, but backwards-compatible all the way down to PHP 7.4.

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

#### Where can I find my Mailchimp API key?
You can [find your API key here](http://kb.mailchimp.com/accounts/management/about-api-keys#Find-or-Generate-Your-API-Key)

#### How to display a form in posts or pages?
Use the `[mc4wp_form]` shortcode or the Gutenberg block.

#### How to display a form in widget areas like the sidebar or footer?
Go to **Appearance > Widgets** and use the **Mailchimp for WP Form** widget that comes with the plugin.

#### How to add a sign-up checkbox to my Contact Form 7 form?
Use the following shortcode in your CF7 form to display a newsletter sign-up checkbox.

`
[mc4wp_checkbox "Subscribe to our newsletter?"]
`

Our knowledge base has more information on [connecting Contact Form 7 and Mailchimp](https://www.mc4wp.com/kb/connecting-contact-form-7-and-mailchimp/).

#### The form shows a success message but subscribers are not added to my list(s)?
If the form shows a success message, there is no doubt that the sign-up request succeeded. Mailchimp could have a slight delay sending the confirmation email though. Please check again in a few minutes (sometimes hours) and don't forget to check your junk folder too.

When you have double opt-in disabled, new subscribers will be seen as *imports* by Mailchimp. They will not show up in your daily digest emails or statistics. [We always recommend leaving double opt-in enabled](http://blog.mailchimp.com/double-opt-in-vs-single-opt-in-stats/).

#### How can I style the sign-up form?
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

#### How do I show a sign-up form in a pop-up?

We recommend the [Boxzilla pop-up plugin](https://wordpress.org/plugins/boxzilla/) for this. You can use the form shortcode in your pop-up box to show a sign-up form.

### How do I subscribe from my WooCommerce checkout form?

You can use our WooCommerce integration for that. [How to subscribe to Mailchimp from the WooCommerce checkout form](https://www.mc4wp.com/kb/connect-woocommerce-store-mailchimp/).

### How to connect my WooCommerce store with Mailchimp?

You can find instructions for [connecting your WooCommerce store with Mailchimp](https://www.mc4wp.com/kb/connect-woocommerce-store-mailchimp/) on our website.

#### I'm getting an "HTTP Error" when trying to connect to Mailchimp.

the "HTTP Error" type is usually because of a firewall configuration issue or outdated software on your web server.

Please contact your webhost and ask them to check the following:

- Whether remote HTTP requests to `https://api.mailchimp.com` are allowed.
- Whether cURL and the PHP-cURL extension are installed and updated to a recent version.

#### My question is not listed here.

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

== Changelog ==


#### 4.10.5 - Jun 25, 2025

- [Ninja Forms] Always show at least one list option so that onchange event fires properly (to load Audience fields).
- Update third-party JS dependencies.
- Optimize SVG icons for reduced file sizes.


#### 4.10.4 - May 26, 2025

- Improved context-dependent escaping in dynamic content tags. 


#### 4.10.3 - Apr 16, 2025

- Update third-party JS dependencies.
- Add message setting for when a form submission is marked as spam.
- Log exact anti-spam rule when a form submission is marked as spam.
- Handle potential Prosopo connection errors gracefully.


#### 4.10.2 - Feb 28, 2025

- Fix WPForms parameter type change causing a fatal error if using WPForms with a Mailchimp sign-up field.
- Add Mailchimp data to Personal Data exporter. Contributed by [David Anderson from UpdraftPlus](https://updraftplus.com/).
- Prevent PHP notices in lists overview on general settings page.


#### 4.10.1 - Feb 06, 2025

- Fix JS error breaking Ninja Forms edit form page when not connected to a Mailchimp account or account has no audiences.
- Remove `sprintf` usage in hot path.
- Lazy load `MC4WP_API_V3` class to save some memory and parse time.
- Save a tiny bit of memory in autoloader implementation by not repeatedly storing plugin directory.
- Remove unused setting key from default options.


#### 4.10.0 - Jan 23, 2025

- Bump required PHP version to 7.4 or higher.
- Obfuscate API key the same way as in the Mailchimp.com interface.
- Delete all plugin data when plugin is uninstalled / deleted via WP Admin.
- Fix several PHP 8.4 deprecation warnings.
- Address warning about translations being loaded too early if using Ninja Forms integration.
- Run stored setting values related to user-facing textual messages through i18n functions to allow translating them through plugins like Loco Translate or WPML.


#### 4.9.21 - Jan 08, 2025

- [Forms] Rename "list choice" to "audience choice" in available form fields.
- [Ninja Forms] Fix gettext being called too early warning in Ninja Forms base class.
- [WooCommerce] Allow pre-checking of sign-up checkbox in Checkout Block.


#### 4.9.20 - Dec 18, 2024

- Fix Ninja Forms integration field no longer showing up.
- Fix "link is expired" message because of missing nonce on button to dismiss API key notice.
- [WPML] Added text_no_lists_selected to the config file so it can be translated. Thanks [Diego Pereira](https://github.com/diiegopereira)!


#### 4.9.19 - Nov 11, 2024

- Add integration with [Prosopo](https://prosopo.io/), a GDPR compliant anti-spam solution for protecting your sign-up forms against bot sign-ups. Thanks [Maxim Akimov](https://github.com/light-source)!


#### 4.9.18 - Oct 21, 2024

- Bump required PHP version to 7.2.
- Prevent non-functional checkbox from showing up on WooCommerce my account page if WooCommerce checkout integration is enabled.
- Update default form content to include a "for" attribute on the label element.
- Minor performance optimizations to `MC4WP_Form::get_subscriber_tags()`
- Begrudgingly rename Mailchimp lists to Mailchimp audiences throughout the plugin's admin interfaces.


#### 4.9.17 - Sep 17, 2024

- Fix compatibility with WooCommerce versions 8.5 to 8.8 because of private method that was later made public.
- Fix potential reflected XSS by stripping and escaping all HTML from `{email}` tag replacements. Thanks to kauenavarro for responsibly disclosing.
- Fix potential stored XSS for attackers with both administrator access and Mailchimp account access by escaping HTML from interest group name. Thanks to Jorge Diaz (ddiax) for responsibly disclosing.


#### 4.9.16 - Sep 11, 2024

- Add support for WooCommerce Checkout Block in sign-up checkbox integration.


#### 4.9.15 - Aug 13, 2024

- Improved anti-spam measures on the [custom form integration](https://www.mc4wp.com/kb/subscribe-mailchimp-custom-html-form/). If you are using the custom form integration (using the `mc4wp-subscribe` checkbox), please test your forms after upgrading and report any issues to us.
- Improved anti-spam measures on all sign-up forms.
- Remove unsupported filter hook from Gravity Forms integration. 


#### 4.9.14 - Jul 17, 2024

- Very minor code-size improvements to public forms related JavaScript.
- Update third-party JS dependencies.
- Bump tested WordPress version to 6.6.


#### 4.9.13 - Apr 25, 2024

- Fix issue with Composer classmap throwing a fatal error when an older version of Composer is already loaded.


#### 4.9.12 - Apr 22, 2024 

- Fix last 10 Mailchimp lists not being pulled-in when having more than 10 lists.


#### 4.9.11 - Jan 8, 2024

- Update third-party JS dependencies.
- Bump tested WordPress version.


#### 4.9.10 - Nov 20, 2023

- Integrations: Update CheckoutWC hook name for WooCommerce checkbox integration.
- Forms: Don't show form preview to users without `edit_posts` capability.
- Forms: Explicitly exclude form preview from search engine indexing.
- General: Don't unnecessarily go through service contrainer while bootstrapping plugin.
- General: Remove some unnecessary JavaScript now that browser support has caught up.


#### 4.9.9 - Oct 3, 2023 

- Fix class "MC4WP_Usage_Tracking" not found error for WP Cron / WP CLI processes.


#### 4.9.8 - Oct 3, 2023

- Remove the opt-in usage tracking functionality as we're not really using it for decision making anymore.
- Add missing label element to the select element for setting the logging level.
- Our JavaScript assets are now transpiled to support the same set of browsers as WordPress core. 
This drops support for some very old browsers, but results in smaller bundle sizes for the supported set of browsers.
- Update third-party JS dependencies to their latest versions.


#### 4.9.7 - Aug 29, 2023

- Update third-party JS dependencies.
- Minor textual improvements.
- Bump tested WordPress version.


#### 4.9.6 - Jul 12, 2023

- Update third-party JS dependencies.
- Address some minor codestyle issues.


#### 4.9.5 - Jun 7, 2023

- Fix generated HTML for list/audience choice fields.
- Fix deprecation warning in includes/admin/class-review-notice.php.
- Update JavaScript dependencies.


#### 4.9.4 - May 2, 2023

- Fallback to default checkbox label if none given. Thanks to [Shojib Khan](https://github.com/kshojib).
- Improve WooCommerce integration settings page by disabling position field if integration is disabled. Thanks to [Shojib Khan](https://github.com/kshojib).
- Update JavaScript dependencies.


#### 4.9.3 - Mar 31, 2023

- Defend against breaking change in latest WPForms update.


#### 4.9.2 - Mar 21, 2023

- Add support for a field named `MARKETING_PERMISSIONS` to enable GDPR fields configured in Mailchimp. A [sample code snippet can be found here](https://github.com/ibericode/mailchimp-for-wordpress/blob/main/sample-code-snippets/forms/gdpr-marketing-permissions.md).
- Remove Google reCaptcha feature. This was already disabled if you were not already using it.


#### 4.9.1 - Feb 7, 2023

- Fix generated value attribute for fields of type choice (dropdown, checkboxes, radio fields).
- Fix type of `marketing_permissions` field in API requests. Thanks to [George Korakas](https://github.com/gkorakas-eli).
- Refactor list overview JS to not depend on Mithril.js anymore.
- Simplify admin footer text asking for a plugin review.
- When renewing lists, renew cached marketing permissions too.


#### 4.9.0 - Jan 13, 2023 

- Removed deprecated filter hook `mc4wp_settings_cap`, use `mc4wp_admin_required_capability` instead.
- Removed deprecated filter hook `mc4wp_merge_vars`, use `mc4wp_form_data` or `mc4wp_integration_data` instead.
- Removed deprecated filter hook `mc4wp_form_merge_vars`, use `mc4wp_form_data` instead.
- Removed deprecated filter hook `mc4wp_integration_merge_vars`, use `mc4wp_integration_data` instead.
- Removed deprecated filter hook `mc4wp_valid_form_request`, use `mc4wp_form_errors` instead.
- Removed deprecated function `mc4wp_get_api()` and deprecated class `MC4WP_API`.
- Removed deprecated function `mc4wp_checkbox()`.
- Removed deprecated function `mc4wp_form()`, use `mc4wp_show_form()` instead.
- Added filter `mc4wp_debug_log_message` to modify or disable messages that are written to the debug log.
- Fix color of invalid Mailchimp API key notice.
- Sanitize IP address value from `$_SERVER['REMOTE_ADDR']` too.
- Fetch GDPR marketing permissions via first subscriber on list and show them in lists overview table.


#### 4.8.12 - Dec 06, 2022

- Minor performance, memory usage & size optimizations for all JavaScript code bundled with this plugin.


#### 4.8.11 - Nov 1, 2022

- Improved default styling for the WooCommerce sign-up checkbox integration.
- Add `<strong>` to allowed HTML elements for GDPR disclaimer text on settings pages.
- Remove all references to obsolete placeholders.js polyfill.
- Move the GiveWP sign-up checkbox closer to the email input field. Thanks [Matthew Lewis](https://github.com/Matthew-Lewis).


#### 4.8.10 - Sep 14, 2022

- Fix mc4wp_get_request_ip_address() to return an IP address that matches Mailchimp's validation format when X-Forwarded-For header contains a port component.


#### 4.8.8 - Aug 25, 2022

- Fix mc4wp_get_request_ip_address() to pass new Mailchimp validation format. This fixes the "This value is not a valid IP." error some users using a proxy may have been seeing.


#### 4.8.7 - Mar 2, 2022

- Fix PHP 8.1 deprecation warnings in `MC4WP_Container` class.
- Fix name of action hook that fires before Mailchimp settings rows are displayed on the settings page. Thanks [LoonSongSoftware](https://github.com/LoonSongSoftware).
- Improve WPML compatibility. Thanks [Sumit Singh](https://github.com/5um17).
- Fix deprecated function for AMP integration.
- Only allow unfiltered HTML if user has `unfiltered_html` capability. Please read the below.

Despite extensive testing, we may have missed some more obscure HTML elements or attributes from our whitelist.
If you notice that some of your form HTML is stripped after saving your form, please get in touch with our support team and provide the HTML you attempted to save.


#### 4.8.6 - Jun 24, 2021

- Add nonce field to button for dismissing notice asking for plugin review.
- Add strings from config/ directory to POT file.
- Add nonce check to AJAX endpoint for refreshing cached Mailchimp lists.
- Add capability check to AJAX endpoint for retrieving list details.
- Schedule event to refresh cached Mailchimp list upon plugin activation.

Thanks to the team over at [pluginvulnerabilities.com](https://www.pluginvulnerabilities.com/) for bringing some of these changes to our attention.


#### 4.8.5 - Jun 1, 2021

Add nonce verification to all URL's using `_mc4wp_action` query parameter.
This fixes a CSRF vulnerability where a malicious website could trick a logged-in admin user in performing unwanted actions.

A special thanks to Erwan from [WPScan](https://wpscan.com/) for bringing this issue to our attention.


#### 4.8.4 - May 7, 2021

- Add `defer` attribute to JS file, so page parsing isn't blocked at all.
- Rewrite plugin CSS to optimize for selector performance and get rid of some duplication.

After installing this update, make sure to also update any add-on plugins like [Mailchimp for WordPress Premium](https://www.mc4wp.com/premium-features/) and [Mailchimp Top Bar](https://wordpress.org/plugins/mailchimp-top-bar/).


#### 4.8.3 - Jan 21, 2021

- Fix fatal error on older PHP versions when submitting form without any subscriber tags set in the form settings.
- Minor performance improvement in bootstrap method of the plugin.


#### 4.8.2 - Jan 20, 2021

- Allow short-circuiting `mc4wp_subscriber_data` filter by returning `null` or `false`.
- Use a subdirectory for the default debug log file location, so that it's easier to protect using htaccess.
- Improved reliability for fetching lists from mailchimp when lists have high stats.member_count property.


#### 4.8.1 - Aug 25, 2020

- Fix notice by explicitly setting `permission_callback` on registered REST route.
- Minor internal code improvements.

#### 4.8 - Jul 9, 2020

- Plugin now requires PHP 5.3 or higher.
- Prefix overlay classname to prevent styling collissions with other plugins.
- Form sign-ups can now add tags to both new and existing subscribers.
- Update JavaScript dependencies.
- Register script early to work with Gutenberg preview.


#### 4.7.8 - Jun 04, 2020

- Add `MC4WP_API_V3::add_template` method.
- Minor code hardening to ensure a default form is always set.
- Update JS dependencies to their latest versions.
- Fix icon for Gutenberg block.


#### 4.7.7 - Apr 28, 2020

- Update JS dependencies to their latest versions.
- API client `add_list_member` method now has an additional parameter to skip merge field validation.
- Simplify code for updating an existing form.


#### 4.7.6 - Apr 9, 2020

- Update JS dependencies to their latest versions.
- Check if className is of type string, fixes a console warning when clicking inside a SVG element.
- Minor improvements to the AMP implementation to address harmless validation warnings.


#### 4.7.5 - Feb 10, 2020

- Add AMP compatibility to sign-up forms, thanks to Claudiu Lodromanean. This uses the [official AMP plugin for WordPress](https://amp-wp.org).
- Add settings key to WPML config so settings can easily by copied over to translated versions of a form.
- Optimize size & performance of JavaScript code, resulting in a file that is 40% smaller.
- Update CodeMirror to its latest version.
- Escape all string translations.


#### 4.7.4 - Dec 7, 2019

**Fixes**

- htaccess config for servers running Apache 2.4 or later.


#### 4.7.3 - Dec 4, 2019

**Fixes**

- Top Bar & User Sync add-on using API v2 since version 4.7.1.
- Revert change in formatter for date fields, breaking all forms with date fields in them.

**Improvements**

- Add getter method for raw (unmodified) data on form class.


#### 4.7.2 - Nov 27, 2019

**Fixes**

- Invalid .htaccess file in case there already is one in the uploads directory.


#### 4.7.1 - Nov 26, 2019

**Improvements**

- Update MemberPress hook names. Thanks [Ian Heggaton](https://github.com/pixelated-au)!
- Use WordPress.org translations instead of bundling translation files in plugin itself.
- Write .htaccess to directory of debug log file, to prevent file access.
- Add some convenient hooks for Checkout for WooCommerce.
- Stop parsing shortcodes in text widgets as WordPress core does this since version 4.9.


#### 4.7 - Nov 7, 2019

**Improvements**

- Add role=alert to form notices.
- Add setting to pre-check sign-up checkbox for Gravity Forms integrations.
- Add new position for WooCommerce integration: directly after the billing_email field.
- Fix PHP notices for submitting a form and saving a form as an administrator.
- Add link to [Koko Analytics plugin](https://wordpress.org/plugins/koko-analytics/).


#### 4.6.2 - Oct 24, 2019

**Fixes**

- Address fields in forms would always be required (even if really optional).

**Improvements**

- Add proper SVG admin menu icon.
- Minor overall performance and memory usage improvements.


#### 4.6.1 - Oct 7, 2019

**Fixes**

- Fixed list cache usage for WPForms, Gravity Forms and Ninja Forms integrations.


#### 4.6.0 - Oct 7, 2019

**Improvements**

- Improved fetch and cache mechanism for retrieving Mailchimp account details, fetching data only when it is required.
- Updated [Mithril](https://mithril.js.org/) and [CodeMirror](https://codemirror.net/) dependencies.
- Decreased size of `forms.js` from 22KB to 9KB.
- No longer requiring jQuery anywhere.
- Increase API HTTP request timeout to 15 seconds.

Please note that installing this update requires you to also update any add-ons like [Mailchimp Top Bar](https://wordpress.org/plugins/mailchimp-top-bar/) and [Mailchimp for WordPress Premium](https://www.mc4wp.com/premium-features/) (if installed).


#### 4.5.5 - Sep 12, 2019

**Fixes**

- Google reCAPTCHA script was still loading even if no forms have it enabled.


#### 4.5.4 - Sep 11, 2019

**Improvements**

- Removed custom color from menu item for improved accessibility.
- Take birthday field format into account when sending data to Mailchimp.
- Print Google reCAPTCHA script in footer.

**Changes**

- Changed plugin name to MC4WP instead of Mailchimp for WordPress.


#### 4.5.3 - July 23, 2019

**Fixes**

- Temporarily switch status of pending subscribers to "unsubscribe" versus deleting susbcriber before re-subscribing.
- Deprecation notice for Gravity Forms version 2.4 and higher.

**Improvements**

- Filter out empty tags when applying tags to new subscribers.
- Show all not installed integrations.
- Show notice when form doesn't have a Mailchimp list selected to subscribe people to.
- Check function existence for compatibility with WordPress 4.7
- Don't submit form when Google reCAPTCHA is enabled but errors.
- Update third-party JavaScript dependencies.


#### 4.5.2 - May 8, 2019

**Improvements**

- Accept more truthy values in custom integration for improved compatibility with third-party forms.
- Update JavaScript dependencies.
- Load Google reCaptcha script in footer (if needed).


#### 4.5.1 - April 8, 2019

**Additions**

- Add sign-up integration for [Give](https://wordpress.org/plugins/give/)
- Add sign-up integration for [UltimateMember](https://wordpress.org/plugins/ultimate-member/)

**Improvements**

- Write to debug log if Google reCAPTCHA secret key is incorrect.
- Validate reCAPTCHA keys when savings form settings.
- Allow setting an empty "successfully subscribed" message.


#### 4.5.0 - March 27, 2019

**Additions**

- Built-in integration with Google reCAPTCHA to prevent bots from subscribing to your Mailchimp lists.

**Improvements**

- Minor improvements to the JavaScript that is loaded on admin pages.


#### 4.4.0 - March 1, 2019

**Fixes**

- AffiliateWP integration subscribing the wrong user if affiliate ID differs from user ID.

**Improvements**

- Renamed "MailChimp" to "Mailchimp" to match Mailchimp's new branding.
- More accurate handling of timeouts for accounts with many MailChimp lists.
- UX improvements for integrations overview page.
- Validate MailChimp API key format when it's entered.
- Improved compatibility with Klarna Checkout in the WooCommerce checkout integration.
- Bumped required PHP version to 5.3 (soft requirement for now).

**Additions**

- Added Gutenberg block for easily adding a form to a post or page.
- Added subscriber tags setting to forms.


#### 4.3.3 - December 31, 2018

**Fixes**

- Update WPForms integration to properly detect if the WPForms plugin is activated.

**Improvements**

- Write API request parameters to the debug log in case of connection timeouts.
- Update JavaScript dependencies.


#### 4.3.2 - December 11, 2018

**Fixes**

- Use of `readonly` function, which is only available in WordPress 4.9 or later.


#### 4.3.1 - November 28, 2018

**Fixes**

- Fatal error on PHP versions older than 5.5


#### 4.3 - November 28, 2018

**Additions**

- Added `MC4WP_API_KEY` PHP constant which can be used to set your Mailchimp API key.
- Add `mc4wp_mailchimp_list_limit` filter hook to modify the maximum number of Mailchimp lists to fetch. Defaults to 200.

**Improvements**

- Apply `mc4wp_integration_gravity-forms_options` filter hook on Gravity Forms integration options so the checkbox can be prechecked and the checkbox label text modified.
- The `updated_subscriber` JS event is now fired forms not using AJAX as well (when applicable).


#### 4.2.5 - Sep 11, 2018

**Improvements**

- Only re-add subscriber to list if we want to re-trigger a double opt-in confirmation email.
- Change Gravity Forms field name to "Mailchimp for WordPress"
- Get rid of cached result of Mailchimp API connection.


#### 4.2.4 - July 9, 2018

**Improvements**

- Ensure type-safety on some global variables.
- Stop showing trashed forms immediately.
- Pre-check Mailchimp list when creating a new form if there is only 1 list.
- Send `null` for unknown values in usage tracking data (only when opted-in).

**Additions**

- Add methods for accessing Mailchimp's e-commerce promo code endpoints to API class.


#### 4.2.3 - June 11, 2018

**Fixes**

- Don't wrap "agree to terms" input in hyperlink element.
- Allow [ENTER] key again after field helper overlay is closed.

**Improvements**

- Fallback to meta-refresh if redirect fails because of "headers already sent" error.



#### 4.2.2 - May 22, 2018

**Fixes**

- Events Manager integration was not working with logged-in users.
- Form preview URL should respect admin HTTP(S) scheme.
- Removed use of PHP 5.4 function.

**Improvements**

- Add "agree to terms" checkbox to field helper.

**Additions**

- Add filter `mc4wp_http_request_args`.


#### 4.2.1 - April 11, 2018

**Fixes**

- Namespace usage warning when running PHP 5.2

**Improvements**

- Remove obsolete `type` attribute from all `<script>` tags printed by the plugin.
- Improved tooltips on settings pages.
- Do not pre-check integration checkboxes by default.
- Add textual warnings to settings that may affect [GDPR compliance](https://www.mc4wp.com/kb/gdpr-compliance/).
- Update translation files.

#### 4.2 - March 5, 2018

**Additions**

- Live form preview while editing form.

**Improvements**

- Improved [conditional fields logic](https://www.mc4wp.com/kb/conditional-fields-elements/).
- Debug log now includes request & response data.
- [Form JavaScript events](https://www.mc4wp.com/kb/javascript-form-events/) are fired in an isolated thread now, to prevent errors in event callbacks from breaking form functionality.
- Don't send empty field values to Mailchimp when updating subscribers.
- Show interest grouping ID in list overview on settings page.

**Fixes**

- Ninja Forms export checkbox would always state "checked" when form contained a Mailchimp sign-up checkbox.



#### 4.1.15 - February 7, 2018

**Fixes**

- Dropdown fields with special characters were not properly passed to Mailchimp.
- Interest groups with an all-numeric ID were not properly passed to Mailchimp.

**Improvements**

- Various minor code optimizations
- Do not redirect when showing "already subscribed" warning.
- Improved scroll to form handling after a form is submitted without AJAX.


#### 4.1.14 - January 8, 2018

**Fixes**

- Validate method was incorrectly checking required array fields.

**Improvements**

- Wrap some missing strings in translate calls. Thanks [morlor](https://github.com/morloi).
- Make it clear that redirecting after successful form submissions will not show the "subscribed" message.



#### 4.1.13 - December 28, 2017

**Fixes**

- Array to string conversion in default form messages.

**Additions**

- Allow marking Gravity Forms sign-up checkbox as a required field.


#### 4.1.12 - December 11, 2017

**Fixes**

- Ninja Forms double opt-in setting was incorrectly inversed.

**Improvements**

- Simplified form processing & notice logic.
- Prevent 404 errors by proactively replacing lowercased `name="name"` input attributes.
- Updated JavaScript dependencies.

**Additions**

- Integration for AffiliateWP.


#### 4.1.11 - November 2, 2017

**Fixes**

- Filter out empty array values when overriding selected Mailchimp lists via `_mc4wp_lists`.

**Improvements**

- Updated JavaScript dependencies.

**Additions**

- Link to the [HTML Forms](https://www.htmlforms.io/) from the plugin settings pages.


#### 4.1.10 - October 19, 2017

**Improvements**

- Remove unused options from Ninja Forms integration.
- Now logging all sign-ups from Ninja Forms integrations when using [Mailchimp for WordPress Premium](https://www.mc4wp.com/premium-features/).

**Additions**

- Added Gravity Forms integration. You can now integrate with Gravity Forms by adding the "Mailchimp" field to your forms.


#### 4.1.9 - September 19, 2017

**Improvements**

- Add `<label>` element to sign-up checkbox for WCAG compatibility.
- Custom integration now works with Enfold theme's contact form element.


#### 4.1.7 & 4.1.8 - September 8, 2017

**Fixes**

- Properly escape the return value of `add_query_arg` when it is used in HTML attributes to prevent cross-site scripting. Thanks to [Karim Ouerghemmi of RIPS](https://www.ripstech.com/) for responsibly disclosing.
- Now loading integrations after WPML so that String Translations work properly.

**Additions**

- Add sign-up integration for WPForms forms.

**Improvements**

- Updated internal JS dependencies.
- Form tag `{data key="foo.bar"}` now allows you to access nested array values.


#### 4.1.6 - July 31, 2017

**Fixes**

- Method on API class for retrieving campaign data.

**Improvements**

- Show Akamai reference number when an API request is blocked by Mailchimp's firewall.
- Minor output buffering improvements in form previewer.


#### 4.1.5 - June 27, 2017

**Fixes**

- Failsafe against outputting sign-up checkbox twice in registration forms.
- Properly close HTML anchor element in French translation files.
- Fix BuddyPress sign-ups when using WordPress Multisite.

**Improvements**

- Fire action hook `mc4wp_form_updated_subscriber` whenever a form was used to update a subscriber in Mailchimp.
- Increase browser timeout for AJAX request when fetching Mailchimp lists.

**Additions**

- Added campaign & template methods to API client class.



#### 4.1.4 - June 15, 2017

**Fixes**

- Some form specific JS events were not firing due to incorrect event names.
- Registration form integration now works with WooCommerce registration form.
- Notice that asks for a plugin review would re-appear after dismissing it.


#### 4.1.3 - May 24, 2017

**Improvements**

- Randomise time of cron event that renews Mailchimp lists.
- Always try to show Mailchimp list info when API key is given.


#### 4.1.2 - May 8, 2017

**Fixes**

- Use earlier hook priority for Ninja Forms 3 integration so action is registered on time.

**Improvements**

- Improved Mailchimp list fetching & memory usage for accounts with many lists.
- Show error message when fetching lists fails.
- Updated plugin translations.

#### 4.1.1 - April 11, 2017

**Fixes**

- WPML String Translation not working with the checkbox label for sign-up integrations.

**Improvements**

- Use updated order methods when using WooCommerce 3.0, thanks to Liam McArthur.
- Updated JavaScript dependencies.


#### 4.1.0 - March 14, 2017

**Improvements**

- Updated all JavaScript dependencies in the plugin.
- Failsafed filter hooks to prevent invalid variable types.
- Explain that greyed out integrations means that specific plugin is not activated.
- Conditional form elements now uses event delegation, so it works with forms in [Boxzilla pop-ups](https://boxzillaplugin.com/).
- Updated language files.

**Additions**

- Added support for Ninja Forms 3.
- Added `mc4wp_integration_show_checkbox` filter.


#### 4.0.13 - February 8, 2017

**Improvements**

- Ensure fields are HTML decoded before sending to Mailchimp.
- Better OptimizePress compatibility.
- Show all address-type fields as required when form contains 1 or more fields of the same address group.


#### 4.0.12 - January 16, 2017

**Fixes**

- Don't call `stripslashes` on POST data twice.

**Improvements**

- Plugin review notice is now dismissible over AJAX.
- Improved formatting of birthday fields.
- Updated Polish translations, thanks to Mateusz Lomber.
- Updated German translations, thanks to Sven de Vries.

**Additions**

- Add `update_ecommerce_store_product` method to API class.
- Throw form specific JavaScript events, like `15.subscribed` to hook into "subscribed" events for form with ID 15.


#### 4.0.11 - December 9, 2016

**Fixes**

- Unescaped request variable on integration settings page, allowing for authenticated XSS. Thanks to [dxwsecurity](https://security.dxw.com/) for responsibly disclosing.

**Improvements**

- Add `$args` parameter to `API::get_lists_activity` method. Relates to the [Mailchimp Activity](https://wordpress.org/plugins/mc4wp-activity/) plugin.


#### 4.0.10 - December 6, 2016

**Improvements**

- You can now enable or disable debug logging from the "Other" settings page.
- No longer using deprecated function in Contact Form 7, thanks to [stodorovic](https://github.com/stodorovic).
- Improved UI for adding hidden interest groupings fields to a form.


#### 4.0.9 - November 23, 2016

**Fixes**

- Issue with escaped HTML when using form tags introduced by previous update.


#### 4.0.8 - November 23, 2016

**Improvements**

- Improved handling of large debug logs.
- Improved error messages when writing exceptions to debug log.
- Show notice when form is missing required Mailchimp fields.
- Custom form integration now handles arrays with 1-level depth. Thanks to [Mardari Igor](https://github.com/GarryOne).
- You can now use nested tags in your form code, eg `{data key="utm_source" default="{current_path}"}`

**Additions**

- Add `data-hide-if` attribute logic to forms. See [conditionally hide form fields](https://www.mc4wp.com/kb/conditional-fields-elements/). Thanks to [Kurt Zenisek](http://kurtzenisek.com/).
- Add hooks for delayed BuddyPress sign-up. Thanks to [Christian Wach](https://profiles.wordpress.org/needle).


#### 4.0.7 - October 25, 2016

**Improvements**

- Obfuscate all email addresses in debug log. Thanks [Sauli Lepola](https://twitter.com/SJLfi).
- Ask for confirmation before disabling double opt-in, which we do not recommend.
- Allow vertical resizing of debug log.
- Failsafe against including JavaScript file twice.
- No longer wrapping CF7 checkbox in paragraph tags.

**Additions**

- Added `mc4wp_form_api_error` action hook for API errors encountered by forms.
- Added `element_class` argument to `[mc4wp_form]` shortcode for adding CSS classes.


#### 4.0.6 - October 10, 2016

**Fixes**

- Issue with lists not showing when using W3 Total Cache with APCu object cache enabled.

**Improvements**

- We're no longer stripping newlines from text fields.

**Additions**

- Added missing e-commerce related API methods to API class.


#### 4.0.5 - September 29, 2016

**Fixes**

- Allow checkbox option for the List Choice field (again).

**Improvements**

- Fetch Mailchimp lists over AJAX, to speed up perceived performance (especially when your account has many lists).
- Periodically fetch Mailchimp lists, so cache is always fresh.
- Improved `<label>` element accessibility for checkbox integrations.
- Stop using double underscore prefix in function names, as these are reserved in PHP 7.
- `{post}` and `{user}` shortcodes now accept a `default` parameter.

**Additions**

- Add [MemberPress](https://www.memberpress.com/) integration.
- Add missing e-commerce related API methods for next week's [WooCommerce Mailchimp e-commerce integration](https://www.mc4wp.com/kb/what-is-ecommerce/) release.


#### 4.0.4 - September 7, 2016

**Improvements**

- Allow re-running previous migrations by visiting a certain admin URL.
- Do not show checkboxes option for fields that only accept a single value.
- Write field specific errors to debug log when Mailchimp denies a sign-up request.
- Write to debug log when custom integrations can not find an EMAIL field.
- Differentiate between connection & authorization errors when testing connection to Mailchimp.
- Bump limit of number of Mailchimp lists to fetch from 100 to 500.


#### 4.0.3 - August 24, 2016

**Fixes**

- Ninja Forms integration not working when using PayPal integration.

**Improvements**

- Show connection errors on Mailchimp settings page.

**Additions**

- Add pre-checked option to Ninja Forms integration.
- You can now [conditionally hide fields or elements](https://www.mc4wp.com/kb/conditional-fields-elements/) using the `data-show-if` attribute.


#### 4.0.2 - August 10, 2016

**Fixes**

- Hidden fields which referenced interest groups by name were ...

== Upgrade Notice ==

= 3.0.3 =

Minor improvements and re-added support for Goodbye Captcha integration.
