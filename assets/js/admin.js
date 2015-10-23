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
},{"./FieldHelper.js":3,"./FormEditor.js":5,"./FormWatcher.js":6,"./clean-this-up.js":7}],2:[function(require,module,exports){
var FieldForms = {};
var rows = require('./FieldRows.js');

// route to one of the other form configs, default to "text"
FieldForms.render = function(type, config) {

	if( typeof( FieldForms[ type ] ) === "function" ) {
		return FieldForms[ type ](config);
	}

	return FieldForms.text(config);
};

FieldForms.text = function(config) {
	return [
		rows.label(config),

		// default value row
		rows.defaultValue(config),

		// placeholder row
		rows.usePlaceholder(config),

		// required field row
		rows.isRequired(config),

		// paragraph wrap row
		rows.useParagraphs(config)
	]
};


module.exports = FieldForms;
},{"./FieldRows.js":4}],3:[function(require,module,exports){
var FieldHelper = function() {
	'use strict';

	var $ = window.jQuery;
	var selectedListsInputs = document.querySelectorAll('.mc4wp-list-input');
	var lists = mc4wp_vars.mailchimp.lists;
	var forms = require('./FieldForms.js');
	var selectedLists = [];
	var availableFields = {};
	var chosenFieldTag = m.prop('');
	var chosenField;
	var active = false;
	var config = {
		useParagraphs: m.prop(false),
		defaultValue: m.prop(''),
		isRequired: m.prop(false),
		usePlaceholder: m.prop(true),
		label: m.prop('')
	};

	/**
	 * Recalculate which lists are selected
	 *
	 * @returns {Array}
	 */
	function updateSelectedLists() {
		selectedLists = [];
		$.map(selectedListsInputs, function(input) {
			if( ! input.checked ) return;
			if( typeof( lists[ input.value ] ) === "object" ){
				selectedLists.push( lists[ input.value ] );
			}
		});

		updateAvailableFields();
		return selectedLists;
	}

	/**
	 * Update the available MailChimp fields to choose from
	 *
	 * @returns {{}}
	 */
	function updateAvailableFields() {
		availableFields = {};

		selectedLists.map(function(list) {
			return list.merge_vars.map(function(field) {
				if( typeof( availableFields[ field.tag ] === "undefined" ) ) {
					availableFields[ field.tag ] = field;
				}
			});
		});

		chooseField('');
		return availableFields;
	}

	/**
	 * Choose a field to open the helper form for
	 *
	 * @todo
	 *
	 * @param value
	 * @returns {*}
	 */
	function chooseField(value) {

		if( typeof(value) !== "string" ) {
			return chooseField('');
		}

		chosenFieldTag(value);
		chosenField = availableFields[ chosenFieldTag() ];
		active = typeof(chosenField) === "object";

		if( active ) {
			config.defaultValue(chosenField.name);
			config.isRequired(chosenField.req);
			config.label(chosenField.name);
		}

		m.redraw();
	}


	/**
	 * Controller
	 */
	function controller() {
		updateSelectedLists();

		window.addEventListener('keydown', function(e) {
			if(e.keyCode !== 27) return;
			chooseField('');
		});

		Array.prototype.map.call( selectedListsInputs, function(input) {
			input.addEventListener('change', updateSelectedLists);
		});
	}

	/**
	 * Create HTML based on current config object
	 * @todo
	 */
	function createHTML() {

		// reset field form
		chooseField('');
	}

	/**
	 * View
	 *
	 * @param ctrl
	 * @returns {*}
	 */
	function view( ctrl ) {

		// build DOM for fields choice
		var fieldsChoice = m( "div.available-fields.small-margin", [
			m("strong", "Choose a MailChimp field to add to the form"),
			$.map(Object.keys(availableFields),function(key) {
				var field = availableFields[key];
				return [
					m("button", {
						class  : "button",
						type   : 'button',
						onclick: m.withAttr("value", chooseField),
						value  : field.tag
					}, field.name)
				];
			})
		]);

		// build DOM for overlay
		var overlay = null;
		if( active ) {
			overlay = [
				m( "div.overlay",[
					m("div.overlay-content", [
						m('span.close.dashicons.dashicons-no', { onclick: chooseField }),

						m("div.field-wizard", [
							m("h3", [
								chosenField.name,
								m("code", chosenField.tag)
							]),

							forms.render(chosenField.field_type, config),

							m("p", [
								m("button", {
									class: "button-primary",
									type: "button",
									onclick: createHTML
								}, "Add to form" )
							])
						])
					])
				]),
				m( "div.overlay-background", {
					onclick: chooseField
				})
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
},{"./FieldForms.js":2}],4:[function(require,module,exports){
var r = {};

r.label = function(config) {
	// label row
	return m("p", [
		m("label", "Field Label"),
		m("input.widefat", {
			type: "text",
			value: config.label(),
			onchange: m.withAttr('value', config.label)
		} )
	]);
};

r.defaultValue = function(config) {
	return m("p", [
		m("label", "Default Value"),
		m("input.widefat", {
			type: "text",
			value: config.defaultValue(),
			oninput: m.withAttr('value', config.defaultValue)
		} )
	]);
};


r.isRequired = function(config) {
	return m('p', [
		m('label.cb-wrap', [
			m('input', {
				type: 'checkbox',
				checked: config.isRequired(),
				onchange: m.withAttr( 'checked', config.isRequired )
			}),
			"Is this field required?"
		])
	]);
};

r.usePlaceholder = function(config) {
	return m("p", [
		m("label.cb-wrap", [
			m("input", {
				type: 'checkbox',
				checked: config.usePlaceholder(),
				onchange: m.withAttr( 'checked', config.usePlaceholder )
			}),
			"Use \""+ config.defaultValue() +"\" as placeholder for the field."
		])
	]);
};

r.useParagraphs = function(config) {
	return m('p', [
		m('label.cb-wrap', [
			m('input', {
				type: 'checkbox',
				checked: config.useParagraphs(),
				onchange: m.withAttr( 'checked', config.useParagraphs )
			}),
			"Wrap in paragraph tags?"
		])
	]);
};

module.exports = r;
},{}],5:[function(require,module,exports){
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
},{}],6:[function(require,module,exports){
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
},{}],7:[function(require,module,exports){
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
