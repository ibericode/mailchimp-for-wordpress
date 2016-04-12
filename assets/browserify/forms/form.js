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
		try {
			populate(form.element, data);
		} catch(e) {
			console.error(e);
		}
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

};

module.exports = Form;
