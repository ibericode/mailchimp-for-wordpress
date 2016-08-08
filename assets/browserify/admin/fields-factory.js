var FieldFactory = function(settings, fields, i18n) {
	'use strict';

	/**
	 * Array of registered fields
	 *
	 * @type {Array}
	 */
	var registeredFields = [];

	/**
	 * Reset all previously registered fields
	 */
	function reset() {
		// clear all of our fields
		registeredFields.forEach(fields.deregister);
	}

	/**
	 * Helper function to quickly register a field and store it in local scope
	 *
	 * @param {object} data
	 * @param {boolean} sticky
	 */
	function register(data, sticky) {
		var field = fields.register(data);

		if( ! sticky ) {
			registeredFields.push(field);
		}
	}

	/**
	 * Normalizes the field type which is passed by MailChimp
	 *
	 * @todo Maybe do this server-side?
	 *
	 * @param type
	 * @returns {*}
	 */
	function getFieldType(type) {

		var map = {
			'phone' : 'tel',
			'dropdown': 'select',
			'checkboxes': 'checkbox',
			'birthday': 'text'
		};

		return typeof map[ type ] !== "undefined" ? map[type] : type;
	}

	/**
	 * Register the various fields for a merge var
	 *
	 * @param mergeField
	 * @returns {boolean}
	 */
	function registerMergeField(mergeField) {

		// name, type, title, value, required, label, placeholder, choices, wrap
		var data = {
			name: mergeField.tag,
			title: mergeField.name,
			required: mergeField.required,
			forceRequired: mergeField.required,
			type: getFieldType(mergeField.field_type),
			choices: mergeField.choices
		};

		if( data.type !== 'address' ) {
			register(data);
		} else {
			register({ name: data.name + '[addr1]', type: 'text', title: i18n.streetAddress });
			register({ name: data.name + '[city]', type: 'text', title: i18n.city });
			register({ name: data.name + '[state]', type: 'text', title: i18n.state  });
			register({ name: data.name + '[zip]', type: 'text', title: i18n.zip });
			register({ name: data.name + '[country]', type: 'select', title: i18n.country, choices: mc4wp_vars.countries });
		}

		return true;
	}

	/**
	 * Register a field for a MailChimp grouping
	 *
	 * @param interestCategory
	 */
	function registerInterestCategory(interestCategory){

		var data = {
			title: interestCategory.name,
			name: 'INTERESTS[' + interestCategory.id + ']',
			type: getFieldType(interestCategory.field_type),
			choices: interestCategory.interests
		};
		register(data);
	}

	/**
	 * Register all fields belonging to a list
	 *
	 * @param list
	 */
	function registerListFields(list) {

		// make sure public fields come first
		list.merge_fields = list.merge_fields.sort(function(a, b) {
			if( a.name === 'EMAIL' || ( a.public && ! b.public ) ) {
				return -1;
			}

			if( ! a.public && b.public ) {
				return 1;
			}

			return 0;
		});

		// loop through merge vars
		list.merge_fields.forEach(registerMergeField);

		// loop through groupings
		list.interest_categories.forEach(registerInterestCategory);
	}

	/**
	 * Register all lists fields
	 *
	 * @param lists
	 */
	function registerListsFields(lists) {
		reset();
		lists.forEach(registerListFields);
	}

	function registerCustomFields(lists) {

		var choices;

		// register submit button
		register({
			name: '',
			value: i18n.subscribe,
			type: "submit",
			title: i18n.submitButton
		}, true);

		// register lists choice field
		choices = {};
		for(var key in lists) {
			choices[lists[key].id] = lists[key].name;
		}

		register({
			name: '_mc4wp_lists',
			type: 'checkbox',
			title: i18n.listChoice,
			choices: choices,
			help: i18n.listChoiceDescription
		}, true);

		choices = {
			'subscribe': "Subscribe",
			'unsubscribe': "Unsubscribe"
		};
		register({
			name: '_mc4wp_action',
			type: 'radio',
			title: i18n.formAction,
			choices: choices,
			value: 'subscribe',
			help: i18n.formActionDescription
		}, true);
	}

	/**
	 * Expose some methods
	 */
	return {
		'registerCustomFields': registerCustomFields,
		'registerListFields': registerListFields,
		'registerListsFields': registerListsFields
	}

};

module.exports = FieldFactory;