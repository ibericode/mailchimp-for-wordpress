var Settings = function(context) {
	'use strict';

	var EventEmitter = require('./EventEmitter.js');

	// vars
	var events = new EventEmitter();
	var listInputs = context.querySelectorAll('.mc4wp-list-input');
	var lists = mc4wp_vars.mailchimp.lists;

	var selectedLists = [];
	var availableFields = [];
	var requiredFields = [];

	// functions
	function getSelectedLists() {
		return selectedLists;
	}

	function updateSelectedLists() {
		selectedLists = [];
		Array.prototype.forEach.call(listInputs, function(input) {
			if( ! input.checked ) return;
			if( typeof( lists[ input.value ] ) === "object" ){
				selectedLists.push( lists[ input.value ] );
			}
		});

		events.trigger('selectedLists.change', [ selectedLists ]);
		return selectedLists;
	}

	function getAvailableFields() {
		return availableFields;
	}

	function updateAvailableFields() {
		availableFields = [];
		selectedLists.forEach(function( list ) {
			list.merge_vars.forEach(function(field) {
				if( availableFields.filter(function(existingField) { return existingField.tag === field.tag; }).length === 0 ){
					availableFields.push(field);
				}
			})
		});
		events.trigger('availableFields.change', [availableFields]);
		return availableFields;
	}

	function getRequiredFields() {
		return requiredFields;
	}

	function updateRequiredFields() {
		requiredFields = [];
		availableFields.forEach(function(field) {
			if(field.req) {
				requiredFields.push(field);
			}
		});
		events.trigger('requiredFields.change', [requiredFields]);
		return requiredFields;
	}

	// constructor code
	events.on('selectedLists.change', updateAvailableFields);
	events.on('availableFields.change', updateRequiredFields);

	Array.prototype.forEach.call( listInputs, function(inputEl) {
		if ( inputEl.addEventListener) {
			inputEl.addEventListener('change', updateSelectedLists);
		} else if (el.attachEvent)  {
			inputEl.attachEvent('change', updateSelectedLists);
		}
	});

	updateSelectedLists();

	return {
		getSelectedLists: getSelectedLists,
		getRequiredFields: getRequiredFields,
		getAvailableFields: getAvailableFields,
		events: events
	}

};

module.exports = Settings;