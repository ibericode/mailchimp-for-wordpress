var FieldFactory = function(settings, fields) {

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
			var regularField = fields.register(data);
			registeredFields.push(regularField);
		} else {
			var addr1Field = fields.register({ name: data.name + '[addr1]', type: 'text', title: 'Street Address' });
			registeredFields.push(addr1Field);

			var cityField = fields.register({ name: data.name + '[city]', type: 'text', title: 'City' });
			registeredFields.push(cityField);

			var stateField = fields.register({ name: data.name + '[state]', type: 'text', title: 'State' });
			registeredFields.push(stateField);

			var zipField = fields.register({ name: data.name + '[zip]', type: 'text', title: 'ZIP' });
			registeredFields.push(zipField);

			var countryField = fields.register({ name: data.name + '[country]', type: 'select', title: 'Country', choices: mc4wp_vars.countries });
			registeredFields.push(countryField);
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
		var field = fields.register(data);
		registeredFields.push(field);
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

	/**
	 * Update list fields
	 *
	 * @param lists
	 */
	function work(lists) {
		reset();
		lists.forEach(registerListFields);

		// todo register custom fields
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