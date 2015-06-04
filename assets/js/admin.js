(function($) {
	'use strict';

	/**
	 * Variables
	 */
	var $context = $(document.getElementById('mc4wp-admin'));
	var $listInputs = $(document.getElementById('mc4wp-lists')).find(':input');

	/**
	 * Functions
	 */
	function showProNotice() {

		// prevent checking of radio buttons
		if( typeof this.checked === 'boolean' ) {
			this.checked = false;
		}

		alert( mc4wp.strings.proOnlyNotice );
		event.stopPropagation();
	}

	function toggleSendWelcomeFields() {

		var $el = $(document.getElementById('mc4wp-send-welcome'));

		if($(this).val() == 0) {
			$el.removeClass('hidden').find(':input').removeAttr('disabled');
		} else {
			$el.addClass('hidden').find(':input').attr('disabled', 'disabled').prop('checked', false);
		}
	}

	function toggleWooCommerceSettings() {
		var $el = $(document.getElementById('woocommerce-settings'));
		$el.toggle(this.checked);
	}

	function toggleFieldWizard() {
		var hasListSelected = $listInputs.filter(':checked').length > 0;
		$(".mc4wp-notice.no-lists-selected").toggle( ! hasListSelected );
		$( document.getElementById( 'mc4wp-fw-mailchimp-fields' )).toggle( hasListSelected );
	}

	function addQTagsButtons() {
		if ( typeof(QTags) === 'undefined' ) {
			return;
		}

		QTags.addButton( 'mc4wp_paragraph', '<p>', '<p>', '</p>', 'paragraph', 'Paragraph tag', 1 );
		QTags.addButton( 'mc4wp_label', 'label', '<label>', '</label>', 'label', 'Label tag', 2 );
		QTags.addButton( 'mc4wp_response', 'form response', '{response}', '', 'response', 'Shows the form response' );
		QTags.addButton( 'mc4wp_subscriber_count', '# of subscribers', '{subscriber_count}', '', 'subscribers', 'Shows number of subscribers of selected list(s)' );

		if( window.mc4wp.hasCaptchaPlugin === true ) {
			QTags.addButton( 'mc4wp_captcha', 'CAPTCHA', '{captcha}', '', 'captcha', 'Display a CAPTCHA field' );
		}
	}

	/**
	 * Bind Event Handlers
	 */

	// show a notice when clicking a pro feature
	$context.find(".pro-feature, .pro-feature label, .pro-feature :radio").click(showProNotice);

	// Show send-welcome field only when double opt-in is disabled
	$context.find('input[name$="[double_optin]"]').change(toggleSendWelcomeFields);

	// show woocommerce settings only when `show at woocommerce checkout` is checked.
	$context.find('input[name$="[show_at_woocommerce_checkout]"]').change(toggleWooCommerceSettings);

	// only show fieldwizard when a list is selected
	$listInputs.change(toggleFieldWizard);

	addQTagsButtons();

	// init Field Wizard
	FormHelper();

})(jQuery);

