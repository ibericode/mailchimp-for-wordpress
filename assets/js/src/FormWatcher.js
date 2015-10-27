var FormWatcher = function(editor, settings, fields) {
	'use strict';

	var missingFieldsNotice = document.getElementById('missing-fields-notice');
	var missingFieldsNoticeList = missingFieldsNotice.querySelector('ul');

	// functions
	function checkRequiredFields() {

		var formContent = editor.getValue();
		var requiredFields = fields.getAllWhere('required', true);

		// let's go
		formContent = formContent.toLowerCase();

		// check presence for each required field
		var missingFields = [];
		requiredFields.forEach(function(field) {
			var fieldSearch = 'name="' + field.name().toLowerCase();
			if( formContent.indexOf( fieldSearch ) == -1 ) {
				missingFields.push(field);
			}
		});

		// do nothing if no fields are missing
		if( missingFields.length === 0 ) {
			missingFieldsNotice.style.display = 'none';
			return;
		}

		// show notice
		var listItems = '';
		missingFields.forEach(function( field ) {
			listItems += "<li>" + field.label() + " (<code>" + field.name() + "</code>)</li>";
		});

		missingFieldsNoticeList.innerHTML = listItems;
		missingFieldsNotice.style.display = 'block';
	}

	// events
	editor.on('change', checkRequiredFields );
	settings.events.on('requiredFields.change', checkRequiredFields);

	// constructor
	checkRequiredFields();

	return {
		checkRequiredFields: checkRequiredFields
	}

};

module.exports = FormWatcher;