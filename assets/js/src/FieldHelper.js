var FieldHelper = function(settings, tabs, editor) {
	'use strict';

	window.m = require('../third-party/mithril.js');
	var fieldGenerator = require('./FieldGenerator.js')();
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
		type: m.prop('text'),
		choices: []
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
			config.name(activeField.name);
			config.defaultValue(activeField.default_value);
			config.isRequired(activeField.required);
			config.label(activeField.label);
			config.type(activeField.type);
			config.choices = activeField.choices.map(function(choice) {
				return {
					label: m.prop( choice.label ),
					value: m.prop( choice.value ),
					selected: m.prop( choice.selected )
				};
			});

			if( config.type() === 'hidden' && ! config.defaultValue() ) {
				config.defaultValue( config.choices.map( function( c) { return c.label() }).join(',') );
			}
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
	function createFieldHTMLAndAddToForm() {

		// generate html
		var html = fieldGenerator.generate(config);

		// add to editor
		editor.insert( html );

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
						}, field.label)
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
						activeField.label,
						m("code", activeField.name)
					]),

					// actual form
					forms.render(activeField.type, config),

					// add to form button
					m("p", [
						m("button", {
							class: "button-primary",
							type: "button",
							onclick: createFieldHTMLAndAddToForm
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