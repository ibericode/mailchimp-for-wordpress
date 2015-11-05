'use strict';

var Form = function(element, EventEmitter) {

	var serialize = require('../../third-party/serialize.js');
	var populate = require('../../third-party/populate.js');
	var form = this;
	var events = new EventEmitter();

	this.id = parseInt( element.dataset.id );
	this.name = "Form #" + this.id;
	this.element = element;
	this.requiredFields = [];
	this.errors = [];

	this.on = function(event,callback) {
		return events.on(event,callback);
	};

	this.trigger = function (event,args) {
		return events.trigger(event,args);
	};

	this.getData = function() {
		return serialize(element);
	};

	this.setConfig = function (config) {
		form.name = config.name;
		form.errors = config.errors;

		// @todo walk through required fields and ensure they have "required" attribute?
		form.requiredFields = config.requiredFields;

		// repopulate form if there are errors
		if( config.data && form.errors.length > 0 ) {
			populate(form.element, config.data);
		}
	};

	this.placeIntoView = function( animate ) {
		// Scroll to form element
		var scrollToHeight = 0;
		var windowHeight = window.innerHeight;
		var obj = form.element;

		if (obj.offsetParent) {
			do {
				scrollToHeight += obj.offsetTop;
			} while (obj = obj.offsetParent);
		} else {
			scrollToHeight = form.element.offsetTop;
		}

		if((windowHeight - 80) > form.element.clientHeight) {
			// vertically center the form, but only if there's enough space for a decent margin
			scrollToHeight = scrollToHeight - ((windowHeight - form.element.clientHeight) / 2);
		} else {
			// the form doesn't fit, scroll a little above the form
			scrollToHeight = scrollToHeight - 80;
		}

		// scroll there. if jQuery is loaded, do it with an animation.
		if( animate && window.jQuery ) {
			window.jQuery('html, body').animate({ scrollTop: scrollToHeight }, 800);
		} else {
			window.scrollTo(0, scrollToHeight);
		}
	};

	// add listeners for default browser events
	element.addEventListener('submit',function(event) {
		form.trigger('submit', [form, event]);
	});
};

module.exports = Form;