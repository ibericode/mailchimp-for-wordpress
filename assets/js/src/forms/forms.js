var forms = function() {
	'use strict';

	// deps
	var EventEmitter = require('../../third-party/event-emitter.js');
	var Form = require('./form.js');
	var gator = require('../../third-party/gator.js');

	// variables
	var events = new EventEmitter();
	var formElements = document.querySelectorAll('.mc4wp-form');
	var config = window.mc4wp_config || {};

	// initialize Form objects
	var forms = Array.prototype.map.call(formElements,function(element) {

		// find form data
		var form = new Form(element, EventEmitter);
		var formConfig = config.forms[form.id] || {};
		form.setConfig(formConfig);

		if( config.auto_scroll && formConfig.data && formConfig.errors.length > 0 ) {
			form.placeIntoView( config.auto_scroll === 'animated' );
		}

		return form;
	});

	// Bind browser events to form events (using delegation to work with AJAX loaded forms as well)
	Gator(document.body).on('submit', '.mc4wp-form', function(event) {
		var form = getFromElement(event.target);
		if( form ) {
			events.trigger('submit', [form, event]);
		}
	});

	Gator(document.body).on('focus', '.mc4wp-form', function(event) {
		var form = getFromElement(event.target);
		if( form && ! form.started ) {
			events.trigger('started', [form, event]);
		}
	});

	Gator(document.body).on('change', '.mc4wp-form', function(event) {
		var form = getFromElement(event.target);
		if( form ) {
			events.trigger('changed', [form, event]);
		}
	});

	// map all global events to individual form object as they happen
	var mapEvents = [ 'submit', 'submitted', 'changed', 'started', 'subscribed', 'unsubscribed' ];
	mapEvents.forEach(function(eventName) {
		events.on(eventName,function(form,event){
			form.trigger(eventName,[form,event]);
		})
	});


	// @todo: allow instantiating an object later on in the lifecycle

	// functions
	function get(form_id) {
		return forms.filter(function(form) {
			return form.id == form_id;
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

	function off(event,callback) {
		return events.off(event,callback);
	}

	function getFromElement(element) {
		var formElement = element.form || element;
		var id = parseInt( formElement.dataset.id );
		return get(id);
	}

	// public API
	return {
		all: all,
		get: get,
		on: on,
		trigger: trigger,
		off: off,
		getFromElement: getFromElement
	}
};

module.exports = forms();

