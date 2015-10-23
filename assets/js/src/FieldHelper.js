var FieldHelper = function() {
	'use strict';

	var selectedListInputs = document.querySelectorAll('.mc4wp-list-input:checked');
	var lists = mc4wp_vars.mailchimp.lists;

	/**
	 *
	 * @returns {Array}
	 */
	function getSelectedLists() {
		var selectedLists = Array.prototype.map.call(selectedListInputs, function(input) {
			return lists[ input.value ] || null;
		});
		selectedLists = selectedLists.filter(function(el) { return !!el;});
		return selectedLists;
	}

	function getAvailableFields( lists ) {
		var fields = {};

		lists.map(function(list) {
			return list.merge_vars.map(function(field) {
				if( typeof( fields[ field.tag ] === "undefined" ) ) {
					fields[ field.tag ] = field;
				}
			});
		});

		return fields;
	}


	/**
	 * Controller
	 */
	function controller() {
		this.selectedLists = getSelectedLists();
		this.fields = getAvailableFields(this.selectedLists);
		this.chosenFieldTag = m.prop('');
	}

	/**
	 * View
	 *
	 * @param ctrl
	 * @returns {*}
	 */
	function view( ctrl ) {

		var chosenField = ctrl.fields[ ctrl.chosenFieldTag() ];
		var active = typeof(chosenField) === "object"; //!== undefined;

		// build DOM for fields choice
		var fieldsChoice = m( "div", [
			Object.keys(ctrl.fields).map(function(key) {
				var field = ctrl.fields[key];
				return [
					m("button", {
						class  : "button",
						type   : 'button',
						onclick: m.withAttr("value", ctrl.chosenFieldTag),
						value  : field.tag
					}, field.name),
					m("span", " ")
				];
			})
		]);

		// build DOM for overlay
		var overlay = null;
		if( active ) {
			overlay = [
				m( "div.overlay", [
					m("h3", chosenField.name)
				]),
				m( "div.overlay-background", { onclick: function() { ctrl.chosenFieldTag(''); }})
			];
		}

		return [
			fieldsChoice,
			overlay
		];
	}

	// expose some variables
	return {
		view: view,
		controller: controller
	}
};

module.exports = FieldHelper;