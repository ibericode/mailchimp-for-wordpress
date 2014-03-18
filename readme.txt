=== MailChimp for WordPress ===
Contributors: DvanKooten
Donate link: http://dannyvankooten.com/donate/
Tags: mailchimp,form,shortcode,widget,checkbox,comment,newsletter,buddypress,multisite,bbpress,woocommerce,easy digital downloads,contact form,contact form 7
Requires at least: 3.1
Tested up to: 3.8.1
Stable tag: 1.5.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The best MailChimp plugin to get more email subscribers. Easily add sign-up forms and sign-up checkboxes to your WordPress website.

== Description ==

= MailChimp for WordPress =

*Adding sign-up methods for your MailChimp lists to your WordPress site should be easy. With this plugin, it is.*

MailChimp for WordPress lets you create a highly customizable sign-up form which you can display wherever you want it to display using a simple shortcode, widget or template function.  You can also add sign-up checkboxes to various forms on your site, like your comment or contact forms. 

= Sign-Up Forms =
Easily create sign-up forms for your MailChimp list and display it using a simple shortcode, widget or template function.

= Sign-Up Checkboxes =
Add sign-up checkboxes to *any* form on your website. The plugin offers built-in integration with comment forms, registration forms, Contact Form 7, BuddyPress, bbPress and WordPress MultiSite.

**MailChimp for WordPress, at a glance..**

- Simple. All you need is your MailChimp API key.
- Customizable. Have the form fields generated for you or write your own HTML. 
- Beautiful. Choose one of the default form themes or write your own CSS.
- Developer friendly.

[Installation](http://wordpress.org/plugins/mailchimp-for-wp/installation/) | [Frequently Asked Questions](http://wordpress.org/plugins/mailchimp-for-wp/faq/) | [Screenshots](http://wordpress.org/plugins/mailchimp-for-wp/screenshots/)

> **Premium features**
> 
> Multiple forms, AJAX, form designer, custom themes, detailed statistics, more built-in checkbox integrations and priority support.
> 
> [More information](http://dannyvankooten.com/mailchimp-for-wordpress/?utm_source=wp-plugin-repo&utm_medium=link&utm_campaign=after-features-link) | [Form demo's](http://dannyvankooten.com/mailchimp-for-wordpress/demo/?utm_source=wp-plugin-repo&utm_medium=link&utm_campaign=after-features-link) | [Upgrade now >>](http://dannyvankooten.com/mailchimp-for-wordpress/?utm_source=wp-plugin-repo&utm_medium=link&utm_campaign=after-features-link)

**More information**

Take a look at some other [WordPress plugins](http://dannyvankooten.com/wordpress-plugins/?utm_source=wp-plugin-repo&utm_medium=link&utm_campaign=more-info-link) by [Danny van Kooten](http://dannyvankooten.com?utm_source=wp-plugin-repo&utm_medium=link&utm_campaign=more-info-link) or contact him on Twitter: [@DannyvanKooten](http://twitter.com/dannyvankooten).


== Installation ==

= Installing the plugin =
1. In your WordPress admin panel, go to *Plugins > New Plugin*, search for *MailChimp for WordPress* and click "Install now"
1. Alternatively, download the plugin and upload the contents of `mailchimp-for-wp.zip` to your plugins directory, which usually is `/wp-content/plugins/`.
1. Activate the plugin
1. Set your MailChimp API key in the plugin settings.

= Configuring Sign-Up Checkboxes =
1. Go to *MailChimp for WP > Checkboxes*
1. Select at least one list to subscribe visitors to.
1. Select at least 1 form to add the checkbox to, eg your comment form.

= Configuring Sign-Up Form(s) =
1. Go to *MailChimp for WP > Forms*
1. Select at least one list to subscribe visitors to.
1. *(Optional)* Add more fields or dynamic content to your form using the **add field** tool.
1. Show the form in pages or posts by using the `[mc4wp_form]` shortcode.
1. Show the form in your widget areas using the plugin widget.
1. Show the form from your template files by calling `mc4wp_form()`

Need help? Please take a look at the [frequently asked questions](http://wordpress.org/plugins/mailchimp-for-wp/faq/) first

= Upgrade to Pro =
If you like the plugin, upgrade to [MailChimp for WordPress Pro](http://dannyvankooten.com/mailchimp-for-wordpress/?utm_source=wp-plugin-repo&utm_medium=link&utm_campaign=installation-instructions-link) for an even better plugin.

== Frequently Asked Questions ==

> **Is there a premium version of this plugin?**
>
> Yes, you will love it. Some Pro features are:
> 
> 1. Multiple forms, each subscribing to one or multiple MailChimp list(s).
> 1. AJAX - no page reload after submitting a sign-up form.
> 1. Custom color themes and a custom form styles designer.
> 1. Statistics & log, learn when, where and how your visitors subscribed. 
> 
> [More Pro features](http://dannyvankooten.com/mailchimp-for-wordpress/?utm_source=wp-plugin-repo&utm_medium=link&utm_campaign=faq-link) | [Demo](http://dannyvankooten.com/mailchimp-for-wordpress/demo/?utm_source=wp-plugin-repo&utm_medium=link&utm_campaign=faq-link)

= How to display a form in posts or pages? =
Use the `[mc4wp_form]` shortcode.

= How to display a form in widget areas like a sidebar? =
Use the **MailChimp for WP Form** Widget that comes with the plugin.

= How to display a form in my template files? =
Use the `mc4wp_form()` function.

`
if( function_exists( 'mc4wp_form' ) ) {
	mc4wp_form();
}
`

= Oops. Something went wrong. =
`Admin notice: FNAME must be provided - Please enter a value`

Your selected MailChimp list requires a field named **FNAME**. Either go into your MailChimp list settings and make the FNAME field optional or add it to your form (using the *Add MailChimp field** select box).

= The form shows a success message but subscribers are not added to my list(s)? =
If the form shows a success message, it means MailChimp accepted the sign-up request and will take over from there. MailChimp could have a slight delay sending the confirmation email though, just be patient.

= How can I style the sign-up form? =
You can use CSS rules to style the sign-up form, use the following CSS selectors to target the various form elements.

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

Add your custom CSS rules to the end of your theme stylesheet, **/wp-content/themes/your-theme-name/style.css**. Do not add them to the plugin stylesheet as they will be automatically overwritten on the next plugin update.

[PS: With the Pro version, you can design beautiful forms without touching any code >>](http://dannyvankooten.com/mailchimp-for-wordpress/?utm_source=wp-plugin-repo&utm_medium=link&utm_campaign=after-css-link)

= Where can I find my MailChimp API key? =
[Here](http://kb.mailchimp.com/article/where-can-i-find-my-api-key)

= How to add a sign-up checkbox to my Contact Form 7 forms? =
Use the following shortcode in your CF7 form mark-up to display a sign-up checkbox. 

`[mc4wp_checkbox "My custom label text"]`

You can then use `[mc4wp_checkbox]` inside your CF7 email templates, it will show "yes" or "no".

If you need more data for your merge fields, prefix the field name with `mc4wp-`.

*Example CF7 template for MailChimp WEBSITE field*
`
[text* mc4wp-WEBSITE]
`

= Can I add a checkbox to third-party forms? =
Yes. Just include a checkbox with name attribute `mc4wp-try-subscribe` and value `1` in your form.

*Example HTML*
`
<label><input type="checkbox" name="mc4wp-try-subscribe" value="1" /> Subscribe to our newsletter?</label>
`

If you need to send more data to your MailChimp list, prefix the name attribute with `mc4wp-`.

*Example HTML code for MailChimp list field called WEBSITE*
`
<label>Your website:</label>
<input type="text" name="mc4wp-WEBSITE" />
`

= How do I add subscribers to certain interest groups? =
Use the field wizard. Or, if you know more about HTML, the following snippet should get you started. *Replace `###` with your grouping ID or grouping name.*

`
<label><input type="checkbox" name="GROUPINGS[###][]" value="Group 1" /> Group 1</label>
<label><input type="checkbox" name="GROUPINGS[###][]" value="Group 2" /> Group 2</label>
`
Or, if you want to use a hidden field...

`
<input type="hidden" name="GROUPINGS[###]" value="Groupname 1,Groupname 2,Groupname 3" />
`

= I don't see new subscribers but they are still added to my list =
When you have double opt-in disabled, new subscribers will be seen as *imports* by MailChimp. They will not show up in your daily digest emails or statistics. My recommendation is to leave double opt-in enabled.

= Can I add more (hidden) fields to the sign-up checkbox? =
Not at the moment, but you can add more data using a filter. Here is a code snippet to [add grouping information to comment form sign-ups](https://gist.github.com/dannyvankooten/7120559).

= Why does the checkbox not show up at my comment form? =
Your theme probably does not support the necessary comment hook this plugin uses to add the checkbox to your comment form. You can manually place the checkbox by placing the following code snippet inside the form tags of your theme's comment form.

`<?php if(function_exists('mc4wp_checkbox')) { mc4wp_checkbox(); }?>`

Your theme folder can be found by browsing to `/wp-content/themes/your-theme-name/`.

== Screenshots ==

1. Simple or advanced sign-up forms that blend in with your theme.
2. A sign-up checkbox in your comment form is an amazing conversion booster.
3. A simple form in the footer of the Twenty Thirteen theme.
4. Add sign-up checkboxes to various places on your site. 
5. Creating sign-up forms is easy. The Pro version allows you to create as many form as you like.
6. Write your own HTML or have it generated for you. Many (optional) customization settings are availabl.
7. **Pro only:** Gain valuable insights which method your visitors used to subscribe for any given time period using beautiful line charts.
8. **Pro only:** Create your own CSS styles with the form designer in the Pro version.


== Changelog ==

= 1.5.7 - March 14, 2014 =
**Fixes**

- Fixed special characters in group names not working

** Improvements**

- Moved away from Singleton pattern
- Added vertical-align to form reset stylesheet to properly align inline fields
- Added a code version number for upgrade routines
- Better class documentation
- MailChimp cached data table improvements
- Base form CSS improvements


= 1.5.6 - March 13, 2014 =
* Fixed: Honeypot textarea showing in some themes
* Improved: Plugin will automatically strip duplicate `<form>` tags from form mark-up
* Improved: Better code documentation
* Improved: Code is now more adhering to WP code standards
* Improved: Add custom error type to error message filter to allow developers to show custom error messages
* Improved: Plugin will now show detailed errors for failed API requests (up to HTTP level)
* Improved: Better way of loading plugin files

= 1.5.5 - February 25, 2014 =
* Fixed: Field generator only generating text fields
* Fixed: Now using correct deactivation hook
* Improved: Plugin now fully compatible with custom folder names

= 1.5.4 - February 17, 2014 =
* Fixed: "Add to form" button not working

= 1.5.3 - February 16, 2014 =
* Fixed: Undefined constant notice on admin pages
* Fixed: "Add to form mark-up" button not working with CKEditor for WordPress
* Improved: Cleaned-up Admin JS
* Improved: You can now use `[mc4wp_checkbox]` inside your CF7 email templates
* Improved: You can now add `default:1` or `default:0` to the CF7 shortcode to check or uncheck the sign-up checkbox.

= 1.5.2 - February 4, 2014 =
* Improved: Improved direct file access security
* Improved: Now using native WP function to catch SSL requests
* Improved: Changed `call` method in API class to public.
* Added: Filter to edit the required capability to access settings pages
* Added: Filter to edit form action
* Added: Filters to allow extra form validation, like a captcha field.
* Added: Added `get_member_info` and `list_has_subscriber` method to API class.

= 1.5.1 - January 5, 2014 =
* Fixed: Having to submit form twice for some www-hosts.
* Improved: Scroll to form now waits until page has completely loaded

= 1.5 - December 18, 2013 =
* Added: BIRTHDAY fields will now be formatted in the DD/MM format automatically
* Added: The plugin will now try to automatically format ADDRESS fields.
* Added: Form fields will now keep their value when a validation error occurs
* Improved: Cache headers for CSS file
* Improved: Added notice when no lists selected and using sign-up checkboxes
* Improved: Various code improvements
* Fixed: Error when activating Pro with the Lite plugin still activated.
* Fixed: BuddyPress & MultiSite checkbox not automatically added

= 1.4.8 - December 10, 2013 =
* Fixed: "bug" that fetched lists again on every plugin settings page - huge performance improvements on the settings pages.
* Improved: Longer cache time for combined CSS file.
* Improved: Prevented indexing of plugin directories
* Improved: Improved default checkbox CSS for themes that have custom checkbox styling.
* Improved: Better scroll to form element after form submit. Vertically centers form element with and without jQuery now. No ugly page jump.
* Improved: WP 3.8 Admin CSS compatibility and other improvements to settings pages, especially for small(er) screens.

= 1.4.7 - December 4, 2013 =
* Fixed: Checkbox width not being reset when loading default CSS.
* Improved: Minor security improvement to prevent some plugin files from being accessed directly.

= 1.4.6 - November 27, 2013 =
* Fixed: Incorrect invalid email address notice showing up every time.
* Fixed: Incorrect form action url for some servers.

= 1.4.4 - November 26, 2013 =
* Fixed: FNAME and LNAME not being guessed from NAME for form sign-ups.
* Added: very small JavaScript fallback for placeholders in older browsers (<= IE9)
* Improved: removed limit from the lists retreived from MailChimp, for users with more than 25 lists.
* Improved: added current page URL to form action attribute for people using `<base>` url's.
* Improved: removed the sidebar from the admin pages on small screens
* Improved: various usability improvements
* Improved: minor improvements to default CSS styles
* Improved: added various action and filter hooks to the form sign-up process

= 1.4.3 - November 19, 2013 =
* Improved: added filter hook `mc4wp_lists` to customize lists before sending request to MailChimp.
* Improved: added empty `index.php` files to directories to prevent directory listings

= 1.4.2 - November 11, 2013 =
* Improved: Minor textual improvements in settings pages
* Improved: Security improvement, plugin file can't be access directly anymore
* Added: GPL license to plugin files

= 1.4.1 - October 29, 2013 =
* Fixed: Grouping data not being sent to MailChimp when using sign-up forms.

= 1.4 - October 28, 2013 =
* Added: default form CSS themes, choose between light, red, green, blue or dark form styling.
* Added: filter to add more variables to Checkbox Sign-Ups.
* Improved: more fields unlocked in "add field" tool when editing forms.
* Improved: smarter auto-detection of name fields when integrating with third-party forms like Contact Form 7
* Changed: links point to new [MailChimp for WordPress](http://dannyvankooten.com/mailchimp-for-wordpress/) page now.

= 1.3.1 - October 20, 2013 =
* Fixed: bug when calling MailChimp API for PHP 5.2
* Improved: better default form CSS
* Improved: Combined checkbox and form stylesheets into 1 file and encouraged browser caching.

= 1.3 - October 13, 2013 =
* Added: Form widget
* Added: Smooth scroll to form element after form submission (if jQuery loaded)
* Improved: Added and removed some buttons from QTags editor toolbar
* Improved: Some UI improvements
* Improved: Optimized integration with third-party forms like Contact Form 7

= 1.2.5 - October 8, 2013 =
* Fixed `undefined function mc4wp_replace_variables` fatal error when using Quick Cache plugin.

= 1.2.4 - October 6, 2013 =
* Improved: code performance improvements
* Improved: added `mc4wp_get_form()` for an easier shortcode callback. Useful to [add a sign-up form to the end of your posts](http://dannyvankooten.com/2577/add-mailchimp-sign-up-form-end-of-posts/).
* Improved default CSS
* Improved: checkbox debug message only shows to WP Administrators when JavaScript is disabled
* Added: form nonce for better security
* Fix: CSS issue where the form caused a huge sidebar gap in some themes.

= 1.2.3 - October 3, 2013 =
* Fixed: bug where some MailChimp fields were not showing in the field wizard / add field tool.

= 1.2.2 - September 30, 2013 =
* Fixed sending extra list fields when integrating with third-party forms like Contact Form 7

= 1.2.1 - September 29, 2013 =
* Improved: total revamp of the form field wizard, many improvements.
* Improved: some textual improvements in the setting pages
* Added: debug message to sign-up checkbox for WP administrators

= 1.2 - September 23, 2013 =
* Improved: updated to MailChimp 2.0 API
* Improved: now using custom light-weight API class using the WordPress HTTP API.
* Improved: huge performance improvements on admin settings pages
* Improved: usability and responsiveness of form settings page
* Improved: clean-up

= 1.1.4 =
* Fixed: usage of textarea elements in the form mark-up for WP3.3+.

= 1.1.3 =
* Added: first and lastname to registration hook, works with Register Redux Plus for example.

= 1.1.2 =
* Fixed: field wizard initial value not being set in some browsers
* Fixed: CF7 checkbox subscribing everyone regardless of checkbox setting
* Added: bbPress compatibility, you can now add a sign-up checkbox to the new topic and new reply forms
* Improved: various code and debug improvements
* Improved: field wizard now wraps radio inputs and checkboxes in a label
* Improved: Usability when using sign-up checkbox with Contact Form 7
* Removed: form usage option

= 1.1.1 =
* Fixed warning for BuddyPress sites

= 1.1 =
* Fixed: spam comments not being filtered
* Fixed: Automatic splitting of NAME into FNAME and LNAME
* Added: HTML 5 url, tel and date fields to field wizard
* Added: Form variables for usage inside form mark-up.
* Improved: default form CSS
* Improved: Contact Form 7 integration

= 1.0.3 =
* Added HTML quicktags to form markup textarea.
* Added option to set custom label when using Contact Form 7 shortcode `[mc4wp_checkbox "Your checkbox label"]`
* Added HTML comments
* Added upgrade link to plugins overview
* Improved compatibility with third-party plugins when using checkbox, smarter e-mail field guessing
* Improved: easier copying of the form shortcode from form settings pages
* Added: uninstall function

= 1.0.2 =
* Improved code, less memory usage
* Added `mc4wp_form()` function for usage inside template files

= 1.0.1 =
* Changed: format for groups is now somewhat easier. Refer to the FAQ and update your form mark-up please. (Backwards compatibility included)
* Added: group preset to form field wizard for hidden fields, checkboxes and radio inputs.
* Added: radio inputs to field wizard
* Improved: the field wizard will now add labels after the checkbox and radio input elements.
* Fixed: regular error messages not being shown in some cases.

= 1.0 =
* Added support for group checkboxes
* Added support for paragraph elements in error and success messages, the messages are now wrapped in `<div>` instead. Update your custom CSS rules
* Added some translation filters for qTranslate and WPML compatibility.

= 0.8.3 =
* Added: Guess first and last name when only using full name field.
* Added: Links to [MailChimp for WordPress Pro](http://dannyvankooten.com/mailchimp-for-wordpress/)
* Fixed: Bug where options could not be saved after adding specific HTML tags to the form mark-up.

= 0.8.2 =
* Improved: Namespaced form CSS classes
* Improved: Improved error messages
* Improved: It is now easier to add fields to your form mark-up by using the wizard. You can choose presets etc.
* Improved: All field names that are of importance for MailChimp should now be uppercased (backwards compatibility is included)
* Improved: Fields named added through the wizard are now validated and sanitized
* Improved: Added caching to the backend which makes it way faster
* Improved: Various usability improvements

= 0.8.1 =
* Fixed: typo in form success message
* Improved: various little improvements
* Added: option to hide the form after a successful sign-up

= 0.8 =
* Changed links to show your appreciation for this plugin.
* Improved: CSS reset now works for registration forms as well.
* Improved: Code, removed unnecessary code, only load classes when not existing yet, etc.
* Improved: hooked into user_register to allow third-party registration form plugins.
* Added: Shortcode for usage inside Contact Form 7 form templates `[mc4wp_checkbox]`
* Added: Catch-all, hook into ANY form using ANY input field with name attribute `mc4wp-try-subscribe` and value `1`.
* Fixed: Subscribe from Multisite sign-up
* Fixed: 404 page when no e-mail given.


= 0.7 =
* Improved: small backend JavaScript improvements / fixes
* Improved: configuration tabs on options page now work with JavaScript disabled as well
* Added: form and checkbox can now subscribe to different lists
* Added: Error messages for WP Administrators (for debugging)
* Added: `mc4wp_checkbox()` function to manually add the checkbox to a comment form.

= 0.6.2 =
* Fixed: Double quotes now enabled in text labels and success / error messages (which enables the use of JavaScript)
* Fixed: Sign-up form failing silently without showing error.

= 0.6.1 =
* Fixed: error notices
* Added: some default CSS for success and error notices
* Added: notice when form mark-up does not contain email field

= 0.6 =
* Fixed: cannot redeclare class MCAPI
* Fixed: scroll to form element
* Added: notice when copying the form mark-up instead of using `[mc4wp_form]`
* Added: CSS classes to form success and error message(s).
* Removed: Static element ID on form success and error message(s) for W3C validity when more than one form on 1 page.

= 0.5 =
* Fixed W3C invalid value "true" for attribute "required"
* Added scroll to form element after form submit.
* Added option to redirect visitors after they subscribed using the sign-up form.

= 0.4.1 =
* Fixed correct and more specific error messages
* Fixed form designer, hidden fields no longer wrapped in paragraph tags
* Added text fields to form designer
* Added error message when email address was already on the list
* Added debug message when there is a problem with one of the (required) merge fields

= 0.4 =
* Improved dashboard, it now has different tabs for the different settings.
* Improved guessing of first and last name.
* Fixed debugging statements on settings page
* Added settings link on plugins overview page
* Added form functionality
* Added form shortcode
* Added necessary filters for shortcodes to work inside text widgets
* Added spam honeypot to form to ignore bot sign-ups
* Added error & success messages to form
* Added Freddy icon to menu

= 0.3 =
* Fixed the missing argument bug when submitting a comment for some users.
* Added support for regular, BuddyPress and MultiSite registration forms.

= 0.2 =
* Fixed small bug where name of comment author was not correctly assigned
* Improved CSS reset for checkbox

= 0.1 =
* BETA release

== Upgrade Notice ==

= 1.5.5 =
Fixed field generator only generating `text` input fields.

= 1.5.4 =
Fixed admin notice and "add to form" button, improved Contact Form 7 integration.

= 1.5.3 =
Fixed admin notice and improved Contact Form 7 integration.

= 1.4.8 =
WP 3.8 compatibility, better scroll to form and huge settings page performance improvement

= 1.4.5 =
Bugfix: fixed invalid email address message after updating to 1.4.4

= 1.4.1 =
Fixed grouping information not being sent to MailChimp when using sign-up forms.

= 1.4 =
New: default form themes, more unlocked fields in the "add field" tool and smarter auto-detection of name fields when integrating with Contact Form 7.

= 1.2.5 =
Fixed CSS issue where the form caused a hue gap in the sidebar for some themes.

= 1.1.1 =
Bugfix for BuddyPress sites

