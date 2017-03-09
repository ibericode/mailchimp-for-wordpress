const Settings = function(context, helpers, events ) {
	'use strict';

	// vars
    let form = context.querySelector('form');
    let listInputs = context.querySelectorAll('.mc4wp-list-input');
    let lists = mc4wp_vars.mailchimp.lists;
    let selectedLists = [];

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
			// skip unchecked checkboxes
			if( typeof( input.checked ) === "boolean" && ! input.checked ) {
				return;
			}

			if( typeof( lists[ input.value ] ) === "object" ){
				selectedLists.push( lists[ input.value ] );
			}
		});

		events.trigger('selectedLists.change', [ selectedLists ]);
		return selectedLists;
	}

	function toggleVisibleLists() {
		let rows = document.querySelectorAll('.lists--only-selected > *');
		Array.prototype.forEach.call(rows, function(el) {

            let listId = el.getAttribute('data-list-id');
            let isSelected = getSelectedListsWhere('id', listId).length > 0;

			if( isSelected ) {
				el.setAttribute('class', el.getAttribute('class').replace('hidden',''));
			} else {
				el.setAttribute('class', el.getAttribute('class') + " hidden" );
			}
		});
	}

	events.on('selectedLists.change', toggleVisibleLists);
	helpers.bindEventToElements(listInputs,'change',updateSelectedLists);

	updateSelectedLists();

	return {
		getSelectedLists: getSelectedLists
	}

};

module.exports = Settings;