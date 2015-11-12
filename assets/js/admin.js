(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
(function() {
	'use strict';

	window.mc4wp = window.mc4wp || {};

	// dependencies
	var FormWatcher = require('./admin/form-watcher.js');
	var FormEditor = require('./admin/form-editor.js');
	var FieldHelper = require('./admin/field-helper.js');
	var FieldsFactory = require('./admin/fields-factory.js');
	var m = require('../third-party/mithril.js');
	var Lucy = require('./admin/lucy.js');

	// vars
	var context = document.getElementById('mc4wp-admin');
	var formContentTextarea = document.getElementById('mc4wp-form-content');
	var tabs = require ('./admin/tabs.js')(context);
	var helpers = require('./admin/helpers.js');
	window.mc4wp.helpers = helpers;
	var settings = require('./admin/settings.js')(context, helpers);
	var fields = require('./admin/fields.js')(m);

	// are we on edit forms page?
	if( formContentTextarea ) {
		// instantiate form editor
		var formEditor = window.formEditor = new FormEditor( formContentTextarea );

		// run field factory (registers fields from merge vars & interest groupings of selected lists)
		var fieldsFactory = new FieldsFactory(settings,fields);
		fieldsFactory.work(settings.getSelectedLists());

		// instantiate form watcher
		var formWatcher = new FormWatcher( formEditor, settings, fields );

		// instantiate form field helper
		var fieldHelper = new FieldHelper( m, tabs, formEditor, fields );
		m.mount( document.getElementById( 'mc4wp-field-wizard'), fieldHelper );
	}

	// Lucy!
	var lucy = new Lucy(
		'https://mc4wp.com/',
		'DA9YFSTRKA',
		'ce1c93fad15be2b70e0aa0b1c2e52d8e',
		'wpkb_articles',
		[
			{
				text: "<span class=\"dashicons dashicons-book\"></span> Knowledge Base",
				href: "https://mc4wp.com/kb/"
			},
			{
				text: "<span class=\"dashicons dashicons-editor-code\"></span> Code Reference",
				href: "http://developer.mc4wp.com/"
			},
			{
				text: "<span class=\"dashicons dashicons-editor-break\"></span> Changelog",
				href: "http://mc4wp.com/documentation/changelog/"
			}

		],
		'mailto:support@mc4wp.com'
	);

	// expose some things
	// @TODO clean-up
	window.m = m;
	window.mc4wp_register_field = fields.register;
	window.mc4wp_deregister_field = fields.deregister;
})();
},{"../third-party/mithril.js":18,"./admin/field-helper.js":5,"./admin/fields-factory.js":6,"./admin/fields.js":7,"./admin/form-editor.js":8,"./admin/form-watcher.js":9,"./admin/helpers.js":10,"./admin/lucy.js":11,"./admin/settings.js":13,"./admin/tabs.js":14}],2:[function(require,module,exports){
var rows = function(m) {
	'use strict';

	var r = {};

	r.label = function (config) {
		// label row
		return m("div", [
			m("label", "Field Label"),
			m("input.widefat", {
				type       : "text",
				value      : config.label(),
				onchange   : m.withAttr('value', config.label),
				placeholder: config.title()
			})
		]);
	};

	r.defaultValue = function (config) {
		return m("div", [
			m("label", "Default Value"),
			m("input.widefat", {
				type   : "text",
				value  : config.value(),
				oninput: m.withAttr('value', config.value)
			})
		]);
	};

	r.numberMinMax = function (config) {
		return m('div', [
			m('div.row', [
				m('div.col.col-3', [
					m('label', "Min"),
					m('input', {type: 'number', onchange: m.withAttr('value', config.min)})
				]),
				m('div.col.col-3', [
					m('label', 'Max'),
					m('input', {type: 'number', onchange: m.withAttr('value', config.max)})
				])
			])
		])
	};


	r.isRequired = function (config) {
		return m('div', [
			m('label.cb-wrap', [
				m('input', {
					type    : 'checkbox',
					checked : config.required(),
					onchange: m.withAttr('checked', config.required)
				}),
				"Is this field required?"
			])
		]);
	};

	r.usePlaceholder = function (config) {

		if (config.value().length > 0) {
			return m("div", [
				m("label.cb-wrap", [
					m("input", {
						type    : 'checkbox',
						checked : config.placeholder(),
						onchange: m.withAttr('checked', config.placeholder)
					}),
					"Use \"" + config.value() + "\" as placeholder for the field."
				])
			]);
		}
	};

	r.useParagraphs = function (config) {
		return m('div', [
			m('label.cb-wrap', [
				m('input', {
					type    : 'checkbox',
					checked : config.wrap(),
					onchange: m.withAttr('checked', config.wrap)
				}),
				"Wrap in paragraph tags?"
			])
		]);
	};

	r.choiceType = function (config) {
		return m('div', [
			m('label', "Choice Type"),
			m('select', {
				value   : config.type(),
				onchange: m.withAttr('value', config.type)
			}, [
				m('option', {
					value   : 'select',
					selected: config.type() === 'select' ? 'selected' : false
				}, 'Dropdown'),
				m('option', {
					value   : 'radio',
					selected: config.type() === 'radio' ? 'selected' : false
				}, 'Radio Button'),
				m('option', {
					value   : 'checkbox',
					selected: config.type() === 'checkbox' ? 'selected' : false
				}, 'Checkboxes')
			])
		]);
	};

	r.choices = function (config) {


		return m('div', [
			m('label', "Choices"),
			m('div.limit-height', [
				m("table", [

					// table body
					config.choices().map(function (choice, index) {
						return m('tr', {
							'data-id': index
						}, [
							m('td.cb', m('input', {
									name    : 'selected',
									type    : (config.type() === 'checkbox' ) ? 'checkbox' : 'radio',
									onchange: m.withAttr('value', config.selectChoice.bind(config)),
									checked: choice.selected(),
									value: choice.value()
								})
							),
							m('td.stretch', m('input.widefat', {
								type       : 'text',
								value      : choice.label(),
								placeholder: choice.title(),
								onchange   : m.withAttr('value', choice.label)
							})),
							m('td', m('span', {
								"class": 'dashicons dashicons-no-alt hover-activated',
								onclick: function (key) {
									this.choices().splice(key, 1);
								}.bind(config, index)
							}, ''))
						])
					})
				]) // end of table
			]) // end of limit-height div
		]);
	};

	return r;
};

module.exports = rows;
},{}],3:[function(require,module,exports){
var forms = function(m) {
	var forms = {};
	var rows = require('./field-forms-rows.js')(m);

	// route to one of the other form configs, default to "text"
	forms.render = function(config) {

		var type = config.type();

		if( typeof( forms[type] ) === "function" ) {
			return forms[ type ](config);
		}

		switch( type ) {
			case 'select':
			case 'radio':
			case 'checkbox':
				return forms.choice(config);
				break;
		}

		// fallback to good old text field
		return forms.text(config);
	};


	forms.text = function(config) {
		return [
			rows.label(config),
			rows.defaultValue(config),
			rows.usePlaceholder(config),
			rows.isRequired(config),
			rows.useParagraphs(config)
		]
	};

	forms.choice = function(config) {
		return [
			rows.label(config),
			rows.choiceType(config),
			rows.choices(config),
			rows.useParagraphs(config)
		]
	};

	forms.hidden = function( config ) {
		return [
			rows.defaultValue(config)
		]
	};

	forms.submit = function(config) {

		config.label('');
		config.placeholder(false);

		return [
			rows.defaultValue(config),
			rows.useParagraphs(config)
		]
	};

	forms.number = function(config) {

		return [
			forms.text(config),
			rows.numberMinMax(config)
		];
	};

	return forms;
};



module.exports = forms;
},{"./field-forms-rows.js":2}],4:[function(require,module,exports){
var g = function(m) {
	'use strict';

	var render = require('../../third-party/render.js');
	var html_beautify = require('../../third-party/beautify-html.js');
	var generators = {};

	/**
	 * Generates a <select> field
	 * @param config
	 * @returns {*}
	 */
	generators.select = function (config) {
		var field = m('select', {name: config.name()}, [
			config.choices().map(function (choice) {
				return m('option', {
					value   : ( choice.value() !== choice.label() ) ? choice.value() : undefined,
					selected: choice.selected()
				}, choice.label())
			})
		]);
		return field;
	};

	/**
	 * Generates a checkbox or radio type input field.
	 *
	 * @param config
	 * @returns {*}
	 */
	generators.checkbox = function (config) {
		var field = config.choices().map(function (choice) {
			return m('label', [
					m('input', {
						name   : config.name() + ( config.type() === 'checkbox' ? '[]' : '' ),
						type   : config.type(),
						value  : choice.value(),
						checked: choice.selected()
					}),
					m('span', choice.label())
				]
			)
		});
		return field;
	};
	generators.radio = generators.checkbox;

	/**
	 * Generates a default field
	 *
	 * - text, url, number, email, date
	 *
	 * @param config
	 * @returns {*}
	 */
	generators['default'] = function (config) {

		var attributes = {
			type: config.type()
		};
		var field;

		if (config.name()) {
			attributes.name = config.name();
		}

		if (config.min()) {
			attributes.min = config.min();
		}

		if (config.max()) {
			attributes.max = config.max();
		}

		if (config.value().length > 0) {
			if (config.placeholder()) {
				attributes.placeholder = config.value();
			} else {
				attributes.value = config.value();
			}
		}

		attributes.required = config.required();

		field = m('input', attributes);
		return field;
	};

	/**
	 * Generates an HTML string based on a field (config) object
	 *
	 * @param config
	 * @returns {*}
	 */
	function generate(config) {
		var label, field;

		label = config.label().length ? m("label", config.label()) : '';
		field = typeof(generators[config.type()]) === "function" ? generators[config.type()](config) : generators['default'](config);

		var html = config.wrap() ? m('p', [label, field]) : [label, field];

		// render HTML
		var rawHTML = render(html);
		rawHTML = html_beautify(rawHTML) + "\n\n";
		return rawHTML;
	}

	return generate;
};

module.exports = g;
},{"../../third-party/beautify-html.js":16,"../../third-party/render.js":19}],5:[function(require,module,exports){
var FieldHelper = function(m,tabs, editor, fields) {
	'use strict';

	var generate = require('./field-generator.js')(m);
	var overlay = require('./overlay.js')(m);
	var forms = require('./field-forms.js')(m);
	var fieldConfig;

	/**
	 * Choose a field to open the helper form for
	 *
	 * @param index
	 * @returns {*}
	 */
	function setActiveField(index) {
		fieldConfig = fields.get(index);
		m.redraw();
	}


	/**
	 * Controller
	 */
	function controller() {

	}

	/**
	 * Create HTML based on current config object
	 */
	function createFieldHTMLAndAddToForm() {

		// generate html
		var html = generate(fieldConfig);

		// add to editor
		editor.insert( html );

		// reset field form
		setActiveField('');
	}

	/**
	 * View
	 * @returns {*}
	 */
	function view() {

		// build DOM for fields choice
		var fieldsChoice = m( "div.available-fields.small-margin", [
			m("strong", "Choose a MailChimp field to add to the form"),

			(fields.getAll().length) ?

				// render fields
				fields.getAll().map(function(field, index) {
					return [
						m("button", {
							"class": "button",
							type   : 'button',
							onclick: m.withAttr("value", setActiveField),
							value  : index
						}, field.title())
					];
				})

				:

				// no fields
				m( "p", [
					"No fields, did you ",
					m("a", {
						onclick: function() { tabs.open('settings'); }
					}, "select a MailChimp list in the form settings?")
				])
		]);

		// build DOM for overlay
		var form = null;
		if( fieldConfig ) {
			form = overlay(
				// field wizard
				m("div.field-wizard", [

					//heading
					m("h3", [
						fieldConfig.title(),
						(fieldConfig.name().length) ? m("code", fieldConfig.name()) : ''
					]),

					// help text
					( fieldConfig.help().length ) ? m('p', m.trust( fieldConfig.help() ) ) : '',

					// actual form
					forms.render(fieldConfig),

					// add to form button
					m("p", [
						m("button", {
							"class": "button-primary",
							type: "button",
							onclick: createFieldHTMLAndAddToForm
						}, "Add to form" )
					])
				]), setActiveField);
		}

		return [
			fieldsChoice,
			form
		];
	}

	// expose some variables
	return {
		view: view,
		controller: controller
	}
};

module.exports = FieldHelper;
},{"./field-forms.js":3,"./field-generator.js":4,"./overlay.js":12}],6:[function(require,module,exports){
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
},{}],7:[function(require,module,exports){
module.exports = function(m) {
	'use strict';

	/**
	 * @internal
	 *
	 * @param data
	 * @constructor
	 */
	var Field = function (data) {
		this.name = m.prop(data.name);
		this.title = m.prop(data.title || data.name);

		this.type = m.prop(data.type);
		this.label = m.prop(data.title || '');
		this.value = m.prop(data.value || '');
		this.placeholder = m.prop(data.placeholder || true);
		this.required = m.prop(data.required || false);
		this.wrap = m.prop(data.wrap || true);
		this.min = m.prop(data.min || null);
		this.max = m.prop(data.max || null);
		this.help = m.prop(data.help || '');
		this.choices = m.prop(data.choices || []);

		this.selectChoice = function(value) {
			var field = this;

			this.choices(this.choices().map(function(choice) {

				if( choice.value() === value ) {
					choice.selected(true);
				} else {
					// only checkboxes allow for multiple selections
					if(field.type() !== 'checkbox' ) {
						choice.selected(false);
					}
				}

				return choice;

			}) );
		}
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
	 * @api
	 *
	 * @returns {{fields: {}, get: get, getAll: getAll, deregister: deregister, register: register}}
	 * @constructor
	 */
	var fields = [];

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
	function register(data) {

		// bail if a field with this name has been registered already
		if (getAllWhere('name', data.name).length > 0) {
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
		return fields.filter(function (field) {
			return field[searchKey]() === searchValue;
		});
	}


	/**
	 * Exposed methods
	 */
	return {
		'fields'     : fields,
		'get'        : get,
		'getAll'     : getAll,
		'deregister' : deregister,
		'register'   : register,
		'getAllWhere': getAllWhere
	};
};
},{}],8:[function(require,module,exports){
/* Editor */
/* todo allow for CodeMirror failures */
var FormEditor = function(element) {

	var r = {};
	var editor;

	r.editor = editor = CodeMirror.fromTextArea(element, {
		selectionPointer: true,
		matchTags: { bothTags: true },
		mode: "text/html",
		htmlMode: true,
		autoCloseTags: true,
		autoRefresh: true
	});

	r.getValue = function() {
		return editor.getValue();
	};

	r.insert = function( html ) {
		editor.replaceSelection( html );
	};

	r.on = function() {
		return editor.on.apply(editor,arguments);
	};

	r.refresh = function() {
		editor.refresh();
	};

	return r;
};

module.exports = FormEditor;
},{}],9:[function(require,module,exports){
var FormWatcher = function(editor, settings, fields) {
	'use strict';

	var missingFieldsNotice = document.getElementById('missing-fields-notice');
	var missingFieldsNoticeList = missingFieldsNotice.querySelector('ul');
	var requiredFieldsInput = document.getElementById('required-fields');

	// functions
	function checkPresenceOfRequiredFields() {

		var formContent = editor.getValue();
		var requiredFields = fields.getAllWhere('required', true);

		// let's go
		formContent = formContent.toLowerCase();

		// check presence for each required field
		var missingFields = [];
		requiredFields.forEach(function(field) {
			var fieldSearch = 'name="' + field.name().toLowerCase();
			if( formContent.indexOf( fieldSearch ) == -1 ) {
				missingFields.push(field);
			}
		});

		// do nothing if no fields are missing
		if( missingFields.length === 0 ) {
			missingFieldsNotice.style.display = 'none';
			return;
		}

		// show notice
		var listItems = '';
		missingFields.forEach(function( field ) {
			listItems += "<li>" + field.label() + " (<code>" + field.name() + "</code>)</li>";
		});

		missingFieldsNoticeList.innerHTML = listItems;
		missingFieldsNotice.style.display = 'block';
	}

	function findRequiredFields() {

		// load content in memory
		var form = document.createElement('div');
		form.innerHTML = editor.getValue();

		// query fields required by MailChimp
		var requiredFields = fields.getAllWhere('required', true).map(function(f) {
			return f.name().toUpperCase();
		});

		// query fields with [required] attribute
		var requiredFieldElements = form.querySelectorAll('[required]');
		Array.prototype.forEach.call(requiredFieldElements, function(el) {
			var name = el.name.toUpperCase();

			// only add field if it's not already in it
			if( requiredFields.indexOf(name) === -1 ) {
				requiredFields.push(name);
			}
		});

		// update meta
		requiredFieldsInput.value = requiredFields.join(',');
	}

	// events
	editor.on('changes', checkPresenceOfRequiredFields );
	editor.on('blur', findRequiredFields );

	settings.events.on('fields.change', checkPresenceOfRequiredFields);

};

module.exports = FormWatcher;
},{}],10:[function(require,module,exports){
'use strict';

var helpers = {};

helpers.toggleElement = function(selector) {
	var elements = document.querySelectorAll(selector);
	for( var i=0; i<elements.length;i++){
		var show = elements[i].clientHeight <= 0;
		elements[i].style.display = show ? '' : 'none';
	}
};

helpers.bindEventToElement = function(element,event,handler) {
	if ( element.addEventListener) {
		element.addEventListener(event, handler);
	} else if (element.attachEvent)  {
		element.attachEvent('on' + event, handler);
	}
};

helpers.bindEventToElements = function( elements, event, handler ) {
	Array.prototype.forEach.call( elements, function(element) {
		helpers.bindEventToElement(element,event,handler);
	});
};

/**
 * Showif.js
 */
(function() {
	var showIfElements = document.querySelectorAll('[data-showif]');

	// dependent elements
	Array.prototype.forEach.call( showIfElements, function(element) {
		var config = JSON.parse( element.getAttribute('data-showif') );
		var parentElements = document.querySelectorAll('[name="'+ config.element +'"]');
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
		helpers.bindEventToElements(parentElements, 'change', toggleElement);
	});
})();

module.exports = helpers;
},{}],11:[function(require,module,exports){
'use strict';

var lucy = function( site_url, algolia_app_id, algolia_api_key, algolia_index_name, defaultLinks, contactLink ) {

	var m = require('../../third-party/mithril.js');
	var algoliasearch = require( '../../third-party/algoliasearch.js');
	var client = algoliasearch( algolia_app_id, algolia_api_key );
	var index = client.initIndex( algolia_index_name );

	var isOpen = false;
	var loader, loadingInterval = 0;
	var searchResults = m.prop([]);
	var searchQuery = m.prop('');

	var element = document.createElement('div');
	element.setAttribute('class','lucy');
	document.body.appendChild(element);

	function maybeClose(e) {

		// close when pressing ESCAPE
		if(e.type === 'keyup' && e.keyCode == 27 ) {
			close();
			return;
		}

		// close when clicking ANY element outside of Lucy
		var clickedElement = event.target || event.srcElement;
		if(e.type === 'click' && ! element.contains(clickedElement) )  {
			close();
		}

	}

	function open() {
		isOpen = true;
		m.redraw();

		document.addEventListener('keyup', maybeClose);
		document.addEventListener('click', maybeClose);
	}

	function close() {
		isOpen = false;
		reset();

		document.removeEventListener('keyup', maybeClose);
		document.removeEventListener('click', maybeClose);
	}

	function reset() {
		searchQuery('');
		searchResults([]);
		m.redraw();
	}

	var module = {};
	module.view = function() {
		var content;

		element.setAttribute('class', 'lucy ' + ( isOpen ? 'open' : 'closed' ) );

		if( searchResults().length > 1 ) {
			content = m('div.search-results', [
				(searchResults().length) ? searchResults().map(function(l) {
					return m('a', { href: l.href }, m.trust(l.text) );
				}) : m("em.search-pending","Hit [ENTER] to search for \""+ searchQuery() +"\"..")
			]);
		} else {
			content = m("div.links", defaultLinks.map(function(l) {
				return m('a', { href: l.href }, m.trust(l.text) );
			}));
		}

		return [
			m('div.lucy--content', { style: { display: isOpen ? 'block' : 'none' } }, [
				m('span.close-icon', { onclick: close }, ""),
				m('div.header', [
					m('h4', 'Looking for help?'),
					m('form', {
						onsubmit: search
					}, [
						m('input', {
							type: 'text',
							value: searchQuery(),
							oninput: function() {
								if( this.value === '' && searchQuery() !== '' ) {
									reset();
								}

								searchQuery(this.value);
							},
							config: function(el) { isOpen && el.focus(); },
							placeholder: 'What are you looking for?'
						}),
						m('span', {
							"class": 'loader',
							config: function(el) {
								loader = el;
							}
						}),
						m('input', { type: 'submit' })
					])
				]),
				m('div.list', content),
				m('div.footer', [
					m("span", "Can't find the answer you're looking for?"),
					m("a", { "class": 'button button-primary', href: contactLink }, "Contact Support")
				])
			]),
			m('span.lucy-button', { onclick: open, style: { display: isOpen ? 'none' : 'block' } }, [
				m('span.lucy-button-text',  "Need help?")
			])
		];
	};


	// create element and float it in bottom right corner
	function search(e) {
		e.preventDefault();
		e.returnValue = false;

		loader.innerText = '.';
		loadingInterval = window.setInterval(function() {
			loader.innerText += '.';

			if( loader.innerText.length > 3 ) {
				loader.innerText = '.';
			}
		}, 333 );

		index.search( searchQuery(), { hitsPerPage: 5 }, function( error, result ) {

			searchResults(result.hits.map(function(r) {
				return { href: r.path, text: r._highlightResult.title.value};
			}));

			m.redraw();

			// clear loader
			loader.innerText = '';
			window.clearInterval(loadingInterval);
		} );

		return false;
	}

	m.mount(element,module);
};

module.exports = lucy;
},{"../../third-party/algoliasearch.js":15,"../../third-party/mithril.js":18}],12:[function(require,module,exports){
var overlay = function( m ) {
	'use strict';

	var _onCloseCallback;

	function onKeyDown(e) {
		if (e.keyCode == 27 && _onCloseCallback ) {
			_onCloseCallback();
		}
	}

	if (window.addEventListener) {
		window.addEventListener('keydown', onKeyDown);
	} else if (el.attachEvent) {
		window.attachEvent('keydown', onKeyDown);
	}

	return function (content, onCloseCallback) {
		_onCloseCallback = onCloseCallback;

		return [
			m("div.overlay", [
				m("div.overlay-content", [

					// close icon
					m('span.close.dashicons.dashicons-no', {
						title  : "Click to close the overlay.",
						onclick: onCloseCallback
					}),

					content
				])
			]),

			// overlay background
			m("div.overlay-background", {
				title  : "Click to close the overlay.",
				onclick: onCloseCallback
			})
		];
	};
};

module.exports = overlay;
},{}],13:[function(require,module,exports){
var Settings = function(context, helpers) {
	'use strict';

	var EventEmitter = require('../../third-party/event-emitter.js');

	// vars
	var events = new EventEmitter();
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

	events.on('selectedLists.change', toggleVisibleLists);
	helpers.bindEventToElements(listInputs,'change',updateSelectedLists);
	updateSelectedLists();

	return {
		getSelectedLists: getSelectedLists,
		events: events
	}

};

module.exports = Settings;
},{"../../third-party/event-emitter.js":17}],14:[function(require,module,exports){
// Tabs
var Tabs = function(context) {

	// @todo last piece of jQuery... can we get rid of it?
	var $ = window.jQuery;

	var $context = $(context);
	var $tabs = $context.find('.tab');
	var $tabNavs = $context.find('.nav-tab');
	var refererField = context.querySelector('input[name="_wp_http_referer"]');

	var URL = {
		parse: function(url) {
			var query = {};
			var a = url.split('&');
			for (var i in a) {
				var b = a[i].split('=');
				query[decodeURIComponent(b[0])] = decodeURIComponent(b[1]);
			}

			return query;
		},
		build: function(data) {
			var ret = [];
			for (var d in data)
				ret.push(d + "=" + encodeURIComponent(data[d]));
			return ret.join("&");
		},
		setParameter: function( url, key, value ) {
			var data = URL.parse( url );
			data[ key ] = value;
			return URL.build( data );
		}
	};

	function open( tab ) {

		// hide all tabs & remove active class
		$tabs.css('display', 'none');
		$tabNavs.removeClass('nav-tab-active');

		// add `nav-tab-active` to this tab
		$tabNavs.filter('#nav-tab-'+tab).addClass('nav-tab-active').blur();

		// show target tab
		var targetId = "tab-" + tab;
		var targetTab = document.getElementById(targetId);

		if( ! targetTab ) {
			return false;
		}

		targetTab.style.display = 'block';

		// create new URL
		var url = URL.setParameter(window.location.href, "tab", tab );

		// update hash
		if( history.pushState ) {
			history.pushState( '', '', url );
		}

		// update referer field
		refererField.value = url;

		// if thickbox is open, close it.
		if( typeof(tb_remove) === "function" ) {
			tb_remove();
		}

		return true;
	}


	function switchTab(e) {

		var tab = this.getAttribute('data-tab');
		if( ! tab ) {
			var urlParams = URL.parse( this.href );
			if( typeof(urlParams.tab) === "undefined" ) {
				return;
			}

			tab = urlParams.tab;
		}

		var opened = open( tab );

		if( opened ) {
			e.preventDefault();
			e.returnValue = false;
			return false;
		}

		return true;
	}

	$tabNavs.click(switchTab);
	$context.on('click', '.tab-link', switchTab);

	return {
		open: open
	}

};

module.exports = Tabs;
},{}],15:[function(require,module,exports){
(function (global){

/*! algoliasearch 3.9.2 | © 2014, 2015 Algolia SAS | github.com/algolia/algoliasearch-client-js */
!function(e){var t;"undefined"!=typeof window?t=window:"undefined"!=typeof self&&(t=self),t.ALGOLIA_MIGRATION_LAYER=e()}(function(){return function e(t,r,n){function o(s,a){if(!r[s]){if(!t[s]){var c="function"==typeof require&&require;if(!a&&c)return c(s,!0);if(i)return i(s,!0);var u=new Error("Cannot find module '"+s+"'");throw u.code="MODULE_NOT_FOUND",u}var l=r[s]={exports:{}};t[s][0].call(l.exports,function(e){var r=t[s][1][e];return o(r?r:e)},l,l.exports,e,t,r,n)}return r[s].exports}for(var i="function"==typeof require&&require,s=0;s<n.length;s++)o(n[s]);return o}({1:[function(e,t,r){function n(e,t){for(var r in t)e.setAttribute(r,t[r])}function o(e,t){e.onload=function(){this.onerror=this.onload=null,t(null,e)},e.onerror=function(){this.onerror=this.onload=null,t(new Error("Failed to load "+this.src),e)}}function i(e,t){e.onreadystatechange=function(){("complete"==this.readyState||"loaded"==this.readyState)&&(this.onreadystatechange=null,t(null,e))}}t.exports=function(e,t,r){var s=document.head||document.getElementsByTagName("head")[0],a=document.createElement("script");"function"==typeof t&&(r=t,t={}),t=t||{},r=r||function(){},a.type=t.type||"text/javascript",a.charset=t.charset||"utf8",a.async="async"in t?!!t.async:!0,a.src=e,t.attrs&&n(a,t.attrs),t.text&&(a.text=""+t.text);var c="onload"in a?o:i;c(a,r),a.onload||o(a,r),s.appendChild(a)}},{}],2:[function(e,t,r){"use strict";function n(e){for(var t=new RegExp("cdn\\.jsdelivr\\.net/algoliasearch/latest/"+e.replace(".","\\.")+"(?:\\.min)?\\.js$"),r=document.getElementsByTagName("script"),n=!1,o=0,i=r.length;i>o;o++)if(r[o].src&&t.test(r[o].src)){n=!0;break}return n}t.exports=n},{}],3:[function(e,t,r){"use strict";function n(t){var r=e(1),n="//cdn.jsdelivr.net/algoliasearch/2/"+t+".min.js",i="-- AlgoliaSearch `latest` warning --\nWarning, you are using the `latest` version string from jsDelivr to load the AlgoliaSearch library.\nUsing `latest` is no more recommended, you should load //cdn.jsdelivr.net/algoliasearch/2/algoliasearch.min.js\n\nAlso, we updated the AlgoliaSearch JavaScript client to V3. If you want to upgrade,\nplease read our migration guide at https://github.com/algolia/algoliasearch-client-js/wiki/Migration-guide-from-2.x.x-to-3.x.x\n-- /AlgoliaSearch  `latest` warning --";window.console&&(window.console.warn?window.console.warn(i):window.console.log&&window.console.log(i));try{document.write("<script>window.ALGOLIA_SUPPORTS_DOCWRITE = true</script>"),window.ALGOLIA_SUPPORTS_DOCWRITE===!0?(document.write('<script src="'+n+'"></script>'),o("document.write")()):r(n,o("DOMElement"))}catch(s){r(n,o("DOMElement"))}}function o(e){return function(){var t="AlgoliaSearch: loaded V2 script using "+e;window.console&&window.console.log&&window.console.log(t)}}t.exports=n},{1:1}],4:[function(e,t,r){"use strict";function n(){var e="-- AlgoliaSearch V2 => V3 error --\nYou are trying to use a new version of the AlgoliaSearch JavaScript client with an old notation.\nPlease read our migration guide at https://github.com/algolia/algoliasearch-client-js/wiki/Migration-guide-from-2.x.x-to-3.x.x\n-- /AlgoliaSearch V2 => V3 error --";window.AlgoliaSearch=function(){throw new Error(e)},window.AlgoliaSearchHelper=function(){throw new Error(e)},window.AlgoliaExplainResults=function(){throw new Error(e)}}t.exports=n},{}],5:[function(e,t,r){"use strict";function n(t){var r=e(2),n=e(3),o=e(4);r(t)?n(t):o()}n("algoliasearch")},{2:2,3:3,4:4}]},{},[5])(5)}),function(e){if("object"==typeof exports&&"undefined"!=typeof module)module.exports=e();else if("function"==typeof define&&define.amd)define([],e);else{var t;t="undefined"!=typeof window?window:"undefined"!=typeof global?global:"undefined"!=typeof self?self:this,t.algoliasearch=e()}}(function(){var e;return function t(e,r,n){function o(s,a){if(!r[s]){if(!e[s]){var c="function"==typeof require&&require;if(!a&&c)return c(s,!0);if(i)return i(s,!0);var u=new Error("Cannot find module '"+s+"'");throw u.code="MODULE_NOT_FOUND",u}var l=r[s]={exports:{}};e[s][0].call(l.exports,function(t){var r=e[s][1][t];return o(r?r:t)},l,l.exports,t,e,r,n)}return r[s].exports}for(var i="function"==typeof require&&require,s=0;s<n.length;s++)o(n[s]);return o}({1:[function(e,t,r){function n(){this._events=this._events||{},this._maxListeners=this._maxListeners||void 0}function o(e){return"function"==typeof e}function i(e){return"number"==typeof e}function s(e){return"object"==typeof e&&null!==e}function a(e){return void 0===e}t.exports=n,n.EventEmitter=n,n.prototype._events=void 0,n.prototype._maxListeners=void 0,n.defaultMaxListeners=10,n.prototype.setMaxListeners=function(e){if(!i(e)||0>e||isNaN(e))throw TypeError("n must be a positive number");return this._maxListeners=e,this},n.prototype.emit=function(e){var t,r,n,i,c,u;if(this._events||(this._events={}),"error"===e&&(!this._events.error||s(this._events.error)&&!this._events.error.length)){if(t=arguments[1],t instanceof Error)throw t;throw TypeError('Uncaught, unspecified "error" event.')}if(r=this._events[e],a(r))return!1;if(o(r))switch(arguments.length){case 1:r.call(this);break;case 2:r.call(this,arguments[1]);break;case 3:r.call(this,arguments[1],arguments[2]);break;default:for(n=arguments.length,i=new Array(n-1),c=1;n>c;c++)i[c-1]=arguments[c];r.apply(this,i)}else if(s(r)){for(n=arguments.length,i=new Array(n-1),c=1;n>c;c++)i[c-1]=arguments[c];for(u=r.slice(),n=u.length,c=0;n>c;c++)u[c].apply(this,i)}return!0},n.prototype.addListener=function(e,t){var r;if(!o(t))throw TypeError("listener must be a function");if(this._events||(this._events={}),this._events.newListener&&this.emit("newListener",e,o(t.listener)?t.listener:t),this._events[e]?s(this._events[e])?this._events[e].push(t):this._events[e]=[this._events[e],t]:this._events[e]=t,s(this._events[e])&&!this._events[e].warned){var r;r=a(this._maxListeners)?n.defaultMaxListeners:this._maxListeners,r&&r>0&&this._events[e].length>r&&(this._events[e].warned=!0,console.error("(node) warning: possible EventEmitter memory leak detected. %d listeners added. Use emitter.setMaxListeners() to increase limit.",this._events[e].length),"function"==typeof console.trace&&console.trace())}return this},n.prototype.on=n.prototype.addListener,n.prototype.once=function(e,t){function r(){this.removeListener(e,r),n||(n=!0,t.apply(this,arguments))}if(!o(t))throw TypeError("listener must be a function");var n=!1;return r.listener=t,this.on(e,r),this},n.prototype.removeListener=function(e,t){var r,n,i,a;if(!o(t))throw TypeError("listener must be a function");if(!this._events||!this._events[e])return this;if(r=this._events[e],i=r.length,n=-1,r===t||o(r.listener)&&r.listener===t)delete this._events[e],this._events.removeListener&&this.emit("removeListener",e,t);else if(s(r)){for(a=i;a-->0;)if(r[a]===t||r[a].listener&&r[a].listener===t){n=a;break}if(0>n)return this;1===r.length?(r.length=0,delete this._events[e]):r.splice(n,1),this._events.removeListener&&this.emit("removeListener",e,t)}return this},n.prototype.removeAllListeners=function(e){var t,r;if(!this._events)return this;if(!this._events.removeListener)return 0===arguments.length?this._events={}:this._events[e]&&delete this._events[e],this;if(0===arguments.length){for(t in this._events)"removeListener"!==t&&this.removeAllListeners(t);return this.removeAllListeners("removeListener"),this._events={},this}if(r=this._events[e],o(r))this.removeListener(e,r);else for(;r.length;)this.removeListener(e,r[r.length-1]);return delete this._events[e],this},n.prototype.listeners=function(e){var t;return t=this._events&&this._events[e]?o(this._events[e])?[this._events[e]]:this._events[e].slice():[]},n.listenerCount=function(e,t){var r;return r=e._events&&e._events[t]?o(e._events[t])?1:e._events[t].length:0}},{}],2:[function(e,t,r){function n(){l=!1,a.length?u=a.concat(u):f=-1,u.length&&o()}function o(){if(!l){var e=setTimeout(n);l=!0;for(var t=u.length;t;){for(a=u,u=[];++f<t;)a&&a[f].run();f=-1,t=u.length}a=null,l=!1,clearTimeout(e)}}function i(e,t){this.fun=e,this.array=t}function s(){}var a,c=t.exports={},u=[],l=!1,f=-1;c.nextTick=function(e){var t=new Array(arguments.length-1);if(arguments.length>1)for(var r=1;r<arguments.length;r++)t[r-1]=arguments[r];u.push(new i(e,t)),1!==u.length||l||setTimeout(o,0)},i.prototype.run=function(){this.fun.apply(null,this.array)},c.title="browser",c.browser=!0,c.env={},c.argv=[],c.version="",c.versions={},c.on=s,c.addListener=s,c.once=s,c.off=s,c.removeListener=s,c.removeAllListeners=s,c.emit=s,c.binding=function(e){throw new Error("process.binding is not supported")},c.cwd=function(){return"/"},c.chdir=function(e){throw new Error("process.chdir is not supported")},c.umask=function(){return 0}},{}],3:[function(e,t,r){"use strict";function n(e,t){return Object.prototype.hasOwnProperty.call(e,t)}t.exports=function(e,t,r,i){t=t||"&",r=r||"=";var s={};if("string"!=typeof e||0===e.length)return s;var a=/\+/g;e=e.split(t);var c=1e3;i&&"number"==typeof i.maxKeys&&(c=i.maxKeys);var u=e.length;c>0&&u>c&&(u=c);for(var l=0;u>l;++l){var f,d,h,p,y=e[l].replace(a,"%20"),m=y.indexOf(r);m>=0?(f=y.substr(0,m),d=y.substr(m+1)):(f=y,d=""),h=decodeURIComponent(f),p=decodeURIComponent(d),n(s,h)?o(s[h])?s[h].push(p):s[h]=[s[h],p]:s[h]=p}return s};var o=Array.isArray||function(e){return"[object Array]"===Object.prototype.toString.call(e)}},{}],4:[function(e,t,r){"use strict";function n(e,t){if(e.map)return e.map(t);for(var r=[],n=0;n<e.length;n++)r.push(t(e[n],n));return r}var o=function(e){switch(typeof e){case"string":return e;case"boolean":return e?"true":"false";case"number":return isFinite(e)?e:"";default:return""}};t.exports=function(e,t,r,a){return t=t||"&",r=r||"=",null===e&&(e=void 0),"object"==typeof e?n(s(e),function(s){var a=encodeURIComponent(o(s))+r;return i(e[s])?n(e[s],function(e){return a+encodeURIComponent(o(e))}).join(t):a+encodeURIComponent(o(e[s]))}).join(t):a?encodeURIComponent(o(a))+r+encodeURIComponent(o(e)):""};var i=Array.isArray||function(e){return"[object Array]"===Object.prototype.toString.call(e)},s=Object.keys||function(e){var t=[];for(var r in e)Object.prototype.hasOwnProperty.call(e,r)&&t.push(r);return t}},{}],5:[function(e,t,r){"use strict";r.decode=r.parse=e(3),r.encode=r.stringify=e(4)},{3:3,4:4}],6:[function(e,t,r){function n(){return"WebkitAppearance"in document.documentElement.style||window.console&&(console.firebug||console.exception&&console.table)||navigator.userAgent.toLowerCase().match(/firefox\/(\d+)/)&&parseInt(RegExp.$1,10)>=31}function o(){var e=arguments,t=this.useColors;if(e[0]=(t?"%c":"")+this.namespace+(t?" %c":" ")+e[0]+(t?"%c ":" ")+"+"+r.humanize(this.diff),!t)return e;var n="color: "+this.color;e=[e[0],n,"color: inherit"].concat(Array.prototype.slice.call(e,1));var o=0,i=0;return e[0].replace(/%[a-z%]/g,function(e){"%%"!==e&&(o++,"%c"===e&&(i=o))}),e.splice(i,0,n),e}function i(){return"object"==typeof console&&console.log&&Function.prototype.apply.call(console.log,console,arguments)}function s(e){try{null==e?r.storage.removeItem("debug"):r.storage.debug=e}catch(t){}}function a(){var e;try{e=r.storage.debug}catch(t){}return e}function c(){try{return window.localStorage}catch(e){}}r=t.exports=e(7),r.log=i,r.formatArgs=o,r.save=s,r.load=a,r.useColors=n,r.storage="undefined"!=typeof chrome&&"undefined"!=typeof chrome.storage?chrome.storage.local:c(),r.colors=["lightseagreen","forestgreen","goldenrod","dodgerblue","darkorchid","crimson"],r.formatters.j=function(e){return JSON.stringify(e)},r.enable(a())},{7:7}],7:[function(e,t,r){function n(){return r.colors[l++%r.colors.length]}function o(e){function t(){}function o(){var e=o,t=+new Date,i=t-(u||t);e.diff=i,e.prev=u,e.curr=t,u=t,null==e.useColors&&(e.useColors=r.useColors()),null==e.color&&e.useColors&&(e.color=n());var s=Array.prototype.slice.call(arguments);s[0]=r.coerce(s[0]),"string"!=typeof s[0]&&(s=["%o"].concat(s));var a=0;s[0]=s[0].replace(/%([a-z%])/g,function(t,n){if("%%"===t)return t;a++;var o=r.formatters[n];if("function"==typeof o){var i=s[a];t=o.call(e,i),s.splice(a,1),a--}return t}),"function"==typeof r.formatArgs&&(s=r.formatArgs.apply(e,s));var c=o.log||r.log||console.log.bind(console);c.apply(e,s)}t.enabled=!1,o.enabled=!0;var i=r.enabled(e)?o:t;return i.namespace=e,i}function i(e){r.save(e);for(var t=(e||"").split(/[\s,]+/),n=t.length,o=0;n>o;o++)t[o]&&(e=t[o].replace(/\*/g,".*?"),"-"===e[0]?r.skips.push(new RegExp("^"+e.substr(1)+"$")):r.names.push(new RegExp("^"+e+"$")))}function s(){r.enable("")}function a(e){var t,n;for(t=0,n=r.skips.length;n>t;t++)if(r.skips[t].test(e))return!1;for(t=0,n=r.names.length;n>t;t++)if(r.names[t].test(e))return!0;return!1}function c(e){return e instanceof Error?e.stack||e.message:e}r=t.exports=o,r.coerce=c,r.disable=s,r.enable=i,r.enabled=a,r.humanize=e(8),r.names=[],r.skips=[],r.formatters={};var u,l=0},{8:8}],8:[function(e,t,r){function n(e){if(e=""+e,!(e.length>1e4)){var t=/^((?:\d+)?\.?\d+) *(milliseconds?|msecs?|ms|seconds?|secs?|s|minutes?|mins?|m|hours?|hrs?|h|days?|d|years?|yrs?|y)?$/i.exec(e);if(t){var r=parseFloat(t[1]),n=(t[2]||"ms").toLowerCase();switch(n){case"years":case"year":case"yrs":case"yr":case"y":return r*f;case"days":case"day":case"d":return r*l;case"hours":case"hour":case"hrs":case"hr":case"h":return r*u;case"minutes":case"minute":case"mins":case"min":case"m":return r*c;case"seconds":case"second":case"secs":case"sec":case"s":return r*a;case"milliseconds":case"millisecond":case"msecs":case"msec":case"ms":return r}}}}function o(e){return e>=l?Math.round(e/l)+"d":e>=u?Math.round(e/u)+"h":e>=c?Math.round(e/c)+"m":e>=a?Math.round(e/a)+"s":e+"ms"}function i(e){return s(e,l,"day")||s(e,u,"hour")||s(e,c,"minute")||s(e,a,"second")||e+" ms"}function s(e,t,r){return t>e?void 0:1.5*t>e?Math.floor(e/t)+" "+r:Math.ceil(e/t)+" "+r+"s"}var a=1e3,c=60*a,u=60*c,l=24*u,f=365.25*l;t.exports=function(e,t){return t=t||{},"string"==typeof e?n(e):t["long"]?i(e):o(e)}},{}],9:[function(t,r,n){(function(n,o){(function(){"use strict";function i(e){return"function"==typeof e||"object"==typeof e&&null!==e}function s(e){return"function"==typeof e}function a(e){return"object"==typeof e&&null!==e}function c(e){G=e}function u(e){X=e}function l(){return function(){n.nextTick(y)}}function f(){return function(){Q(y)}}function d(){var e=0,t=new Z(y),r=document.createTextNode("");return t.observe(r,{characterData:!0}),function(){r.data=e=++e%2}}function h(){var e=new MessageChannel;return e.port1.onmessage=y,function(){e.port2.postMessage(0)}}function p(){return function(){setTimeout(y,1)}}function y(){for(var e=0;W>e;e+=2){var t=re[e],r=re[e+1];t(r),re[e]=void 0,re[e+1]=void 0}W=0}function m(){try{var e=t,r=e("vertx");return Q=r.runOnLoop||r.runOnContext,f()}catch(n){return p()}}function v(){}function b(){return new TypeError("You cannot resolve a promise with itself")}function g(){return new TypeError("A promises callback cannot return that same promise.")}function w(e){try{return e.then}catch(t){return se.error=t,se}}function x(e,t,r,n){try{e.call(t,r,n)}catch(o){return o}}function _(e,t,r){X(function(e){var n=!1,o=x(r,t,function(r){n||(n=!0,t!==r?A(e,r):k(e,r))},function(t){n||(n=!0,S(e,t))},"Settle: "+(e._label||" unknown promise"));!n&&o&&(n=!0,S(e,o))},e)}function j(e,t){t._state===oe?k(e,t._result):t._state===ie?S(e,t._result):U(t,void 0,function(t){A(e,t)},function(t){S(e,t)})}function T(e,t){if(t.constructor===e.constructor)j(e,t);else{var r=w(t);r===se?S(e,se.error):void 0===r?k(e,t):s(r)?_(e,t,r):k(e,t)}}function A(e,t){e===t?S(e,b()):i(t)?T(e,t):k(e,t)}function O(e){e._onerror&&e._onerror(e._result),I(e)}function k(e,t){e._state===ne&&(e._result=t,e._state=oe,0!==e._subscribers.length&&X(I,e))}function S(e,t){e._state===ne&&(e._state=ie,e._result=t,X(O,e))}function U(e,t,r,n){var o=e._subscribers,i=o.length;e._onerror=null,o[i]=t,o[i+oe]=r,o[i+ie]=n,0===i&&e._state&&X(I,e)}function I(e){var t=e._subscribers,r=e._state;if(0!==t.length){for(var n,o,i=e._result,s=0;s<t.length;s+=3)n=t[s],o=t[s+r],n?E(r,n,o,i):o(i);e._subscribers.length=0}}function R(){this.error=null}function P(e,t){try{return e(t)}catch(r){return ae.error=r,ae}}function E(e,t,r,n){var o,i,a,c,u=s(r);if(u){if(o=P(r,n),o===ae?(c=!0,i=o.error,o=null):a=!0,t===o)return void S(t,g())}else o=n,a=!0;t._state!==ne||(u&&a?A(t,o):c?S(t,i):e===oe?k(t,o):e===ie&&S(t,o))}function q(e,t){try{t(function(t){A(e,t)},function(t){S(e,t)})}catch(r){S(e,r)}}function C(e,t){var r=this;r._instanceConstructor=e,r.promise=new e(v),r._validateInput(t)?(r._input=t,r.length=t.length,r._remaining=t.length,r._init(),0===r.length?k(r.promise,r._result):(r.length=r.length||0,r._enumerate(),0===r._remaining&&k(r.promise,r._result))):S(r.promise,r._validationError())}function N(e){return new ce(this,e).promise}function L(e){function t(e){A(o,e)}function r(e){S(o,e)}var n=this,o=new n(v);if(!V(e))return S(o,new TypeError("You must pass an array to race.")),o;for(var i=e.length,s=0;o._state===ne&&i>s;s++)U(n.resolve(e[s]),void 0,t,r);return o}function D(e){var t=this;if(e&&"object"==typeof e&&e.constructor===t)return e;var r=new t(v);return A(r,e),r}function M(e){var t=this,r=new t(v);return S(r,e),r}function H(){throw new TypeError("You must pass a resolver function as the first argument to the promise constructor")}function J(){throw new TypeError("Failed to construct 'Promise': Please use the 'new' operator, this object constructor cannot be called as a function.")}function K(e){this._id=he++,this._state=void 0,this._result=void 0,this._subscribers=[],v!==e&&(s(e)||H(),this instanceof K||J(),q(this,e))}function B(){var e;if("undefined"!=typeof o)e=o;else if("undefined"!=typeof self)e=self;else try{e=Function("return this")()}catch(t){throw new Error("polyfill failed because global object is unavailable in this environment")}var r=e.Promise;(!r||"[object Promise]"!==Object.prototype.toString.call(r.resolve())||r.cast)&&(e.Promise=pe)}var F;F=Array.isArray?Array.isArray:function(e){return"[object Array]"===Object.prototype.toString.call(e)};var Q,G,$,V=F,W=0,X=({}.toString,function(e,t){re[W]=e,re[W+1]=t,W+=2,2===W&&(G?G(y):$())}),Y="undefined"!=typeof window?window:void 0,z=Y||{},Z=z.MutationObserver||z.WebKitMutationObserver,ee="undefined"!=typeof n&&"[object process]"==={}.toString.call(n),te="undefined"!=typeof Uint8ClampedArray&&"undefined"!=typeof importScripts&&"undefined"!=typeof MessageChannel,re=new Array(1e3);$=ee?l():Z?d():te?h():void 0===Y&&"function"==typeof t?m():p();var ne=void 0,oe=1,ie=2,se=new R,ae=new R;C.prototype._validateInput=function(e){return V(e)},C.prototype._validationError=function(){return new Error("Array Methods must be provided an Array")},C.prototype._init=function(){this._result=new Array(this.length)};var ce=C;C.prototype._enumerate=function(){for(var e=this,t=e.length,r=e.promise,n=e._input,o=0;r._state===ne&&t>o;o++)e._eachEntry(n[o],o)},C.prototype._eachEntry=function(e,t){var r=this,n=r._instanceConstructor;a(e)?e.constructor===n&&e._state!==ne?(e._onerror=null,r._settledAt(e._state,t,e._result)):r._willSettleAt(n.resolve(e),t):(r._remaining--,r._result[t]=e)},C.prototype._settledAt=function(e,t,r){var n=this,o=n.promise;o._state===ne&&(n._remaining--,e===ie?S(o,r):n._result[t]=r),0===n._remaining&&k(o,n._result)},C.prototype._willSettleAt=function(e,t){var r=this;U(e,void 0,function(e){r._settledAt(oe,t,e)},function(e){r._settledAt(ie,t,e)})};var ue=N,le=L,fe=D,de=M,he=0,pe=K;K.all=ue,K.race=le,K.resolve=fe,K.reject=de,K._setScheduler=c,K._setAsap=u,K._asap=X,K.prototype={constructor:K,then:function(e,t){var r=this,n=r._state;if(n===oe&&!e||n===ie&&!t)return this;var o=new this.constructor(v),i=r._result;if(n){var s=arguments[n-1];X(function(){E(n,o,s,i)})}else U(r,o,e,t);return o},"catch":function(e){return this.then(null,e)}};var ye=B,me={Promise:pe,polyfill:ye};"function"==typeof e&&e.amd?e(function(){return me}):"undefined"!=typeof r&&r.exports?r.exports=me:"undefined"!=typeof this&&(this.ES6Promise=me),ye()}).call(this)}).call(this,t(2),"undefined"!=typeof global?global:"undefined"!=typeof self?self:"undefined"!=typeof window?window:{})},{2:2}],10:[function(e,t,r){"function"==typeof Object.create?t.exports=function(e,t){e.super_=t,e.prototype=Object.create(t.prototype,{constructor:{value:e,enumerable:!1,writable:!0,configurable:!0}})}:t.exports=function(e,t){e.super_=t;var r=function(){};r.prototype=t.prototype,e.prototype=new r,e.prototype.constructor=e}},{}],11:[function(e,t,r){var n=e(14),o=e(18),i=e(30),s=i(n,o);t.exports=s},{14:14,18:18,30:30}],12:[function(e,t,r){function n(e,t){if("function"!=typeof e)throw new TypeError(o);return t=i(void 0===t?e.length-1:+t||0,0),function(){for(var r=arguments,n=-1,o=i(r.length-t,0),s=Array(o);++n<o;)s[n]=r[t+n];switch(t){case 0:return e.call(this,s);case 1:return e.call(this,r[0],s);case 2:return e.call(this,r[0],r[1],s)}var a=Array(t+1);for(n=-1;++n<t;)a[n]=r[n];return a[t]=s,e.apply(this,a)}}var o="Expected a function",i=Math.max;t.exports=n},{}],13:[function(e,t,r){function n(e,t){var r=-1,n=e.length;for(t||(t=Array(n));++r<n;)t[r]=e[r];return t}t.exports=n},{}],14:[function(e,t,r){function n(e,t){for(var r=-1,n=e.length;++r<n&&t(e[r],r,e)!==!1;);return e}t.exports=n},{}],15:[function(e,t,r){function n(e,t){return null==t?e:o(t,i(t),e)}var o=e(17),i=e(53);t.exports=n},{17:17,53:53}],16:[function(e,t,r){function n(e,t,r,p,y,m,v){var g;if(r&&(g=y?r(e,p,y):r(e)),void 0!==g)return g;if(!d(e))return e;var w=f(e);if(w){if(g=c(e),!t)return o(e,g)}else{var _=D.call(e),j=_==b;if(_!=x&&_!=h&&(!j||y))return N[_]?u(e,_,t):y?e:{};if(g=l(j?{}:e),!t)return s(g,e)}m||(m=[]),v||(v=[]);for(var T=m.length;T--;)if(m[T]==e)return v[T];return m.push(e),v.push(g),(w?i:a)(e,function(o,i){g[i]=n(o,t,r,i,e,m,v)}),g}var o=e(13),i=e(14),s=e(15),a=e(21),c=e(33),u=e(34),l=e(35),f=e(46),d=e(49),h="[object Arguments]",p="[object Array]",y="[object Boolean]",m="[object Date]",v="[object Error]",b="[object Function]",g="[object Map]",w="[object Number]",x="[object Object]",_="[object RegExp]",j="[object Set]",T="[object String]",A="[object WeakMap]",O="[object ArrayBuffer]",k="[object Float32Array]",S="[object Float64Array]",U="[object Int8Array]",I="[object Int16Array]",R="[object Int32Array]",P="[object Uint8Array]",E="[object Uint8ClampedArray]",q="[object Uint16Array]",C="[object Uint32Array]",N={};N[h]=N[p]=N[O]=N[y]=N[m]=N[k]=N[S]=N[U]=N[I]=N[R]=N[w]=N[x]=N[_]=N[T]=N[P]=N[E]=N[q]=N[C]=!0,N[v]=N[b]=N[g]=N[j]=N[A]=!1;var L=Object.prototype,D=L.toString;t.exports=n},{13:13,14:14,15:15,21:21,33:33,34:34,35:35,46:46,49:49}],17:[function(e,t,r){function n(e,t,r){r||(r={});for(var n=-1,o=t.length;++n<o;){var i=t[n];r[i]=e[i]}return r}t.exports=n},{}],18:[function(e,t,r){var n=e(21),o=e(28),i=o(n);t.exports=i},{21:21,28:28}],19:[function(e,t,r){var n=e(29),o=n();t.exports=o},{29:29}],20:[function(e,t,r){function n(e,t){return o(e,t,i)}var o=e(19),i=e(54);t.exports=n},{19:19,54:54}],21:[function(e,t,r){function n(e,t){return o(e,t,i)}var o=e(19),i=e(53);t.exports=n},{19:19,53:53}],22:[function(e,t,r){function n(e,t,r,d,h){if(!c(e))return e;var p=a(t)&&(s(t)||l(t)),y=p?void 0:f(t);return o(y||t,function(o,s){if(y&&(s=o,o=t[s]),u(o))d||(d=[]),h||(h=[]),i(e,t,s,n,r,d,h);else{var a=e[s],c=r?r(a,o,s,e,t):void 0,l=void 0===c;l&&(c=o),void 0===c&&(!p||s in e)||!l&&(c===c?c===a:a!==a)||(e[s]=c)}}),e}var o=e(14),i=e(23),s=e(46),a=e(36),c=e(49),u=e(40),l=e(51),f=e(53);t.exports=n},{14:14,23:23,36:36,40:40,46:46,49:49,51:51,53:53}],23:[function(e,t,r){function n(e,t,r,n,f,d,h){for(var p=d.length,y=t[r];p--;)if(d[p]==y)return void(e[r]=h[p]);var m=e[r],v=f?f(m,y,r,e,t):void 0,b=void 0===v;b&&(v=y,a(y)&&(s(y)||u(y))?v=s(m)?m:a(m)?o(m):[]:c(y)||i(y)?v=i(m)?l(m):c(m)?m:{}:b=!1),d.push(y),h.push(v),b?e[r]=n(v,y,f,d,h):(v===v?v!==m:m===m)&&(e[r]=v)}var o=e(13),i=e(45),s=e(46),a=e(36),c=e(50),u=e(51),l=e(52);t.exports=n},{13:13,36:36,45:45,46:46,50:50,51:51,52:52}],24:[function(e,t,r){function n(e){return function(t){return null==t?void 0:t[e]}}t.exports=n},{}],25:[function(e,t,r){function n(e,t,r){if("function"!=typeof e)return o;if(void 0===t)return e;switch(r){case 1:return function(r){return e.call(t,r)};case 3:return function(r,n,o){return e.call(t,r,n,o)};case 4:return function(r,n,o,i){return e.call(t,r,n,o,i)};case 5:return function(r,n,o,i,s){return e.call(t,r,n,o,i,s)}}return function(){return e.apply(t,arguments)}}var o=e(56);t.exports=n},{56:56}],26:[function(e,t,r){(function(e){function r(e){var t=new n(e.byteLength),r=new o(t);return r.set(new o(e)),t}var n=e.ArrayBuffer,o=e.Uint8Array;t.exports=r}).call(this,"undefined"!=typeof global?global:"undefined"!=typeof self?self:"undefined"!=typeof window?window:{})},{}],27:[function(e,t,r){function n(e){return s(function(t,r){var n=-1,s=null==t?0:r.length,a=s>2?r[s-2]:void 0,c=s>2?r[2]:void 0,u=s>1?r[s-1]:void 0;for("function"==typeof a?(a=o(a,u,5),s-=2):(a="function"==typeof u?u:void 0,s-=a?1:0),c&&i(r[0],r[1],c)&&(a=3>s?void 0:a,s=1);++n<s;){var l=r[n];l&&e(t,l,a)}return t})}var o=e(25),i=e(38),s=e(12);t.exports=n},{12:12,25:25,38:38}],28:[function(e,t,r){function n(e,t){return function(r,n){var a=r?o(r):0;if(!i(a))return e(r,n);for(var c=t?a:-1,u=s(r);(t?c--:++c<a)&&n(u[c],c,u)!==!1;);return r}}var o=e(31),i=e(39),s=e(42);t.exports=n},{31:31,39:39,42:42}],29:[function(e,t,r){function n(e){return function(t,r,n){for(var i=o(t),s=n(t),a=s.length,c=e?a:-1;e?c--:++c<a;){var u=s[c];if(r(i[u],u,i)===!1)break}return t}}var o=e(42);t.exports=n},{42:42}],30:[function(e,t,r){function n(e,t){return function(r,n,s){return"function"==typeof n&&void 0===s&&i(r)?e(r,n):t(r,o(n,s,3))}}var o=e(25),i=e(46);t.exports=n},{25:25,46:46}],31:[function(e,t,r){var n=e(24),o=n("length");t.exports=o},{24:24}],32:[function(e,t,r){function n(e,t){var r=null==e?void 0:e[t];return o(r)?r:void 0}var o=e(48);t.exports=n},{48:48}],33:[function(e,t,r){function n(e){var t=e.length,r=new e.constructor(t);return t&&"string"==typeof e[0]&&i.call(e,"index")&&(r.index=e.index,r.input=e.input),r}var o=Object.prototype,i=o.hasOwnProperty;t.exports=n},{}],34:[function(e,t,r){function n(e,t,r){var n=e.constructor;switch(t){case l:return o(e);case i:case s:return new n(+e);case f:case d:case h:case p:case y:case m:case v:case b:case g:var x=e.buffer;return new n(r?o(x):x,e.byteOffset,e.length);case a:case u:return new n(e);case c:var _=new n(e.source,w.exec(e));_.lastIndex=e.lastIndex}return _}var o=e(26),i="[object Boolean]",s="[object Date]",a="[object Number]",c="[object RegExp]",u="[object String]",l="[object ArrayBuffer]",f="[object Float32Array]",d="[object Float64Array]",h="[object Int8Array]",p="[object Int16Array]",y="[object Int32Array]",m="[object Uint8Array]",v="[object Uint8ClampedArray]",b="[object Uint16Array]",g="[object Uint32Array]",w=/\w*$/;t.exports=n},{26:26}],35:[function(e,t,r){function n(e){var t=e.constructor;return"function"==typeof t&&t instanceof t||(t=Object),new t}t.exports=n},{}],36:[function(e,t,r){function n(e){return null!=e&&i(o(e))}var o=e(31),i=e(39);t.exports=n},{31:31,39:39}],37:[function(e,t,r){function n(e,t){return e="number"==typeof e||o.test(e)?+e:-1,t=null==t?i:t,e>-1&&e%1==0&&t>e}var o=/^\d+$/,i=9007199254740991;t.exports=n},{}],38:[function(e,t,r){function n(e,t,r){if(!s(r))return!1;var n=typeof t;if("number"==n?o(r)&&i(t,r.length):"string"==n&&t in r){var a=r[t];return e===e?e===a:a!==a}return!1}var o=e(36),i=e(37),s=e(49);t.exports=n},{36:36,37:37,49:49}],39:[function(e,t,r){function n(e){return"number"==typeof e&&e>-1&&e%1==0&&o>=e}var o=9007199254740991;t.exports=n},{}],40:[function(e,t,r){function n(e){return!!e&&"object"==typeof e}t.exports=n},{}],41:[function(e,t,r){function n(e){for(var t=c(e),r=t.length,n=r&&e.length,u=!!n&&a(n)&&(i(e)||o(e)),f=-1,d=[];++f<r;){var h=t[f];(u&&s(h,n)||l.call(e,h))&&d.push(h)}return d}var o=e(45),i=e(46),s=e(37),a=e(39),c=e(54),u=Object.prototype,l=u.hasOwnProperty;t.exports=n},{37:37,39:39,45:45,46:46,54:54}],42:[function(e,t,r){function n(e){return o(e)?e:Object(e)}var o=e(49);t.exports=n},{49:49}],43:[function(e,t,r){function n(e,t,r,n){return t&&"boolean"!=typeof t&&s(e,t,r)?t=!1:"function"==typeof t&&(n=r,r=t,t=!1),"function"==typeof r?o(e,t,i(r,n,3)):o(e,t)}var o=e(16),i=e(25),s=e(38);t.exports=n},{16:16,25:25,38:38}],44:[function(e,t,r){function n(e,t,r){return"function"==typeof t?o(e,!0,i(t,r,3)):o(e,!0)}var o=e(16),i=e(25);t.exports=n},{16:16,25:25}],45:[function(e,t,r){function n(e){return i(e)&&o(e)&&a.call(e,"callee")&&!c.call(e,"callee")}var o=e(36),i=e(40),s=Object.prototype,a=s.hasOwnProperty,c=s.propertyIsEnumerable;t.exports=n},{36:36,40:40}],46:[function(e,t,r){var n=e(32),o=e(39),i=e(40),s="[object Array]",a=Object.prototype,c=a.toString,u=n(Array,"isArray"),l=u||function(e){return i(e)&&o(e.length)&&c.call(e)==s};t.exports=l},{32:32,39:39,40:40}],47:[function(e,t,r){function n(e){return o(e)&&a.call(e)==i}var o=e(49),i="[object Function]",s=Object.prototype,a=s.toString;t.exports=n},{49:49}],48:[function(e,t,r){function n(e){return null==e?!1:o(e)?l.test(c.call(e)):i(e)&&s.test(e)}var o=e(47),i=e(40),s=/^\[object .+?Constructor\]$/,a=Object.prototype,c=Function.prototype.toString,u=a.hasOwnProperty,l=RegExp("^"+c.call(u).replace(/[\\^$.*+?()[\]{}|]/g,"\\$&").replace(/hasOwnProperty|(function).*?(?=\\\()| for .+?(?=\\\])/g,"$1.*?")+"$");t.exports=n},{40:40,47:47}],49:[function(e,t,r){function n(e){var t=typeof e;return!!e&&("object"==t||"function"==t)}t.exports=n},{}],50:[function(e,t,r){function n(e){var t;if(!s(e)||l.call(e)!=a||i(e)||!u.call(e,"constructor")&&(t=e.constructor,"function"==typeof t&&!(t instanceof t)))return!1;var r;return o(e,function(e,t){r=t}),void 0===r||u.call(e,r)}var o=e(20),i=e(45),s=e(40),a="[object Object]",c=Object.prototype,u=c.hasOwnProperty,l=c.toString;t.exports=n},{20:20,40:40,45:45}],51:[function(e,t,r){function n(e){return i(e)&&o(e.length)&&!!U[R.call(e)]}var o=e(39),i=e(40),s="[object Arguments]",a="[object Array]",c="[object Boolean]",u="[object Date]",l="[object Error]",f="[object Function]",d="[object Map]",h="[object Number]",p="[object Object]",y="[object RegExp]",m="[object Set]",v="[object String]",b="[object WeakMap]",g="[object ArrayBuffer]",w="[object Float32Array]",x="[object Float64Array]",_="[object Int8Array]",j="[object Int16Array]",T="[object Int32Array]",A="[object Uint8Array]",O="[object Uint8ClampedArray]",k="[object Uint16Array]",S="[object Uint32Array]",U={};U[w]=U[x]=U[_]=U[j]=U[T]=U[A]=U[O]=U[k]=U[S]=!0,U[s]=U[a]=U[g]=U[c]=U[u]=U[l]=U[f]=U[d]=U[h]=U[p]=U[y]=U[m]=U[v]=U[b]=!1;var I=Object.prototype,R=I.toString;t.exports=n},{39:39,40:40}],52:[function(e,t,r){function n(e){return o(e,i(e))}var o=e(17),i=e(54);t.exports=n},{17:17,54:54}],53:[function(e,t,r){var n=e(32),o=e(36),i=e(49),s=e(41),a=n(Object,"keys"),c=a?function(e){var t=null==e?void 0:e.constructor;return"function"==typeof t&&t.prototype===e||"function"!=typeof e&&o(e)?s(e):i(e)?a(e):[]}:s;t.exports=c},{32:32,36:36,41:41,49:49}],54:[function(e,t,r){function n(e){if(null==e)return[];c(e)||(e=Object(e));var t=e.length;t=t&&a(t)&&(i(e)||o(e))&&t||0;for(var r=e.constructor,n=-1,u="function"==typeof r&&r.prototype===e,f=Array(t),d=t>0;++n<t;)f[n]=n+"";for(var h in e)d&&s(h,t)||"constructor"==h&&(u||!l.call(e,h))||f.push(h);return f}var o=e(45),i=e(46),s=e(37),a=e(39),c=e(49),u=Object.prototype,l=u.hasOwnProperty;t.exports=n},{37:37,39:39,45:45,46:46,49:49}],55:[function(e,t,r){var n=e(22),o=e(27),i=o(n);t.exports=i},{22:22,27:27}],56:[function(e,t,r){function n(e){return e}t.exports=n},{}],57:[function(e,t,r){"use strict";function n(t,r,n){var s=e(6)("algoliasearch"),a=e(43),c=e(46),u="Usage: algoliasearch(applicationID, apiKey, opts)";if(!t)throw new h.AlgoliaSearchError("Please provide an application ID. "+u);if(!r)throw new h.AlgoliaSearchError("Please provide an API key. "+u);this.applicationID=t,this.apiKey=r;var l=[this.applicationID+"-1.algolianet.com",this.applicationID+"-2.algolianet.com",this.applicationID+"-3.algolianet.com"];this.hosts={read:[],write:[]},this.hostIndex={read:0,write:0},n=n||{};var f=n.protocol||"https:",d=void 0===n.timeout?2e3:n.timeout;
	if(/:$/.test(f)||(f+=":"),"http:"!==n.protocol&&"https:"!==n.protocol)throw new h.AlgoliaSearchError("protocol must be `http:` or `https:` (was `"+n.protocol+"`)");n.hosts?c(n.hosts)?(this.hosts.read=a(n.hosts),this.hosts.write=a(n.hosts)):(this.hosts.read=a(n.hosts.read),this.hosts.write=a(n.hosts.write)):(this.hosts.read=[this.applicationID+"-dsn.algolia.net"].concat(l),this.hosts.write=[this.applicationID+".algolia.net"].concat(l)),this.hosts.read=o(this.hosts.read,i(f)),this.hosts.write=o(this.hosts.write,i(f)),this.requestTimeout=d,this.extraHeaders=[],this.cache={},this._ua=n._ua,this._useCache=void 0===n._useCache?!0:n._useCache,this._setTimeout=n._setTimeout,s("init done, %j",this)}function o(e,t){for(var r=[],n=0;n<e.length;++n)r.push(t(e[n],n));return r}function i(e){return function(t){return e+"//"+t.toLowerCase()}}function s(){var e="Not implemented in this environment.\nIf you feel this is a mistake, write to support@algolia.com";throw new h.AlgoliaSearchError(e)}function a(e,t){var r=e.toLowerCase().replace(".","").replace("()","");return"algoliasearch: `"+e+"` was replaced by `"+t+"`. Please see https://github.com/algolia/algoliasearch-client-js/wiki/Deprecated#"+r}function c(e,t){t(e,0)}function u(e,t){function r(){return n||(console.log(t),n=!0),e.apply(this,arguments)}var n=!1;return r}function l(e){if(void 0===Array.prototype.toJSON)return JSON.stringify(e);var t=Array.prototype.toJSON;delete Array.prototype.toJSON;var r=JSON.stringify(e);return Array.prototype.toJSON=t,r}function f(e){return function(t,r,n){if("function"==typeof t&&"object"==typeof r||"object"==typeof n)throw new h.AlgoliaSearchError("index.search usage is index.search(query, params, cb)");0===arguments.length||"function"==typeof t?(n=t,t=""):(1===arguments.length||"function"==typeof r)&&(n=r,r=void 0),"object"==typeof t&&null!==t?(r=t,t=void 0):(void 0===t||null===t)&&(t="");var o="";return void 0!==t&&(o+=e+"="+encodeURIComponent(t)),void 0!==r&&(o=this.as._getSearchParams(r,o)),this._search(o,n)}}t.exports=n;var d=d||void 0;d&&d.env,1;var h=e(63);n.prototype={deleteIndex:function(e,t){return this._jsonRequest({method:"DELETE",url:"/1/indexes/"+encodeURIComponent(e),hostType:"write",callback:t})},moveIndex:function(e,t,r){var n={operation:"move",destination:t};return this._jsonRequest({method:"POST",url:"/1/indexes/"+encodeURIComponent(e)+"/operation",body:n,hostType:"write",callback:r})},copyIndex:function(e,t,r){var n={operation:"copy",destination:t};return this._jsonRequest({method:"POST",url:"/1/indexes/"+encodeURIComponent(e)+"/operation",body:n,hostType:"write",callback:r})},getLogs:function(e,t,r){return 0===arguments.length||"function"==typeof e?(r=e,e=0,t=10):(1===arguments.length||"function"==typeof t)&&(r=t,t=10),this._jsonRequest({method:"GET",url:"/1/logs?offset="+e+"&length="+t,hostType:"read",callback:r})},listIndexes:function(e,t){var r="";return void 0===e||"function"==typeof e?t=e:r="?page="+e,this._jsonRequest({method:"GET",url:"/1/indexes"+r,hostType:"read",callback:t})},initIndex:function(e){return new this.Index(this,e)},listUserKeys:function(e){return this._jsonRequest({method:"GET",url:"/1/keys",hostType:"read",callback:e})},getUserKeyACL:function(e,t){return this._jsonRequest({method:"GET",url:"/1/keys/"+e,hostType:"read",callback:t})},deleteUserKey:function(e,t){return this._jsonRequest({method:"DELETE",url:"/1/keys/"+e,hostType:"write",callback:t})},addUserKey:function(t,r,n){var o=e(46),i="Usage: client.addUserKey(arrayOfAcls[, params, callback])";if(!o(t))throw new Error(i);(1===arguments.length||"function"==typeof r)&&(n=r,r=null);var s={acl:t};return r&&(s.validity=r.validity,s.maxQueriesPerIPPerHour=r.maxQueriesPerIPPerHour,s.maxHitsPerQuery=r.maxHitsPerQuery,s.indexes=r.indexes,s.description=r.description,r.queryParameters&&(s.queryParameters=this._getSearchParams(r.queryParameters,"")),s.referers=r.referers),this._jsonRequest({method:"POST",url:"/1/keys",body:s,hostType:"write",callback:n})},addUserKeyWithValidity:u(function(e,t,r){return this.addUserKey(e,t,r)},a("client.addUserKeyWithValidity()","client.addUserKey()")),updateUserKey:function(t,r,n,o){var i=e(46),s="Usage: client.updateUserKey(key, arrayOfAcls[, params, callback])";if(!i(r))throw new Error(s);(2===arguments.length||"function"==typeof n)&&(o=n,n=null);var a={acl:r};return n&&(a.validity=n.validity,a.maxQueriesPerIPPerHour=n.maxQueriesPerIPPerHour,a.maxHitsPerQuery=n.maxHitsPerQuery,a.indexes=n.indexes,a.description=n.description,n.queryParameters&&(a.queryParameters=this._getSearchParams(n.queryParameters,"")),a.referers=n.referers),this._jsonRequest({method:"PUT",url:"/1/keys/"+t,body:a,hostType:"write",callback:o})},setSecurityTags:function(e){if("[object Array]"===Object.prototype.toString.call(e)){for(var t=[],r=0;r<e.length;++r)if("[object Array]"===Object.prototype.toString.call(e[r])){for(var n=[],o=0;o<e[r].length;++o)n.push(e[r][o]);t.push("("+n.join(",")+")")}else t.push(e[r]);e=t.join(",")}this.securityTags=e},setUserToken:function(e){this.userToken=e},startQueriesBatch:u(function(){this._batch=[]},a("client.startQueriesBatch()","client.search()")),addQueryInBatch:u(function(e,t,r){this._batch.push({indexName:e,query:t,params:r})},a("client.addQueryInBatch()","client.search()")),clearCache:function(){this.cache={}},sendQueriesBatch:u(function(e){return this.search(this._batch,e)},a("client.sendQueriesBatch()","client.search()")),setRequestTimeout:function(e){e&&(this.requestTimeout=parseInt(e,10))},search:function(t,r){var n=e(46),i="Usage: client.search(arrayOfQueries[, callback])";if(!n(t))throw new Error(i);var s=this,a={requests:o(t,function(e){var t="";return void 0!==e.query&&(t+="query="+encodeURIComponent(e.query)),{indexName:e.indexName,params:s._getSearchParams(e.params,t)}})};return this._jsonRequest({cache:this.cache,method:"POST",url:"/1/indexes/*/queries",body:a,hostType:"read",callback:r})},batch:function(t,r){var n=e(46),o="Usage: client.batch(operations[, callback])";if(!n(t))throw new Error(o);return this._jsonRequest({method:"POST",url:"/1/indexes/*/batch",body:{requests:t},hostType:"write",callback:r})},destroy:s,enableRateLimitForward:s,disableRateLimitForward:s,useSecuredAPIKey:s,disableSecuredAPIKey:s,generateSecuredApiKey:s,Index:function(e,t){this.indexName=t,this.as=e,this.typeAheadArgs=null,this.typeAheadValueOption=null,this.cache={}},setExtraHeader:function(e,t){this.extraHeaders.push({name:e.toLowerCase(),value:t})},addAlgoliaAgent:function(e){this._ua+=";"+e},_sendQueriesBatch:function(e,t){function r(){for(var t="",r=0;r<e.requests.length;++r){var n="/1/indexes/"+encodeURIComponent(e.requests[r].indexName)+"?"+e.requests[r].params;t+=r+"="+encodeURIComponent(n)+"&"}return t}return this._jsonRequest({cache:this.cache,method:"POST",url:"/1/indexes/*/queries",body:e,hostType:"read",fallback:{method:"GET",url:"/1/indexes/*",body:{params:r()}},callback:t})},_jsonRequest:function(t){function r(e,c){function f(e){var t=e&&e.body&&e.body.message&&e.body.status||e.statusCode||e&&e.body&&200;o("received response: statusCode: %s, computed statusCode: %d, headers: %j",e.statusCode,t,e.headers),d&&d.env.DEBUG&&-1!==d.env.DEBUG.indexOf("debugBody")&&o("body: %j",e.body);var r=200===t||201===t,n=!r&&4!==Math.floor(t/100)&&1!==Math.floor(t/100);if(s._useCache&&r&&i&&(i[m]=e.responseText),r)return e.body;if(n)return a+=1,y();var c=new h.AlgoliaSearchError(e.body&&e.body.message);return s._promise.reject(c)}function p(n){return o("error: %s, stack: %s",n.message,n.stack),n instanceof h.AlgoliaSearchError||(n=new h.Unknown(n&&n.message,n)),a+=1,n instanceof h.Unknown||n instanceof h.UnparsableJSON||a>=s.hosts[t.hostType].length&&(u||!t.fallback||!s._request.fallback)?s._promise.reject(n):(s.hostIndex[t.hostType]=++s.hostIndex[t.hostType]%s.hosts[t.hostType].length,n instanceof h.RequestTimeout?y():(s._request.fallback&&!s.useFallback&&(s.useFallback=!0),r(e,c)))}function y(){return s.hostIndex[t.hostType]=++s.hostIndex[t.hostType]%s.hosts[t.hostType].length,c.timeout=s.requestTimeout*(a+1),r(e,c)}var m;if(s._useCache&&(m=t.url),s._useCache&&n&&(m+="_body_"+c.body),s._useCache&&i&&void 0!==i[m])return o("serving response from cache"),s._promise.resolve(JSON.parse(i[m]));if(a>=s.hosts[t.hostType].length||s.useFallback&&!u)return t.fallback&&s._request.fallback&&!u?(o("switching to fallback"),a=0,c.method=t.fallback.method,c.url=t.fallback.url,c.jsonBody=t.fallback.body,c.jsonBody&&(c.body=l(c.jsonBody)),c.timeout=s.requestTimeout*(a+1),s.hostIndex[t.hostType]=0,u=!0,r(s._request.fallback,c)):(o("could not get any response"),s._promise.reject(new h.AlgoliaSearchError("Cannot connect to the AlgoliaSearch API. Send an email to support@algolia.com to report and resolve the issue. Application id was: "+s.applicationID)));var v=s.hosts[t.hostType][s.hostIndex[t.hostType]]+c.url,b={body:n,jsonBody:t.body,method:c.method,headers:s._computeRequestHeaders(),timeout:c.timeout,debug:o};return o("method: %s, url: %s, headers: %j, timeout: %d",b.method,v,b.headers,b.timeout),e===s._request.fallback&&o("using fallback"),e.call(s,v,b).then(f,p)}var n,o=e(6)("algoliasearch:"+t.url),i=t.cache,s=this,a=0,u=!1;void 0!==t.body&&(n=l(t.body)),o("request start");var f=s.useFallback&&t.fallback,p=f?t.fallback:t,y=r(f?s._request.fallback:s._request,{url:p.url,method:p.method,body:n,jsonBody:t.body,timeout:s.requestTimeout*(a+1)});return t.callback?void y.then(function(e){c(function(){t.callback(null,e)},s._setTimeout||setTimeout)},function(e){c(function(){t.callback(e)},s._setTimeout||setTimeout)}):y},_getSearchParams:function(e,t){if(this._isUndefined(e)||null===e)return t;for(var r in e)null!==r&&void 0!==e[r]&&e.hasOwnProperty(r)&&(t+=""===t?"":"&",t+=r+"="+encodeURIComponent("[object Array]"===Object.prototype.toString.call(e[r])?l(e[r]):e[r]));return t},_isUndefined:function(e){return void 0===e},_computeRequestHeaders:function(){var t=e(11),r={"x-algolia-api-key":this.apiKey,"x-algolia-application-id":this.applicationID,"x-algolia-agent":this._ua};return this.userToken&&(r["x-algolia-usertoken"]=this.userToken),this.securityTags&&(r["x-algolia-tagfilters"]=this.securityTags),this.extraHeaders&&t(this.extraHeaders,function(e){r[e.name]=e.value}),r}},n.prototype.Index.prototype={clearCache:function(){this.cache={}},addObject:function(e,t,r){var n=this;return(1===arguments.length||"function"==typeof t)&&(r=t,t=void 0),this.as._jsonRequest({method:void 0!==t?"PUT":"POST",url:"/1/indexes/"+encodeURIComponent(n.indexName)+(void 0!==t?"/"+encodeURIComponent(t):""),body:e,hostType:"write",callback:r})},addObjects:function(t,r){var n=e(46),o="Usage: index.addObjects(arrayOfObjects[, callback])";if(!n(t))throw new Error(o);for(var i=this,s={requests:[]},a=0;a<t.length;++a){var c={action:"addObject",body:t[a]};s.requests.push(c)}return this.as._jsonRequest({method:"POST",url:"/1/indexes/"+encodeURIComponent(i.indexName)+"/batch",body:s,hostType:"write",callback:r})},getObject:function(e,t,r){var n=this;(1===arguments.length||"function"==typeof t)&&(r=t,t=void 0);var o="";if(void 0!==t){o="?attributes=";for(var i=0;i<t.length;++i)0!==i&&(o+=","),o+=t[i]}return this.as._jsonRequest({method:"GET",url:"/1/indexes/"+encodeURIComponent(n.indexName)+"/"+encodeURIComponent(e)+o,hostType:"read",callback:r})},getObjects:function(t,r,n){var i=e(46),s="Usage: index.getObjects(arrayOfObjectIDs[, callback])";if(!i(t))throw new Error(s);var a=this;(1===arguments.length||"function"==typeof r)&&(n=r,r=void 0);var c={requests:o(t,function(e){var t={indexName:a.indexName,objectID:e};return r&&(t.attributesToRetrieve=r.join(",")),t})};return this.as._jsonRequest({method:"POST",url:"/1/indexes/*/objects",hostType:"read",body:c,callback:n})},partialUpdateObject:function(e,t){var r=this;return this.as._jsonRequest({method:"POST",url:"/1/indexes/"+encodeURIComponent(r.indexName)+"/"+encodeURIComponent(e.objectID)+"/partial",body:e,hostType:"write",callback:t})},partialUpdateObjects:function(t,r){var n=e(46),o="Usage: index.partialUpdateObjects(arrayOfObjects[, callback])";if(!n(t))throw new Error(o);for(var i=this,s={requests:[]},a=0;a<t.length;++a){var c={action:"partialUpdateObject",objectID:t[a].objectID,body:t[a]};s.requests.push(c)}return this.as._jsonRequest({method:"POST",url:"/1/indexes/"+encodeURIComponent(i.indexName)+"/batch",body:s,hostType:"write",callback:r})},saveObject:function(e,t){var r=this;return this.as._jsonRequest({method:"PUT",url:"/1/indexes/"+encodeURIComponent(r.indexName)+"/"+encodeURIComponent(e.objectID),body:e,hostType:"write",callback:t})},saveObjects:function(t,r){var n=e(46),o="Usage: index.saveObjects(arrayOfObjects[, callback])";if(!n(t))throw new Error(o);for(var i=this,s={requests:[]},a=0;a<t.length;++a){var c={action:"updateObject",objectID:t[a].objectID,body:t[a]};s.requests.push(c)}return this.as._jsonRequest({method:"POST",url:"/1/indexes/"+encodeURIComponent(i.indexName)+"/batch",body:s,hostType:"write",callback:r})},deleteObject:function(e,t){if("function"==typeof e||"string"!=typeof e&&"number"!=typeof e){var r=new h.AlgoliaSearchError("Cannot delete an object without an objectID");return t=e,"function"==typeof t?t(r):this.as._promise.reject(r)}var n=this;return this.as._jsonRequest({method:"DELETE",url:"/1/indexes/"+encodeURIComponent(n.indexName)+"/"+encodeURIComponent(e),hostType:"write",callback:t})},deleteObjects:function(t,r){var n=e(46),i="Usage: index.deleteObjects(arrayOfObjectIDs[, callback])";if(!n(t))throw new Error(i);var s=this,a={requests:o(t,function(e){return{action:"deleteObject",objectID:e,body:{objectID:e}}})};return this.as._jsonRequest({method:"POST",url:"/1/indexes/"+encodeURIComponent(s.indexName)+"/batch",body:a,hostType:"write",callback:r})},deleteByQuery:function(t,r,n){function i(e){if(0===e.nbHits)return e;var t=o(e.hits,function(e){return e.objectID});return d.deleteObjects(t).then(s).then(a)}function s(e){return d.waitTask(e.taskID)}function a(){return d.deleteByQuery(t,r)}function u(){c(function(){n(null)},h._setTimeout||setTimeout)}function l(e){c(function(){n(e)},h._setTimeout||setTimeout)}var f=e(43),d=this,h=d.as;1===arguments.length||"function"==typeof r?(n=r,r={}):r=f(r),r.attributesToRetrieve="objectID",r.hitsPerPage=1e3,r.distinct=!1,this.clearCache();var p=this.search(t,r).then(i);return n?void p.then(u,l):p},search:f("query"),similarSearch:f("similarQuery"),browse:function(t,r,n){var o,i,s=e(55),a=this;0===arguments.length||1===arguments.length&&"function"==typeof arguments[0]?(o=0,n=arguments[0],t=void 0):"number"==typeof arguments[0]?(o=arguments[0],"number"==typeof arguments[1]?i=arguments[1]:"function"==typeof arguments[1]&&(n=arguments[1],i=void 0),t=void 0,r=void 0):"object"==typeof arguments[0]?("function"==typeof arguments[1]&&(n=arguments[1]),r=arguments[0],t=void 0):"string"==typeof arguments[0]&&"function"==typeof arguments[1]&&(n=arguments[1],r=void 0),r=s({},r||{},{page:o,hitsPerPage:i,query:t});var c=this.as._getSearchParams(r,"");return this.as._jsonRequest({method:"GET",url:"/1/indexes/"+encodeURIComponent(a.indexName)+"/browse?"+c,hostType:"read",callback:n})},browseFrom:function(e,t){return this.as._jsonRequest({method:"GET",url:"/1/indexes/"+encodeURIComponent(this.indexName)+"/browse?cursor="+encodeURIComponent(e),hostType:"read",callback:t})},browseAll:function(t,r){function n(e){if(!a._stopped){var t;t=void 0!==e?"cursor="+encodeURIComponent(e):l,c._jsonRequest({method:"GET",url:"/1/indexes/"+encodeURIComponent(u.indexName)+"/browse?"+t,hostType:"read",callback:o})}}function o(e,t){return a._stopped?void 0:e?void a._error(e):(a._result(t),void 0===t.cursor?void a._end():void n(t.cursor))}"object"==typeof t&&(r=t,t=void 0);var i=e(55),s=e(58),a=new s,c=this.as,u=this,l=c._getSearchParams(i({},r||{},{query:t}),"");return n(),a},ttAdapter:function(e){var t=this;return function(r,n,o){var i;i="function"==typeof o?o:n,t.search(r,e,function(e,t){return e?void i(e):void i(t.hits)})}},waitTask:function(e,t){function r(){return l._jsonRequest({method:"GET",hostType:"read",url:"/1/indexes/"+encodeURIComponent(u.indexName)+"/task/"+e}).then(function(e){a++;var t=i*a*a;return t>s&&(t=s),"published"!==e.status?l._promise.delay(t).then(r):e})}function n(e){c(function(){t(null,e)},l._setTimeout||setTimeout)}function o(e){c(function(){t(e)},l._setTimeout||setTimeout)}var i=100,s=5e3,a=0,u=this,l=u.as,f=r();return t?void f.then(n,o):f},clearIndex:function(e){var t=this;return this.as._jsonRequest({method:"POST",url:"/1/indexes/"+encodeURIComponent(t.indexName)+"/clear",hostType:"write",callback:e})},getSettings:function(e){var t=this;return this.as._jsonRequest({method:"GET",url:"/1/indexes/"+encodeURIComponent(t.indexName)+"/settings",hostType:"read",callback:e})},setSettings:function(e,t){var r=this;return this.as._jsonRequest({method:"PUT",url:"/1/indexes/"+encodeURIComponent(r.indexName)+"/settings",hostType:"write",body:e,callback:t})},listUserKeys:function(e){var t=this;return this.as._jsonRequest({method:"GET",url:"/1/indexes/"+encodeURIComponent(t.indexName)+"/keys",hostType:"read",callback:e})},getUserKeyACL:function(e,t){var r=this;return this.as._jsonRequest({method:"GET",url:"/1/indexes/"+encodeURIComponent(r.indexName)+"/keys/"+e,hostType:"read",callback:t})},deleteUserKey:function(e,t){var r=this;return this.as._jsonRequest({method:"DELETE",url:"/1/indexes/"+encodeURIComponent(r.indexName)+"/keys/"+e,hostType:"write",callback:t})},addUserKey:function(t,r,n){var o=e(46),i="Usage: index.addUserKey(arrayOfAcls[, params, callback])";if(!o(t))throw new Error(i);(1===arguments.length||"function"==typeof r)&&(n=r,r=null);var s={acl:t};return r&&(s.validity=r.validity,s.maxQueriesPerIPPerHour=r.maxQueriesPerIPPerHour,s.maxHitsPerQuery=r.maxHitsPerQuery,s.description=r.description,r.queryParameters&&(s.queryParameters=this.as._getSearchParams(r.queryParameters,"")),s.referers=r.referers),this.as._jsonRequest({method:"POST",url:"/1/indexes/"+encodeURIComponent(this.indexName)+"/keys",body:s,hostType:"write",callback:n})},addUserKeyWithValidity:u(function(e,t,r){return this.addUserKey(e,t,r)},a("index.addUserKeyWithValidity()","index.addUserKey()")),updateUserKey:function(t,r,n,o){var i=e(46),s="Usage: index.updateUserKey(key, arrayOfAcls[, params, callback])";if(!i(r))throw new Error(s);(2===arguments.length||"function"==typeof n)&&(o=n,n=null);var a={acl:r};return n&&(a.validity=n.validity,a.maxQueriesPerIPPerHour=n.maxQueriesPerIPPerHour,a.maxHitsPerQuery=n.maxHitsPerQuery,a.description=n.description,n.queryParameters&&(a.queryParameters=this.as._getSearchParams(n.queryParameters,"")),a.referers=n.referers),this.as._jsonRequest({method:"PUT",url:"/1/indexes/"+encodeURIComponent(this.indexName)+"/keys/"+t,body:a,hostType:"write",callback:o})},_search:function(e,t){return this.as._jsonRequest({cache:this.cache,method:"POST",url:"/1/indexes/"+encodeURIComponent(this.indexName)+"/query",body:{params:e},hostType:"read",fallback:{method:"GET",url:"/1/indexes/"+encodeURIComponent(this.indexName),body:{params:e}},callback:t})},as:null,indexName:null,typeAheadArgs:null,typeAheadValueOption:null}},{11:11,43:43,46:46,55:55,58:58,6:6,63:63}],58:[function(e,t,r){"use strict";function n(){}t.exports=n;var o=e(10),i=e(1).EventEmitter;o(n,i),n.prototype.stop=function(){this._stopped=!0,this._clean()},n.prototype._end=function(){this.emit("end"),this._clean()},n.prototype._error=function(e){this.emit("error",e),this._clean()},n.prototype._result=function(e){this.emit("result",e)},n.prototype._clean=function(){this.removeAllListeners("stop"),this.removeAllListeners("end"),this.removeAllListeners("error"),this.removeAllListeners("result")}},{1:1,10:10}],59:[function(e,t,r){"use strict";function n(t,r,i){var s=e(44),a=e(60);return i=s(i||{}),void 0===i.protocol&&(i.protocol=a()),i._ua=i._ua||n.ua,new o(t,r,i)}function o(){a.apply(this,arguments)}t.exports=n;var i=e(10),s=window.Promise||e(9).Promise,a=e(57),c=e(63),u=e(61),l=e(62);n.version=e(64),n.ua="Algolia for vanilla JavaScript "+n.version,window.__algolia={debug:e(6),algoliasearch:n};var f={hasXMLHttpRequest:"XMLHttpRequest"in window,hasXDomainRequest:"XDomainRequest"in window,cors:"withCredentials"in new XMLHttpRequest,timeout:"timeout"in new XMLHttpRequest};i(o,a),o.prototype._request=function(e,t){return new s(function(r,n){function o(){if(!l){f.timeout||clearTimeout(a);var e;try{e={body:JSON.parse(h.responseText),responseText:h.responseText,statusCode:h.status,headers:h.getAllResponseHeaders&&h.getAllResponseHeaders()||{}}}catch(t){e=new c.UnparsableJSON({more:h.responseText})}e instanceof c.UnparsableJSON?n(e):r(e)}}function i(e){l||(f.timeout||clearTimeout(a),n(new c.Network({more:e})))}function s(){f.timeout||(l=!0,h.abort()),n(new c.RequestTimeout)}if(!f.cors&&!f.hasXDomainRequest)return void n(new c.Network("CORS not supported"));e=u(e,t.headers);var a,l,d=t.body,h=f.cors?new XMLHttpRequest:new XDomainRequest;h instanceof XMLHttpRequest?h.open(t.method,e,!0):h.open(t.method,e),f.cors&&(d&&("POST"===t.method?h.setRequestHeader("content-type","application/x-www-form-urlencoded"):h.setRequestHeader("content-type","application/json")),h.setRequestHeader("accept","application/json")),h.onprogress=function(){},h.onload=o,h.onerror=i,f.timeout?(h.timeout=t.timeout,h.ontimeout=s):a=setTimeout(s,t.timeout),h.send(d)})},o.prototype._request.fallback=function(e,t){return e=u(e,t.headers),new s(function(r,n){l(e,t,function(e,t){return e?void n(e):void r(t)})})},o.prototype._promise={reject:function(e){return s.reject(e)},resolve:function(e){return s.resolve(e)},delay:function(e){return new s(function(t){setTimeout(t,e)})}}},{10:10,44:44,57:57,6:6,60:60,61:61,62:62,63:63,64:64,9:9}],60:[function(e,t,r){"use strict";function n(){var e=window.document.location.protocol;return"http:"!==e&&"https:"!==e&&(e="http:"),e}t.exports=n},{}],61:[function(e,t,r){"use strict";function n(e,t){return e+=/\?/.test(e)?"&":"?",e+o.encode(t)}t.exports=n;var o=e(5)},{5:5}],62:[function(e,t,r){"use strict";function n(e,t,r){function n(){t.debug("JSONP: success"),y||f||(y=!0,l||(t.debug("JSONP: Fail. Script loaded but did not call the callback"),a(),r(new o.JSONPScriptFail)))}function s(){("loaded"===this.readyState||"complete"===this.readyState)&&n()}function a(){clearTimeout(m),h.onload=null,h.onreadystatechange=null,h.onerror=null,d.removeChild(h);try{delete window[p],delete window[p+"_loaded"]}catch(e){window[p]=null,window[p+"_loaded"]=null}}function c(){t.debug("JSONP: Script timeout"),f=!0,a(),r(new o.RequestTimeout)}function u(){t.debug("JSONP: Script error"),y||f||(a(),r(new o.JSONPScriptError))}if("GET"!==t.method)return void r(new Error("Method "+t.method+" "+e+" is not supported by JSONP."));t.debug("JSONP: start");var l=!1,f=!1;i+=1;var d=document.getElementsByTagName("head")[0],h=document.createElement("script"),p="algoliaJSONP_"+i,y=!1;window[p]=function(e){try{delete window[p]}catch(t){window[p]=void 0}f||(l=!0,a(),r(null,{body:e}))},e+="&callback="+p,t.jsonBody&&t.jsonBody.params&&(e+="&"+t.jsonBody.params);var m=setTimeout(c,t.timeout);h.onreadystatechange=s,h.onload=n,h.onerror=u,h.async=!0,h.defer=!0,h.src=e,d.appendChild(h)}t.exports=n;var o=e(63),i=0},{63:63}],63:[function(e,t,r){"use strict";function n(t,r){var n=e(11),o=this;"function"==typeof Error.captureStackTrace?Error.captureStackTrace(this,this.constructor):o.stack=(new Error).stack||"Cannot get a stacktrace, browser is too old",this.name=this.constructor.name,this.message=t||"Unknown error",r&&n(r,function(e,t){o[t]=e})}function o(e,t){function r(){var r=Array.prototype.slice.call(arguments,0);"string"!=typeof r[0]&&r.unshift(t),n.apply(this,r),this.name="AlgoliaSearch"+e+"Error"}return i(r,n),r}var i=e(10);i(n,Error),t.exports={AlgoliaSearchError:n,UnparsableJSON:o("UnparsableJSON","Could not parse the incoming response as JSON, see err.more for details"),RequestTimeout:o("RequestTimeout","Request timedout before getting a response"),Network:o("Network","Network issue, see err.more for details"),JSONPScriptFail:o("JSONPScriptFail","<script> was loaded but did not call our provided callback"),JSONPScriptError:o("JSONPScriptError","<script> unable to load due to an `error` event on it"),Unknown:o("Unknown","Unknown error occured")}},{10:10,11:11}],64:[function(e,t,r){"use strict";t.exports="3.9.2"},{}]},{},[59])(59)});

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{}],16:[function(require,module,exports){
/*jshint curly:true, eqeqeq:true, laxbreak:true, noempty:false */
/*

  The MIT License (MIT)

  Copyright (c) 2007-2013 Einar Lielmanis and contributors.

  Permission is hereby granted, free of charge, to any person
  obtaining a copy of this software and associated documentation files
  (the "Software"), to deal in the Software without restriction,
  including without limitation the rights to use, copy, modify, merge,
  publish, distribute, sublicense, and/or sell copies of the Software,
  and to permit persons to whom the Software is furnished to do so,
  subject to the following conditions:

  The above copyright notice and this permission notice shall be
  included in all copies or substantial portions of the Software.

  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
  EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
  MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
  NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS
  BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN
  ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
  CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
  SOFTWARE.


 Style HTML
---------------

  Written by Nochum Sossonko, (nsossonko@hotmail.com)

  Based on code initially developed by: Einar Lielmanis, <elfz@laacz.lv>
    http://jsbeautifier.org/

  Usage:
    style_html(html_source);

    style_html(html_source, options);

  The options are:
    indent_inner_html (default false)  — indent <head> and <body> sections,
    indent_size (default 4)          — indentation size,
    indent_char (default space)      — character to indent with,
    wrap_line_length (default 250)            -  maximum amount of characters per line (0 = disable)
    brace_style (default "collapse") - "collapse" | "expand" | "end-expand"
            put braces on the same line as control statements (default), or put braces on own line (Allman / ANSI style), or just put end braces on own line.
    unformatted (defaults to inline tags) - list of tags, that shouldn't be reformatted
    indent_scripts (default normal)  - "keep"|"separate"|"normal"
    preserve_newlines (default true) - whether existing line breaks before elements should be preserved
                                        Only works before elements, not inside tags or for text.
    max_preserve_newlines (default unlimited) - maximum number of line breaks to be preserved in one chunk
    indent_handlebars (default false) - format and indent {{#foo}} and {{/foo}}

    e.g.

    style_html(html_source, {
      'indent_inner_html': false,
      'indent_size': 2,
      'indent_char': ' ',
      'wrap_line_length': 78,
      'brace_style': 'expand',
      'unformatted': ['a', 'sub', 'sup', 'b', 'i', 'u'],
      'preserve_newlines': true,
      'max_preserve_newlines': 5,
      'indent_handlebars': false
    });
*/

(function() {

    function trim(s) {
        return s.replace(/^\s+|\s+$/g, '');
    }

    function ltrim(s) {
        return s.replace(/^\s+/g, '');
    }

    function style_html(html_source, options, js_beautify, css_beautify) {
        //Wrapper function to invoke all the necessary constructors and deal with the output.

        var multi_parser,
            indent_inner_html,
            indent_size,
            indent_character,
            wrap_line_length,
            brace_style,
            unformatted,
            preserve_newlines,
            max_preserve_newlines;

        options = options || {};

        // backwards compatibility to 1.3.4
        if ((options.wrap_line_length === undefined || parseInt(options.wrap_line_length, 10) === 0) &&
                (options.max_char === undefined || parseInt(options.max_char, 10) === 0)) {
            options.wrap_line_length = options.max_char;
        }

        indent_inner_html = options.indent_inner_html || false;
        indent_size = parseInt(options.indent_size || 4, 10);
        indent_character = options.indent_char || ' ';
        brace_style = options.brace_style || 'collapse';
        wrap_line_length =  parseInt(options.wrap_line_length, 10) === 0 ? 32786 : parseInt(options.wrap_line_length || 250, 10);
        unformatted = options.unformatted || ['a', 'span', 'bdo', 'em', 'strong', 'dfn', 'code', 'samp', 'kbd', 'var', 'cite', 'abbr', 'acronym', 'q', 'sub', 'sup', 'tt', 'i', 'b', 'big', 'small', 'u', 's', 'strike', 'font', 'ins', 'del', 'pre', 'address', 'dt', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
        preserve_newlines = options.preserve_newlines || true;
        max_preserve_newlines = preserve_newlines ? parseInt(options.max_preserve_newlines || 32786, 10) : 0;
        indent_handlebars = options.indent_handlebars || false;

        function Parser() {

            this.pos = 0; //Parser position
            this.token = '';
            this.current_mode = 'CONTENT'; //reflects the current Parser mode: TAG/CONTENT
            this.tags = { //An object to hold tags, their position, and their parent-tags, initiated with default values
                parent: 'parent1',
                parentcount: 1,
                parent1: ''
            };
            this.tag_type = '';
            this.token_text = this.last_token = this.last_text = this.token_type = '';
            this.newlines = 0;
            this.indent_content = indent_inner_html;

            this.Utils = { //Uilities made available to the various functions
                whitespace: "\n\r\t ".split(''),
                single_token: 'br,input,link,meta,!doctype,basefont,base,area,hr,wbr,param,img,isindex,?xml,embed,?php,?,?='.split(','), //all the single tags for HTML
                extra_liners: 'head,body,/html'.split(','), //for tags that need a line of whitespace before them
                in_array: function(what, arr) {
                    for (var i = 0; i < arr.length; i++) {
                        if (what === arr[i]) {
                            return true;
                        }
                    }
                    return false;
                }
            };

            this.traverse_whitespace = function() {
                var input_char = '';

                input_char = this.input.charAt(this.pos);
                if (this.Utils.in_array(input_char, this.Utils.whitespace)) {
                    this.newlines = 0;
                    while (this.Utils.in_array(input_char, this.Utils.whitespace)) {
                        if (preserve_newlines && input_char === '\n' && this.newlines <= max_preserve_newlines) {
                            this.newlines += 1;
                        }

                        this.pos++;
                        input_char = this.input.charAt(this.pos);
                    }
                    return true;
                }
                return false;
            };

            this.get_content = function() { //function to capture regular content between tags

                var input_char = '',
                    content = [],
                    space = false; //if a space is needed

                while (this.input.charAt(this.pos) !== '<') {
                    if (this.pos >= this.input.length) {
                        return content.length ? content.join('') : ['', 'TK_EOF'];
                    }

                    if (this.traverse_whitespace()) {
                        if (content.length) {
                            space = true;
                        }
                        continue; //don't want to insert unnecessary space
                    }

                    if (indent_handlebars) {
                        // Handlebars parsing is complicated.
                        // {{#foo}} and {{/foo}} are formatted tags.
                        // {{something}} should get treated as content, except:
                        // {{else}} specifically behaves like {{#if}} and {{/if}}
                        var peek3 = this.input.substr(this.pos, 3);
                        if (peek3 === '{{#' || peek3 === '{{/') {
                            // These are tags and not content.
                            break;
                        } else if (this.input.substr(this.pos, 2) === '{{') {
                            if (this.get_tag(true) === '{{else}}') {
                                break;
                            }
                        }
                    }

                    input_char = this.input.charAt(this.pos);
                    this.pos++;

                    if (space) {
                        if (this.line_char_count >= this.wrap_line_length) { //insert a line when the wrap_line_length is reached
                            this.print_newline(false, content);
                            this.print_indentation(content);
                        } else {
                            this.line_char_count++;
                            content.push(' ');
                        }
                        space = false;
                    }
                    this.line_char_count++;
                    content.push(input_char); //letter at-a-time (or string) inserted to an array
                }
                return content.length ? content.join('') : '';
            };

            this.get_contents_to = function(name) { //get the full content of a script or style to pass to js_beautify
                if (this.pos === this.input.length) {
                    return ['', 'TK_EOF'];
                }
                var input_char = '';
                var content = '';
                var reg_match = new RegExp('</' + name + '\\s*>', 'igm');
                reg_match.lastIndex = this.pos;
                var reg_array = reg_match.exec(this.input);
                var end_script = reg_array ? reg_array.index : this.input.length; //absolute end of script
                if (this.pos < end_script) { //get everything in between the script tags
                    content = this.input.substring(this.pos, end_script);
                    this.pos = end_script;
                }
                return content;
            };

            this.record_tag = function(tag) { //function to record a tag and its parent in this.tags Object
                if (this.tags[tag + 'count']) { //check for the existence of this tag type
                    this.tags[tag + 'count']++;
                    this.tags[tag + this.tags[tag + 'count']] = this.indent_level; //and record the present indent level
                } else { //otherwise initialize this tag type
                    this.tags[tag + 'count'] = 1;
                    this.tags[tag + this.tags[tag + 'count']] = this.indent_level; //and record the present indent level
                }
                this.tags[tag + this.tags[tag + 'count'] + 'parent'] = this.tags.parent; //set the parent (i.e. in the case of a div this.tags.div1parent)
                this.tags.parent = tag + this.tags[tag + 'count']; //and make this the current parent (i.e. in the case of a div 'div1')
            };

            this.retrieve_tag = function(tag) { //function to retrieve the opening tag to the corresponding closer
                if (this.tags[tag + 'count']) { //if the openener is not in the Object we ignore it
                    var temp_parent = this.tags.parent; //check to see if it's a closable tag.
                    while (temp_parent) { //till we reach '' (the initial value);
                        if (tag + this.tags[tag + 'count'] === temp_parent) { //if this is it use it
                            break;
                        }
                        temp_parent = this.tags[temp_parent + 'parent']; //otherwise keep on climbing up the DOM Tree
                    }
                    if (temp_parent) { //if we caught something
                        this.indent_level = this.tags[tag + this.tags[tag + 'count']]; //set the indent_level accordingly
                        this.tags.parent = this.tags[temp_parent + 'parent']; //and set the current parent
                    }
                    delete this.tags[tag + this.tags[tag + 'count'] + 'parent']; //delete the closed tags parent reference...
                    delete this.tags[tag + this.tags[tag + 'count']]; //...and the tag itself
                    if (this.tags[tag + 'count'] === 1) {
                        delete this.tags[tag + 'count'];
                    } else {
                        this.tags[tag + 'count']--;
                    }
                }
            };

            this.indent_to_tag = function(tag) {
                // Match the indentation level to the last use of this tag, but don't remove it.
                if (!this.tags[tag + 'count']) {
                    return;
                }
                var temp_parent = this.tags.parent;
                while (temp_parent) {
                    if (tag + this.tags[tag + 'count'] === temp_parent) {
                        break;
                    }
                    temp_parent = this.tags[temp_parent + 'parent'];
                }
                if (temp_parent) {
                    this.indent_level = this.tags[tag + this.tags[tag + 'count']];
                }
            };

            this.get_tag = function(peek) { //function to get a full tag and parse its type
                var input_char = '',
                    content = [],
                    comment = '',
                    space = false,
                    tag_start, tag_end,
                    tag_start_char,
                    orig_pos = this.pos,
                    orig_line_char_count = this.line_char_count;

                peek = peek !== undefined ? peek : false;

                do {
                    if (this.pos >= this.input.length) {
                        if (peek) {
                            this.pos = orig_pos;
                            this.line_char_count = orig_line_char_count;
                        }
                        return content.length ? content.join('') : ['', 'TK_EOF'];
                    }

                    input_char = this.input.charAt(this.pos);
                    this.pos++;

                    if (this.Utils.in_array(input_char, this.Utils.whitespace)) { //don't want to insert unnecessary space
                        space = true;
                        continue;
                    }

                    if (input_char === "'" || input_char === '"') {
                        input_char += this.get_unformatted(input_char);
                        space = true;

                    }

                    if (input_char === '=') { //no space before =
                        space = false;
                    }

                    if (content.length && content[content.length - 1] !== '=' && input_char !== '>' && space) {
                        //no space after = or before >
                        if (this.line_char_count >= this.wrap_line_length) {
                            this.print_newline(false, content);
                            this.print_indentation(content);
                        } else {
                            content.push(' ');
                            this.line_char_count++;
                        }
                        space = false;
                    }

                    if (indent_handlebars && tag_start_char === '<') {
                        // When inside an angle-bracket tag, put spaces around
                        // handlebars not inside of strings.
                        if ((input_char + this.input.charAt(this.pos)) === '{{') {
                            input_char += this.get_unformatted('}}');
                            if (content.length && content[content.length - 1] !== ' ' && content[content.length - 1] !== '<') {
                                input_char = ' ' + input_char;
                            }
                            space = true;
                        }
                    }

                    if (input_char === '<' && !tag_start_char) {
                        tag_start = this.pos - 1;
                        tag_start_char = '<';
                    }

                    if (indent_handlebars && !tag_start_char) {
                        if (content.length >= 2 && content[content.length - 1] === '{' && content[content.length - 2] == '{') {
                            if (input_char === '#' || input_char === '/') {
                                tag_start = this.pos - 3;
                            } else {
                                tag_start = this.pos - 2;
                            }
                            tag_start_char = '{';
                        }
                    }

                    this.line_char_count++;
                    content.push(input_char); //inserts character at-a-time (or string)

                    if (content[1] && content[1] === '!') { //if we're in a comment, do something special
                        // We treat all comments as literals, even more than preformatted tags
                        // we just look for the appropriate close tag
                        content = [this.get_comment(tag_start)];
                        break;
                    }

                    if (indent_handlebars && tag_start_char === '{' && content.length > 2 && content[content.length - 2] === '}' && content[content.length - 1] === '}') {
                        break;
                    }
                } while (input_char !== '>');

                var tag_complete = content.join('');
                var tag_index;
                var tag_offset;

                if (tag_complete.indexOf(' ') !== -1) { //if there's whitespace, thats where the tag name ends
                    tag_index = tag_complete.indexOf(' ');
                } else if (tag_complete[0] === '{') {
                    tag_index = tag_complete.indexOf('}');
                } else { //otherwise go with the tag ending
                    tag_index = tag_complete.indexOf('>');
                }
                if (tag_complete[0] === '<' || !indent_handlebars) {
                    tag_offset = 1;
                } else {
                    tag_offset = tag_complete[2] === '#' ? 3 : 2;
                }
                var tag_check = tag_complete.substring(tag_offset, tag_index).toLowerCase();
                if (tag_complete.charAt(tag_complete.length - 2) === '/' ||
                    this.Utils.in_array(tag_check, this.Utils.single_token)) { //if this tag name is a single tag type (either in the list or has a closing /)
                    if (!peek) {
                        this.tag_type = 'SINGLE';
                    }
                } else if (indent_handlebars && tag_complete[0] === '{' && tag_check === 'else') {
                    if (!peek) {
                        this.indent_to_tag('if');
                        this.tag_type = 'HANDLEBARS_ELSE';
                        this.indent_content = true;
                        this.traverse_whitespace();
                    }
                } else if (tag_check === 'script') { //for later script handling
                    if (!peek) {
                        this.record_tag(tag_check);
                        this.tag_type = 'SCRIPT';
                    }
                } else if (tag_check === 'style') { //for future style handling (for now it justs uses get_content)
                    if (!peek) {
                        this.record_tag(tag_check);
                        this.tag_type = 'STYLE';
                    }
                } else if (this.is_unformatted(tag_check, unformatted)) { // do not reformat the "unformatted" tags
                    comment = this.get_unformatted('</' + tag_check + '>', tag_complete); //...delegate to get_unformatted function
                    content.push(comment);
                    // Preserve collapsed whitespace either before or after this tag.
                    if (tag_start > 0 && this.Utils.in_array(this.input.charAt(tag_start - 1), this.Utils.whitespace)) {
                        content.splice(0, 0, this.input.charAt(tag_start - 1));
                    }
                    tag_end = this.pos - 1;
                    if (this.Utils.in_array(this.input.charAt(tag_end + 1), this.Utils.whitespace)) {
                        content.push(this.input.charAt(tag_end + 1));
                    }
                    this.tag_type = 'SINGLE';
                } else if (tag_check.charAt(0) === '!') { //peek for <! comment
                    // for comments content is already correct.
                    if (!peek) {
                        this.tag_type = 'SINGLE';
                        this.traverse_whitespace();
                    }
                } else if (!peek) {
                    if (tag_check.charAt(0) === '/') { //this tag is a double tag so check for tag-ending
                        this.retrieve_tag(tag_check.substring(1)); //remove it and all ancestors
                        this.tag_type = 'END';
                        this.traverse_whitespace();
                    } else { //otherwise it's a start-tag
                        this.record_tag(tag_check); //push it on the tag stack
                        if (tag_check.toLowerCase() !== 'html') {
                            this.indent_content = true;
                        }
                        this.tag_type = 'START';

                        // Allow preserving of newlines after a start tag
                        this.traverse_whitespace();
                    }
                    if (this.Utils.in_array(tag_check, this.Utils.extra_liners)) { //check if this double needs an extra line
                        this.print_newline(false, this.output);
                        if (this.output.length && this.output[this.output.length - 2] !== '\n') {
                            this.print_newline(true, this.output);
                        }
                    }
                }

                if (peek) {
                    this.pos = orig_pos;
                    this.line_char_count = orig_line_char_count;
                }

                return content.join(''); //returns fully formatted tag
            };

            this.get_comment = function(start_pos) { //function to return comment content in its entirety
                // this is will have very poor perf, but will work for now.
                var comment = '',
                    delimiter = '>',
                    matched = false;

                this.pos = start_pos;
                input_char = this.input.charAt(this.pos);
                this.pos++;

                while (this.pos <= this.input.length) {
                    comment += input_char;

                    // only need to check for the delimiter if the last chars match
                    if (comment[comment.length - 1] === delimiter[delimiter.length - 1] &&
                        comment.indexOf(delimiter) !== -1) {
                        break;
                    }

                    // only need to search for custom delimiter for the first few characters
                    if (!matched && comment.length < 10) {
                        if (comment.indexOf('<![if') === 0) { //peek for <![if conditional comment
                            delimiter = '<![endif]>';
                            matched = true;
                        } else if (comment.indexOf('<![cdata[') === 0) { //if it's a <[cdata[ comment...
                            delimiter = ']]>';
                            matched = true;
                        } else if (comment.indexOf('<![') === 0) { // some other ![ comment? ...
                            delimiter = ']>';
                            matched = true;
                        } else if (comment.indexOf('<!--') === 0) { // <!-- comment ...
                            delimiter = '-->';
                            matched = true;
                        }
                    }

                    input_char = this.input.charAt(this.pos);
                    this.pos++;
                }

                return comment;
            };

            this.get_unformatted = function(delimiter, orig_tag) { //function to return unformatted content in its entirety

                if (orig_tag && orig_tag.toLowerCase().indexOf(delimiter) !== -1) {
                    return '';
                }
                var input_char = '';
                var content = '';
                var min_index = 0;
                var space = true;
                do {

                    if (this.pos >= this.input.length) {
                        return content;
                    }

                    input_char = this.input.charAt(this.pos);
                    this.pos++;

                    if (this.Utils.in_array(input_char, this.Utils.whitespace)) {
                        if (!space) {
                            this.line_char_count--;
                            continue;
                        }
                        if (input_char === '\n' || input_char === '\r') {
                            content += '\n';
                            /*  Don't change tab indention for unformatted blocks.  If using code for html editing, this will greatly affect <pre> tags if they are specified in the 'unformatted array'
                for (var i=0; i<this.indent_level; i++) {
                  content += this.indent_string;
                }
                space = false; //...and make sure other indentation is erased
                */
                            this.line_char_count = 0;
                            continue;
                        }
                    }
                    content += input_char;
                    this.line_char_count++;
                    space = true;

                    if (indent_handlebars && input_char === '{' && content.length && content[content.length - 2] === '{') {
                        // Handlebars expressions in strings should also be unformatted.
                        content += this.get_unformatted('}}');
                        // These expressions are opaque.  Ignore delimiters found in them.
                        min_index = content.length;
                    }
                } while (content.toLowerCase().indexOf(delimiter, min_index) === -1);
                return content;
            };

            this.get_token = function() { //initial handler for token-retrieval
                var token;

                if (this.last_token === 'TK_TAG_SCRIPT' || this.last_token === 'TK_TAG_STYLE') { //check if we need to format javascript
                    var type = this.last_token.substr(7);
                    token = this.get_contents_to(type);
                    if (typeof token !== 'string') {
                        return token;
                    }
                    return [token, 'TK_' + type];
                }
                if (this.current_mode === 'CONTENT') {
                    token = this.get_content();
                    if (typeof token !== 'string') {
                        return token;
                    } else {
                        return [token, 'TK_CONTENT'];
                    }
                }

                if (this.current_mode === 'TAG') {
                    token = this.get_tag();
                    if (typeof token !== 'string') {
                        return token;
                    } else {
                        var tag_name_type = 'TK_TAG_' + this.tag_type;
                        return [token, tag_name_type];
                    }
                }
            };

            this.get_full_indent = function(level) {
                level = this.indent_level + level || 0;
                if (level < 1) {
                    return '';
                }

                return Array(level + 1).join(this.indent_string);
            };

            this.is_unformatted = function(tag_check, unformatted) {
                //is this an HTML5 block-level link?
                if (!this.Utils.in_array(tag_check, unformatted)) {
                    return false;
                }

                if (tag_check.toLowerCase() !== 'a' || !this.Utils.in_array('a', unformatted)) {
                    return true;
                }

                //at this point we have an  tag; is its first child something we want to remain
                //unformatted?
                var next_tag = this.get_tag(true /* peek. */ );

                // test next_tag to see if it is just html tag (no external content)
                var tag = (next_tag || "").match(/^\s*<\s*\/?([a-z]*)\s*[^>]*>\s*$/);

                // if next_tag comes back but is not an isolated tag, then
                // let's treat the 'a' tag as having content
                // and respect the unformatted option
                if (!tag || this.Utils.in_array(tag, unformatted)) {
                    return true;
                } else {
                    return false;
                }
            };

            this.printer = function(js_source, indent_character, indent_size, wrap_line_length, brace_style) { //handles input/output and some other printing functions

                this.input = js_source || ''; //gets the input for the Parser
                this.output = [];
                this.indent_character = indent_character;
                this.indent_string = '';
                this.indent_size = indent_size;
                this.brace_style = brace_style;
                this.indent_level = 0;
                this.wrap_line_length = wrap_line_length;
                this.line_char_count = 0; //count to see if wrap_line_length was exceeded

                for (var i = 0; i < this.indent_size; i++) {
                    this.indent_string += this.indent_character;
                }

                this.print_newline = function(force, arr) {
                    this.line_char_count = 0;
                    if (!arr || !arr.length) {
                        return;
                    }
                    if (force || (arr[arr.length - 1] !== '\n')) { //we might want the extra line
                        arr.push('\n');
                    }
                };

                this.print_indentation = function(arr) {
                    for (var i = 0; i < this.indent_level; i++) {
                        arr.push(this.indent_string);
                        this.line_char_count += this.indent_string.length;
                    }
                };

                this.print_token = function(text) {
                    if (text || text !== '') {
                        if (this.output.length && this.output[this.output.length - 1] === '\n') {
                            this.print_indentation(this.output);
                            text = ltrim(text);
                        }
                    }
                    this.print_token_raw(text);
                };

                this.print_token_raw = function(text) {
                    if (text && text !== '') {
                        if (text.length > 1 && text[text.length - 1] === '\n') {
                            // unformatted tags can grab newlines as their last character
                            this.output.push(text.slice(0, -1));
                            this.print_newline(false, this.output);
                        } else {
                            this.output.push(text);
                        }
                    }

                    for (var n = 0; n < this.newlines; n++) {
                        this.print_newline(n > 0, this.output);
                    }
                    this.newlines = 0;
                };

                this.indent = function() {
                    this.indent_level++;
                };

                this.unindent = function() {
                    if (this.indent_level > 0) {
                        this.indent_level--;
                    }
                };
            };
            return this;
        }

        /*_____________________--------------------_____________________*/

        multi_parser = new Parser(); //wrapping functions Parser
        multi_parser.printer(html_source, indent_character, indent_size, wrap_line_length, brace_style); //initialize starting values

        while (true) {
            var t = multi_parser.get_token();
            multi_parser.token_text = t[0];
            multi_parser.token_type = t[1];

            if (multi_parser.token_type === 'TK_EOF') {
                break;
            }

            switch (multi_parser.token_type) {
                case 'TK_TAG_START':
                    multi_parser.print_newline(false, multi_parser.output);
                    multi_parser.print_token(multi_parser.token_text);
                    if (multi_parser.indent_content) {
                        multi_parser.indent();
                        multi_parser.indent_content = false;
                    }
                    multi_parser.current_mode = 'CONTENT';
                    break;
                case 'TK_TAG_STYLE':
                case 'TK_TAG_SCRIPT':
                    multi_parser.print_newline(false, multi_parser.output);
                    multi_parser.print_token(multi_parser.token_text);
                    multi_parser.current_mode = 'CONTENT';
                    break;
                case 'TK_TAG_END':
                    //Print new line only if the tag has no content and has child
                    if (multi_parser.last_token === 'TK_CONTENT' && multi_parser.last_text === '') {
                        var tag_name = multi_parser.token_text.match(/\w+/)[0];
                        var tag_extracted_from_last_output = null;
                        if (multi_parser.output.length) {
                            tag_extracted_from_last_output = multi_parser.output[multi_parser.output.length - 1].match(/(?:<|{{#)\s*(\w+)/);
                        }
                        if (tag_extracted_from_last_output === null ||
                            tag_extracted_from_last_output[1] !== tag_name) {
                            multi_parser.print_newline(false, multi_parser.output);
                        }
                    }
                    multi_parser.print_token(multi_parser.token_text);
                    multi_parser.current_mode = 'CONTENT';
                    break;
                case 'TK_TAG_SINGLE':
                    // Don't add a newline before elements that should remain unformatted.
                    var tag_check = multi_parser.token_text.match(/^\s*<([a-z]+)/i);
                    if (!tag_check || !multi_parser.Utils.in_array(tag_check[1], unformatted)) {
                        multi_parser.print_newline(false, multi_parser.output);
                    }
                    multi_parser.print_token(multi_parser.token_text);
                    multi_parser.current_mode = 'CONTENT';
                    break;
                case 'TK_TAG_HANDLEBARS_ELSE':
                    multi_parser.print_token(multi_parser.token_text);
                    if (multi_parser.indent_content) {
                        multi_parser.indent();
                        multi_parser.indent_content = false;
                    }
                    multi_parser.current_mode = 'CONTENT';
                    break;
                case 'TK_CONTENT':
                    multi_parser.print_token(multi_parser.token_text);
                    multi_parser.current_mode = 'TAG';
                    break;
                case 'TK_STYLE':
                case 'TK_SCRIPT':
                    if (multi_parser.token_text !== '') {
                        multi_parser.print_newline(false, multi_parser.output);
                        var text = multi_parser.token_text,
                            _beautifier,
                            script_indent_level = 1;
                        if (multi_parser.token_type === 'TK_SCRIPT') {
                            _beautifier = typeof js_beautify === 'function' && js_beautify;
                        } else if (multi_parser.token_type === 'TK_STYLE') {
                            _beautifier = typeof css_beautify === 'function' && css_beautify;
                        }

                        if (options.indent_scripts === "keep") {
                            script_indent_level = 0;
                        } else if (options.indent_scripts === "separate") {
                            script_indent_level = -multi_parser.indent_level;
                        }

                        var indentation = multi_parser.get_full_indent(script_indent_level);
                        if (_beautifier) {
                            // call the Beautifier if avaliable
                            text = _beautifier(text.replace(/^\s*/, indentation), options);
                        } else {
                            // simply indent the string otherwise
                            var white = text.match(/^\s*/)[0];
                            var _level = white.match(/[^\n\r]*$/)[0].split(multi_parser.indent_string).length - 1;
                            var reindent = multi_parser.get_full_indent(script_indent_level - _level);
                            text = text.replace(/^\s*/, indentation)
                                .replace(/\r\n|\r|\n/g, '\n' + reindent)
                                .replace(/\s+$/, '');
                        }
                        if (text) {
                            multi_parser.print_token_raw(indentation + trim(text));
                            multi_parser.print_newline(false, multi_parser.output);
                        }
                    }
                    multi_parser.current_mode = 'TAG';
                    break;
            }
            multi_parser.last_token = multi_parser.token_type;
            multi_parser.last_text = multi_parser.token_text;
        }
        return multi_parser.output.join('');
    }

    if (typeof module !== "undefined" && typeof( module.exports ) !== "undefined" ) {
		module.exports = style_html;
    } else if (typeof window !== "undefined") {
		// If we're running a web page and don't have either of the above, add our one global
		window.html_beautify = style_html;
	}

}());
},{}],17:[function(require,module,exports){
/*!
 * EventEmitter v4.2.11 - git.io/ee
 * Unlicense - http://unlicense.org/
 * Oliver Caldwell - http://oli.me.uk/
 * @preserve
 */

;(function () {
	'use strict';

	/**
	 * Class for managing events.
	 * Can be extended to provide event functionality in other classes.
	 *
	 * @class EventEmitter Manages event registering and emitting.
	 */
	function EventEmitter() {}

	// Shortcuts to improve speed and size
	var proto = EventEmitter.prototype;
	var exports = this;
	var originalGlobalValue = exports.EventEmitter;

	/**
	 * Finds the index of the listener for the event in its storage array.
	 *
	 * @param {Function[]} listeners Array of listeners to search through.
	 * @param {Function} listener Method to look for.
	 * @return {Number} Index of the specified listener, -1 if not found
	 * @api private
	 */
	function indexOfListener(listeners, listener) {
		var i = listeners.length;
		while (i--) {
			if (listeners[i].listener === listener) {
				return i;
			}
		}

		return -1;
	}

	/**
	 * Alias a method while keeping the context correct, to allow for overwriting of target method.
	 *
	 * @param {String} name The name of the target method.
	 * @return {Function} The aliased method
	 * @api private
	 */
	function alias(name) {
		return function aliasClosure() {
			return this[name].apply(this, arguments);
		};
	}

	/**
	 * Returns the listener array for the specified event.
	 * Will initialise the event object and listener arrays if required.
	 * Will return an object if you use a regex search. The object contains keys for each matched event. So /ba[rz]/ might return an object containing bar and baz. But only if you have either defined them with defineEvent or added some listeners to them.
	 * Each property in the object response is an array of listener functions.
	 *
	 * @param {String|RegExp} evt Name of the event to return the listeners from.
	 * @return {Function[]|Object} All listener functions for the event.
	 */
	proto.getListeners = function getListeners(evt) {
		var events = this._getEvents();
		var response;
		var key;

		// Return a concatenated array of all matching events if
		// the selector is a regular expression.
		if (evt instanceof RegExp) {
			response = {};
			for (key in events) {
				if (events.hasOwnProperty(key) && evt.test(key)) {
					response[key] = events[key];
				}
			}
		}
		else {
			response = events[evt] || (events[evt] = []);
		}

		return response;
	};

	/**
	 * Takes a list of listener objects and flattens it into a list of listener functions.
	 *
	 * @param {Object[]} listeners Raw listener objects.
	 * @return {Function[]} Just the listener functions.
	 */
	proto.flattenListeners = function flattenListeners(listeners) {
		var flatListeners = [];
		var i;

		for (i = 0; i < listeners.length; i += 1) {
			flatListeners.push(listeners[i].listener);
		}

		return flatListeners;
	};

	/**
	 * Fetches the requested listeners via getListeners but will always return the results inside an object. This is mainly for internal use but others may find it useful.
	 *
	 * @param {String|RegExp} evt Name of the event to return the listeners from.
	 * @return {Object} All listener functions for an event in an object.
	 */
	proto.getListenersAsObject = function getListenersAsObject(evt) {
		var listeners = this.getListeners(evt);
		var response;

		if (listeners instanceof Array) {
			response = {};
			response[evt] = listeners;
		}

		return response || listeners;
	};

	/**
	 * Adds a listener function to the specified event.
	 * The listener will not be added if it is a duplicate.
	 * If the listener returns true then it will be removed after it is called.
	 * If you pass a regular expression as the event name then the listener will be added to all events that match it.
	 *
	 * @param {String|RegExp} evt Name of the event to attach the listener to.
	 * @param {Function} listener Method to be called when the event is emitted. If the function returns true then it will be removed after calling.
	 * @return {Object} Current instance of EventEmitter for chaining.
	 */
	proto.addListener = function addListener(evt, listener) {
		var listeners = this.getListenersAsObject(evt);
		var listenerIsWrapped = typeof listener === 'object';
		var key;

		for (key in listeners) {
			if (listeners.hasOwnProperty(key) && indexOfListener(listeners[key], listener) === -1) {
				listeners[key].push(listenerIsWrapped ? listener : {
					listener: listener,
					once: false
				});
			}
		}

		return this;
	};

	/**
	 * Alias of addListener
	 */
	proto.on = alias('addListener');

	/**
	 * Semi-alias of addListener. It will add a listener that will be
	 * automatically removed after its first execution.
	 *
	 * @param {String|RegExp} evt Name of the event to attach the listener to.
	 * @param {Function} listener Method to be called when the event is emitted. If the function returns true then it will be removed after calling.
	 * @return {Object} Current instance of EventEmitter for chaining.
	 */
	proto.addOnceListener = function addOnceListener(evt, listener) {
		return this.addListener(evt, {
			listener: listener,
			once: true
		});
	};

	/**
	 * Alias of addOnceListener.
	 */
	proto.once = alias('addOnceListener');

	/**
	 * Defines an event name. This is required if you want to use a regex to add a listener to multiple events at once. If you don't do this then how do you expect it to know what event to add to? Should it just add to every possible match for a regex? No. That is scary and bad.
	 * You need to tell it what event names should be matched by a regex.
	 *
	 * @param {String} evt Name of the event to create.
	 * @return {Object} Current instance of EventEmitter for chaining.
	 */
	proto.defineEvent = function defineEvent(evt) {
		this.getListeners(evt);
		return this;
	};

	/**
	 * Uses defineEvent to define multiple events.
	 *
	 * @param {String[]} evts An array of event names to define.
	 * @return {Object} Current instance of EventEmitter for chaining.
	 */
	proto.defineEvents = function defineEvents(evts) {
		for (var i = 0; i < evts.length; i += 1) {
			this.defineEvent(evts[i]);
		}
		return this;
	};

	/**
	 * Removes a listener function from the specified event.
	 * When passed a regular expression as the event name, it will remove the listener from all events that match it.
	 *
	 * @param {String|RegExp} evt Name of the event to remove the listener from.
	 * @param {Function} listener Method to remove from the event.
	 * @return {Object} Current instance of EventEmitter for chaining.
	 */
	proto.removeListener = function removeListener(evt, listener) {
		var listeners = this.getListenersAsObject(evt);
		var index;
		var key;

		for (key in listeners) {
			if (listeners.hasOwnProperty(key)) {
				index = indexOfListener(listeners[key], listener);

				if (index !== -1) {
					listeners[key].splice(index, 1);
				}
			}
		}

		return this;
	};

	/**
	 * Alias of removeListener
	 */
	proto.off = alias('removeListener');

	/**
	 * Adds listeners in bulk using the manipulateListeners method.
	 * If you pass an object as the second argument you can add to multiple events at once. The object should contain key value pairs of events and listeners or listener arrays. You can also pass it an event name and an array of listeners to be added.
	 * You can also pass it a regular expression to add the array of listeners to all events that match it.
	 * Yeah, this function does quite a bit. That's probably a bad thing.
	 *
	 * @param {String|Object|RegExp} evt An event name if you will pass an array of listeners next. An object if you wish to add to multiple events at once.
	 * @param {Function[]} [listeners] An optional array of listener functions to add.
	 * @return {Object} Current instance of EventEmitter for chaining.
	 */
	proto.addListeners = function addListeners(evt, listeners) {
		// Pass through to manipulateListeners
		return this.manipulateListeners(false, evt, listeners);
	};

	/**
	 * Removes listeners in bulk using the manipulateListeners method.
	 * If you pass an object as the second argument you can remove from multiple events at once. The object should contain key value pairs of events and listeners or listener arrays.
	 * You can also pass it an event name and an array of listeners to be removed.
	 * You can also pass it a regular expression to remove the listeners from all events that match it.
	 *
	 * @param {String|Object|RegExp} evt An event name if you will pass an array of listeners next. An object if you wish to remove from multiple events at once.
	 * @param {Function[]} [listeners] An optional array of listener functions to remove.
	 * @return {Object} Current instance of EventEmitter for chaining.
	 */
	proto.removeListeners = function removeListeners(evt, listeners) {
		// Pass through to manipulateListeners
		return this.manipulateListeners(true, evt, listeners);
	};

	/**
	 * Edits listeners in bulk. The addListeners and removeListeners methods both use this to do their job. You should really use those instead, this is a little lower level.
	 * The first argument will determine if the listeners are removed (true) or added (false).
	 * If you pass an object as the second argument you can add/remove from multiple events at once. The object should contain key value pairs of events and listeners or listener arrays.
	 * You can also pass it an event name and an array of listeners to be added/removed.
	 * You can also pass it a regular expression to manipulate the listeners of all events that match it.
	 *
	 * @param {Boolean} remove True if you want to remove listeners, false if you want to add.
	 * @param {String|Object|RegExp} evt An event name if you will pass an array of listeners next. An object if you wish to add/remove from multiple events at once.
	 * @param {Function[]} [listeners] An optional array of listener functions to add/remove.
	 * @return {Object} Current instance of EventEmitter for chaining.
	 */
	proto.manipulateListeners = function manipulateListeners(remove, evt, listeners) {
		var i;
		var value;
		var single = remove ? this.removeListener : this.addListener;
		var multiple = remove ? this.removeListeners : this.addListeners;

		// If evt is an object then pass each of its properties to this method
		if (typeof evt === 'object' && !(evt instanceof RegExp)) {
			for (i in evt) {
				if (evt.hasOwnProperty(i) && (value = evt[i])) {
					// Pass the single listener straight through to the singular method
					if (typeof value === 'function') {
						single.call(this, i, value);
					}
					else {
						// Otherwise pass back to the multiple function
						multiple.call(this, i, value);
					}
				}
			}
		}
		else {
			// So evt must be a string
			// And listeners must be an array of listeners
			// Loop over it and pass each one to the multiple method
			i = listeners.length;
			while (i--) {
				single.call(this, evt, listeners[i]);
			}
		}

		return this;
	};

	/**
	 * Removes all listeners from a specified event.
	 * If you do not specify an event then all listeners will be removed.
	 * That means every event will be emptied.
	 * You can also pass a regex to remove all events that match it.
	 *
	 * @param {String|RegExp} [evt] Optional name of the event to remove all listeners for. Will remove from every event if not passed.
	 * @return {Object} Current instance of EventEmitter for chaining.
	 */
	proto.removeEvent = function removeEvent(evt) {
		var type = typeof evt;
		var events = this._getEvents();
		var key;

		// Remove different things depending on the state of evt
		if (type === 'string') {
			// Remove all listeners for the specified event
			delete events[evt];
		}
		else if (evt instanceof RegExp) {
			// Remove all events matching the regex.
			for (key in events) {
				if (events.hasOwnProperty(key) && evt.test(key)) {
					delete events[key];
				}
			}
		}
		else {
			// Remove all listeners in all events
			delete this._events;
		}

		return this;
	};

	/**
	 * Alias of removeEvent.
	 *
	 * Added to mirror the node API.
	 */
	proto.removeAllListeners = alias('removeEvent');

	/**
	 * Emits an event of your choice.
	 * When emitted, every listener attached to that event will be executed.
	 * If you pass the optional argument array then those arguments will be passed to every listener upon execution.
	 * Because it uses `apply`, your array of arguments will be passed as if you wrote them out separately.
	 * So they will not arrive within the array on the other side, they will be separate.
	 * You can also pass a regular expression to emit to all events that match it.
	 *
	 * @param {String|RegExp} evt Name of the event to emit and execute listeners for.
	 * @param {Array} [args] Optional array of arguments to be passed to each listener.
	 * @return {Object} Current instance of EventEmitter for chaining.
	 */
	proto.emitEvent = function emitEvent(evt, args) {
		var listenersMap = this.getListenersAsObject(evt);
		var listeners;
		var listener;
		var i;
		var key;
		var response;

		for (key in listenersMap) {
			if (listenersMap.hasOwnProperty(key)) {
				listeners = listenersMap[key].slice(0);
				i = listeners.length;

				while (i--) {
					// If the listener returns true then it shall be removed from the event
					// The function is executed either with a basic call or an apply if there is an args array
					listener = listeners[i];

					if (listener.once === true) {
						this.removeListener(evt, listener.listener);
					}

					response = listener.listener.apply(this, args || []);

					if (response === this._getOnceReturnValue()) {
						this.removeListener(evt, listener.listener);
					}
				}
			}
		}

		return this;
	};

	/**
	 * Alias of emitEvent
	 */
	proto.trigger = alias('emitEvent');

	/**
	 * Subtly different from emitEvent in that it will pass its arguments on to the listeners, as opposed to taking a single array of arguments to pass on.
	 * As with emitEvent, you can pass a regex in place of the event name to emit to all events that match it.
	 *
	 * @param {String|RegExp} evt Name of the event to emit and execute listeners for.
	 * @param {...*} Optional additional arguments to be passed to each listener.
	 * @return {Object} Current instance of EventEmitter for chaining.
	 */
	proto.emit = function emit(evt) {
		var args = Array.prototype.slice.call(arguments, 1);
		return this.emitEvent(evt, args);
	};

	/**
	 * Sets the current value to check against when executing listeners. If a
	 * listeners return value matches the one set here then it will be removed
	 * after execution. This value defaults to true.
	 *
	 * @param {*} value The new value to check for when executing listeners.
	 * @return {Object} Current instance of EventEmitter for chaining.
	 */
	proto.setOnceReturnValue = function setOnceReturnValue(value) {
		this._onceReturnValue = value;
		return this;
	};

	/**
	 * Fetches the current value to check against when executing listeners. If
	 * the listeners return value matches this one then it should be removed
	 * automatically. It will return true by default.
	 *
	 * @return {*|Boolean} The current value to check for or the default, true.
	 * @api private
	 */
	proto._getOnceReturnValue = function _getOnceReturnValue() {
		if (this.hasOwnProperty('_onceReturnValue')) {
			return this._onceReturnValue;
		}
		else {
			return true;
		}
	};

	/**
	 * Fetches the events object and creates one if required.
	 *
	 * @return {Object} The events storage object.
	 * @api private
	 */
	proto._getEvents = function _getEvents() {
		return this._events || (this._events = {});
	};

	/**
	 * Reverts the global {@link EventEmitter} to its previous value and returns a reference to this version.
	 *
	 * @return {Function} Non conflicting EventEmitter class.
	 */
	EventEmitter.noConflict = function noConflict() {
		exports.EventEmitter = originalGlobalValue;
		return EventEmitter;
	};

	// Expose the class either via AMD, CommonJS or the global object
	if (typeof define === 'function' && define.amd) {
		define(function () {
			return EventEmitter;
		});
	}
	else if (typeof module === 'object' && module.exports){
		module.exports = EventEmitter;
	}
	else {
		exports.EventEmitter = EventEmitter;
	}
}.call(this));
},{}],18:[function(require,module,exports){
var m = (function app(window, undefined) {
	var OBJECT = "[object Object]", ARRAY = "[object Array]", STRING = "[object String]", FUNCTION = "function";
	var type = {}.toString;
	var parser = /(?:(^|#|\.)([^#\.\[\]]+))|(\[.+?\])/g, attrParser = /\[(.+?)(?:=("|'|)(.*?)\2)?\]/;
	var voidElements = /^(AREA|BASE|BR|COL|COMMAND|EMBED|HR|IMG|INPUT|KEYGEN|LINK|META|PARAM|SOURCE|TRACK|WBR)$/;
	var noop = function() {}

	// caching commonly used variables
	var $document, $location, $requestAnimationFrame, $cancelAnimationFrame;

	// self invoking function needed because of the way mocks work
	function initialize(window){
		$document = window.document;
		$location = window.location;
		$cancelAnimationFrame = window.cancelAnimationFrame || window.clearTimeout;
		$requestAnimationFrame = window.requestAnimationFrame || window.setTimeout;
	}

	initialize(window);


	/**
	 * @typedef {String} Tag
	 * A string that looks like -> div.classname#id[param=one][param2=two]
	 * Which describes a DOM node
	 */

	/**
	 *
	 * @param {Tag} The DOM node tag
	 * @param {Object=[]} optional key-value pairs to be mapped to DOM attrs
	 * @param {...mNode=[]} Zero or more Mithril child nodes. Can be an array, or splat (optional)
	 *
	 */
	function m() {
		var args = [].slice.call(arguments);
		var hasAttrs = args[1] != null && type.call(args[1]) === OBJECT && !("tag" in args[1] || "view" in args[1]) && !("subtree" in args[1]);
		var attrs = hasAttrs ? args[1] : {};
		var classAttrName = "class" in attrs ? "class" : "className";
		var cell = {tag: "div", attrs: {}};
		var match, classes = [];
		if (type.call(args[0]) != STRING) throw new Error("selector in m(selector, attrs, children) should be a string")
		while (match = parser.exec(args[0])) {
			if (match[1] === "" && match[2]) cell.tag = match[2];
			else if (match[1] === "#") cell.attrs.id = match[2];
			else if (match[1] === ".") classes.push(match[2]);
			else if (match[3][0] === "[") {
				var pair = attrParser.exec(match[3]);
				cell.attrs[pair[1]] = pair[3] || (pair[2] ? "" :true)
			}
		}

		var children = hasAttrs ? args.slice(2) : args.slice(1);
		if (children.length === 1 && type.call(children[0]) === ARRAY) {
			cell.children = children[0]
		}
		else {
			cell.children = children
		}
		
		for (var attrName in attrs) {
			if (attrs.hasOwnProperty(attrName)) {
				if (attrName === classAttrName && attrs[attrName] != null && attrs[attrName] !== "") {
					classes.push(attrs[attrName])
					cell.attrs[attrName] = "" //create key in correct iteration order
				}
				else cell.attrs[attrName] = attrs[attrName]
			}
		}
		if (classes.length > 0) cell.attrs[classAttrName] = classes.join(" ");
		
		return cell
	}
	function build(parentElement, parentTag, parentCache, parentIndex, data, cached, shouldReattach, index, editable, namespace, configs) {
		//`build` is a recursive function that manages creation/diffing/removal of DOM elements based on comparison between `data` and `cached`
		//the diff algorithm can be summarized as this:
		//1 - compare `data` and `cached`
		//2 - if they are different, copy `data` to `cached` and update the DOM based on what the difference is
		//3 - recursively apply this algorithm for every array and for the children of every virtual element

		//the `cached` data structure is essentially the same as the previous redraw's `data` data structure, with a few additions:
		//- `cached` always has a property called `nodes`, which is a list of DOM elements that correspond to the data represented by the respective virtual element
		//- in order to support attaching `nodes` as a property of `cached`, `cached` is *always* a non-primitive object, i.e. if the data was a string, then cached is a String instance. If data was `null` or `undefined`, cached is `new String("")`
		//- `cached also has a `configContext` property, which is the state storage object exposed by config(element, isInitialized, context)
		//- when `cached` is an Object, it represents a virtual element; when it's an Array, it represents a list of elements; when it's a String, Number or Boolean, it represents a text node

		//`parentElement` is a DOM element used for W3C DOM API calls
		//`parentTag` is only used for handling a corner case for textarea values
		//`parentCache` is used to remove nodes in some multi-node cases
		//`parentIndex` and `index` are used to figure out the offset of nodes. They're artifacts from before arrays started being flattened and are likely refactorable
		//`data` and `cached` are, respectively, the new and old nodes being diffed
		//`shouldReattach` is a flag indicating whether a parent node was recreated (if so, and if this node is reused, then this node must reattach itself to the new parent)
		//`editable` is a flag that indicates whether an ancestor is contenteditable
		//`namespace` indicates the closest HTML namespace as it cascades down from an ancestor
		//`configs` is a list of config functions to run after the topmost `build` call finishes running

		//there's logic that relies on the assumption that null and undefined data are equivalent to empty strings
		//- this prevents lifecycle surprises from procedural helpers that mix implicit and explicit return statements (e.g. function foo() {if (cond) return m("div")}
		//- it simplifies diffing code
		//data.toString() might throw or return null if data is the return value of Console.log in Firefox (behavior depends on version)
		try {if (data == null || data.toString() == null) data = "";} catch (e) {data = ""}
		if (data.subtree === "retain") return cached;
		var cachedType = type.call(cached), dataType = type.call(data);
		if (cached == null || cachedType !== dataType) {
			if (cached != null) {
				if (parentCache && parentCache.nodes) {
					var offset = index - parentIndex;
					var end = offset + (dataType === ARRAY ? data : cached.nodes).length;
					clear(parentCache.nodes.slice(offset, end), parentCache.slice(offset, end))
				}
				else if (cached.nodes) clear(cached.nodes, cached)
			}
			cached = new data.constructor;
			if (cached.tag) cached = {}; //if constructor creates a virtual dom element, use a blank object as the base cached node instead of copying the virtual el (#277)
			cached.nodes = []
		}

		if (dataType === ARRAY) {
			//recursively flatten array
			for (var i = 0, len = data.length; i < len; i++) {
				if (type.call(data[i]) === ARRAY) {
					data = data.concat.apply([], data);
					i-- //check current index again and flatten until there are no more nested arrays at that index
					len = data.length
				}
			}
			
			var nodes = [], intact = cached.length === data.length, subArrayCount = 0;

			//keys algorithm: sort elements without recreating them if keys are present
			//1) create a map of all existing keys, and mark all for deletion
			//2) add new keys to map and mark them for addition
			//3) if key exists in new list, change action from deletion to a move
			//4) for each key, handle its corresponding action as marked in previous steps
			var DELETION = 1, INSERTION = 2 , MOVE = 3;
			var existing = {}, shouldMaintainIdentities = false;
			for (var i = 0; i < cached.length; i++) {
				if (cached[i] && cached[i].attrs && cached[i].attrs.key != null) {
					shouldMaintainIdentities = true;
					existing[cached[i].attrs.key] = {action: DELETION, index: i}
				}
			}
			
			var guid = 0
			for (var i = 0, len = data.length; i < len; i++) {
				if (data[i] && data[i].attrs && data[i].attrs.key != null) {
					for (var j = 0, len = data.length; j < len; j++) {
						if (data[j] && data[j].attrs && data[j].attrs.key == null) data[j].attrs.key = "__mithril__" + guid++
					}
					break
				}
			}
			
			if (shouldMaintainIdentities) {
				var keysDiffer = false
				if (data.length != cached.length) keysDiffer = true
				else for (var i = 0, cachedCell, dataCell; cachedCell = cached[i], dataCell = data[i]; i++) {
					if (cachedCell.attrs && dataCell.attrs && cachedCell.attrs.key != dataCell.attrs.key) {
						keysDiffer = true
						break
					}
				}
				
				if (keysDiffer) {
					for (var i = 0, len = data.length; i < len; i++) {
						if (data[i] && data[i].attrs) {
							if (data[i].attrs.key != null) {
								var key = data[i].attrs.key;
								if (!existing[key]) existing[key] = {action: INSERTION, index: i};
								else existing[key] = {
									action: MOVE,
									index: i,
									from: existing[key].index,
									element: cached.nodes[existing[key].index] || $document.createElement("div")
								}
							}
						}
					}
					var actions = []
					for (var prop in existing) actions.push(existing[prop])
					var changes = actions.sort(sortChanges);
					var newCached = new Array(cached.length)
					newCached.nodes = cached.nodes.slice()

					for (var i = 0, change; change = changes[i]; i++) {
						if (change.action === DELETION) {
							clear(cached[change.index].nodes, cached[change.index]);
							newCached.splice(change.index, 1)
						}
						if (change.action === INSERTION) {
							var dummy = $document.createElement("div");
							dummy.key = data[change.index].attrs.key;
							parentElement.insertBefore(dummy, parentElement.childNodes[change.index] || null);
							newCached.splice(change.index, 0, {attrs: {key: data[change.index].attrs.key}, nodes: [dummy]})
							newCached.nodes[change.index] = dummy
						}

						if (change.action === MOVE) {
							if (parentElement.childNodes[change.index] !== change.element && change.element !== null) {
								parentElement.insertBefore(change.element, parentElement.childNodes[change.index] || null)
							}
							newCached[change.index] = cached[change.from]
							newCached.nodes[change.index] = change.element
						}
					}
					cached = newCached;
				}
			}
			//end key algorithm

			for (var i = 0, cacheCount = 0, len = data.length; i < len; i++) {
				//diff each item in the array
				var item = build(parentElement, parentTag, cached, index, data[i], cached[cacheCount], shouldReattach, index + subArrayCount || subArrayCount, editable, namespace, configs);
				if (item === undefined) continue;
				if (!item.nodes.intact) intact = false;
				if (item.$trusted) {
					//fix offset of next element if item was a trusted string w/ more than one html element
					//the first clause in the regexp matches elements
					//the second clause (after the pipe) matches text nodes
					subArrayCount += (item.match(/<[^\/]|\>\s*[^<]/g) || [0]).length
				}
				else subArrayCount += type.call(item) === ARRAY ? item.length : 1;
				cached[cacheCount++] = item
			}
			if (!intact) {
				//diff the array itself
				
				//update the list of DOM nodes by collecting the nodes from each item
				for (var i = 0, len = data.length; i < len; i++) {
					if (cached[i] != null) nodes.push.apply(nodes, cached[i].nodes)
				}
				//remove items from the end of the array if the new array is shorter than the old one
				//if errors ever happen here, the issue is most likely a bug in the construction of the `cached` data structure somewhere earlier in the program
				for (var i = 0, node; node = cached.nodes[i]; i++) {
					if (node.parentNode != null && nodes.indexOf(node) < 0) clear([node], [cached[i]])
				}
				if (data.length < cached.length) cached.length = data.length;
				cached.nodes = nodes
			}
		}
		else if (data != null && dataType === OBJECT) {
			var views = [], controllers = []
			while (data.view) {
				var view = data.view.$original || data.view
				var controllerIndex = m.redraw.strategy() == "diff" && cached.views ? cached.views.indexOf(view) : -1
				var controller = controllerIndex > -1 ? cached.controllers[controllerIndex] : new (data.controller || noop)
				var key = data && data.attrs && data.attrs.key
				data = pendingRequests == 0 || (cached && cached.controllers && cached.controllers.indexOf(controller) > -1) ? data.view(controller) : {tag: "placeholder"}
				if (data.subtree === "retain") return cached;
				if (key) {
					if (!data.attrs) data.attrs = {}
					data.attrs.key = key
				}
				if (controller.onunload) unloaders.push({controller: controller, handler: controller.onunload})
				views.push(view)
				controllers.push(controller)
			}
			if (!data.tag && controllers.length) throw new Error("Component template must return a virtual element, not an array, string, etc.")
			if (!data.attrs) data.attrs = {};
			if (!cached.attrs) cached.attrs = {};

			var dataAttrKeys = Object.keys(data.attrs)
			var hasKeys = dataAttrKeys.length > ("key" in data.attrs ? 1 : 0)
			//if an element is different enough from the one in cache, recreate it
			if (data.tag != cached.tag || dataAttrKeys.sort().join() != Object.keys(cached.attrs).sort().join() || data.attrs.id != cached.attrs.id || data.attrs.key != cached.attrs.key || (m.redraw.strategy() == "all" && (!cached.configContext || cached.configContext.retain !== true)) || (m.redraw.strategy() == "diff" && cached.configContext && cached.configContext.retain === false)) {
				if (cached.nodes.length) clear(cached.nodes);
				if (cached.configContext && typeof cached.configContext.onunload === FUNCTION) cached.configContext.onunload()
				if (cached.controllers) {
					for (var i = 0, controller; controller = cached.controllers[i]; i++) {
						if (typeof controller.onunload === FUNCTION) controller.onunload({preventDefault: noop})
					}
				}
			}
			if (type.call(data.tag) != STRING) return;

			var node, isNew = cached.nodes.length === 0;
			if (data.attrs.xmlns) namespace = data.attrs.xmlns;
			else if (data.tag === "svg") namespace = "http://www.w3.org/2000/svg";
			else if (data.tag === "math") namespace = "http://www.w3.org/1998/Math/MathML";
			
			if (isNew) {
				if (data.attrs.is) node = namespace === undefined ? $document.createElement(data.tag, data.attrs.is) : $document.createElementNS(namespace, data.tag, data.attrs.is);
				else node = namespace === undefined ? $document.createElement(data.tag) : $document.createElementNS(namespace, data.tag);
				cached = {
					tag: data.tag,
					//set attributes first, then create children
					attrs: hasKeys ? setAttributes(node, data.tag, data.attrs, {}, namespace) : data.attrs,
					children: data.children != null && data.children.length > 0 ?
						build(node, data.tag, undefined, undefined, data.children, cached.children, true, 0, data.attrs.contenteditable ? node : editable, namespace, configs) :
						data.children,
					nodes: [node]
				};
				if (controllers.length) {
					cached.views = views
					cached.controllers = controllers
					for (var i = 0, controller; controller = controllers[i]; i++) {
						if (controller.onunload && controller.onunload.$old) controller.onunload = controller.onunload.$old
						if (pendingRequests && controller.onunload) {
							var onunload = controller.onunload
							controller.onunload = noop
							controller.onunload.$old = onunload
						}
					}
				}
				
				if (cached.children && !cached.children.nodes) cached.children.nodes = [];
				//edge case: setting value on <select> doesn't work before children exist, so set it again after children have been created
				if (data.tag === "select" && "value" in data.attrs) setAttributes(node, data.tag, {value: data.attrs.value}, {}, namespace);
				parentElement.insertBefore(node, parentElement.childNodes[index] || null)
			}
			else {
				node = cached.nodes[0];
				if (hasKeys) setAttributes(node, data.tag, data.attrs, cached.attrs, namespace);
				cached.children = build(node, data.tag, undefined, undefined, data.children, cached.children, false, 0, data.attrs.contenteditable ? node : editable, namespace, configs);
				cached.nodes.intact = true;
				if (controllers.length) {
					cached.views = views
					cached.controllers = controllers
				}
				if (shouldReattach === true && node != null) parentElement.insertBefore(node, parentElement.childNodes[index] || null)
			}
			//schedule configs to be called. They are called after `build` finishes running
			if (typeof data.attrs["config"] === FUNCTION) {
				var context = cached.configContext = cached.configContext || {};

				// bind
				var callback = function(data, args) {
					return function() {
						return data.attrs["config"].apply(data, args)
					}
				};
				configs.push(callback(data, [node, !isNew, context, cached]))
			}
		}
		else if (typeof data != FUNCTION) {
			//handle text nodes
			var nodes;
			if (cached.nodes.length === 0) {
				if (data.$trusted) {
					nodes = injectHTML(parentElement, index, data)
				}
				else {
					nodes = [$document.createTextNode(data)];
					if (!parentElement.nodeName.match(voidElements)) parentElement.insertBefore(nodes[0], parentElement.childNodes[index] || null)
				}
				cached = "string number boolean".indexOf(typeof data) > -1 ? new data.constructor(data) : data;
				cached.nodes = nodes
			}
			else if (cached.valueOf() !== data.valueOf() || shouldReattach === true) {
				nodes = cached.nodes;
				if (!editable || editable !== $document.activeElement) {
					if (data.$trusted) {
						clear(nodes, cached);
						nodes = injectHTML(parentElement, index, data)
					}
					else {
						//corner case: replacing the nodeValue of a text node that is a child of a textarea/contenteditable doesn't work
						//we need to update the value property of the parent textarea or the innerHTML of the contenteditable element instead
						if (parentTag === "textarea") parentElement.value = data;
						else if (editable) editable.innerHTML = data;
						else {
							if (nodes[0].nodeType === 1 || nodes.length > 1) { //was a trusted string
								clear(cached.nodes, cached);
								nodes = [$document.createTextNode(data)]
							}
							parentElement.insertBefore(nodes[0], parentElement.childNodes[index] || null);
							nodes[0].nodeValue = data
						}
					}
				}
				cached = new data.constructor(data);
				cached.nodes = nodes
			}
			else cached.nodes.intact = true
		}

		return cached
	}
	function sortChanges(a, b) {return a.action - b.action || a.index - b.index}
	function setAttributes(node, tag, dataAttrs, cachedAttrs, namespace) {
		for (var attrName in dataAttrs) {
			var dataAttr = dataAttrs[attrName];
			var cachedAttr = cachedAttrs[attrName];
			if (!(attrName in cachedAttrs) || (cachedAttr !== dataAttr)) {
				cachedAttrs[attrName] = dataAttr;
				try {
					//`config` isn't a real attributes, so ignore it
					if (attrName === "config" || attrName == "key") continue;
					//hook event handlers to the auto-redrawing system
					else if (typeof dataAttr === FUNCTION && attrName.indexOf("on") === 0) {
						node[attrName] = autoredraw(dataAttr, node)
					}
					//handle `style: {...}`
					else if (attrName === "style" && dataAttr != null && type.call(dataAttr) === OBJECT) {
						for (var rule in dataAttr) {
							if (cachedAttr == null || cachedAttr[rule] !== dataAttr[rule]) node.style[rule] = dataAttr[rule]
						}
						for (var rule in cachedAttr) {
							if (!(rule in dataAttr)) node.style[rule] = ""
						}
					}
					//handle SVG
					else if (namespace != null) {
						if (attrName === "href") node.setAttributeNS("http://www.w3.org/1999/xlink", "href", dataAttr);
						else if (attrName === "className") node.setAttribute("class", dataAttr);
						else node.setAttribute(attrName, dataAttr)
					}
					//handle cases that are properties (but ignore cases where we should use setAttribute instead)
					//- list and form are typically used as strings, but are DOM element references in js
					//- when using CSS selectors (e.g. `m("[style='']")`), style is used as a string, but it's an object in js
					else if (attrName in node && !(attrName === "list" || attrName === "style" || attrName === "form" || attrName === "type" || attrName === "width" || attrName === "height")) {
						//#348 don't set the value if not needed otherwise cursor placement breaks in Chrome
						if (tag !== "input" || node[attrName] !== dataAttr) node[attrName] = dataAttr
					}
					else node.setAttribute(attrName, dataAttr)
				}
				catch (e) {
					//swallow IE's invalid argument errors to mimic HTML's fallback-to-doing-nothing-on-invalid-attributes behavior
					if (e.message.indexOf("Invalid argument") < 0) throw e
				}
			}
			//#348 dataAttr may not be a string, so use loose comparison (double equal) instead of strict (triple equal)
			else if (attrName === "value" && tag === "input" && node.value != dataAttr) {
				node.value = dataAttr
			}
		}
		return cachedAttrs
	}
	function clear(nodes, cached) {
		for (var i = nodes.length - 1; i > -1; i--) {
			if (nodes[i] && nodes[i].parentNode) {
				try {nodes[i].parentNode.removeChild(nodes[i])}
				catch (e) {} //ignore if this fails due to order of events (see http://stackoverflow.com/questions/21926083/failed-to-execute-removechild-on-node)
				cached = [].concat(cached);
				if (cached[i]) unload(cached[i])
			}
		}
		if (nodes.length != 0) nodes.length = 0
	}
	function unload(cached) {
		if (cached.configContext && typeof cached.configContext.onunload === FUNCTION) {
			cached.configContext.onunload();
			cached.configContext.onunload = null
		}
		if (cached.controllers) {
			for (var i = 0, controller; controller = cached.controllers[i]; i++) {
				if (typeof controller.onunload === FUNCTION) controller.onunload({preventDefault: noop});
			}
		}
		if (cached.children) {
			if (type.call(cached.children) === ARRAY) {
				for (var i = 0, child; child = cached.children[i]; i++) unload(child)
			}
			else if (cached.children.tag) unload(cached.children)
		}
	}
	function injectHTML(parentElement, index, data) {
		var nextSibling = parentElement.childNodes[index];
		if (nextSibling) {
			var isElement = nextSibling.nodeType != 1;
			var placeholder = $document.createElement("span");
			if (isElement) {
				parentElement.insertBefore(placeholder, nextSibling || null);
				placeholder.insertAdjacentHTML("beforebegin", data);
				parentElement.removeChild(placeholder)
			}
			else nextSibling.insertAdjacentHTML("beforebegin", data)
		}
		else parentElement.insertAdjacentHTML("beforeend", data);
		var nodes = [];
		while (parentElement.childNodes[index] !== nextSibling) {
			nodes.push(parentElement.childNodes[index]);
			index++
		}
		return nodes
	}
	function autoredraw(callback, object) {
		return function(e) {
			e = e || event;
			m.redraw.strategy("diff");
			m.startComputation();
			try {return callback.call(object, e)}
			finally {
				endFirstComputation()
			}
		}
	}

	var html;
	var documentNode = {
		appendChild: function(node) {
			if (html === undefined) html = $document.createElement("html");
			if ($document.documentElement && $document.documentElement !== node) {
				$document.replaceChild(node, $document.documentElement)
			}
			else $document.appendChild(node);
			this.childNodes = $document.childNodes
		},
		insertBefore: function(node) {
			this.appendChild(node)
		},
		childNodes: []
	};
	var nodeCache = [], cellCache = {};
	m.render = function(root, cell, forceRecreation) {
		var configs = [];
		if (!root) throw new Error("Ensure the DOM element being passed to m.route/m.mount/m.render is not undefined.");
		var id = getCellCacheKey(root);
		var isDocumentRoot = root === $document;
		var node = isDocumentRoot || root === $document.documentElement ? documentNode : root;
		if (isDocumentRoot && cell.tag != "html") cell = {tag: "html", attrs: {}, children: cell};
		if (cellCache[id] === undefined) clear(node.childNodes);
		if (forceRecreation === true) reset(root);
		cellCache[id] = build(node, null, undefined, undefined, cell, cellCache[id], false, 0, null, undefined, configs);
		for (var i = 0, len = configs.length; i < len; i++) configs[i]()
	};
	function getCellCacheKey(element) {
		var index = nodeCache.indexOf(element);
		return index < 0 ? nodeCache.push(element) - 1 : index
	}

	m.trust = function(value) {
		value = new String(value);
		value.$trusted = true;
		return value
	};

	function gettersetter(store) {
		var prop = function() {
			if (arguments.length) store = arguments[0];
			return store
		};

		prop.toJSON = function() {
			return store
		};

		return prop
	}

	m.prop = function (store) {
		//note: using non-strict equality check here because we're checking if store is null OR undefined
		if (((store != null && type.call(store) === OBJECT) || typeof store === FUNCTION) && typeof store.then === FUNCTION) {
			return propify(store)
		}

		return gettersetter(store)
	};

	var roots = [], components = [], controllers = [], lastRedrawId = null, lastRedrawCallTime = 0, computePreRedrawHook = null, computePostRedrawHook = null, prevented = false, topComponent, unloaders = [];
	var FRAME_BUDGET = 16; //60 frames per second = 1 call per 16 ms
	function parameterize(component, args) {
		var controller = function() {
			return (component.controller || noop).apply(this, args) || this
		}
		var view = function(ctrl) {
			if (arguments.length > 1) args = args.concat([].slice.call(arguments, 1))
			return component.view.apply(component, args ? [ctrl].concat(args) : [ctrl])
		}
		view.$original = component.view
		var output = {controller: controller, view: view}
		if (args[0] && args[0].key != null) output.attrs = {key: args[0].key}
		return output
	}
	m.component = function(component) {
		return parameterize(component, [].slice.call(arguments, 1))
	}
	m.mount = m.module = function(root, component) {
		if (!root) throw new Error("Please ensure the DOM element exists before rendering a template into it.");
		var index = roots.indexOf(root);
		if (index < 0) index = roots.length;
		
		var isPrevented = false;
		var event = {preventDefault: function() {
			isPrevented = true;
			computePreRedrawHook = computePostRedrawHook = null;
		}};
		for (var i = 0, unloader; unloader = unloaders[i]; i++) {
			unloader.handler.call(unloader.controller, event)
			unloader.controller.onunload = null
		}
		if (isPrevented) {
			for (var i = 0, unloader; unloader = unloaders[i]; i++) unloader.controller.onunload = unloader.handler
		}
		else unloaders = []
		
		if (controllers[index] && typeof controllers[index].onunload === FUNCTION) {
			controllers[index].onunload(event)
		}
		
		if (!isPrevented) {
			m.redraw.strategy("all");
			m.startComputation();
			roots[index] = root;
			if (arguments.length > 2) component = subcomponent(component, [].slice.call(arguments, 2))
			var currentComponent = topComponent = component = component || {controller: function() {}};
			var constructor = component.controller || noop
			var controller = new constructor;
			//controllers may call m.mount recursively (via m.route redirects, for example)
			//this conditional ensures only the last recursive m.mount call is applied
			if (currentComponent === topComponent) {
				controllers[index] = controller;
				components[index] = component
			}
			endFirstComputation();
			return controllers[index]
		}
	};
	var redrawing = false
	m.redraw = function(force) {
		if (redrawing) return
		redrawing = true
		//lastRedrawId is a positive number if a second redraw is requested before the next animation frame
		//lastRedrawID is null if it's the first redraw and not an event handler
		if (lastRedrawId && force !== true) {
			//when setTimeout: only reschedule redraw if time between now and previous redraw is bigger than a frame, otherwise keep currently scheduled timeout
			//when rAF: always reschedule redraw
			if ($requestAnimationFrame === window.requestAnimationFrame || new Date - lastRedrawCallTime > FRAME_BUDGET) {
				if (lastRedrawId > 0) $cancelAnimationFrame(lastRedrawId);
				lastRedrawId = $requestAnimationFrame(redraw, FRAME_BUDGET)
			}
		}
		else {
			redraw();
			lastRedrawId = $requestAnimationFrame(function() {lastRedrawId = null}, FRAME_BUDGET)
		}
		redrawing = false
	};
	m.redraw.strategy = m.prop();
	function redraw() {
		if (computePreRedrawHook) {
			computePreRedrawHook()
			computePreRedrawHook = null
		}
		for (var i = 0, root; root = roots[i]; i++) {
			if (controllers[i]) {
				var args = components[i].controller && components[i].controller.$$args ? [controllers[i]].concat(components[i].controller.$$args) : [controllers[i]]
				m.render(root, components[i].view ? components[i].view(controllers[i], args) : "")
			}
		}
		//after rendering within a routed context, we need to scroll back to the top, and fetch the document title for history.pushState
		if (computePostRedrawHook) {
			computePostRedrawHook();
			computePostRedrawHook = null
		}
		lastRedrawId = null;
		lastRedrawCallTime = new Date;
		m.redraw.strategy("diff")
	}

	var pendingRequests = 0;
	m.startComputation = function() {pendingRequests++};
	m.endComputation = function() {
		pendingRequests = Math.max(pendingRequests - 1, 0);
		if (pendingRequests === 0) m.redraw()
	};
	var endFirstComputation = function() {
		if (m.redraw.strategy() == "none") {
			pendingRequests--
			m.redraw.strategy("diff")
		}
		else m.endComputation();
	}

	m.withAttr = function(prop, withAttrCallback) {
		return function(e) {
			e = e || event;
			var currentTarget = e.currentTarget || this;
			withAttrCallback(prop in currentTarget ? currentTarget[prop] : currentTarget.getAttribute(prop))
		}
	};

	//routing
	var modes = {pathname: "", hash: "#", search: "?"};
	var redirect = noop, routeParams, currentRoute, isDefaultRoute = false;
	m.route = function() {
		//m.route()
		if (arguments.length === 0) return currentRoute;
		//m.route(el, defaultRoute, routes)
		else if (arguments.length === 3 && type.call(arguments[1]) === STRING) {
			var root = arguments[0], defaultRoute = arguments[1], router = arguments[2];
			redirect = function(source) {
				var path = currentRoute = normalizeRoute(source);
				if (!routeByValue(root, router, path)) {
					if (isDefaultRoute) throw new Error("Ensure the default route matches one of the routes defined in m.route")
					isDefaultRoute = true
					m.route(defaultRoute, true)
					isDefaultRoute = false
				}
			};
			var listener = m.route.mode === "hash" ? "onhashchange" : "onpopstate";
			window[listener] = function() {
				var path = $location[m.route.mode]
				if (m.route.mode === "pathname") path += $location.search
				if (currentRoute != normalizeRoute(path)) {
					redirect(path)
				}
			};
			computePreRedrawHook = setScroll;
			window[listener]()
		}
		//config: m.route
		else if (arguments[0].addEventListener || arguments[0].attachEvent) {
			var element = arguments[0];
			var isInitialized = arguments[1];
			var context = arguments[2];
			var vdom = arguments[3];
			element.href = (m.route.mode !== 'pathname' ? $location.pathname : '') + modes[m.route.mode] + vdom.attrs.href;
			if (element.addEventListener) {
				element.removeEventListener("click", routeUnobtrusive);
				element.addEventListener("click", routeUnobtrusive)
			}
			else {
				element.detachEvent("onclick", routeUnobtrusive);
				element.attachEvent("onclick", routeUnobtrusive)
			}
		}
		//m.route(route, params, shouldReplaceHistoryEntry)
		else if (type.call(arguments[0]) === STRING) {
			var oldRoute = currentRoute;
			currentRoute = arguments[0];
			var args = arguments[1] || {}
			var queryIndex = currentRoute.indexOf("?")
			var params = queryIndex > -1 ? parseQueryString(currentRoute.slice(queryIndex + 1)) : {}
			for (var i in args) params[i] = args[i]
			var querystring = buildQueryString(params)
			var currentPath = queryIndex > -1 ? currentRoute.slice(0, queryIndex) : currentRoute
			if (querystring) currentRoute = currentPath + (currentPath.indexOf("?") === -1 ? "?" : "&") + querystring;

			var shouldReplaceHistoryEntry = (arguments.length === 3 ? arguments[2] : arguments[1]) === true || oldRoute === arguments[0];

			if (window.history.pushState) {
				computePreRedrawHook = setScroll
				computePostRedrawHook = function() {
					window.history[shouldReplaceHistoryEntry ? "replaceState" : "pushState"](null, $document.title, modes[m.route.mode] + currentRoute);
				};
				redirect(modes[m.route.mode] + currentRoute)
			}
			else {
				$location[m.route.mode] = currentRoute
				redirect(modes[m.route.mode] + currentRoute)
			}
		}
	};
	m.route.param = function(key) {
		if (!routeParams) throw new Error("You must call m.route(element, defaultRoute, routes) before calling m.route.param()")
		return routeParams[key]
	};
	m.route.mode = "search";
	function normalizeRoute(route) {
		return route.slice(modes[m.route.mode].length)
	}
	function routeByValue(root, router, path) {
		routeParams = {};

		var queryStart = path.indexOf("?");
		if (queryStart !== -1) {
			routeParams = parseQueryString(path.substr(queryStart + 1, path.length));
			path = path.substr(0, queryStart)
		}

		// Get all routes and check if there's
		// an exact match for the current path
		var keys = Object.keys(router);
		var index = keys.indexOf(path);
		if(index !== -1){
			m.mount(root, router[keys [index]]);
			return true;
		}

		for (var route in router) {
			if (route === path) {
				m.mount(root, router[route]);
				return true
			}

			var matcher = new RegExp("^" + route.replace(/:[^\/]+?\.{3}/g, "(.*?)").replace(/:[^\/]+/g, "([^\\/]+)") + "\/?$");

			if (matcher.test(path)) {
				path.replace(matcher, function() {
					var keys = route.match(/:[^\/]+/g) || [];
					var values = [].slice.call(arguments, 1, -2);
					for (var i = 0, len = keys.length; i < len; i++) routeParams[keys[i].replace(/:|\./g, "")] = decodeURIComponent(values[i])
					m.mount(root, router[route])
				});
				return true
			}
		}
	}
	function routeUnobtrusive(e) {
		e = e || event;
		if (e.ctrlKey || e.metaKey || e.which === 2) return;
		if (e.preventDefault) e.preventDefault();
		else e.returnValue = false;
		var currentTarget = e.currentTarget || e.srcElement;
		var args = m.route.mode === "pathname" && currentTarget.search ? parseQueryString(currentTarget.search.slice(1)) : {};
		while (currentTarget && currentTarget.nodeName.toUpperCase() != "A") currentTarget = currentTarget.parentNode
		m.route(currentTarget[m.route.mode].slice(modes[m.route.mode].length), args)
	}
	function setScroll() {
		if (m.route.mode != "hash" && $location.hash) $location.hash = $location.hash;
		else window.scrollTo(0, 0)
	}
	function buildQueryString(object, prefix) {
		var duplicates = {}
		var str = []
		for (var prop in object) {
			var key = prefix ? prefix + "[" + prop + "]" : prop
			var value = object[prop]
			var valueType = type.call(value)
			var pair = (value === null) ? encodeURIComponent(key) :
				valueType === OBJECT ? buildQueryString(value, key) :
				valueType === ARRAY ? value.reduce(function(memo, item) {
					if (!duplicates[key]) duplicates[key] = {}
					if (!duplicates[key][item]) {
						duplicates[key][item] = true
						return memo.concat(encodeURIComponent(key) + "=" + encodeURIComponent(item))
					}
					return memo
				}, []).join("&") :
				encodeURIComponent(key) + "=" + encodeURIComponent(value)
			if (value !== undefined) str.push(pair)
		}
		return str.join("&")
	}
	function parseQueryString(str) {
		if (str.charAt(0) === "?") str = str.substring(1);
		
		var pairs = str.split("&"), params = {};
		for (var i = 0, len = pairs.length; i < len; i++) {
			var pair = pairs[i].split("=");
			var key = decodeURIComponent(pair[0])
			var value = pair.length == 2 ? decodeURIComponent(pair[1]) : null
			if (params[key] != null) {
				if (type.call(params[key]) !== ARRAY) params[key] = [params[key]]
				params[key].push(value)
			}
			else params[key] = value
		}
		return params
	}
	m.route.buildQueryString = buildQueryString
	m.route.parseQueryString = parseQueryString
	
	function reset(root) {
		var cacheKey = getCellCacheKey(root);
		clear(root.childNodes, cellCache[cacheKey]);
		cellCache[cacheKey] = undefined
	}

	m.deferred = function () {
		var deferred = new Deferred();
		deferred.promise = propify(deferred.promise);
		return deferred
	};
	function propify(promise, initialValue) {
		var prop = m.prop(initialValue);
		promise.then(prop);
		prop.then = function(resolve, reject) {
			return propify(promise.then(resolve, reject), initialValue)
		};
		return prop
	}
	//Promiz.mithril.js | Zolmeister | MIT
	//a modified version of Promiz.js, which does not conform to Promises/A+ for two reasons:
	//1) `then` callbacks are called synchronously (because setTimeout is too slow, and the setImmediate polyfill is too big
	//2) throwing subclasses of Error cause the error to be bubbled up instead of triggering rejection (because the spec does not account for the important use case of default browser error handling, i.e. message w/ line number)
	function Deferred(successCallback, failureCallback) {
		var RESOLVING = 1, REJECTING = 2, RESOLVED = 3, REJECTED = 4;
		var self = this, state = 0, promiseValue = 0, next = [];

		self["promise"] = {};

		self["resolve"] = function(value) {
			if (!state) {
				promiseValue = value;
				state = RESOLVING;

				fire()
			}
			return this
		};

		self["reject"] = function(value) {
			if (!state) {
				promiseValue = value;
				state = REJECTING;

				fire()
			}
			return this
		};

		self.promise["then"] = function(successCallback, failureCallback) {
			var deferred = new Deferred(successCallback, failureCallback);
			if (state === RESOLVED) {
				deferred.resolve(promiseValue)
			}
			else if (state === REJECTED) {
				deferred.reject(promiseValue)
			}
			else {
				next.push(deferred)
			}
			return deferred.promise
		};

		function finish(type) {
			state = type || REJECTED;
			next.map(function(deferred) {
				state === RESOLVED && deferred.resolve(promiseValue) || deferred.reject(promiseValue)
			})
		}

		function thennable(then, successCallback, failureCallback, notThennableCallback) {
			if (((promiseValue != null && type.call(promiseValue) === OBJECT) || typeof promiseValue === FUNCTION) && typeof then === FUNCTION) {
				try {
					// count protects against abuse calls from spec checker
					var count = 0;
					then.call(promiseValue, function(value) {
						if (count++) return;
						promiseValue = value;
						successCallback()
					}, function (value) {
						if (count++) return;
						promiseValue = value;
						failureCallback()
					})
				}
				catch (e) {
					m.deferred.onerror(e);
					promiseValue = e;
					failureCallback()
				}
			} else {
				notThennableCallback()
			}
		}

		function fire() {
			// check if it's a thenable
			var then;
			try {
				then = promiseValue && promiseValue.then
			}
			catch (e) {
				m.deferred.onerror(e);
				promiseValue = e;
				state = REJECTING;
				return fire()
			}
			thennable(then, function() {
				state = RESOLVING;
				fire()
			}, function() {
				state = REJECTING;
				fire()
			}, function() {
				try {
					if (state === RESOLVING && typeof successCallback === FUNCTION) {
						promiseValue = successCallback(promiseValue)
					}
					else if (state === REJECTING && typeof failureCallback === "function") {
						promiseValue = failureCallback(promiseValue);
						state = RESOLVING
					}
				}
				catch (e) {
					m.deferred.onerror(e);
					promiseValue = e;
					return finish()
				}

				if (promiseValue === self) {
					promiseValue = TypeError();
					finish()
				}
				else {
					thennable(then, function () {
						finish(RESOLVED)
					}, finish, function () {
						finish(state === RESOLVING && RESOLVED)
					})
				}
			})
		}
	}
	m.deferred.onerror = function(e) {
		if (type.call(e) === "[object Error]" && !e.constructor.toString().match(/ Error/)) throw e
	};

	m.sync = function(args) {
		var method = "resolve";
		function synchronizer(pos, resolved) {
			return function(value) {
				results[pos] = value;
				if (!resolved) method = "reject";
				if (--outstanding === 0) {
					deferred.promise(results);
					deferred[method](results)
				}
				return value
			}
		}

		var deferred = m.deferred();
		var outstanding = args.length;
		var results = new Array(outstanding);
		if (args.length > 0) {
			for (var i = 0; i < args.length; i++) {
				args[i].then(synchronizer(i, true), synchronizer(i, false))
			}
		}
		else deferred.resolve([]);

		return deferred.promise
	};
	function identity(value) {return value}

	function ajax(options) {
		if (options.dataType && options.dataType.toLowerCase() === "jsonp") {
			var callbackKey = "mithril_callback_" + new Date().getTime() + "_" + (Math.round(Math.random() * 1e16)).toString(36);
			var script = $document.createElement("script");

			window[callbackKey] = function(resp) {
				script.parentNode.removeChild(script);
				options.onload({
					type: "load",
					target: {
						responseText: resp
					}
				});
				window[callbackKey] = undefined
			};

			script.onerror = function(e) {
				script.parentNode.removeChild(script);

				options.onerror({
					type: "error",
					target: {
						status: 500,
						responseText: JSON.stringify({error: "Error making jsonp request"})
					}
				});
				window[callbackKey] = undefined;

				return false
			};

			script.onload = function(e) {
				return false
			};

			script.src = options.url
				+ (options.url.indexOf("?") > 0 ? "&" : "?")
				+ (options.callbackKey ? options.callbackKey : "callback")
				+ "=" + callbackKey
				+ "&" + buildQueryString(options.data || {});
			$document.body.appendChild(script)
		}
		else {
			var xhr = new window.XMLHttpRequest;
			xhr.open(options.method, options.url, true, options.user, options.password);
			xhr.onreadystatechange = function() {
				if (xhr.readyState === 4) {
					if (xhr.status >= 200 && xhr.status < 300) options.onload({type: "load", target: xhr});
					else options.onerror({type: "error", target: xhr})
				}
			};
			if (options.serialize === JSON.stringify && options.data && options.method !== "GET") {
				xhr.setRequestHeader("Content-Type", "application/json; charset=utf-8")
			}
			if (options.deserialize === JSON.parse) {
				xhr.setRequestHeader("Accept", "application/json, text/*");
			}
			if (typeof options.config === FUNCTION) {
				var maybeXhr = options.config(xhr, options);
				if (maybeXhr != null) xhr = maybeXhr
			}

			var data = options.method === "GET" || !options.data ? "" : options.data
			if (data && (type.call(data) != STRING && data.constructor != window.FormData)) {
				throw "Request data should be either be a string or FormData. Check the `serialize` option in `m.request`";
			}
			xhr.send(data);
			return xhr
		}
	}
	function bindData(xhrOptions, data, serialize) {
		if (xhrOptions.method === "GET" && xhrOptions.dataType != "jsonp") {
			var prefix = xhrOptions.url.indexOf("?") < 0 ? "?" : "&";
			var querystring = buildQueryString(data);
			xhrOptions.url = xhrOptions.url + (querystring ? prefix + querystring : "")
		}
		else xhrOptions.data = serialize(data);
		return xhrOptions
	}
	function parameterizeUrl(url, data) {
		var tokens = url.match(/:[a-z]\w+/gi);
		if (tokens && data) {
			for (var i = 0; i < tokens.length; i++) {
				var key = tokens[i].slice(1);
				url = url.replace(tokens[i], data[key]);
				delete data[key]
			}
		}
		return url
	}

	m.request = function(xhrOptions) {
		if (xhrOptions.background !== true) m.startComputation();
		var deferred = new Deferred();
		var isJSONP = xhrOptions.dataType && xhrOptions.dataType.toLowerCase() === "jsonp";
		var serialize = xhrOptions.serialize = isJSONP ? identity : xhrOptions.serialize || JSON.stringify;
		var deserialize = xhrOptions.deserialize = isJSONP ? identity : xhrOptions.deserialize || JSON.parse;
		var extract = isJSONP ? function(jsonp) {return jsonp.responseText} : xhrOptions.extract || function(xhr) {
			return xhr.responseText.length === 0 && deserialize === JSON.parse ? null : xhr.responseText
		};
		xhrOptions.method = (xhrOptions.method || 'GET').toUpperCase();
		xhrOptions.url = parameterizeUrl(xhrOptions.url, xhrOptions.data);
		xhrOptions = bindData(xhrOptions, xhrOptions.data, serialize);
		xhrOptions.onload = xhrOptions.onerror = function(e) {
			try {
				e = e || event;
				var unwrap = (e.type === "load" ? xhrOptions.unwrapSuccess : xhrOptions.unwrapError) || identity;
				var response = unwrap(deserialize(extract(e.target, xhrOptions)), e.target);
				if (e.type === "load") {
					if (type.call(response) === ARRAY && xhrOptions.type) {
						for (var i = 0; i < response.length; i++) response[i] = new xhrOptions.type(response[i])
					}
					else if (xhrOptions.type) response = new xhrOptions.type(response)
				}
				deferred[e.type === "load" ? "resolve" : "reject"](response)
			}
			catch (e) {
				m.deferred.onerror(e);
				deferred.reject(e)
			}
			if (xhrOptions.background !== true) m.endComputation()
		};
		ajax(xhrOptions);
		deferred.promise = propify(deferred.promise, xhrOptions.initialValue);
		return deferred.promise
	};

	//testing API
	m.deps = function(mock) {
		initialize(window = mock || window);
		return window;
	};
	//for internal testing only, do not use `m.deps.factory`
	m.deps.factory = app;

	return m
})(typeof window != "undefined" ? window : {});

if (typeof module != "undefined" && module !== null && module.exports) module.exports = m;
else if (typeof define === "function" && define.amd) define(function() {return m});

},{}],19:[function(require,module,exports){
'use strict';

var VOID_TAGS = ['area', 'base', 'br', 'col', 'command', 'embed', 'hr',
	'img', 'input', 'keygen', 'link', 'meta', 'param', 'source', 'track',
	'wbr', '!doctype'];

function isArray(thing) {
	return Object.prototype.toString.call(thing) === '[object Array]';
}

function camelToDash(str) {
	return str.replace(/\W+/g, '-')
		.replace(/([a-z\d])([A-Z])/g, '$1-$2');
}

function removeEmpties(n) {
	return n != '';
}

// shameless stolen from https://github.com/punkave/sanitize-html
function escapeHtml(s, replaceDoubleQuote) {
	if (s === 'undefined') {
		s = '';
	}
	if (typeof(s) !== 'string') {
		s = s + '';
	}
	s =  s.replace(/\&/g, '&amp;').replace(/</g, '&lt;').replace(/\>/g, '&gt;');
	if (replaceDoubleQuote) {
		return s.replace(/\"/g, '&quot;');
	}
	return s;
}

function createAttrString(attrs) {
	if (!attrs || !Object.keys(attrs).length) {
		return '';
	}

	return Object.keys(attrs).map(function(name) {
		var value = attrs[name];
		if (typeof value === 'undefined' || value === null || typeof value === 'function') {
			return;
		}
		if (typeof value === 'boolean') {
			return value ? ' ' + name : '';
		}
		if (name === 'style') {
			if (!value) {
				return;
			}
			var styles = attrs.style;
			if (typeof styles === 'object') {
				styles = Object.keys(styles).map(function(property) {
					return styles[property] != '' ? [camelToDash(property).toLowerCase(), styles[property]].join(':') : '';
				}).filter(removeEmpties).join(';');
			}
			return styles != '' ? ' style="' + escapeHtml(styles, true) + '"' : '';
		}
		return ' ' + escapeHtml(name === 'className' ? 'class' : name) + '="' + escapeHtml(value, true) + '"';
	}).join('');
}

function createChildrenContent(view) {
	if(isArray(view.children) && !view.children.length) {
		return '';
	}

	return render(view.children);
}

function render(view) {
	var type = typeof view;

	if (type === 'string') {
		return escapeHtml(view);
	}

	if(type === 'number' || type === 'boolean') {
		return view;
	}

	if (!view) {
		return '';
	}

	if (isArray(view)) {
		return view.map(render).join('');
	}

	//compontent
	if (view.view) {
		var scope = view.controller ? new view.controller : {};
		var result = render(view.view(scope));
		if (scope.onunload) {
			scope.onunload();
		}
		return result;
	}

	if (view.$trusted) {
		return '' + view;
	}
	var children = createChildrenContent(view);
	if (!children && VOID_TAGS.indexOf(view.tag.toLowerCase()) >= 0) {
		return '<' + view.tag + createAttrString(view.attrs) + '>';
	}
	return [
		'<', view.tag, createAttrString(view.attrs), '>',
		children,
		'</', view.tag, '>',
	].join('');
}

module.exports = render;
},{}]},{},[1]);
