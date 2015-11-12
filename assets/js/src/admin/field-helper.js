var FieldHelper = function(m,tabs, editor, fields) {
	'use strict';

	var generate = require('./field-generator.js')(m);
	var overlay = require('./overlay.js')(m);
	var forms = require('./field-forms.js')(m);
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

	}

	/**
	 * Create HTML based on current config object
	 */
	function createFieldHTMLAndAddToForm() {

		// generate html
		var html = generate(fieldConfig);

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
							"class": "button",
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
						(fieldConfig.name().length) ? m("code", fieldConfig.name()) : ''
					]),

					// help text
					( fieldConfig.help().length ) ? m('p', m.trust( fieldConfig.help() ) ) : '',

					// actual form
					forms.render(fieldConfig),

					// add to form button
					m("p", [
						m("button", {
							"class": "button-primary",
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