var FormWatcher = function(m, editor, settings, fields, events, helpers) {
	'use strict';

	var requiredFieldsInput = document.getElementById('required-fields');

	function updateFields() {
		fields.getAll().forEach(function(field) {
			// don't run for empty field names
			if(field.name().length <= 0) return;

			var fieldName = field.name();
			if( field.type() === 'checkbox' ) {
				fieldName += '[]';
			}

			var inForm = editor.containsField( fieldName );
			field.inFormContent( inForm );
		});

		findRequiredFields();
		m.redraw();
	}

	function findRequiredFields() {

		// query fields required by MailChimp
		var requiredFields = fields.getAllWhere('forceRequired', true).map(function(f) { return f.name().toUpperCase(); });

		// query fields in form with [required] attribute
		var requiredFieldElements = editor.query('[required]');
		Array.prototype.forEach.call(requiredFieldElements, function(el) {
			var name = el.name.toUpperCase();

			// bail if name attr starts with underscore
			if( name[0] === '_' ) {
				return;
			}

			// replace array brackets with dot style notation
			name = name.replace(/\[(\w+)\]/g, '.$1' );

			// only add field if it's not already in it
			if( requiredFields.indexOf(name) === -1 ) {
				requiredFields.push(name);
			}
		});

		// update meta
		requiredFieldsInput.value = requiredFields.join(',');
	}

	// events
	editor.on('change', helpers.debounce(updateFields, 500));
	events.on('fields.change', helpers.debounce(updateFields, 500));

};

module.exports = FormWatcher;