var FormWatcher = function(m, editor, settings, fields, events) {
	'use strict';

	var missingFieldsNotice = document.getElementById('missing-fields-notice');
	var missingFieldsNoticeList = missingFieldsNotice.querySelector('ul');
	var requiredFieldsInput = document.getElementById('required-fields');

	// polling
	function debounce(func, wait, immediate) {
		var timeout;
		return function() {
			var context = this, args = arguments;
			var later = function() {
				timeout = null;
				if (!immediate) func.apply(context, args);
			};
			var callNow = immediate && !timeout;
			clearTimeout(timeout);
			timeout = setTimeout(later, wait);
			if (callNow) func.apply(context, args);
		};
	}

	function updateFields() {
		fields.getAll().forEach(function(field) {
			// don't run for empty field names
			if(field.name().length <= 0) return;

			var inForm = editor.containsField( field.name() );
			field.inFormContent( inForm );
		});

		checkRequiredFields();

		m.redraw();
	}

	// functions
	function checkRequiredFields() {

		var requiredFields = fields.getAllWhere('required', true);

		// check presence for each required field
		var missingFields = [];
		requiredFields.forEach(function(field) {
			if( ! field.inFormContent() ) {
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

	function findRequiredFields() {

		// query fields required by MailChimp
		var requiredFields = fields.getAllWhere('required', true).map(function(f) {
			return f.name().toUpperCase();
		});

		// query fields with [required] attribute
		var requiredFieldElements = editor.query('[required]');
		Array.prototype.forEach.call(requiredFieldElements, function(el) {
			var name = el.name.toUpperCase();

			// only add field if it's not already in it
			if( requiredFields.indexOf(name) === -1 ) {
				requiredFields.push(name);
			}
		});

		// update meta
		requiredFieldsInput.value = requiredFields.join(',');
	}

	// events
	editor.on('change', debounce(updateFields,334));
	editor.on('blur', findRequiredFields );
	events.on('fields.change', debounce(updateFields, 500));

};

module.exports = FormWatcher;