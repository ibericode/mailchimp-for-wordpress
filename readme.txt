=== MailChimp for WordPress ===
Contributors: Ibericode, DvanKooten, iMazed, hchouhan
Donate link: https://mc4wp.com/#utm_source=wp-plugin-repo&utm_medium=mailchimp-for-wp&utm_campaign=donate-link
Tags: email, mailchimp, marketing, newsletter, signup, widget, mc4wp, contact form 7, woocommerce, buddypress,ibericode
Requires at least: 3.7
Tested up to: 4.3.1
Stable tag: 2.3.14
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

MailChimp for WordPress, the absolute best. Add subscribers to your MailChimp lists from your WordPress site, with ease.

== Description ==

= MailChimp for WordPress =

*Adding sign-up methods for your MailChimp lists to your WordPress site should be easy. With this MailChimp for WordPress, it finally is.*

This plugin helps you add subscribers to your MailChimp lists using various methods. You can create good looking opt-in forms or integrate with any other form on your site, like your comment form or WooCommerce checkout.

= MailChimp for WordPress features =

- Connect with your MailChimp account in just 1 click.
- User friendly & mobile optimized sign-up forms.
- Complete control over your form fields. Send anything you like to MailChimp.
- 1-click MailChimp sign-up for your comment and registration form(s).
- Show sign-up forms in your posts or pages using the form shortcode, or in your sidebar or footer using the MailChimp widget.
- Redirect users to a "thank you" page after subscribing to your MailChimp list(s).
- All fields & messages are customizable so you can translate them into your preferred language.
- Built-in integration with Contact Form 7, WooCommerce and many other plugins.
- Developer friendly. You have full control over the form HTML and there are many available action & filter hooks.

> **MailChimp for WordPress Pro**<br /><br />
> This plugin has a premium version which comes with the following features.<br /><br />
> - As many forms as you want, each subscribing to one or multiple MailChimp lists.<br />
> - AJAX Forms. Forms do not require a full page reload.<br />
> - Visual Styles Builder, create your own style without the need for code.<br />
> - Custom Color Themes, a quick way to blend-in with your theme.<br />
> - Log & Statistics, providing you with useful insights like your new MailChimp subscribers came from.<br />
> - Priority support over email.<br /><br />
> [Upgrade to MailChimp for WordPress Pro >>](https://mc4wp.com/features/#utm_source=wp-plugin-repo&utm_medium=mailchimp-for-wp&utm_campaign=after-features-link)

= Wait, but what is MailChimp? =

MailChimp for WordPress acts as a bridge between your WordPress site and your MailChimp account. MailChimp is a newsletter service that allows you to send out email campaigns to a list of email subscribers. MailChimp is absolutely free for lists up to 2000 subscribers, which is why it is the go-to choice for small businesses or starting entrepreneurs. That doesn't mean MailChimp is not a great choice for bigger businesses though.

If you do not yet have a MailChimp account, [creating one is 100% free and only takes you about 30 seconds](http://mailchimp.com/monkey-rewards/?utm_source=freemium_newsletter&utm_medium=email&utm_campaign=monkey_rewards&aid=a2d08947dcd3683512ce174c5&afl=1).

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

= Support =

Use the [WordPress.org plugin forums](https://wordpress.org/support/plugin/mailchimp-for-wp) for community support where we try to help all of our users. If you found a bug, please create an issue on Github where we can act upon them more efficiently.

If you're a premium user, please use the email address inside the plugin for support as that will guarantee a faster response time.

Please take a look at the [MailChimp for WordPress knowledge base](https://mc4wp.com/kb/#utm_source=wp-plugin-repo&utm_medium=mailchimp-for-wp&utm_campaign=description) as well.

= Add-on plugins =

There are several [add-on plugins for MailChimp for WordPress](https://mc4wp.com/add-ons/#utm_source=wp-plugin-repo&utm_medium=mailchimp-for-wp&utm_campaign=description), which help you get even more out of your site.

= Translations =

The plugin is translated using Transifex. If you want to help out, please head over to the [translation project on Transifex](https://www.transifex.com/projects/p/mailchimp-for-wordpress/).

= Development =

MailChimp for WordPress is being developed on GitHub. If you want to collaborate, please look at [ibericode/mailchimp-for-wordpress](https://github.com/ibericode/mailchimp-for-wordpress).

== Screenshots ==

1. A static sign-up form in the sidebar of the Twenty Fifteen theme.
2. Highly effective 1-click subscribe option in your comment, registration or other forms.
3. Use your own fields or use our Field Builder.
4. Integrations for many popular plugins.
5. Don't know CSS? No worries, our Styles Builder will do the heavy lifting for you! **(Pro Feature)**
6. Detailed statistics on which methods are generating the most subscribers. **(Pro Feature)**

== Changelog ==

= 2.3.14 - September 25 =

**Fixes**

- Use of undefined constant in previous update.

= 2.3.13 - September 25, 2015 =

**Fixes**

- Honeypot causing horizontal scrollbar on RTL sites.
- List choice fields not showing when using one of the default form themes.

**Improvements**

- Minor styling improvements for RTL sites.
- MailChimp list fields of type "website" will now become HTML5 `url` type fields.
- Auto-prefix fields of type `url` with `http://`

= 2.3.12 - September 21, 2015 =

**Fixes**

- Issue with interest groupings not being fetched after updating to version 2.3.11

= 2.3.11 - September 21, 2015 =

**Fixes**

- Honeypot field being filled by browser's autocomplete.
- Styling issue for submit buttons in Mobile Safari.
- Empty response from MailChimp API

**Improvements**

- Do not query MailChimp API for interest groupings if list has none.
- Integration errors are now logged to PHP's error log for easier debugging.

**Additions**

- You can now use shortcodes in the form content.

= 2.3.10 - September 7, 2015 =

**Fixes**

- Showing "not connected" when the plugin was actually connected to MailChimp.
- Issue with `address` fields when `addr1` was not given.
- Comment form checkbox not outputted for some older themes.

**Improvements**

- Do not flush MailChimp cache on every settings save.
- Add default CSS styles for `number` fields.
- Placeholders will now work in older version of IE as well.

= 2.3.9 - September 1, 2015 =

**Improvements**

- MailChimp lists cache is now automatically flushed after changing your API key setting.
- Better field population after submitting a form with errors.
- More helpful error message when no list is selected.
- Translate options when installing plugin from a language other than English.
- Add form mark-up to WPML configuration file.
- Sign-up checkbox in comment form is now shown before the "submit comment" button.
- URL-encode variables in "Redirect URL" setting.
- Better error message when connected to MailChimp but account has no lists.

**Additions**

- Add `mc4wp_form_action` filter to set a custom `action` attribute on the form element.

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

== Upgrade Notice ==

