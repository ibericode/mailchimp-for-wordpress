var forms = function() {
	'use strict';

	// deps
	var EventEmitter = require('../../third-party/event-emitter.js');
	var Form = require('./form.js');

	// variables
	var events = new EventEmitter();
	var forms = [];

	// get form by its id
	function get(formId) {
		var form = forms.filter(function(form) {
			return form.id == formId;
		}).pop();

		if( form ) {
			return form;
		}

		var formElement = document.querySelector('.mc4wp-form-' + formId);
		return createFromElement(formElement,formId) || null;
	}

	// get form by <form> element (or any input in form)
	function getByElement(element) {
		var formElement = element.form || element;
		var id = parseInt( formElement.getAttribute('data-id') );
		var form = get(id);
		return form || createFromElement(element,id);
	}

	// create form object from <form> element
	function createFromElement(formElement,id) {
		id = id || parseInt( formElement.getAttribute('data-id') );
		var form = new Form(id,formElement,EventEmitter);
		forms.push(form);
		return form;
	}

	function all() {
		return forms;
	}

	function on(event,callback) {
		return events.on(event,callback);
	}

	function trigger(event,args) {
		return events.trigger(event,args);
	}

	function off(event,callback) {
		return events.off(event,callback);
	}

	// public API
	return {
		all: all,
		get: get,
		getByElement: getByElement,
		on: on,
		trigger: trigger,
		off: off
	}
};

module.exports = forms();

