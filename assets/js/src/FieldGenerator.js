var FieldGenerator = function() {

	var render = require('../third-party/Render.js');
	var html_beautify = require('../third-party/beautify-html.js');

	/**
	 * Generates an HTML string based on a field (config) object
	 *
	 * @param config
	 * @returns {*}
	 */
	function generate( config ) {
		var label, field;

		label = config.label().length ? m("label", config.label()) : '';
		var fieldAttributes =  {
			type: config.type(),
			name: config.name()
		};

		switch( config.type() ) {
			case 'select':

				field = m('select', [
					config.choices().map(function(choice) {
						return m('option', {
							name: config.name(),
							value: ( choice.value() !== choice.label() ) ? choice.value() : undefined,
							selected: choice.selected()
						}, choice.label())
					})
				]);

				break;


			case 'checkbox':
			case 'radio':

				field = config.choices().map(function(choice) {
					return m('label', [
							m('input', {
								name: config.name() + ( config.type() === 'checkbox' ? '[]' : '' ),
								type: config.type(),
								value: (choice.value() !== choice.label()) ? choice.value() : undefined,
								checked: choice.selected()
							}),
							m( 'span', choice.label() )
						]
					)
				});

				break;

			default:

				if( config.placeholder() == true ) {
					fieldAttributes.placeholder = config.value();
				} else {
					fieldAttributes.value = config.value();
				}

				field = m( 'input', fieldAttributes );

				break;
		}

		fieldAttributes.required = config.required();

		var html = config.wrap() ? m('p', [ label, field ]) : [ label, field ];

		// render HTML
		var rawHTML = render( html );
		rawHTML = html_beautify( rawHTML ) + "\n\n";
		return rawHTML;
	}

	return {
		generate: generate
	}
};

module.exports = FieldGenerator;