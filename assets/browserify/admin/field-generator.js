var g = function(m) {
	'use strict';

	var html_beautify = require('../third-party/beautify-html.js');
	var generators = {};

	/**
	 * Generates a <select> field
	 * @param config
	 * @returns {*}
	 */
	generators.select = function (config) {
		var field = m('select', {name: config.name()}, [
			config.choices().map(function (choice) {
				return m('option', {
					value   : ( choice.value() !== choice.label() ) ? choice.value() : undefined,
					selected: choice.selected()
				}, choice.label())
			})
		]);
		return field;
	};

	/**
	 * Generates a checkbox or radio type input field.
	 *
	 * @param config
	 * @returns {*}
	 */
	generators.checkbox = function (config) {
		var field = config.choices().map(function (choice) {
			return m('label', [
					m('input', {
						name   : config.name() + ( config.type() === 'checkbox' ? '[]' : '' ),
						type   : config.type(),
						value  : choice.value(),
						checked: choice.selected()
					}),
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
			if (config.placeholder()) {
				attributes.placeholder = config.value();
			} else {
				attributes.value = config.value();
			}
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
		var div = document.createElement('div');
		m.render(div, htmlTemplate);
		html = div.innerHTML;

		// prettify html
		html = html_beautify(html);

		return html + "\n";
	}

	return generate;
};

module.exports = g;