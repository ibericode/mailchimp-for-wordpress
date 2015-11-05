var forms = function() {
	'use strict';

	// deps
	var EventEmitter = require('../../third-party/event-emitter.js');
	var Form = require('./form.js');

	// variables
	var events = new EventEmitter();
	var formElements = document.querySelectorAll('.mc4wp-form');
	var config = window.mc4wp_config || {};

	// initialize forms
	var forms = Array.prototype.map.call(formElements,function(element) {

		// find form data
		var form = new Form(element, EventEmitter);
		var formConfig = config.forms[form.id] || {};
		form.setConfig(formConfig);

		if( config.auto_scroll && formConfig.data && formConfig.errors.length > 0 ) {
			form.placeIntoView( config.auto_scroll === 'animated' );
		}

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

