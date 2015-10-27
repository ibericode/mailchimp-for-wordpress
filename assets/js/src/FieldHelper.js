var FieldHelper = function(settings, tabs, editor, fields) {
	'use strict';

	var fieldGenerator = require('./FieldGenerator.js')();
	var overlay = require('./Overlay.js');
	var forms = require('./FieldForms.js');
	var fieldConfig;

	/**
	 * Choose a field to open the helper form for
	 *
	 * @param index
	 * @returns {*}
	 */
	function setActiveField(index) {
		fieldConfig = fields.get(index);
		m.redraw();
	}


	/**
	 * Controller
	 */
	function controller() {
		settings.events.on('selectedLists.change', function() { m.redraw(); });
	}

	/**
	 * Create HTML based on current config object
	 */
	function createFieldHTMLAndAddToForm() {

		// generate html
		var html = fieldGenerator.generate(fieldConfig);

		// add to editor
		editor.insert( html );

		// reset field form
		setActiveField('');
	}

	/**
	 * View
	 * @returns {*}
	 */
	function view() {

		// build DOM for fields choice
		var fieldsChoice = m( "div.available-fields.small-margin", [
			m("strong", "Choose a MailChimp field to add to the form"),

			(fields.getAll().length) ?

				// render fields
				fields.getAll().map(function(field, index) {
					return [
						m("button", {
							class  : "button",
							type   : 'button',
							onclick: m.withAttr("value", setActiveField),
							value  : index
						}, field.title())
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
		if( fieldConfig ) {
			form = overlay(
				// field wizard
				m("div.field-wizard", [

					//heading
					m("h3", [
						fieldConfig.title(),
						m("code", fieldConfig.name())
					]),

					// actual form
					forms.render(fieldConfig),

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