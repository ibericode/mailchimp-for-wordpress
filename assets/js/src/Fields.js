'use strict';

/**
 * @internal
 *
 * @param data
 * @constructor
 */
var Field = function(data) {
	this.name = m.prop(data.name);
	this.title = m.prop( data.title || data.name );

	this.type = m.prop(data.type);
	this.label = m.prop(data.title || '');
	this.value = m.prop(data.value || '');
	this.placeholder = m.prop(data.placeholder || true);
	this.required = m.prop(data.required || false);
	this.wrap = m.prop(data.wrap || false);

	// auto convert associative arrays to FieldChoice objects
	this.choices = m.prop(data.choices || []);
};

/**
 * @internal
 *
 * @param data
 * @constructor
 */
var FieldChoice = function( data ) {
	this.label = m.prop( data.label );
	this.selected = m.prop( data.selected || false );
	this.value = m.prop( data.value || data.label );
};


/**
 * @api
 *
 * @returns {{fields: {}, get: get, getAll: getAll, deregister: deregister, register: register}}
 * @constructor
 */
var Fields = function() {
	var fields = [];

	/**
	 * Creates FieldChoice objects from an (associative) array of data objects
	 *
	 * @todo allow for 'selected' property
	 *
	 * @param data
	 * @returns {Array}
	 */
	function createChoices(data) {
		var choices = [];
		if( typeof( data.map ) === "function" )  {
			choices = data.map(function(choiceLabel) {
				return new FieldChoice({ label: choiceLabel });
			});
		} else {
			choices = Object.keys(data).map(function(key) {
				var choiceLabel = data[key];
				return new FieldChoice({ label: choiceLabel, value: key });
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
	function register(data) {

		// bail if a field with this name has been registered already
		if( getAllWhere('name', data.name).length > 0 ) {
			return;
		}

		// array of choices given? convert to FieldChoice objects
		if( data.choices ) {
			data.choices = createChoices(data.choices);
		}

		// create Field object
		var field = new Field(data);
		fields.push(field);

		// redraw view
		m.redraw();

		return field;
	}

	/**
	 * @api
	 *
	 * @param field
	 */
	function deregister(field) {
		var index = fields.indexOf(field);
		if( index) {
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
		return fields;
	}

	/**
	 * Get all fields where a property matches the given value
	 *
	 * @param searchKey
	 * @param searchValue
	 * @returns {Array|*}
	 */
	function getAllWhere(searchKey, searchValue) {
		return fields.filter(function(field){
			return field[searchKey]() === searchValue;
		});
	}


	/**
	 * Exposed methods
	 */
	return {
		'fields': fields,
		'get': get,
		'getAll': getAll,
		'deregister': deregister,
		'register': register,
		'getAllWhere': getAllWhere
	};
};

module.exports = Fields();