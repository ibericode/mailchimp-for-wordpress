'use strict';

// deps
var EventEmitter = require('wolfy87-eventemitter');
var Form = require('./form.js');

// variables
var events = new EventEmitter();
var forms = [];

// get form by its id
// please note that this will get the FIRST occurence of the form with that ID on the page
function get(formId) {
	formId = parseInt(formId);

	// do we have form for this one already?
	for(var i=0; i<forms.length;i++) {
		if(forms[i].id === formId) {
			return forms[i];
		}
	}

	// try to create from first occurence of this element
	var formElement = document.querySelector('.mc4wp-form-' + formId);
	return createFromElement(formElement,formId);
}

// get form by <form> element (or any input in form)
function getByElement(element) {
	var formElement = element.form || element;

	for(var i=0; i < forms.length; i++) {
		if(forms[i].element === formElement) {
			return forms[i];
		}
	}

	return createFromElement(formElement);
}

// create form object from <form> element
function createFromElement(formElement, id) {
	id = id || parseInt( formElement.getAttribute('data-id') ) || 0;
	var form = new Form(id, formElement);
	forms.push(form);
	return form;
}

function all() {
	return forms;
}

function triggerEvent(eventName, eventArgs) {
	if(eventName === 'submit' || eventName.indexOf('.submit') > 0) {
		// don't spin up new thread for submit event as we want to preventDefault()... 
		events.trigger(eventName, eventArgs);
	} else {
		// process in separate thread to prevent errors from breaking core functionality
		window.setTimeout(function() {
			events.trigger(eventName, eventArgs);
		}, 1);
	}
}

module.exports = {
	"all": all,
	"get": get,
	"getByElement": getByElement,
	"on": events.on.bind(events),
	"trigger": triggerEvent,
	"off": events.off.bind(events)
};

