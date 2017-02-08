'use strict';

module.exports = function(m, events) {
    var timeout;
	var fields = [];
	var categories = [];


	/**
	 * @internal
	 *
	 *
	 * @param data
	 * @constructor
	 */
	var Field = function (data) {
		this.name = m.prop(data.name);
		this.title = m.prop(data.title || data.name);
		this.type = m.prop(data.type);
		this.mailchimpType = m.prop(data.mailchimpType || '');
		this.label = m.prop(data.title || '');
		this.value = m.prop(data.value || '');
		this.placeholder = m.prop(data.placeholder || '');
		this.required = m.prop(data.required || false);
		this.forceRequired = m.prop( data.forceRequired || false );
		this.wrap = m.prop(data.wrap || true);
		this.min = m.prop(data.min || null);
		this.max = m.prop(data.max || null);
		this.help = m.prop(data.help || '');
		this.choices = m.prop(data.choices || []);
		this.inFormContent = m.prop(null);
		this.acceptsMultipleValues = data.acceptsMultipleValues;

		this.selectChoice = function(value) {
			var field = this;

			this.choices(this.choices().map(function(choice) {

				if( choice.value() === value ) {
					choice.selected(true);
				} else {
					// only checkboxes allow for multiple selections
					if( field.type() !== 'checkbox' ) {
						choice.selected(false);
					}
				}

				return choice;

			}));
		};
	};

	/**
	 * @internal
	 *
	 * @param data
	 * @constructor
	 */
	var FieldChoice = function (data) {
		this.label = m.prop(data.label);
		this.title = m.prop(data.title || data.label);
		this.selected = m.prop(data.selected || false);
		this.value = m.prop(data.value || data.label);
	};

	/**
	 * Creates FieldChoice objects from an (associative) array of data objects
	 *
	 * @param data
	 * @returns {Array}
	 */
	function createChoices(data) {
		var choices = [];
		if (typeof( data.map ) === "function") {
			choices = data.map(function (choiceLabel) {
				return new FieldChoice({label: choiceLabel});
			});
		} else {
			choices = Object.keys(data).map(function (key) {
				var choiceLabel = data[key];
				return new FieldChoice({label: choiceLabel, value: key});
			});
		}

		return choices;
	}

	/**
	 * Factory method
	 *
	 * @api
	 *
	 * @param data
	 * @returns {Field}
	 */
	function register(category, data) {

		var field;
		var existingField = getAllWhere('name', data.name).shift();

		// a field with the same "name" already exists
		if(existingField) {

			// update "required" status
			if( ! existingField.forceRequired() && data.forceRequired ) {
				existingField.forceRequired(true);
			}

			// bail
			return undefined;
		}

		// array of choices given? convert to FieldChoice objects
		if (data.choices) {
			data.choices = createChoices(data.choices);

			if( data.value) {
				data.choices = data.choices.map(function(choice) {
					if(choice.value() === data.value) {
						choice.selected(true);
					}
					return choice;
				});
			}
		}

		// register category
		if( categories.indexOf(category) < 0 ) {
			categories.push(category);
		}

		// create Field object
		field = new Field(data);
		field.category = category;

		// add to array
		fields.push(field);

		// redraw view
        timeout && window.clearTimeout(timeout);
        timeout = window.setTimeout(m.redraw, 200);

		// trigger event
		events.trigger('fields.change');

		return field;
	}

	/**
	 * @api
	 *
	 * @param field
	 */
	function deregister(field) {
		var index = fields.indexOf(field);
		if (index > -1) {
			delete fields[index];
			m.redraw();
		}
	}

	/**
	 * Get a field config object
	 *
	 * @param name
	 * @returns {*}
	 */
	function get(name) {
		return fields[name];
	}

	/**
	 * Get all field config objects
	 *
	 * @returns {Array|*}
	 */
	function getAll() {
		// rebuild index property on all fields
		fields = fields.map(function(f, i) {
			f.index = i;
			return f;
		});

		return fields;
	}

	function getCategories() {
		return categories;
	}

	/**
	 * Get all fields where a property matches the given value
	 *
	 * @param searchKey
	 * @param searchValue
	 * @returns {Array|*}
	 */
	function getAllWhere(searchKey, searchValue) {
		return fields.filter(function (field) {
			return field[searchKey]() === searchValue;
		});
	}


	/**
	 * Exposed methods
	 */
	return {
		'get'        : get,
		'getAll'     : getAll,
		'getCategories': getCategories,
		'deregister' : deregister,
		'register'   : register,
		'getAllWhere': getAllWhere
	};
};