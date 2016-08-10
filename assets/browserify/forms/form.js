'use strict';

var serialize = require('form-serialize');
var populate = require('populate.js');

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
		return serialize(form.element, { hash: true });
	};

	this.getSerializedData = function() {
		return serialize(form.element);
	};

	this.setResponse = function( msg ) {
		form.element.querySelector('.mc4wp-response').innerHTML = msg;
	};

	// revert back to original state
	this.reset = function() {
		this.setResponse('');
		form.element.querySelector('.mc4wp-form-fields').style.display = '';
		form.element.reset();
	}

};

module.exports = Form;
