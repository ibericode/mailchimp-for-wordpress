module.exports = (function() {
	'use strict';

	/**
	 * Variables
	 */
	var $ = window.jQuery;
	var $context = $(document.getElementById('mc4wp-admin'));


	/**
	 * Functions
	 */
	function showProNotice() {

		// prevent checking of radio buttons
		if( typeof this.checked === 'boolean' ) {
			this.checked = false;
		}

		alert( mc4wp_vars.l10n.pro_only );
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

	/**
	 * Bind Event Handlers
	 */

		// show a notice when clicking a pro feature
	$context.find(".pro-feature, .pro-feature label, .pro-feature :radio").click(showProNotice);

	// Show send-welcome field only when double opt-in is disabled
	$context.find('input[name$="[double_optin]"]').change(toggleSendWelcomeFields);


	/* Grey out integration settings when "enabled" is not ticked */
	(function() {
		var $toggles = $('.integration-toggles-wrap input');
		var $settings = $('.integration-toggled-settings');
		$toggles.change(toggleSettings);

		function toggleSettings() {
			var enabled = $toggles.filter(':checked').val() > 0;
			var opacity = enabled ? '1' : '0.5';
			$settings.css( 'opacity', opacity );
		}
	})();

})();



