(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
(function() {
	'use strict';

	// dependencies
	var FormWatcher = require('./FormWatcher.js');
	var FormEditor = require('./FormEditor.js');
	var FieldHelper = require('./FieldHelper.js');

	// vars
	var form_editor = window.form_editor = new FormEditor( document.getElementById('mc4wp-form-content'));
	var form_watcher = new FormWatcher( form_editor );
	var field_helper = new FieldHelper();
	m.mount( document.getElementById( 'mc4wp-field-wizard'), field_helper );

	// events
	form_editor.on('change', form_watcher.checkRequiredFields );

	// @todo: clean this up
	require('./clean-this-up.js');

})();
},{"./FieldHelper.js":2,"./FormEditor.js":3,"./FormWatcher.js":4,"./clean-this-up.js":5}],2:[function(require,module,exports){
var FieldHelper = function() {
	'use strict';

	var selectedListInputs = document.querySelectorAll('.mc4wp-list-input:checked');
	var lists = mc4wp_vars.mailchimp.lists;

	/**
	 *
	 * @returns {Array}
	 */
	function getSelectedLists() {
		var selectedLists = Array.prototype.map.call(selectedListInputs, function(input) {
			return lists[ input.value ] || null;
		});
		selectedLists = selectedLists.filter(function(el) { return !!el;});
		return selectedLists;
	}

	function getAvailableFields( lists ) {
		var fields = {};

		lists.map(function(list) {
			return list.merge_vars.map(function(field) {
				if( typeof( fields[ field.tag ] === "undefined" ) ) {
					fields[ field.tag ] = field;
				}
			});
		});

		return fields;
	}


	/**
	 * Controller
	 */
	function controller() {
		this.selectedLists = getSelectedLists();
		this.fields = getAvailableFields(this.selectedLists);
		this.chosenFieldTag = m.prop('');
	}

	/**
	 * View
	 *
	 * @param ctrl
	 * @returns {*}
	 */
	function view( ctrl ) {

		var chosenField = ctrl.fields[ ctrl.chosenFieldTag() ];
		var active = typeof(chosenField) === "object"; //!== undefined;

		// build DOM for fields choice
		var fieldsChoice = m( "div", [
			Object.keys(ctrl.fields).map(function(key) {
				var field = ctrl.fields[key];
				return [
					m("button", {
						class  : "button",
						type   : 'button',
						onclick: m.withAttr("value", ctrl.chosenFieldTag),
						value  : field.tag
					}, field.name),
					m("span", " ")
				];
			})
		]);

		// build DOM for overlay
		var overlay = null;
		if( active ) {
			overlay = [
				m( "div.overlay", [
					m("h3", chosenField.name)
				]),
				m( "div.overlay-background", { onclick: function() { ctrl.chosenFieldTag(''); }})
			];
		}

		return [
			fieldsChoice,
			overlay
		];
	}

	// expose some variables
	return {
		view: view,
		controller: controller
	}
};

module.exports = FieldHelper;
},{}],3:[function(require,module,exports){
/* Editor */
var FormEditor = function(element) {
	var editor  = CodeMirror.fromTextArea(element, {
		selectionPointer: true,
		matchTags: { bothTags: true },
		mode: "text/html",
		htmlMode: true,
		autoCloseTags: true
	});

	return editor;
};

module.exports = FormEditor;
},{}],4:[function(require,module,exports){
var FormWatcher = function(editor) {

	var $ = window.jQuery;

	// @todo fill this dynamically (get from selected lists)
	var requiredFields = [ { tag: 'EMAIL', name: 'Email Address' } ];
	var $missingFieldsList = $(document.getElementById('missing-fields-list'));
	var $missingFieldsNotice = $(document.getElementById('missing-fields-notice'));

	// functions
	function checkRequiredFields() {

		var formContent = editor.getValue();

		// let's go
		formContent = formContent.toLowerCase();

		// check presence of reach required field
		var missingFields = {};
		for(var i=0; i<requiredFields.length; i++) {
			var htmlString = 'name="' + requiredFields[i].tag.toLowerCase();
			if( formContent.indexOf( htmlString ) == -1 ) {
				missingFields[requiredFields[i].tag] = requiredFields[i];
			}
		}

		// do nothing if no fields are missing
		if($.isEmptyObject(missingFields)) {
			$missingFieldsNotice.hide();
			return;
		}

		// show notice
		$missingFieldsList.html('');
		for( var key in missingFields ) {
			var field = missingFields[key];
			var $listItem = $("<li></li>");
			$listItem.html( field.name + " (<code>" + field.tag + "</code>)");
			$listItem.appendTo( $missingFieldsList );
		}

		$missingFieldsNotice.show();
	}


	return {
		checkRequiredFields: checkRequiredFields
	}

};

module.exports = FormWatcher;
},{}],5:[function(require,module,exports){
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




},{}]},{},[1]);
