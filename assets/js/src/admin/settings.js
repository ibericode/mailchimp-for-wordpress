var Settings = function(context) {
	'use strict';

	var EventEmitter = require('../../third-party/event-emitter.js');

	// vars
	var events = new EventEmitter();
	var listInputs = context.querySelectorAll('.mc4wp-list-input');
	var proFeatures = context.querySelectorAll('.pro-feature, .pro-feature label, .pro-feature input');
	var showIfElements = context.querySelectorAll('[data-showif]');
	var lists = mc4wp_vars.mailchimp.lists;
	var selectedLists = [];

	function initShowIf() {
		// dependent elements
		Array.prototype.forEach.call( showIfElements, function(element) {
			var config = JSON.parse( element.dataset.showif );
			var parentElements = context.querySelectorAll('[name="'+ config.element +'"]');
			var inputs = element.querySelectorAll('input');

			function toggleElement() {
				// if this is called for radio or checkboxes, we require it to be checked to count the "value".
				var conditionMet = ( typeof( this.checked ) === "undefined" || this.checked ) &&  this.value == config.value;
				element.style.display = conditionMet ? '' : 'none';

				// disable input fields
				Array.prototype.forEach.call( inputs, function(inputElement) {
					inputElement.disabled = !conditionMet;
				});
			}

			// find checked element and call toggleElement function
			Array.prototype.forEach.call( parentElements, function( parentElement ) {
				toggleElement.call(parentElement);
			});

			// bind on all changes
			bindEventToElements(parentElements, 'change', toggleElement);
		});
	}

	function bindEventToElement(element,event,handler) {
		if ( element.addEventListener) {
			element.addEventListener(event, handler);
		} else if (element.attachEvent)  {
			element.attachEvent('on' + event, handler);
		}
	}

	function bindEventToElements( elements, event, handler ) {
		Array.prototype.forEach.call( elements, function(element) {
			bindEventToElement(element,event,handler);
		});
	}

	// functions
	function getSelectedListsWhere(searchKey,searchValue) {
		return selectedLists.filter(function(el) {
			return el[searchKey] === searchValue;
		});
	}

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

	function toggleVisibleLists() {
		var rows = document.querySelectorAll('.lists--only-selected > *');
		Array.prototype.forEach.call(rows, function(el) {

			var listId = el.dataset.id;
			var isSelected = getSelectedListsWhere('id', listId).length > 0;

			if( isSelected ) {
				el.classList.remove('hidden');
			} else {
				el.classList.add('hidden');
			}

		});
	}

	function showProFeatureNotice() {
		// prevent checking of radio buttons
		if( typeof this.checked === 'boolean' ) {
			this.checked = false;
		}

		alert( mc4wp_vars.l10n.pro_only );
	}

	events.on('selectedLists.change', toggleVisibleLists);
	bindEventToElements(listInputs,'change',updateSelectedLists);
	bindEventToElements(proFeatures,'click',showProFeatureNotice);

	updateSelectedLists();
	initShowIf();

	return {
		getSelectedLists: getSelectedLists,
		events: events
	}

};

module.exports = Settings;