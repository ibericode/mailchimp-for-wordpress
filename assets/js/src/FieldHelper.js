var FieldHelper = function(settings, tabs, editor) {
	'use strict';

	window.m = require('../third-party/mithril.js');
	var render = require('./Render.js');
	var html_beautify = require('../third-party/beautify-html.js');
	var overlay = require('./Overlay.js');
	var forms = require('./FieldForms.js');
	var availableFields = [];
	var activeField;
	var config = {
		name: m.prop(''),
		useParagraphs: m.prop(false),
		defaultValue: m.prop(''),
		isRequired: m.prop(false),
		usePlaceholder: m.prop(true),
		label: m.prop(''),
		type: m.prop('text')
	};


	/**
	 * Update the available MailChimp fields to choose from
	 *
	 * @returns {{}}
	 */
	function setAvailableFields(fields) {
		availableFields = settings.getAvailableFields();
		setActiveField(false);
		m.redraw();
	}

	/**
	 * Choose a field to open the helper form for
	 *
	 * @param index
	 * @returns {*}
	 */
	function setActiveField( index ) {
		index = parseInt(index);
		activeField = availableFields[ index ];
		var active = typeof( activeField ) === "object";

		if( active ) {
			config.name(activeField.tag);
			config.defaultValue(activeField.name);
			config.isRequired(activeField.req);
			config.label(activeField.name);
		}

		m.redraw();
	}


	/**
	 * Controller
	 */
	function controller() {
		availableFields = settings.getAvailableFields();
		settings.events.on('availableFields.change', setAvailableFields);
	}

	/**
	 * Create HTML based on current config object
	 */
	function createHTML() {

		var label = config.label().length ? m("label", config.label()) : '';
		var fieldAttributes =  {
			type: config.type(),
			name: config.name()
		};

		if( config.usePlaceholder() == true ) {
			fieldAttributes.placeholder = config.defaultValue();
		} else {
			fieldAttributes.value = config.defaultValue();
		}

		fieldAttributes.required = config.isRequired();

		var field = m( 'input', fieldAttributes );
		var html = config.useParagraphs() ? m('p', [ label, field ]) : [ label, field ];

		// render HTML
		var rawHTML = render( html );
		rawHTML = html_beautify( rawHTML ) + "\n\n";

		// add to editor
		editor.insert( rawHTML );

		// reset field form
		setActiveField('');
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

			(availableFields.length) ?

				// render fields
				availableFields.map(function(field, index) {
					return [
						m("button", {
							class  : "button",
							type   : 'button',
							onclick: m.withAttr("value", setActiveField),
							value  : index
						}, field.name)
					];
				})

				:

				// no fields
				m( "p", [
					"No fields, did you ",
					m("a", {
						onclick: function() { tabs.open('settings'); }
					}, "select a MailChimp list in the form settings?")
				])
		]);

		// build DOM for overlay
		var form = null;
		if( activeField ) {
			form = overlay(
				// field wizard
				m("div.field-wizard", [

					//heading
					m("h3", [
						activeField.name,
						m("code", activeField.tag)
					]),

					// actual form
					forms.render(activeField.field_type, config),

					// add to form button
					m("p", [
						m("button", {
							class: "button-primary",
							type: "button",
							onclick: createHTML
						}, "Add to form" )
					])
				]), setActiveField);
		}

		return [
			fieldsChoice,
			form
		];
	}

	// expose some variables
	return {
		view: view,
		controller: controller
	}
};

module.exports = FieldHelper;