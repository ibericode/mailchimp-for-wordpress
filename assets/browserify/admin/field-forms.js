var forms = function(m, i18n) {
	var forms = {};
	var rows = require('./field-forms-rows.js')(m, i18n);

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
			rows.placeholder(config),
			rows.value(config),
			rows.isRequired(config),
			rows.useParagraphs(config)
		]
	};

	forms.choice = function(config) {
		var visibleRows = [
			rows.label(config),
			rows.choiceType(config),
			rows.choices(config),
		];

		if( config.type() === 'select' ) {
			visibleRows.push(rows.placeholder(config));
		}

		visibleRows.push(rows.useParagraphs(config));

		if( config.type() === 'select' || config.type() === 'radio' ) {
			visibleRows.push(rows.isRequired(config));
		}

		return visibleRows;
	};

	forms.hidden = function( config ) {
		config.placeholder('');

		// if this hidden field has choices (hidden goups), glue them together by their label.
		if( config.choices().length > 0 ) {
			config.value( config.choices().map(function(c) {
				return c.label();
			}).join(','));
		}

		return [
			rows.value(config)
		]
	};

	forms.submit = function(config) {

		config.label('');
		config.placeholder('');

		return [
			rows.value(config),
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