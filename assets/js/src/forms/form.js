'use strict';

var Form = function(element, settings, EventEmitter) {

	var serialize = require('../../third-party/serialize.js');
	var form = this;
	var events = new EventEmitter();

	this.id = parseInt( element.dataset.id );
	this.element = element;
	this.settings = settings;

	this.on = function(event,callback) {
		return events.on(event,callback);
	};

	this.trigger = function (event,args) {
		return events.trigger(event,args);
	};

	this.getData = function() {
		return serialize(element.querySelector('form'));
	};

	// add listeners
	element.querySelector('form').addEventListener('submit',function(event) {
		form.trigger('submit', [form, event]);
	});
};

module.exports = Form;