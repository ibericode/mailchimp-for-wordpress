var Settings = function(context, helpers, events ) {
	'use strict';

	// vars
	var unsaved = false;
	var form = context.querySelector('form');
	var listInputs = context.querySelectorAll('.mc4wp-list-input');
	var lists = mc4wp_vars.mailchimp.lists;
	var selectedLists = [];

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

			var listId = el.getAttribute('data-list-id');
			var isSelected = getSelectedListsWhere('id', listId).length > 0;

			if( isSelected ) {
				el.setAttribute('class', el.getAttribute('class').replace('hidden',''));
			} else {
				el.setAttribute('class', el.getAttribute('class') + " hidden" );
			}
		});
	}

	// TODO: make this translatable
	function confirmPageLeave(e) {
		if(!unsaved) return true;

		e = e|| window.event;
		var confirmationMessage = 'It looks like you have been editing something. '
			+ 'If you leave before saving, your changes will be lost.';

		e.returnValue = confirmationMessage; //Gecko + IE
		return confirmationMessage; //Gecko + Webkit, Safari, Chrome etc.
	}

	events.on('selectedLists.change', toggleVisibleLists);
	helpers.bindEventToElements(listInputs,'change',updateSelectedLists);

	// only way to leave a page with changes is using a form
	if( form ) {
		helpers.bindEventToElement(form,'change',function() { unsaved = true; });
		helpers.bindEventToElement(form,'submit',function() { unsaved = false; });
		helpers.bindEventToElement(window,'beforeunload', confirmPageLeave);
	}

	updateSelectedLists();

	return {
		getSelectedLists: getSelectedLists
	}

};

module.exports = Settings;