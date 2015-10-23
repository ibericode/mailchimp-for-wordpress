var FieldForms = {};
var rows = require('./FieldRows.js');

// route to one of the other form configs, default to "text"
FieldForms.render = function(type, config) {

	if( typeof( FieldForms[ type ] ) === "function" ) {
		return FieldForms[ type ](config);
	}

	return FieldForms.text(config);
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


module.exports = FieldForms;