var FieldForms = {};
var rows = require('./FieldRows.js');

// route to one of the other form configs, default to "text"
FieldForms.render = function(type, config) {

	switch( type ) {
		case 'select':
		case 'radio':
		case 'checkbox':
			return FieldForms.choice(config);

		default:
			return FieldForms.text(config);
	}
};

FieldForms.text = function(config) {
	return [
		rows.label(config),

		// default value row
		rows.defaultValue(config),

		// placeholder row
		rows.usePlaceholder(config),

		// required field row
		rows.isRequired(config),

		// paragraph wrap row
		rows.useParagraphs(config)
	]
};

FieldForms.choice = function(config) {
	return [
		rows.label(config),
		rows.choiceType(config),
		rows.choices(config),
		rows.useParagraphs(config)
	]
};


module.exports = FieldForms;