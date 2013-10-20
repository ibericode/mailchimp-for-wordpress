=== Plugin Name ===
Contributors: DvanKooten
Donate link: http://dannyvankooten.com/donate/
Tags: mailchimp, newsletter, mailinglist, email, email list, form, widget form, sign-up form, subscribe form, comments, comment form, mailchimp widget, buddypress, multisite
Requires at least: 3.1
Tested up to: 3.6.1
Stable tag: 1.3.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The best MailChimp plugin to get more email subscribers. Easily add sign-up forms and sign-up checkboxes to your WordPress website.

== Description ==

= MailChimp for WordPress =

Want to get more email subscribers for your MailChimp lists? This plugin will be a BIG help by adding sign-up forms and sign-up checkboxes to your WordPress website.

Easily build sign-up forms and display them in your posts, pages and widget areas. 

Add "sign up to our newsletter" checkboxes to your comment form, contact forms or any form you like, making subscribing to your list(s) effortless for your visitors. 

> MailChimp for WP comes with a premium version.
> Create (multiple) beautiful highly converting AJAX powered forms, absolutely no HTML or CSS knowledge required!
> *[Upgrade to MailChimp for WordPress Pro now >>](http://dannyvankooten.com/wordpress-plugins/mailchimp-for-wordpress/?utm_source=wp-plugin-repo&utm_medium=link&utm_campaign=first-link)*

**Plugin Features**

* Easily create a highly customizable sign-up form and show it in your posts, pages and widgets by using a simple shortcode `[mc4wp_form]`
* Add a "sign-up to our newsletter" checkbox to ANY form you like, like your comment form.
* Built-in integration with [BuddyPress](http://buddypress.org/) and WordPress MultiSite registration forms, [bbPress](http://bbpress.org/) new topic and new reply forms and [Contact Form 7](http://contactform7.com/) forms. You can add sign-up checkboxes to these forms with 1 simple click.
* Uses the MailChimp API, superfast and reliable. Also, visitors never have to leave your website to subscribe.
* Configuring is easy, all you need is your MailChimp API key.

**Premium Features**

* Form designer, design beautiful sign-up forms from your admin panel. No CSS knowledge required.
* (Multiple) AJAX powered sign-up forms (no page reload after submitting).
* Unlocked field wizard containing all your list fields and interest groupings. No HTML knowledge required.
* Reports. Gain insights in when, where and how your visitors subscribe with the subscribers log and beautiful statistical charts.
* Built-in integration with WooCommerce and Easy Digital Downloads.
* Priority support.

[More information](http://dannyvankooten.com/wordpress-plugins/mailchimp-for-wordpress/?utm_source=wp-plugin-repo&utm_medium=link&utm_campaign=after-features-link) | [Screenshots](http://dannyvankooten.com/wordpress-plugins/mailchimp-for-wordpress/screenshots/?utm_source=wp-plugin-repo&utm_medium=link&utm_campaign=after-features-link) | [Demo](http://dannyvankooten.com/wordpress-plugins/mailchimp-for-wordpress/demo-sign-up-forms/) | [Upgrade now >>](http://dannyvankooten.com/wordpress-plugins/mailchimp-for-wordpress/?utm_source=wp-plugin-repo&utm_medium=link&utm_campaign=after-features-link)

= MailChimp Sign-Up Form =
The plugin comes with an easy to way to build sign-up forms for your MailChimp lists. Add as many fields as you like and customize labels, placeholders, initial values etcetera in a simple way. Visitors never have to leave your website to subscribe to your MailChimp lists.

Use the `[mc4wp_form]` shortcode to show a sign-up form in your posts and pages or use the widget to display a form in your widget areas.

= "Sign-up to our newsletter?" Checkboxes =
People who comment or register on your website are valuable visitors and most likely interested to be on your mailinglist as well. This plugin makes it easy for them to subscribe to your MailChimp lists, one mouse-click is all they need.

You can add sign-up checkboxes to ANY form you like, including Contact Form 7 forms.

**More information**

Check out more [WordPress plugins](http://dannyvankooten.com/wordpress-plugins/?utm_source=wp-plugin-repo&utm_medium=link&utm_campaign=more-info-link) by [Danny van Kooten](http://dannyvankooten.com?utm_source=wp-plugin-repo&utm_medium=link&utm_campaign=more-info-link) or [contact him on Twitter](http://twitter.com/dannyvankooten).


== Installation ==

1. In your WordPress admin panel, go to Plugins > New Plugin, search for "MailChimp for WP" and click "Install now"
1. Alternatively, download the plugin and upload the contents of mailchimp-for-wp.zip to your plugins directory, which usually is `/wp-content/plugins/`.
1. Activate the plugin
1. Set your MailChimp API key in the plugin settings.
1. Select at least one list to subscribe visitors to.
1. (Optional) Select to which forms the sign-up checkbox should be added.
1. (Optional) Create a form and show it in your posts, pages or text widgets using the shortcode `[mc4wp_form]`.
1. (Optional) If you like the plugin, upgrade to [MailChimp for WordPress Pro](http://dannyvankooten.com/wordpress-plugins/mailchimp-for-wordpress/?utm_source=wp-plugin-repo&utm_medium=link&utm_campaign=installation-instructions-link) for an even better plugin.

== Frequently Asked Questions ==

= Is there a premium version of this plugin? =
Yes, there is and it is awesome. Pro features include:

1. (Multiple) AJAX Sign-up Forms (so the page doesn't reload after clicking the submit button)
1. Create beautiful forms from your admin panel with the Form Designer. No CSS knowledge required!
1. Reports, learn when, where and how your visitors subscribed. 
1. The form field wizard makes it easy to add your MailChimp list fields to your sign-up forms.
1. Sign-up checkbox integration with WooCommerce and Easy Digital Downloads
1. Custom checkbox labels
1. Priority support

[More information](http://dannyvankooten.com/wordpress-plugins/mailchimp-for-wordpress/?utm_source=wp-plugin-repo&utm_medium=link&utm_campaign=faq-link) | [Screenshots](http://dannyvankooten.com/wordpress-plugins/mailchimp-for-wordpress/screenshots/?utm_source=wp-plugin-repo&utm_medium=link&utm_campaign=faq-link) | [Demo](http://dannyvankooten.com/wordpress-plugins/mailchimp-for-wordpress/demo-sign-up-forms/?utm_source=wp-plugin-repo&utm_medium=link&utm_campaign=faq-link)

= How to display a form in posts or pages? =
Use the `[mc4wp_form]` shortcode.

= How to display a form in widget areas like a sidebar? =
Use the *MailChimp Sign-Up Form* Widget that comes with the plugin.

= How to display a form in my template files? =
Use the `mc4wp_form()` function.

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

[PS: The form designer in Pro creates the CSS rules for you >>](http://dannyvankooten.com/wordpress-plugins/mailchimp-for-wordpress/)

= Where can I find my MailChimp API key? =
[Here](http://kb.mailchimp.com/article/where-can-i-find-my-api-key)

= How to add a sign-up checkbox to my Contact Form 7 forms? =
Use the following shortcode to render a sign-up checkbox in your CF7 forms. 

`[mc4wp_checkbox "My custom label text"]`

If you need more data for your merge fields, prefix the field name with `mc4wp-`.

*Example CF7 template for MailChimp WEBSITE field*
`
[text* mc4wp-WEBSITE]
`

= Can I add a checkbox to a third-party form? =
Yes, you can. Go to MailChimp for WP > Checkbox Settings and tick the "show checkbox at other forms (manual)" checkbox. Then, include a checkbox with name attribute `mc4wp-try-subscribe` and value `1` in your form.

*Example HTML for checkbox in third-party form*
`
<input type="checkbox" name="mc4wp-try-subscribe" value="1" /><label>Subscribe to our newsletter?</label>
`

If you need to send more data for your list merge fields, prefix the name attribute with **mc4wp-**. The plugin will look for fields prefixed with `mc4wp-` and send the field values to MailChimp.

*Example HTML code for MailChimp WEBSITE merge tag*
`<input type="text" name="mc4wp-WEBSITE" /><label>Your website:</label>`

= How do I add subscribers to certain interest groups? =
Use the field wizard. Or, if you know more about HTML, the following snippet should get you started. *Replace `###` with your grouping ID or grouping name.*

`
<input type="hidden" name="GROUPINGS[###]" value="Groupname 1,Groupname 2,Groupname 3" />
`
Or, if you want to use checkboxes...

`
<input type="checkbox" name="GROUPINGS[###][]" value="Group 1" /><label>Group 1</label>
<input type="checkbox" name="GROUPINGS[###][]" value="Group 2" /><label>Group 2</label>
`

= Why does the checkbox not show up at my comment form? =
Your theme probably does not support the necessary comment hook this plugin uses to add the checkbox to your comment form. You can manually place the checkbox by placing the following code snippet inside the form tags of your theme's comment form.

`<?php if(function_exists('mc4wp_checkbox')) { mc4wp_checkbox(); }?>`

Your theme folder can be found by browsing to `/wp-content/themes/your-theme-name/`.

== Screenshots ==

1. **Premium only:** Design beautiful sign-up forms using the form CSS designer.
2. Add a sign-up checkbox to various places on your website.
3. An example sign-up checkbox.
4. An example sign-up form in my footer on dannyvankooten.com. More [MailChimp sign-up form examples](http://dannyvankooten.com/wordpress-plugins/mailchimp-for-wordpress/demo-sign-up-forms/?utm_source=wp-plugin-repo&utm_medium=link&utm_campaign=screenshots-link) are available on my website.
5. **Premium only:** Create multiple AJAX powered sign-up forms.
6. **Premium only:** Use the field wizard to easily add advanced fields to your form mark-up. You don't have to write any HTML, if you want. 
7. **Premium only:** Gain valuable insights which method your visitors used to subscribe for any given time period using beautiful line charts. [Upgrade to the premium version now.](http://dannyvankooten.com/wordpress-plugins/mailchimp-for-wordpress/?utm_source=wp-plugin-repo&utm_medium=link&utm_campaign=screenshots-link)


== Changelog ==

= 1.3.1 =
* Fixed: bug when calling MailChimp API for PHP 5.2
* Improved: better default form CSS
* Improved: Combined checkbox and form stylesheets into 1 file and encouraged browser caching.

= 1.3 =
* Added: Form widget
* Added: Smooth scroll to form element after form submission (if jQuery loaded)
* Improved: Added and removed some buttons from QTags editor toolbar
* Improved: Some UI improvements
* Improved: Optimized integration with third-party forms like Contact Form 7

= 1.2.5 =
* Fixed `undefined function mc4wp_replace_variables` fatal error when using Quick Cache plugin.

= 1.2.4 =
* Improved: code performance improvements
* Improved: added `mc4wp_get_form()` for an easier shortcode callback. Useful to [add a sign-up form to the end of your posts](http://dannyvankooten.com/2577/add-mailchimp-sign-up-form-end-of-posts/).
* Improved default CSS
* Improved: checkbox debug message only shows to WP Administrators when JavaScript is disabled
* Added: form nonce for better security
* Fix: CSS issue where the form caused a huge sidebar gap in some themes.

= 1.2.3 =
* Fixed: bug where some MailChimp fields were not showing in the field wizard / add field tool.

= 1.2.2 =
* Fixed sending extra list fields when integrating with third-party forms like Contact Form 7

= 1.2.1 =
* Improved: total revamp of the form field wizard, many improvements.
* Improved: some textual improvements in the setting pages
* Added: debug message to sign-up checkbox for WP administrators

= 1.2 =
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
* Added: Links to [MailChimp for WordPress Pro](http://dannyvankooten.com/wordpress-plugins/mailchimp-for-wordpress/)
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

= 1.2.5 =
Fixed CSS issue where the form caused a hue gap in the sidebar for some themes.

= 1.1.1 =
Bugfix for BuddyPress sites