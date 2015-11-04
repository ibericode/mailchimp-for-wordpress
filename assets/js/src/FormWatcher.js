var FormWatcher = function(editor, settings, fields) {
	'use strict';

	var missingFieldsNotice = document.getElementById('missing-fields-notice');
	var missingFieldsNoticeList = missingFieldsNotice.querySelector('ul');
	var requiredFieldsInput = document.getElementById('required-fields');

	// functions
	function checkPresenceOfRequiredFields() {

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

	function findRequiredFields(content) {

		// load content in memory
		var form = document.createElement('div');
		form.innerHTML = editor.getValue();

		// query fields with [required] attribute
		var requiredFieldElements = form.querySelectorAll('[required]');
		var requiredFields = Array.prototype.map.call(requiredFieldElements, function(el) {
			return el.name;
		});

		// update meta
		requiredFieldsInput.value = requiredFields.join(',');
	}

	// events
	editor.on('change', checkPresenceOfRequiredFields );
	editor.on('change', findRequiredFields );

	settings.events.on('requiredFields.change', checkPresenceOfRequiredFields);

};

module.exports = FormWatcher;