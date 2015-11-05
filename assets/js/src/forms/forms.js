var forms = function() {
	'use strict';

	// deps
	var EventEmitter = require('../../third-party/event-emitter.js');
	var Form = require('./form.js');

	// variables
	var events = new EventEmitter();
	var formElements = document.querySelectorAll('.mc4wp-form');
	var forms = Array.prototype.map.call(formElements,function(element) {
		var settings = {};
		var form = new Form(element, settings, EventEmitter);

		// map all events to global events
		form.on('submit',function(form,event) {
			events.trigger('submit', [form,event])
		});

		return form;
	});

	// functions
	function get(form_id) {
		return forms.filter(function(form) {
			return form.id === form_id;
		}).pop();
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

	// public API
	return {
		all: all,
		get: get,
		on: on,
		trigger: trigger
	}
};

module.exports = forms();

