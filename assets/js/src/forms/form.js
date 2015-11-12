'use strict';

var serialize = require('../../third-party/serialize.js');
var populate = require('../../third-party/populate.js');
var formToJson = require('../../third-party/form2js.js');

var Form = function(id, element) {

	var form = this;

	this.id = id;
	this.element = element;
	this.name = element.getAttribute('data-name') || "Form #" + this.id;
	this.errors = [];
	this.started = false;

	this.setData = function(data) {
		populate(form.element, data);
	};

	this.getData = function() {
		return formToJson(form.element);
	};

	this.getSerializedData = function() {
		return serialize(form.element);
	};

	this.setResponse = function( msg ) {
		form.element.querySelector('.mc4wp-response').innerHTML = msg;
	};

	this.placeIntoView = function( animate ) {
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
};

module.exports = Form;