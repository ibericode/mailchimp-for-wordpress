var forms = function(m) {
	var forms = {};
	var rows = require('./FieldRows.js')(m);

	// route to one of the other form configs, default to "text"
	forms.render = function(config) {

		var type = config.type();

		if( typeof( forms[type] ) === "function" ) {
			return forms[ type ](config);
		}

		switch( type ) {
			case 'select':
			case 'radio':
			case 'checkbox':
				return forms.choice(config);
				break;
		}

		// fallback to good old text field
		return forms.text(config);
	};


	forms.text = function(config) {
		return [
			rows.label(config),
			rows.defaultValue(config),
			rows.usePlaceholder(config),
			rows.isRequired(config),
			rows.useParagraphs(config)
		]
	};

	forms.choice = function(config) {
		return [
			rows.label(config),
			rows.choiceType(config),
			rows.choices(config),
			rows.useParagraphs(config)
		]
	};

	forms.hidden = function( config ) {
		return [
			rows.defaultValue(config)
		]
	};

	forms.submit = function(config) {

		config.label('');
		config.placeholder(false);

		return [
			rows.defaultValue(config),
			rows.useParagraphs(config)
		]
	};

	forms.number = function(config) {

		return [
			forms.text(config),
			rows.numberMinMax(config)
		];
	};

	return forms;
};



module.exports = forms;