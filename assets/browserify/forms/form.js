'use strict';

var serialize = require('../third-party/serialize.js');
var populate = require('populate.js');
var formToJson = require('../third-party/form2js.js');

var Form = function(id, element) {

	var form = this;

	this.id = id;
	this.element = element || document.createElement('form');
	this.name = this.element.getAttribute('data-name') || "Form #" + this.id;
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
		var responseEl = form.element.querySelector('.mc4wp-response');

		if( responseEl ) {
			if( msg.indexOf( 'mc4wp-response' ) > 0 ) {
				responseEl.outerHTML = msg;
			} else {
				responseEl.innerHTML = msg;
			}
		} else {
			form.element.innerHTML = form.element.innerHTML + msg;
		}

	};

};

module.exports = Form;
