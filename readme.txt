=== MailChimp for WordPress ===
Contributors: ibericode, DvanKooten, iMazed, hchouhan
Donate link: https://mc4wp.com/#utm_source=wp-plugin-repo&utm_medium=mailchimp-for-wp&utm_campaign=donate-link
Tags: email, mailchimp, marketing, newsletter, signup, widget, mc4wp, contact form 7, woocommerce, buddypress
Requires at least: 3.7
Tested up to: 4.2.4
Stable tag: 2.3.8
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

MailChimp for WordPress, the absolute best. Add subscribers to your MailChimp lists from your WordPress site, with ease.

== Description ==

*Adding sign-up methods for your MailChimp lists to your WordPress site should be easy. With this plugin, it finally is.*

MailChimp for WordPress provides you with various methods to add subscribers to your MailChimp lists. You can create good looking opt-in forms or integrate with any other form on your site, like your comment form or WooCommerce checkout.

= MailChimp for WordPress features =

- Connect with your MailChimp account in just 1 click.
- User friendly & mobile optimized sign-up forms, containing as many fields as you prefer.
- 1-click MailChimp sign-up for your comment and registration form(s).
- Show sign-up forms in your posts or pages using the form shortcode, or in your sidebar or footer using the MailChimp widget.
- Redirect users to a "thank you" page after subscribing to your MailChimp list(s).
- All fields & messages are customizable so you can translate them into your preferred language.
- Built-in integration with Contact Form 7, WooCommerce and many others.
- Developer friendly. You have full control over the form HTML and there are many available action & filter hooks.

= Add-on plugins =

There are several [add-on plugins for MailChimp for WordPress](https://mc4wp.com/add-ons/#utm_source=wp-plugin-repo&utm_medium=mailchimp-for-wp&utm_campaign=description), which help you get even more out of your site.

= Contributing =

You can [contribute code to this plugin via GitHub](https://github.com/ibericode/mailchimp-for-wordpress) or [help to translate the plugin using Transifex](https://www.transifex.com/projects/p/mailchimp-for-wordpress/).

= Support =

Use the [WordPress.org plugin forums](https://wordpress.org/support/plugin/mailchimp-for-wp) for community support where we try to help all of our users. If you found a bug, please create an issue on Github where we can act upon them more efficiently.

If you're a premium user, please use the email address inside the plugin for support as that will guarantee a faster response time.

Please take a look at the [MailChimp for WordPress knowledge base](https://mc4wp.com/kb/#utm_source=wp-plugin-repo&utm_medium=mailchimp-for-wp&utm_campaign=description) as well.

> **MailChimp for WordPress Pro**<br /><br />
> This plugin has a premium version which comes with the following features.<br /><br />
> - As many forms as you want, each subscribing to one or multiple MailChimp lists.<br />
> - AJAX Forms. Forms do not require a full page reload.<br />
> - Visual Styles Builder, create your own style without the need for code.<br />
> - Custom Color Themes, a quick way to blend-in with your theme.<br />
> - Log & Statistics, providing you with useful insights like your new MailChimp subscribers came from.<br />
> - Priority support over email.<br /><br />
> [Upgrade to MailChimp for WordPress Pro](https://mc4wp.com/features/#utm_source=wp-plugin-repo&utm_medium=mailchimp-for-wp&utm_campaign=after-features-link)

= What is MailChimp? =

MailChimp for WordPress is a plugin that connects your WordPress site to MailChimp, so you will need to have a MailChimp account first. MailChimp is a newsletter service that allows you to send out email campaigns to a list of email subscribers. MailChimp is absolutely free for lists up to 2000 subscribers, which is why it is the go-to choice for small businesses or starting entrepreneurs. That doesn't mean MailChimp is not a great choice for bigger businesses though.

If you do not yet have a MailChimp account, [creating one is 100% free and only takes you about 30 seconds](https://mc4wp.com/out/mailchimp).

== Installation ==

= Installing the plugin =
1. In your WordPress admin panel, go to *Plugins > New Plugin*, search for **MailChimp for WordPress** and click "*Install now*"
1. Alternatively, download the plugin and upload the contents of `mailchimp-for-wp.zip` to your plugins directory, which usually is `/wp-content/plugins/`.
1. Activate the plugin
1. Set [your MailChimp API key](https://admin.mailchimp.com/account/api) in the plugin settings.

= Configuring Sign-Up Form(s) =
1. Go to *MailChimp for WP > Forms*
2. Select at least one MailChimp list to subscribe people to.
3. *(Optional)* Add more fields to your form using the **add MailChimp field** dropdown.
4. Embed a sign-up form in pages or posts by using the `[mc4wp_form]` shortcode.
5. Show a sign-up form in your widget areas using the "MailChimp Sign-Up Form" widget.
6. Show a sign-up form from your theme files by using the following PHP function.

`
<?php

if( function_exists( 'mc4wp_form' ) ) {
	mc4wp_form();
}
`

= Need help? =
Please take a look at the [MailChimp for WordPress knowledge base](https://mc4wp.com/kb/#utm_source=wp-plugin-repo&utm_medium=mailchimp-for-wp&utm_campaign=installation-instructions-link) first. If you can't find your answer there, please look through the [MailChimp for WordPress plugin support forums](https://wordpress.org/support/plugin/mailchimp-for-wp) or start your own topic.

== Frequently Asked Questions ==

= More documentation =
More detailed documentation can be found in the [MailChimp for WordPress knowledge base](https://mc4wp.com/kb/#utm_source=wp-plugin-repo&utm_medium=mailchimp-for-wp&utm_campaign=faq).

= How to display a form in posts or pages? =
Use the `[mc4wp_form]` shortcode.

= How to display a form in widget areas like the sidebar or footer? =
Go to **Appearance > Widgets** and use the **MailChimp for WP Form** widget that comes with the plugin.

= How to display a form in my template files? =
Use the `mc4wp_form()` function.

`
if( function_exists( 'mc4wp_form' ) ) {
	mc4wp_form();
}
`

= Where can I find my MailChimp API key? =
[You can find your MailChimp API key here](http://kb.mailchimp.com/accounts/management/about-api-keys#Find-or-Generate-Your-API-Key)

= How to add a sign-up checkbox to my Contact Form 7 form? =
Use the following shortcode in your CF7 form to display a MailChimp sign-up checkbox.

`
[mc4wp_checkbox "Subscribe to our newsletter?"]
`

= The form shows a success message but subscribers are not added to my list(s)? =
If the form shows a success message, there is no doubt that the sign-up request succeeded. MailChimp could have a slight delay sending the confirmation email though, please just be patient and make sure to check your SPAM folder.

When you have double opt-in disabled, new subscribers will be seen as *imports* by MailChimp. They will not show up in your daily digest emails or statistics. [We always recommend leaving double opt-in enabled](http://blog.mailchimp.com/double-opt-in-vs-single-opt-in-stats/).

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

= I'm getting an "HTTP Error" when trying to connect to MailChimp =

If you're getting an `HTTP Error` when trying to connect to your MailChimp account, please contact your webhost and ask them if they have PHP CURL installed and updated to the latest version (7.40.x).
Also, please ask them to allow requests to `https://api.mailchimp.com/`.

= My question is not listed =

Please head over to the [MailChimp for WordPress knowledge base](https://mc4wp.com/kb/#utm_source=wp-plugin-repo&utm_medium=mailchimp-for-wp&utm_campaign=faq) for more detailed documentation.

== Other Notes ==

= Translations =

The plugin is translated using Transifex. If you want to help out, please head over to the [translation project on Transifex](https://www.transifex.com/projects/p/mailchimp-for-wordpress/).

= Development =

Development of the plugin happens on GitHub: [ibericode/mailchimp-for-wordpress](https://github.com/ibericode/mailchimp-for-wordpress)

== Screenshots ==

1. Simple or advanced MailChimp sign-up forms that blend in with your theme.
2. A sign-up checkbox in your comment form is an amazing conversion booster.
3. A simple form in the footer of the Twenty Thirteen theme.
4. Add sign-up checkboxes to various places on your site.
5. Creating sign-up forms for your MailChimp lists is easy. The Pro version allows you to create as many form as you like.
6. Write your own HTML or have it generated for you. Many (optional) customization settings are available.
7. **Pro only:** Gain valuable insights which method your visitors used to subscribe for any given time period using beautiful line charts.
8. **Pro only:** Create your own CSS styles with the form designer in the Pro version.

== Changelog ==

= 2.3.8 - August 18, 2015 =

**Fixes**

- Prevented JS error when outputting forms with no submit button.
- Using `0` as a Redirect URL resulted in a blank page.
- Sign-up checkbox was showing twice in the Easy Digital Downloads checkout when showing registration fields, thanks [Daniel Espinoza](https://github.com/growdev).
- Default form was not automatically translated for languages other than English.

**Improvements**

- Better way to hide the honeypot field, which stops bots from subscribing to your lists.
- role="form" is no longer needed, thanks [XhmikosR](https://github.com/XhmikosR)!
- Filter `mc4wp_form_animate_scroll` now disables just the scroll animation, not the scroll itself.
- Revamped UI for MailChimp lists overview
- Updated German & Greek translations.

**Additions**

- Added `mc4wp_form_is_submitted()` and `mc4wp_form_get_response_html()` functions.

= 2.3.7 - July 13, 2015 =

**Improvements**

- Use the same order as MailChimp.com, which is useful when you have over 100 MailChimp lists.
- Use `/* ... */` for inline JavaScript comments to prevent errors with minified HTML - props [Ed Gifford](https://github.com/egifford)

**Additions**

- Filter: `mc4wp_form_animate_scroll` to disable animated scroll-to after submitting a form.
- Add `{current_path}` variable to use in form templates.
- Add `default` attribute to `{data_name}` variables, usage: `{data_something default="The default value"}`

= 2.3.6 - July 6, 2015 =

**Fixes**

- Undefined index notice when visitor's USER_AGENT is not set.

**Improvements**

- Relayed the browser's Accept-Language header to MailChimp for auto-detecting a subscriber's language.
- Better CSS for form reset
- Updated HTML5 placeholder polyfill

= 2.3.5 - June 24, 2015 =

**Fixes**

- Faulty update for v3.0 appearing for people running GitHub updater plugin.

**Improvements**

- Updated language files.
- Now passing the form as a parameter to `mc4wp_form_css_classes` filter.

= 2.3.4 - May 29, 2015 =

**Fixes**

- Issue with GROUPINGS not being sent to MailChimp

**Improvements**

- Code preview in Field Builder is now read-only

= 2.3.3 - May 27, 2015 =

**Fixes**

- Get correct IP address when using proxy like Cloudflare or Sucuri WAF.
- Use strict type check for printing inline CSS that hides honeypot field

**Improvements**

- Add `contactemail` and `contactname` to field name guesses when integrating with third-party form.
- Re-enable `sslverify`

= 2.3.2 - May 12, 2015 =

**Fixes**

- Groupings not being sent to MailChimp
- Issue when using more than one `{data_xx}` replacement

**Improvements**

- IE8 compatibility for honeypot fallback script.

= 2.3.1 - May 6, 2015 =

**Fixes**

- PHP notice in `includes/class-tools.php`, introduced by version 2.3.

= 2.3 - May 6, 2015 =

**Fixes**

- The email address is no longer automatically added to the Redirect URL as this is against Google Analytics policy. To add it again, use `?email={email}` in your Redirect URL setting.
- Registration type integrations were not correctly picking up on first- and last names.
- JavaScript error in IE8 because of `setAttribute` call on honeypot field.
- API class `subscribe` method now always returns a boolean.

**Improvements**

- Add `role` attribute to form elements
- Major code refactoring for easier unit testing and improved code readability.
- Use Composer for autoloading all plugin classes (PHP 5.2 compatible)
- You can now use [form variables in both forms, messages as checkbox label texts](https://mc4wp.com/kb/using-variables-in-your-form-or-messages/).

**Additions**

- You can now handle unsubscribe calls with our forms too.
- Added Portugese, Indonesian, German (CH) and Spanish (PR) translations.

= 2.2.9 - April 15, 2015 =

**Fixes**

- Menu item for settings page not appearing on Google App Engine ([#88](https://github.com/ibericode/mailchimp-for-wordpress/issues/88))

**Improvements**

- Updated Italian, Russian & Turkish translations. [Want to help translate the plugin? Full translations get a free Pro license](https://www.transifex.com/projects/p/mailchimp-for-wordpress/).

= 2.2.8 - March 24, 2015 =

**Fixes**

- API key field value was not properly escaped.
- Background images were stripped from submit buttons.

**Improvements**

- Better sanitising of all settings
- Updated all translations

**Additions**

- Added `mc4wp_before_checkbox` and `mc4wp_after_checkbox` filters to easily add more fields to sign-up checkbox integrations.
- Added some helper methods related to interest groupings to `MC4WP_MailChimp` class.
- Allow setting custom MailChimp lists to subscribe to using `lists` attribute on shortcode.

= 2.2.7 - March 11, 2015 =

**Fixes**

- Honeypot field was visible for themes or templates not calling `wp_head()` and `wp_footer()`

**Improvements**

- Various minor code improvements
- Updated German, Spanish, Brazilian, French, Hungarian and Russian translations.

**Additions**

- Added [mc4wp_form_success](https://github.com/ibericode/mailchimp-for-wordpress/blob/06f0c833027f347a288d2cb9805e0614767409b6/includes/class-form-request.php#L292-L301) action hook to hook into successful sign-ups
- Added [mc4wp_form_data](https://github.com/ibericode/mailchimp-for-wordpress/blob/06f0c833027f347a288d2cb9805e0614767409b6/includes/class-form-request.php#L138-L142) filter hook to modify all form data before processing


= 2.2.6 - February 26, 2015 =

**Fixes**

- CSS reset wasn't working for WooCommerce checkout sign-up checkbox.
- `mc4wp-submitted` class was not added in IE8
- Incorrect `action` attribute on form element for some server configurations

**Improvements**

- Anti-SPAM improvements: a better honeypot field and a timestamp field to prevent instant form submissions.
- Reset `background-image` on submit buttons when using CSS themes
- Smarter email detection when integrating with third-party forms
- Updated all translations

**Additions**

- Custom fallback for browsers not supporting `input[type="date"]`


= 2.2.5 - February 13, 2015 =

**Fixed**

- Issue where WooCommerce checkout sign-up was not working for cheque payments.
- Translation were loaded too late to properly translate some strings, like the admin menu items.

**Improvements**

- The presence of required list fields in form mark-up is now checked as you type.
- Number fields will now repopulate if an error occurred.
- Updated all translations.
- Make sure there is only one plugin instance.
- Various other code improvements.

**Additions**

- Added support for [GitHub Updater Plugin](https://github.com/afragen/github-updater).
- You can now specify whether you want to send a welcome email (only with double opt-in disabled).

A huge thank you to [Stefan Oderbolz](http://metaodi.ch/) for various fixed and improvements related to translations in this release.


= 2.2.4 - February 4, 2015 =

**Fixed**

- Textual fix as entering "0" for no redirection does not work.

**Improvements**

- Moved third-party scripts to their own directory for easier exclusion
- All code is now adhering to the WP Code Standards
- Updated [Dutch, German, Spanish, Hungarian, French, Italian and Turkish translations](https://www.transifex.com/projects/p/mailchimp-for-wordpress/).

**Additions**

- Now showing a heads up when at limit of 100 MailChimp lists. ([#71](https://github.com/ibericode/mailchimp-for-wordpress/issues/71))
- Added `wpml-config.xml` file for better WPML compatibility
- Added filter `mc4wp_menu_items` for adding & removing menu items from add-ons

= 2.2.3 - January 24, 2015 =

Minor improvements and additions for compatibility with the [MailChimp Sync plugin](https://wordpress.org/plugins/mailchimp-sync/).

= 2.2.2 - January 13, 2015 =

**Fixes**

- Plugin wasn't connecting to MailChimp for users on MailChimp server `us10` (API keys ending in `-us10`)

= 2.2.1 - January 12, 2015 =

**Improvements**

- Use JS object to transfer lists data to Field Wizard.
- Field Wizard strings are now translatable
- Add `is_spam` method to checkbox integration to battle spam sign-ups
- Minor code & code style improvements
- Updated Danish, German, Spanish, French, Italian and Portugese (Brazil) translations

**Additions**

- You can now set `MC_LOCATION`, `MC_NOTES` and `MC_LANGUAGE` from your form HTML
- The submit button now has a default value when generating HTML for it


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

= 2.3.6 =
Fixes undefined index notice for visitors with empty USER_AGENT. Updated languages.