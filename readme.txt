=== MailChimp for WordPress ===
Contributors: DvanKooten
Donate link: https://mc4wp.com/
Tags: mailchimp,newsletter,optin,mailinglist,sign-up form,subscribe,form,shortcode,widget,checkbox,comment,buddypress,multisite,bbpress,woocommerce,easy digital downloads,contact form,contact form 7,events manager,comment form,registration form,captcha
Requires at least: 3.6
Tested up to: 4.0.1
Stable tag: 2.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The best MailChimp plugin to get more email subscribers. Easily add sign-up forms and sign-up checkboxes to your WordPress website.

== Description ==

= MailChimp for WordPress =

*Adding sign-up methods for your MailChimp lists to your WordPress site should be easy. With this plugin, it finally is.*

MailChimp for WordPress lets you create a highly customizable sign-up form which you can display wherever you want it to display using a simple shortcode, widget or template function.  You can also add sign-up checkboxes to various forms on your site, like your comment or contact forms.

= Sign-Up Forms =
Easily create sign-up forms for your MailChimp list and display it using a simple shortcode, a widget or PHP function.

= Sign-Up Checkboxes =
Add sign-up checkboxes to *any* form on your website. The plugin offers built-in integration with comment forms, registration forms, Contact Form 7, BuddyPress, bbPress and WordPress MultiSite.

**MailChimp for WordPress, at a glance..**

- Simple. All you need is your MailChimp API key.
- Customizable. Have the form fields generated for you or use your own customized HTML.
- Beautiful. Choose one of the default form themes or use your own styles.
- Integrates with Contact Form 7, WooCommerce checkout and other popular form plugins.
- Developer friendly.

[Installation](https://wordpress.org/plugins/mailchimp-for-wp/installation/) | [Frequently Asked Questions](https://wordpress.org/plugins/mailchimp-for-wp/faq/) | [Screenshots](https://wordpress.org/plugins/mailchimp-for-wp/screenshots/)

> #### MailChimp for WordPress Pro
> This plugin has a premium version which comes with the following features.<br />
>
> Multiple Forms. Each form can subscribe to one or multiple MailChimp lists<br />
> Easy CSS Styles Builder & Custom Color Themes.<br />
> AJAX Forms. Forms do not require a page reload.<br />
> Log & Statistics. Useful insights.<br />
> Priority Support<br />
>
> [Upgrade to MailChimp for WordPress Pro](https://mc4wp.com/features/#utm_source=wp-plugin-repo&utm_medium=link&utm_campaign=after-features-link)

**Translations**

The plugin is fully translated into the following languages: English, Dutch, Spanish, Brazilian, German, Italian, Slovak and Hungarian.

The [MailChimp for WordPress translations](https://wordpress.org/plugins/mailchimp-for-wp/other_notes/) page lists all the wonderful people that helped translate the plugin.

Want to [translate the plugin into your own language](https://www.transifex.com/projects/p/mailchimp-for-wordpress/)?


**More information**

- [MailChimp for WordPress Pro](https://mc4wp.com/), containing [additional features](https://mc4wp.com/features/#utm_source=wp-plugin-repo&utm_medium=link&utm_campaign=more-info-link).
- [Translate the MailChimp for WordPress plugin into your language](https://www.transifex.com/projects/p/mailchimp-for-wordpress/)
- Developers; follow or contribute to the [MailChimp for WP plugin on GitHub](https://github.com/dannyvankooten/mailchimp-for-wordpress)
- Other [WordPress plugins](https://dannyvankooten.com/wordpress-plugins/#utm_source=wp-plugin-repo&utm_medium=link&utm_campaign=more-info-link) by [Danny van Kooten](http://dannyvankooten.com#utm_source=wp-plugin-repo&utm_medium=link&utm_campaign=more-info-link)
- [@DannyvanKooten](https://twitter.com/dannyvankooten) on Twitter


== Installation ==

= Installing the plugin =
1. In your WordPress admin panel, go to *Plugins > New Plugin*, search for **MailChimp for WordPress** and click "*Install now*"
1. Alternatively, download the plugin and upload the contents of `mailchimp-for-wp.zip` to your plugins directory, which usually is `/wp-content/plugins/`.
1. Activate the plugin
1. Set [your MailChimp API key](https://admin.mailchimp.com/account/api) in the plugin settings.

= Configuring Sign-Up Checkboxes =
1. Go to *MailChimp for WP > Checkboxes*
2. Select at least one of your MailChimp lists to subscribe to.
3. Select the forms you want to add a sign-up checkbox to, eg your comment form.
4. Integrate the sign-up checkbox with any other form by using the following HTML.

`
<label><input type="checkbox" name="mc4wp-subscribe" value="1" /> Subscribe to our newsletter.</label>
`

= Configuring Sign-Up Form(s) =
1. Go to *MailChimp for WP > Forms*
2. Select at least one of your MailChimp lists to subscribe to.
3. *(Optional)* Add more fields or dynamic content to your form using the **add MailChimp field** dropdown.
4. Embed a sign-up form in pages or posts by using the following shortcode.

`
[mc4wp_form]
`
5. Show a sign-up form in your widget areas using the "MailChimp Sign-Up Form" widget.
6. Show a sign-up form from your theme files by using the following PHP function.

`
<?php

if( function_exists( 'mc4wp_form' ) ) {
	mc4wp_form();
}
`

Need help? Please take a look at the [frequently asked questions](https://wordpress.org/plugins/mailchimp-for-wp/faq/) first. If you can't find your answer there, start a topic in the [MailChimp for WordPress plugin support forums](http://wordpress.org/support/plugin/mailchimp-for-wp).

= Upgrade to MailChimp for WordPress Pro =
If you like the plugin, [get the Pro version of MailChimp for WordPress](https://mc4wp.com/#utm_source=wp-plugin-repo&utm_medium=link&utm_campaign=installation-instructions-link) for an even better plugin!

== Frequently Asked Questions ==


> **What are the main differences between Pro and Lite?**<br />
>
> MailChimp for WordPress Pro comes with some additional features.<br />
>
> **Multiple Forms.** Each form can have unique settings. <br />
> **AJAX Forms.** Submitting forms does not require a page reload.<br />
> **Styles Builder..** Alter the design of your forms and have the styling rules generated for you.<br />
> **Statistics & Logging.** Beautiful line charts and log tables, providing useful insights.<br /><br />
>
> [Differences Lite & Pro](https://mc4wp.com/features/#utm_source=wp-plugin-repo&utm_medium=link&utm_campaign=faq-link) | [Demo](https://mc4wp.com/demo/#utm_source=wp-plugin-repo&utm_medium=link&utm_campaign=faq-link) | [Upgrade Now](https://mc4wp.com/checkout/#utm_source=wp-plugin-repo&utm_medium=link&utm_campaign=faq-link)

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

[>> With the Pro plugin it's really easy to design beautiful forms <<](https://mc4wp.com/#utm_source=wp-plugin-repo&utm_medium=link&utm_campaign=after-css-link)

= Where can I find my MailChimp API key? =
[You can find your MailChimp API key here](http://kb.mailchimp.com/article/where-can-i-find-my-api-key)

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
Yes. Just include a checkbox with name attribute `mc4wp-subscribe` and value `1` in your form.

*Example HTML*
`
<label><input type="checkbox" name="mc4wp-subscribe" value="1" /> Subscribe to our newsletter?</label>
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

= How do I add a Captcha to my forms? =
Install the [BWS Captcha](https://wordpress.org/plugins/captcha/) plugin, then use `[captcha]` inside your form mark-up.

= How do I add a checkbox to my Events Manager booking form? =
Add a checkbox field to your booking form and name it `mc4wp-subscribe`.

Optionally, add more MailChimp fields by prefixing the MailChimp list field name with `mc4wp-`, eg `mc4wp-website`.

= Why does the checkbox not show up at my comment form? =
Your theme probably does not support the necessary comment hook this plugin uses to add the checkbox to your comment form. You can manually place the checkbox by placing the following code snippet inside the form tags of your theme's comment form.

`<?php if( function_exists( 'mc4wp_checkbox' ) ) { mc4wp_checkbox(); }?>`

Your theme folder can be found by browsing to `/wp-content/themes/your-theme-name/`.

== Translations ==

The MailChimp for WordPress plugin is translated into the following languages.

- English (en_US) - [Danny van Kooten](https://dannyvankooten.com)
- Dutch (nl_NL) - [Danny van Kooten](https://dannyvankooten.com)
- Spanish (es_ES) - [Paul Benitez ](http://www.administrandowp.com/) & [Enrique Pinto](http://www.thehivecluster.com/en)
- Brazilian (pt_BR) - [Felipe Scuissiatto](http://www.evonline.com.br/)
- German (de_DE) - [Jochen Gererstorfer](http://slotnerd.de/)
- Italian (it_IT) - [Gianpaolo Rolando](http://www.gianpaolorolando.eu/) & [Tiziano D'Angelo](http://www.dangelos.it/)
- Slovak (sk_SK) - [Henrich Koszegi](http://www.webworks.sk/)
- Hungarian (hu_HU) - Németh Balázs

It's easy to translate the plugin into your own language. Head over to the [translation project on Transifex](https://www.transifex.com/projects/p/mailchimp-for-wordpress/) and click on "[Help translate MailChimp for WordPress](https://www.transifex.com/signup/?join_project=mailchimp-for-wordpress)".

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

= 2.2 - December 9, 2014 =

**Fixes**

- "Select at least one list" notice appearing when unselecting any MailChimp list in Form settings
- If an error occurs, textareas will no longer lose their value

**Improvements**

- Improved the way form submissions are handled
- Minor code & documentation improvements
- Updated Dutch, French, Portugese and Spanish translations

**Additions**

- Added sign-up checkbox integration for [WooCommerce](https://wordpress.org/plugins/woocommerce/) checkout.
- Added sign-up checkbox integration for [Easy Digital Downloads](https://wordpress.org/plugins/easy-digital-downloads/) checkout.
- The entered email will now be appended to the URL when redirecting to another page

= 2.1.7 - December 1, 2014 =

**Fixes**

- Fixes onclick event in older versions of IE, props [Simon Schick](https://github.com/SimonSimCity)

**Improvements**

- Updated Dutch, French, Hungarian, Italian, Norwegian, Swedish and Taiwanese translations.
- Some textual improvements.

**Additions**

- {email} shortcode to use in form mark-up.

= 2.1.6 - November 18, 2014 =

**Fixes**

- Notice in `class-widget.php` when widget options are never saved.

**Improvements**

- Added some missing gettext calls so strings can be translated.
- Updated translations

= 2.1.5 - October 13, 2014 =

**Fixes**

- Notice in `class-mailchimp.php` when fetching lists from MailChimp.

= 2.1.4 - October 13, 2014 =

**Fixes**

- Fixed `mc4wp_get_current_url()` function for IIS servers using `index.php` in URL's.
- Nonce verification was failing with aggressive caching
- Only call `is_email()` on strings.

**Improvements**

- Minor improvements to memory usage and overall performance
- Improved sanitization for third-party integrations
- Wrapped debug messages for checkbox integrations in gettext calls so they can be translated
- Updated Dutch translations

**Additions**

- Submitted forms now get `mc4wp-form-submitted` CSS class.
- Filter: `mc4wp_cookie_expiration_time` to alter expiration time of email cookie. Defaults to 30 days.
- Hungarian translation, thanks to Németh Balázs
- Partial French translations


= 2.1.3 - September 15, 2014 =

**Improvements**

- Updated Spanish and Dutch translations
- Fixed missing text domains
- Removed obsolete code in upgrade routine
- All settings are now properly sanitized before being stored.

**Additions**

- Added Slovak language files, thanks to [Henrich Koszegi - Webworks.sk](http://www.webworks.sk/).


= 2.1.2 - August, 26, 2014 =

**Fixes**

- Remove `type` attribute from `textarea` elements
- Check for array fields in form when checking presence of required MailChimp list fields

**Improvements**

- Added `-webkit-appearance` reset to checkbox CSS
- Updated Italian translations
- Updated links to point to the new [MailChimp for WordPress Pro](https://mc4wp.com/) site.
- Don't use `{response}` tag if form is hidden after successful submissions

**Additions**

- Added official integration with [Events Manager](https://wordpress.org/plugins/events-manager/). Just include a `mc4wp-subscribe` checkbox field and MailChimp for WordPress will do the rest.

= 2.1.1 - August 12, 2014 =

**Fixes**

- `mc4wp_get_current_url()` now takes ports and the WP site url option into account
- Quicktags buttons were not showing because script was not loaded, now it is.

**Improvements**

- Improved CSS reset for the sign-up checkbox
- Added deprecated warning to some functions
- Improvements to third-party forms integration for the sign-up checkbox. Integrating with the [Events Manager](https://wordpress.org/plugins/events-manager/) plugin should work now.
- Updated Dutch translations
- Updated English translations

**Additions**

- Added `mc4wp_form_error_{ERROR_CODE}` action hook to allow hooking into all form errors.
- Added `{response}` tag to allow setting a custom response position
- Added various filters to customize form HTML
- Added German language, thanks to [Jochen Gererstorfer](http://slotnerd.de/)
- Added Italian language, thanks to [Gianpaolo Rolando](http://www.gianpaolorolando.eu/)

= 2.1 - July 29, 2014 =

**Fixes**

- Some fields lost its value when a form error occurred

**Improvements**

- Minified all CSS and JS files
- Required MailChimp fields are now validated server side as well.
- Birthday and address fields are now automatically formatted in the correct format
- Improved code, memory usage and class documentation

**Additions**

- Brazilian translations, thanks to [Felipe Scuissiatto of Evonline](http://www.evonline.com.br/)
- `mc4wp_form_messages` filter to register custom error messages
- `mc4wp_form_message_position` filter to set position of error messages (before or after fields)
- Option to set the text for when a required field is missing

= 2.0.5 - July 21, 2014 =

**Improvements**

- Ignore Captcha fields in sign-up data
- Updated Spanish translations
- Minor improvements to Admin and MailChimp API class
- Show field tag and required status in Lists overview table

**Additions**

- Add visitor IP address to sign-up data


= 2.0.4 - July 2, 2014 =

**Fixes**

- Double sign-up requests for checkbox sign-ups

**Improvements**

- Reset checkbox label in default CSS for improved theme compatibility
- Improved checkbox integration classes
- Optimised function to retrieve the current URL

**Additions**

- Added `{language}` text variable to print the current site language.
- Added merge tag names to list overview table

= 2.0.3 - June 17, 2014 =

**Fixes**

- Fixed undefined index notice in Contact Form 7 integration class

**Improvements**

- Reset form width in all stylesheets

= 2.0.2 - June 12, 2014 =

**Fixes**

- Fix fatal error when using `mc4wp_checkbox()` function
- No more double API request when integrating with Contact Form 7

**Improvements**

- Template functions are now always loaded when needed
- A warning will now show when required fields are missing in the form mark-up
- Required form classes can no longer be accidentally removed
- Various checkbox integration improvements
- Various CSS improvements to colored form themes
- Updated Spanish translations

= 2.0.1 - May 15, 2014 =

**Improvements**

- Allowed translation of more strings in the settings screens.
- Added Spanish translations, thanks [Paul Benitez - Tecnofilos](http://www.administrandowp.com/)
- Minor code improvements

**Additions**

- Saving forms without an `EMAIL` field or submit button will show a notice.

= 2.0 - April 29, 2014 =

**Improvements**

- CSS is now served as static CSS instead of being served through PHP.
- The anti-spam honeypot is now added to the sign-up checkbox as well.
- Improved object-oriented code architecture and better class documentation
- Better CSS reset for the various form themes to increase theme compatibility
- Added class autoloading to the plugin
- Various minor code improvements

**Additions**

- You can now add a captcha field to your sign-up forms by installing the [BWS Captcha](http://wordpress.org/plugins/captcha/) plugin and using `[captcha]` inside your form mark-up.
- All settings pages are now fully translatable. The plugin has just 2 translations available yet (`en_US` and `nl_NL`) so if you're good at translating, please send me your language pack for the plugin.
- You can now use tab indentation in the form markup textarea

= 1.5.8 - March 26, 2014 =

**Fixes**

- 'call to undefined function' when using Avia Layout Builder
- "Already subscribed" message never showing

= 1.5.7 - March 18, 2014 =
**Fixes**

- Fixed special characters in group names not working
- Fixed BIRTHDAY field format (mm/dd)

**Improvements**

- Moved away from Singleton pattern
- Added a code version number for upgrade routines
- Better class documentation
- MailChimp cached data improvements. Now showing subscriber count.
- Base form CSS improvements, added vertical-align to field elements and removed padding from paragraph elements.
- Updated Placeholders.js for old IE versions

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
* Improved: added `mc4wp_get_form()` for an easier shortcode callback. Useful to [add a sign-up form to the end of your posts](http://dannyvankooten.com/add-mailchimp-sign-up-form-end-of-posts/).
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

== Upgrade Notice ==

= 2.2 =
Contains many code improvements, updated translations and checkbox integration with WooCommerce & EDD checkout.