'use strict';

const m = require('mithril');
let timeout;
let fields = [];
let categories = [];
let listeners = {};

const Field = function (data) {
	return {
		name: data.name,
		title: data.title || data.name,
		type: data.type,
		mailchimpType: data.mailchimpType || null,
		label: data.label || data.title || '',
		showLabel: typeof(data.showLabel) === "boolean" ? data.showLabel : true,
		value: data.value || '',
		placeholder: data.placeholder || '',
		required: data.required || false,
		forceRequired: data.forceRequired || false,
		wrap: typeof(data.wrap) === "boolean" ? data.wrap : true,
		min: data.min,
		max: data.max,
		help: data.help || '',
		choices: data.choices || [],
		inFormContent: null,
		acceptsMultipleValues: data.acceptsMultipleValues,
		link: data.link || ''
	};
};

/**
 * @internal
 *
 * @param data
 * @constructor
 */
const FieldChoice = function (data) {
	return {
		title: data.title || data.label,
		selected: data.selected || false,
		value: data.value || data.label,
		label: data.label,
	};
};

/**
 * Creates FieldChoice objects from an (associative) array of data objects
 *
 * @param data
 * @returns {Array}
 */
function createChoices(data) {
    let choices = [];
    if (typeof( data.map ) === "function") {
        choices = data.map(function (choiceLabel) {
            return new FieldChoice({label: choiceLabel});
        });
    } else {
        choices = Object.keys(data).map(function (key) {
            let choiceLabel = data[key];
            return new FieldChoice({label: choiceLabel, value: key});
        });
    }

    return choices;
}

/**
 * Factory method
 *
 * @returns {Field}
 */
function register(category, data) {
    let field;
    let existingField = getAllWhere('name', data.name).shift();

    // a field with the same "name" already exists
    if(existingField) {

        // update "required" status
        if( ! existingField.forceRequired && data.forceRequired ) {
            existingField.forceRequired = true;
        }

        // bail
        return undefined;
    }

    // array of choices given? convert to FieldChoice objects
    if (data.choices) {
        data.choices = createChoices(data.choices);

        if( data.value) {
            data.choices = data.choices.map(function(choice) {
                if(choice.value === data.value) {
                    choice.selected = true;
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
	// TODO: Move this out
    timeout && window.clearTimeout(timeout);
    timeout = window.setTimeout(m.redraw, 200);

    // trigger event
	emit('change');

    return field;
}

function emit(event, args) {
	listeners[event] = listeners[event] || [];
	listeners[event].forEach(f => f.apply(null, args));
}

function on(event, func) {
	listeners[event] = listeners[event] || [];
	listeners[event].push(func);
}

/**
 * @api
 *
 * @param field
 */
function deregister(field) {
    let index = fields.indexOf(field);
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
    return categories.sort((a, b) => {
        return a !== "Form fields" ? -1 : 1;
    });
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
        return field[searchKey] === searchValue;
    });
}


/**
 * Exposed methods
 */
module.exports =  {
    'get'        : get,
    'getAll'     : getAll,
    'getCategories': getCategories,
    'deregister' : deregister,
    'register'   : register,
    'getAllWhere': getAllWhere,
	on
};
