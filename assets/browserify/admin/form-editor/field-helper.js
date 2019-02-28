var FieldHelper = function(m, tabs, editor, fields, events, i18n) {
	'use strict';

	var generate = require('./field-generator.js')(m);
	var overlay = require('../overlay.js')(m,i18n);
	var forms = require('./field-forms.js')(m, i18n);
	var fieldConfig;

	editor.on('blur', m.redraw);

	/**
	 * Choose a field to open the helper form for
	 *
	 * @param index
	 * @returns {*}
	 */
	function setActiveField(index) {

		fieldConfig = fields.get(index);

		// if this hidden field has choices (hidden groups), glue them together by their label.
		if( fieldConfig && fieldConfig.choices().length > 0 ) {
			fieldConfig.value( fieldConfig.choices().map(function(c) {
				return c.label();
			}).join('|'));
		}

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

		// redraw
		m.redraw();
	}

	/**
	 * View
	 * @returns {*}
	 */
	function view() {

		// build DOM for fields choice
		var fieldCategories = fields.getCategories();
		var availableFields = fields.getAll();

		var fieldsChoice = m( "div.available-fields.small-margin", [
			m("h4", i18n.chooseField),

			fieldCategories.map(function(category) {
				var categoryFields = availableFields.filter(function(f) {
					return f.category === category;
				});

				if( ! categoryFields.length ) {
					return;
				}

				return m("div.tiny-margin",[
					m("strong", category),

					// render fields
					categoryFields.map(function(field) {
						var className = "button";
						if( field.forceRequired() ) {
							className += " is-required";
						}

						var inForm = field.inFormContent();
						if( inForm !== null ) {
							className += " " + ( inForm ? 'in-form' : 'not-in-form' );
						}

						return m("button", {
							className: className,
							type   : 'button',
							onclick: m.withAttr("value", setActiveField),
							value  : field.index
						}, field.title() );
					})
				]);
			})
		]);

		// build DOM for overlay
		var form = null;
		if( fieldConfig ) {
			form = m(overlay(
				// field wizard
				m("div.field-wizard", [

					//heading
					m("h3", [
						fieldConfig.title(),
						fieldConfig.forceRequired() ? m('span.red', '*' ) : '',
						fieldConfig.name().length ? m("code", fieldConfig.name()) : ''
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
							onkeydown: function(e) {
								e = e || window.event;
								if(e.keyCode == 13) {
									createFieldHTMLAndAddToForm();
								}
							},
							onclick: createFieldHTMLAndAddToForm
						}, i18n.addToForm )
					])
				]), setActiveField));
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
