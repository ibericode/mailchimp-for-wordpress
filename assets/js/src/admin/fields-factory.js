var FieldFactory = function(settings, fields) {
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
		registeredFields.forEach(function(field) {
			fields.deregister(field);
		});
	}

	/**
	 * Helper function to quickly register a field and store it in local scope
	 *
	 * @param data
	 */
	function register(data) {
		var field = fields.register(data);
		registeredFields.push(field);
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
		switch(type) {
			case 'phone':
				return 'tel';
				break;

			case 'dropdown':
				return 'select';

			case 'checkboxes':
				return 'checkbox';
		}

		return type;
	}

	/**
	 * Register the various fields for a merge var
	 *
	 * @param mergeVar
	 * @returns {boolean}
	 */
	function registerMergeVar(mergeVar) {

		// only register merge var field if it's public
		if( ! mergeVar.public ) {
			return false;
		}

		// name, type, title, value, required, label, placeholder, choices, wrap
		var data = {
			name: mergeVar.tag,
			title: mergeVar.name,
			required: mergeVar.required,
			type: getFieldType(mergeVar.field_type),
			choices: mergeVar.choices
		};

		if( data.type !== 'address' ) {
			register(data);
		} else {
			register({ name: data.name + '[addr1]', type: 'text', title: 'Street Address' });
			register({ name: data.name + '[city]', type: 'text', title: 'City' });
			register({ name: data.name + '[state]', type: 'text', title: 'State' });
			register({ name: data.name + '[zip]', type: 'text', title: 'ZIP' });
			register({ name: data.name + '[country]', type: 'select', title: 'Country', choices: mc4wp_vars.countries });
		}

		return true;
	}

	/**
	 * Register a field for a MailChimp grouping
	 *
	 * @param grouping
	 */
	function registerGrouping(grouping){

		var data = {
			title: grouping.name,
			name: 'GROUPINGS[' + grouping.id + ']',
			type: getFieldType(grouping.field_type),
			choices: grouping.groups
		};
		register(data);
	}

	/**
	 * Register all fields belonging to a list
	 *
	 * @param list
	 */
	function registerListFields(list) {
		// loop through merge vars
		list.merge_vars.forEach(registerMergeVar);

		// loop through groupings
		list.groupings.forEach(registerGrouping);
	}

	function registerCustomFields(lists) {

		var choices;

		// register submit button
		register({
			name: '',
			value: "Subscribe",
			type: "submit",
			title: "Submit Button"
		});

		// register lists choice field
		choices = {};
		lists.forEach(function(list) {
			choices[list.id] = list.name;
		});
		register({
			name: '_mc4wp_lists',
			type: 'checkbox',
			title: "List Choice",
			choices: choices,
			help: 'This field will allow your visitors to choose a list to subscribe to. <a href="#" data-tab="settings" class="tab-link">Click here to select more lists to show</a>.'
		});

		choices = {
			'subscribe': "Subscribe",
			'unsubscribe': "Unsubscribe"
		};
		register({
			name: '_mc4wp_action',
			type: 'radio',
			title: "Subscribe / Unsubscribe",
			choices: choices,
			value: 'subscribe',
			help: 'This field will allow your visitors to choose whether they would like to subscribe or unsubscribe'
		});
	}

	/**
	 * Update list fields
	 *
	 * @param lists
	 */
	function work(lists) {

		// clear our fields
		reset();

		// register list specific fields
		lists.forEach(registerListFields);

		// register global fields like "submit" & "list choice"
		registerCustomFields(lists);

		settings.events.trigger('fields.change');

		if(window.m) {
			m.redraw();
		}
	}

	settings.events.on('selectedLists.change',work);

	/**
	 * Expose some methods
	 */
	return {
		'work': work
	}

};

module.exports = FieldFactory;