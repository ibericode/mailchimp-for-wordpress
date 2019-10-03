const FieldHelper = function(m, tabs, editor, fields, events, i18n) {
	'use strict';

	const generate = require('./field-generator.js')(m);
	const overlay = require('../overlay.js')(m,i18n);
	const forms = require('./field-forms.js')(m, i18n);
	let fieldConfig;

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
	 * Create HTML based on current config object
	 */
	function createFieldHTMLAndAddToForm() {

		// generate html
		const html = generate(fieldConfig);

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
		let fieldCategories = fields.getCategories();
		let availableFields = fields.getAll();

		let fieldsChoice = m( "div.available-fields.small-margin", [
			m("h4", i18n.chooseField),

			fieldCategories.map(function(category) {
				let categoryFields = availableFields.filter(function(f) {
					return f.category === category;
				});

				if( ! categoryFields.length ) {
					return;
				}

				return m("div.tiny-margin",[
					m("strong", category),

					// render fields
					categoryFields.map(function(field) {
						let className = "button";
						if( field.forceRequired() ) {
							className += " is-required";
						}

						let inForm = field.inFormContent();
						if( inForm !== null ) {
							className += " " + ( inForm ? 'in-form' : 'not-in-form' );
						}

						return m("button", {
							className: className,
							type   : 'button',
							onclick: (evt) => setActiveField(evt.target.value),
							value  : field.index
						}, field.title() );
					})
				]);
			})
		]);

		// build DOM for overlay
		let form = null;
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
							onkeydown: function(evt) {
								if(evt.keyCode === 13) {
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
	}
};

module.exports = FieldHelper;
