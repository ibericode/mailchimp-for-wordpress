'use strict';

var render = require('../third-party/render.js');
var html_beautify = require('../third-party/beautify-html.js');

var g = function(m) {
	var generators = {};

	/**
	 * Generates a <select> field
	 * @param config
	 * @returns {*}
	 */
	generators.select = function (config) {
		var attributes = {
			name: config.name(),
			required: config.required()
		};
		var hasSelection = false;

		var options = config.choices().map(function (choice) {

			if( choice.selected() ) {
				hasSelection = true;
			}

			return m('option', {
				value   : ( choice.value() !== choice.label() ) ? choice.value() : undefined,
				"selected": choice.selected()
			}, choice.label())
		});

		var placeholder = config.placeholder();
		if(placeholder.length > 0 ) {
			options.unshift(
				m('option', {
					'disabled': true,
					'value': '',
					'selected': ! hasSelection
				}, placeholder)
			);
		}

		return m('select', attributes, options );
	};

	/**
	 * Generates a checkbox or radio type input field.
	 *
	 * @param config
	 * @returns {*}
	 */
	generators.checkbox = function (config) {
		var field = config.choices().map(function (choice) {
			var name = config.name() + ( config.type() === 'checkbox' ? '[]' : '' );
			var required = config.required() && config.type() === 'radio';

			return m('label', [
					m('input', {
						name    : name,
						type    : config.type(),
						value   : choice.value(),
						checked : choice.selected(),
						required: required
					}),
					' ',
					m('span', choice.label())
				]
			)
		});
		
		return field;
	};
	generators.radio = generators.checkbox;

	/**
	 * Generates a default field
	 *
	 * - text, url, number, email, date
	 *
	 * @param config
	 * @returns {*}
	 */
	generators['default'] = function (config) {

		var attributes = {
			type: config.type()
		};
		var field;

		if (config.name()) {
			attributes.name = config.name();
		}

		if (config.min()) {
			attributes.min = config.min();
		}

		if (config.max()) {
			attributes.max = config.max();
		}

		if (config.value().length > 0) {
			attributes.value = config.value();
		}

		if( config.placeholder().length > 0 ) {
			attributes.placeholder = config.placeholder();
		}

		attributes.required = config.required();

		field = m('input', attributes);
		return field;
	};

	/**
	 * Generates an HTML string based on a field (config) object
	 *
	 * @param config
	 * @returns {*}
	 */
	function generate(config) {
		var label, field, htmlTemplate, html;

		label = config.label().length ? m("label", config.label()) : '';
		field = typeof(generators[config.type()]) === "function" ? generators[config.type()](config) : generators['default'](config);

		htmlTemplate = config.wrap() ? m('p', [label, field]) : [label, field];

		// render HTML on memory node
		html = render(htmlTemplate);

		// prettify html
		html = html_beautify(html);

		return html + "\n";
	}

	return generate;
};

module.exports = g;