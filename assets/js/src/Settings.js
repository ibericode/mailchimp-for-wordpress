var Settings = function(context) {
	'use strict';

	var EventEmitter = require('./EventEmitter.js');

	// vars
	var events = new EventEmitter();
	var listInputs = context.querySelectorAll('.mc4wp-list-input');
	var proFeatures = context.querySelectorAll('.pro-feature, .pro-feature label, .pro-feature input');
	var doubleOptInInputs = context.querySelectorAll('input[name$="[double_optin]"]');
	var sendWelcomeEmailInputs = context.querySelectorAll('input[name$="[send_welcome]"]');
	var updateExistingInputs = context.querySelectorAll('input[name$="[update_existing]"]');
	var replaceInterestInputs = context.querySelectorAll('input[name$="[replace_interests]"]');

	var lists = mc4wp_vars.mailchimp.lists;

	var selectedLists = [];
	var availableFields = [];
	var requiredFields = [];

	function bindEventToElements( elements, event, handler ) {
		Array.prototype.forEach.call( elements, function(el) {
			if ( el.addEventListener) {
				el.addEventListener(event, handler);
			} else if (el.attachEvent)  {
				el.attachEvent('on' + event, handler);
			}
		});
	}

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

	function showProFeatureNotice() {
		// prevent checking of radio buttons
		if( typeof this.checked === 'boolean' ) {
			this.checked = false;
		}

		alert( mc4wp_vars.l10n.pro_only );
	}

	function toggleSendWelcomeEmailFields(e) {
		var doubleOptInIsEnabled = parseInt(e.target.value);
		sendWelcomeEmailInputs.item(0).parentNode.parentNode.parentNode.style.display = ( doubleOptInIsEnabled ? 'none' : 'table-row' );
	}

	function toggleReplaceInterestFields(e) {
		var updateExistingIsEnabled = parseInt(e.target.value);
		replaceInterestInputs.item(0).parentNode.parentNode.parentNode.style.display = ( updateExistingIsEnabled ? 'table-row' : 'none' );
	}

	// constructor code
	events.on('selectedLists.change', updateAvailableFields);
	events.on('availableFields.change', updateRequiredFields);

	bindEventToElements(listInputs,'change',updateSelectedLists);
	bindEventToElements(proFeatures,'click',showProFeatureNotice);
	bindEventToElements(doubleOptInInputs, 'change', toggleSendWelcomeEmailFields);
	bindEventToElements(updateExistingInputs, 'change', toggleReplaceInterestFields);

	updateSelectedLists();

	return {
		getSelectedLists: getSelectedLists,
		getRequiredFields: getRequiredFields,
		getAvailableFields: getAvailableFields,
		events: events
	}

};

module.exports = Settings;