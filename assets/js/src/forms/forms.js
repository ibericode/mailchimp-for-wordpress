var forms = function() {
	'use strict';

	// deps
	var EventEmitter = require('../../third-party/event-emitter.js');
	var Form = require('./form.js');
	var Gator = require('../../third-party/gator.js');

	// variables
	var events = new EventEmitter();
	var config = window.mc4wp_config || {};
	var forms = [];


	function find() {
		var formElements = document.querySelectorAll('.mc4wp-form');
		forms = Array.prototype.map.call(formElements,function(element) {

			// in forms array already?
			var form_id = element.getAttribute('data-id');
			var form;

			form = get( form_id );
			if( form ) {
				return form;
			}

			// nope, let's create an object for it.
			form = new Form(form_id, element, EventEmitter);
			var formConfig = config.forms[form.id] || {};
			form.setConfig(formConfig);

			if( config.auto_scroll && formConfig.data && formConfig.errors.length > 0 ) {
				form.placeIntoView( config.auto_scroll === 'animated' );
			}

			return form;
		});
	}

	// Bind browser events to form events (using delegation to work with AJAX loaded forms as well)
	Gator(document.body).on('submit', '.mc4wp-form', function(event) {
		var form = getFromElement(event.target);
		events.trigger('submit', [form, event]);
	});

	Gator(document.body).on('focus', '.mc4wp-form', function(event) {
		var form = getFromElement(event.target);
		if( ! form.started ) {
			events.trigger('started', [form, event]);
		}
	});

	Gator(document.body).on('change', '.mc4wp-form', function(event) {
		var form = getFromElement(event.target);
		events.trigger('changed', [form, event]);
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
		var id = parseInt( formElement.getAttribute('data-id') );
		var form = get(id);

		if( form ) {
			return form;
		}

		form = new Form(id,formElement,EventEmitter);
		forms.push(form);
		return form;
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

