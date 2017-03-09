(function () { var require = undefined; var define = undefined; (function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
'use strict';

var settings = mc4wp.settings;
var events = mc4wp.events;
var notice = document.getElementById('notice-additional-fields');

function checkRequiredListFields() {

	var lists = settings.getSelectedLists();

	var showNotice = false;
	var allowedFields = ['EMAIL', 'FNAME', 'NAME', 'LNAME'];

	loop: for (var i = 0; i < lists.length; i++) {
		var list = lists[i];

		for (var j = 0; j < list.merge_fields.length; j++) {
			var f = list.merge_fields[j];

			if (f.required && allowedFields.indexOf(f.tag) < 0) {
				showNotice = true;
				break loop;
			}
		}
	}

	notice.style.display = showNotice ? '' : 'none';
}

if (notice) {
	checkRequiredListFields();
	events.on('selectedLists.change', checkRequiredListFields);
}

},{}]},{},[1]);
 })();