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

	function toggleWooCommerceSettings() {
		var $el = $(document.getElementById('woocommerce-settings'));
		$el.toggle(this.checked);
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

	// Tabs
	(function( $context ) {

		var $tabs = $context.find('.tab');
		var $tabNav = $context.find('.nav-tab');
		var $tabLinks = $context.find('.tab-link');
		var $refererField = $context.find('input[name="_wp_http_referer"]');

		function parseQuery(qstr) {
			var query = {};
			var a = qstr.split('&');
			for (var i in a) {
				var b = a[i].split('=');
				query[decodeURIComponent(b[0])] = decodeURIComponent(b[1]);
			}

			return query;
		}

		function switchTab() {

			var urlParams = parseQuery( this.href );
			if( typeof(urlParams.tab) === "undefined" ) {
				return;
			}

			// hide all tabs & remove active class
			$tabs.hide();
			$tabNav.removeClass('nav-tab-active');

			// add `nav-tab-active` to this tab
			$(document.getElementById('nav-tab-' + urlParams.tab )).addClass('nav-tab-active').blur();

			// show target tab
			var targetId = "tab-" + urlParams.tab;
			document.getElementById(targetId).style.display = 'block';

			// update hash
			if( history.pushState ) {
				history.pushState( '', '', this.href );
			}

			// update referer field
			$refererField.val(this.href);

			// if thickbox is open, close it.
			if( typeof(tb_remove) === "function" ) {
				tb_remove();
			}

			// focus on codemirror textarea, this fixes bug with blank textarea
			window.form_editor.refresh();

			// prevent page jump
			return false;
		}

		// add tab listener
		$tabNav.click(switchTab);
		$tabLinks.click(switchTab);

	})($(document.getElementById('mc4wp-admin')));


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



