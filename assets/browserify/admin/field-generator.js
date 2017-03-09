'use strict';

const htmlutil = require('html');

const g = function(m) {
	let generators = {};

	/**
	 * Generates a <select> field
	 * @param config
	 * @returns {*}
	 */
	generators.select = function (config) {
        let attributes = {
			name: config.name(),
			required: config.required()
		};
        let hasSelection = false;

        let options = config.choices().map(function (choice) {

			if( choice.selected() ) {
				hasSelection = true;
			}

			return m('option', {
				value: ( choice.value() !== choice.label() ) ? choice.value() : undefined,
				"selected": choice.selected(),
                oncreate: function(vnode) {
                    if(vnode.dom.selected) {
                        vnode.dom.setAttribute("selected", "true");
                    }
                }
			}, choice.label())
		});

		const placeholder = config.placeholder();
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
		let fields = config.choices().map(function (choice) {
            const name = config.name() + ( config.type() === 'checkbox' ? '[]' : '' );
			const required = config.required() && config.type() === 'radio';

			return m('label', [
					m('input', {
						name    : name,
						type    : config.type(),
						value   : choice.value(),
						checked : choice.selected(),
						required: required,
                        oncreate: function(vnode) {
						    if(vnode.dom.checked) {
						        vnode.dom.setAttribute("checked", "true");
                            }
                        },
					}),
					' ',
					m('span', choice.label())
				]
			)
		});
		
		return fields;
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
		let attributes = {
			type: config.type()
		};


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

		return m('input', attributes);
	};

	/**
	 * Generates an HTML string based on a field (config) object
	 *
	 * @param config
	 * @returns {*}
	 */
	function generate(config) {
		let label, field, htmlTemplate, html,
			vdom = document.createElement('div');

		label = config.label().length > 0 ? m("label", {}, config.label()) : '';
		field = typeof(generators[config.type()]) === "function" ? generators[config.type()](config) : generators['default'](config);
		htmlTemplate = config.wrap() ? m('p', [label, field]) : [label, field];

		// render in vdom
		m.render(vdom, htmlTemplate);

		// prettify html
		html = htmlutil.prettyPrint(vdom.innerHTML);

		return html + "\n";
	}

	return generate;
};

module.exports = g;