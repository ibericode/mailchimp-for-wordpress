(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
'use strict';

// deps
var Gator = require('gator');
var forms = require('./forms/forms.js');
var listeners = window.mc4wp && window.mc4wp.listeners ? window.mc4wp.listeners : [];
var config = window.mc4wp_forms_config || {};

// expose stuff, this overrides dummy javascript
window.mc4wp = {
	"forms": forms
};

// register early listeners
for(var i=0; i<listeners.length;i++) {
	forms.on(listeners[i].event, listeners[i].callback);
}

// was a form submitted?
if( config.submitted_form && config.submitted_form.id ) {
	var form = forms.get(config.submitted_form.id);

	// add class & trigger event
	forms.trigger( 'submitted', [form]);

	if( config.submitted_form.errors ) {
		// form has errors, repopulate it.
		form.setData(config.submitted_form.data);
		forms.trigger('error', [form, config.submitted_form.errors]);
	} else {
		// form was successfully submitted
		forms.trigger('success', [form, config.submitted_form.data]);
		forms.trigger(config.submitted_form.action + "d", [form, config.submitted_form.data]);
	}
}

// Bind browser events to form events (using delegation to work with AJAX loaded forms as well)
Gator(document.body).on('submit', '.mc4wp-form', function(event) {
	event = event || window.event;
	var form = forms.getByElement(event.target || event.srcElement);
	forms.trigger('submit', [form, event]);
});

Gator(document.body).on('focus', '.mc4wp-form', function(event) {
	event = event || window.event;
	var form = forms.getByElement(event.target || event.srcElement);
	if( ! form.started ) {
		forms.trigger('start', [form, event]);
	}
});

Gator(document.body).on('change', '.mc4wp-form', function(event) {
	event = event || window.event;
	var form = forms.getByElement(event.target || event.srcElement);
	forms.trigger('change', [form,event]);
});




},{"./forms/forms.js":3,"gator":6}],2:[function(require,module,exports){
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
},{"../third-party/form2js.js":4,"../third-party/serialize.js":5,"populate.js":7}],3:[function(require,module,exports){
'use strict';

// deps
var EventEmitter = require('wolfy87-eventemitter');
var Form = require('./form.js');

// variables
var events = new EventEmitter();
var forms = [];

// get form by its id
function get(formId) {
	for(var i=0; i<forms.length;i++) {
		if(forms[i].id == formId) {
			return forms[i];
		}
	}

	var formElement = document.querySelector('.mc4wp-form-' + formId);
	return createFromElement(formElement,formId);
}

// get form by <form> element (or any input in form)
function getByElement(element) {
	var formElement = element.form || element;
	for(var i=0; i<forms.length;i++) {
		if(forms[i].element == formElement) {
			return forms[i];
		}
	}

	return createFromElement(element);
}

// create form object from <form> element
function createFromElement(formElement,id) {
	id = id || parseInt( formElement.getAttribute('data-id') ) || 0;
	var form = new Form(id,formElement);
	forms.push(form);
	return form;
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

module.exports = {
	"all": all,
	"get": get,
	"getByElement": getByElement,
	"on": on,
	"trigger": trigger,
	"off": off
};


},{"./form.js":2,"wolfy87-eventemitter":8}],4:[function(require,module,exports){
/**
 * Copyright (c) 2010 Maxim Vasiliev
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @author Maxim Vasiliev
 * Date: 09.09.2010
 * Time: 19:02:33
 */


(function (root, factory)
{
	if (typeof exports !== 'undefined' && typeof module !== 'undefined' && module.exports) {
		// NodeJS
		module.exports = factory();
	}
	else if (typeof define === 'function' && define.amd)
	{
		// AMD. Register as an anonymous module.
		define(factory);
	}
	else
	{
		// Browser globals
		root.form2js = factory();
	}
}(this, function ()
{
	"use strict";

	/**
	 * Returns form values represented as Javascript object
	 * "name" attribute defines structure of resulting object
	 *
	 * @param rootNode {Element|String} root form element (or it's id) or array of root elements
	 * @param delimiter {String} structure parts delimiter defaults to '.'
	 * @param skipEmpty {Boolean} should skip empty text values, defaults to true
	 * @param nodeCallback {Function} custom function to get node value
	 * @param useIdIfEmptyName {Boolean} if true value of id attribute of field will be used if name of field is empty
	 */
	function form2js(rootNode, delimiter, skipEmpty, nodeCallback, useIdIfEmptyName, getDisabled)
	{
		getDisabled = getDisabled ? true : false;
		if (typeof skipEmpty == 'undefined' || skipEmpty == null) skipEmpty = true;
		if (typeof delimiter == 'undefined' || delimiter == null) delimiter = '.';
		if (arguments.length < 5) useIdIfEmptyName = false;

		rootNode = typeof rootNode == 'string' ? document.getElementById(rootNode) : rootNode;

		var formValues = [],
			currNode,
			i = 0;

		/* If rootNode is array - combine values */
		if (rootNode.constructor == Array || (typeof NodeList != "undefined" && rootNode.constructor == NodeList))
		{
			while(currNode = rootNode[i++])
			{
				formValues = formValues.concat(getFormValues(currNode, nodeCallback, useIdIfEmptyName, getDisabled));
			}
		}
		else
		{
			formValues = getFormValues(rootNode, nodeCallback, useIdIfEmptyName, getDisabled);
		}

		return processNameValues(formValues, skipEmpty, delimiter);
	}

	/**
	 * Processes collection of { name: 'name', value: 'value' } objects.
	 * @param nameValues
	 * @param skipEmpty if true skips elements with value == '' or value == null
	 * @param delimiter
	 */
	function processNameValues(nameValues, skipEmpty, delimiter)
	{
		var result = {},
			arrays = {},
			i, j, k, l,
			value,
			nameParts,
			currResult,
			arrNameFull,
			arrName,
			arrIdx,
			namePart,
			name,
			_nameParts;

		for (i = 0; i < nameValues.length; i++)
		{
			value = nameValues[i].value;

			if (skipEmpty && (value === '' || value === null)) continue;

			name = nameValues[i].name;
			_nameParts = name.split(delimiter);
			nameParts = [];
			currResult = result;
			arrNameFull = '';

			for(j = 0; j < _nameParts.length; j++)
			{
				namePart = _nameParts[j].split('][');
				if (namePart.length > 1)
				{
					for(k = 0; k < namePart.length; k++)
					{
						if (k == 0)
						{
							namePart[k] = namePart[k] + ']';
						}
						else if (k == namePart.length - 1)
						{
							namePart[k] = '[' + namePart[k];
						}
						else
						{
							namePart[k] = '[' + namePart[k] + ']';
						}

						arrIdx = namePart[k].match(/([a-z_]+)?\[([a-z_][a-z0-9_]+?)\]/i);
						if (arrIdx)
						{
							for(l = 1; l < arrIdx.length; l++)
							{
								if (arrIdx[l]) nameParts.push(arrIdx[l]);
							}
						}
						else{
							nameParts.push(namePart[k]);
						}
					}
				}
				else
					nameParts = nameParts.concat(namePart);
			}

			for (j = 0; j < nameParts.length; j++)
			{
				namePart = nameParts[j];

				if (namePart.indexOf('[]') > -1 && j == nameParts.length - 1)
				{
					arrName = namePart.substr(0, namePart.indexOf('['));
					arrNameFull += arrName;

					if (!currResult[arrName]) currResult[arrName] = [];
					currResult[arrName].push(value);
				}
				else if (namePart.indexOf('[') > -1)
				{
					arrName = namePart.substr(0, namePart.indexOf('['));
					arrIdx = namePart.replace(/(^([a-z_]+)?\[)|(\]$)/gi, '');

					/* Unique array name */
					arrNameFull += '_' + arrName + '_' + arrIdx;

					/*
					 * Because arrIdx in field name can be not zero-based and step can be
					 * other than 1, we can't use them in target array directly.
					 * Instead we're making a hash where key is arrIdx and value is a reference to
					 * added array element
					 */

					if (!arrays[arrNameFull]) arrays[arrNameFull] = {};
					if (arrName != '' && !currResult[arrName]) currResult[arrName] = [];

					if (j == nameParts.length - 1)
					{
						if (arrName == '')
						{
							currResult.push(value);
							arrays[arrNameFull][arrIdx] = currResult[currResult.length - 1];
						}
						else
						{
							currResult[arrName].push(value);
							arrays[arrNameFull][arrIdx] = currResult[arrName][currResult[arrName].length - 1];
						}
					}
					else
					{
						if (!arrays[arrNameFull][arrIdx])
						{
							if ((/^[0-9a-z_]+\[?/i).test(nameParts[j+1])) currResult[arrName].push({});
							else currResult[arrName].push([]);

							arrays[arrNameFull][arrIdx] = currResult[arrName][currResult[arrName].length - 1];
						}
					}

					currResult = arrays[arrNameFull][arrIdx];
				}
				else
				{
					arrNameFull += namePart;

					if (j < nameParts.length - 1) /* Not the last part of name - means object */
					{
						if (!currResult[namePart]) currResult[namePart] = {};
						currResult = currResult[namePart];
					}
					else
					{
						currResult[namePart] = value;
					}
				}
			}
		}

		return result;
	}

	function getFormValues(rootNode, nodeCallback, useIdIfEmptyName, getDisabled)
	{
		var result = extractNodeValues(rootNode, nodeCallback, useIdIfEmptyName, getDisabled);
		return result.length > 0 ? result : getSubFormValues(rootNode, nodeCallback, useIdIfEmptyName, getDisabled);
	}

	function getSubFormValues(rootNode, nodeCallback, useIdIfEmptyName, getDisabled)
	{
		var result = [],
			currentNode = rootNode.firstChild;

		while (currentNode)
		{
			result = result.concat(extractNodeValues(currentNode, nodeCallback, useIdIfEmptyName, getDisabled));
			currentNode = currentNode.nextSibling;
		}

		return result;
	}

	function extractNodeValues(node, nodeCallback, useIdIfEmptyName, getDisabled) {
		if (node.disabled && !getDisabled) return [];

		var callbackResult, fieldValue, result, fieldName = getFieldName(node, useIdIfEmptyName);

		callbackResult = nodeCallback && nodeCallback(node);

		if (callbackResult && callbackResult.name) {
			result = [callbackResult];
		}
		else if (fieldName != '' && node.nodeName.match(/INPUT|TEXTAREA/i)) {
			fieldValue = getFieldValue(node, getDisabled);
			if (null === fieldValue) {
				result = [];
			} else {
				result = [ { name: fieldName, value: fieldValue} ];
			}
		}
		else if (fieldName != '' && node.nodeName.match(/SELECT/i)) {
			fieldValue = getFieldValue(node, getDisabled);
			result = [ { name: fieldName.replace(/\[\]$/, ''), value: fieldValue } ];
		}
		else {
			result = getSubFormValues(node, nodeCallback, useIdIfEmptyName, getDisabled);
		}

		return result;
	}

	function getFieldName(node, useIdIfEmptyName)
	{
		if (node.name && node.name != '') return node.name;
		else if (useIdIfEmptyName && node.id && node.id != '') return node.id;
		else return '';
	}


	function getFieldValue(fieldNode, getDisabled)
	{
		if (fieldNode.disabled && !getDisabled) return null;

		switch (fieldNode.nodeName) {
			case 'INPUT':
			case 'TEXTAREA':
				switch (fieldNode.type.toLowerCase()) {
					case 'radio':
						if (fieldNode.checked && fieldNode.value === "false") return false;
					case 'checkbox':
						if (fieldNode.checked && fieldNode.value === "true") return true;
						if (!fieldNode.checked && fieldNode.value === "true") return false;
						if (fieldNode.checked) return fieldNode.value;
						break;

					case 'button':
					case 'reset':
					case 'submit':
					case 'image':
						return '';
						break;

					default:
						return fieldNode.value;
						break;
				}
				break;

			case 'SELECT':
				return getSelectedOptionValue(fieldNode);
				break;

			default:
				break;
		}

		return null;
	}

	function getSelectedOptionValue(selectNode)
	{
		var multiple = selectNode.multiple,
			result = [],
			options,
			i, l;

		if (!multiple) return selectNode.value;

		for (options = selectNode.getElementsByTagName("option"), i = 0, l = options.length; i < l; i++)
		{
			if (options[i].selected) result.push(options[i].value);
		}

		return result;
	}

	return form2js;

}));
},{}],5:[function(require,module,exports){
// get successful control from form and assemble into object
// http://www.w3.org/TR/html401/interact/forms.html#h-17.13.2

// types which indicate a submit action and are not successful controls
// these will be ignored
var k_r_submitter = /^(?:submit|button|image|reset|file)$/i;

// node names which could be successful controls
var k_r_success_contrls = /^(?:input|select|textarea|keygen)/i;

// Matches bracket notation.
var brackets = /(\[[^\[\]]*\])/g;

// serializes form fields
// @param form MUST be an HTMLForm element
// @param options is an optional argument to configure the serialization. Default output
// with no options specified is a url encoded string
//    - hash: [true | false] Configure the output type. If true, the output will
//    be a js object.
//    - serializer: [function] Optional serializer function to override the default one.
//    The function takes 3 arguments (result, key, value) and should return new result
//    hash and url encoded str serializers are provided with this module
//    - disabled: [true | false]. If true serialize disabled fields.
//    - empty: [true | false]. If true serialize empty fields
function serialize(form, options) {
	if (typeof options != 'object') {
		options = { hash: !!options };
	}
	else if (options.hash === undefined) {
		options.hash = true;
	}

	var result = (options.hash) ? {} : '';
	var serializer = options.serializer || ((options.hash) ? hash_serializer : str_serialize);

	var elements = form && form.elements ? form.elements : [];

	//Object store each radio and set if it's empty or not
	var radio_store = Object.create(null);

	for (var i=0 ; i<elements.length ; ++i) {
		var element = elements[i];

		// ingore disabled fields
		if ((!options.disabled && element.disabled) || !element.name) {
			continue;
		}
		// ignore anyhting that is not considered a success field
		if (!k_r_success_contrls.test(element.nodeName) ||
			k_r_submitter.test(element.type)) {
			continue;
		}

		var key = element.name;
		var val = element.value;

		// we can't just use element.value for checkboxes cause some browsers lie to us
		// they say "on" for value when the box isn't checked
		if ((element.type === 'checkbox' || element.type === 'radio') && !element.checked) {
			val = undefined;
		}

		// If we want empty elements
		if (options.empty) {
			// for checkbox
			if (element.type === 'checkbox' && !element.checked) {
				val = '';
			}

			// for radio
			if (element.type === 'radio') {
				if (!radio_store[element.name] && !element.checked) {
					radio_store[element.name] = false;
				}
				else if (element.checked) {
					radio_store[element.name] = true;
				}
			}

			// if options empty is true, continue only if its radio
			if (!val && element.type == 'radio') {
				continue;
			}
		}
		else {
			// value-less fields are ignored unless options.empty is true
			if (!val) {
				continue;
			}
		}

		// multi select boxes
		if (element.type === 'select-multiple') {
			val = [];

			var selectOptions = element.options;
			var isSelectedOptions = false;
			for (var j=0 ; j<selectOptions.length ; ++j) {
				var option = selectOptions[j];
				var allowedEmpty = options.empty && !option.value;
				var hasValue = (option.value || allowedEmpty);
				if (option.selected && hasValue) {
					isSelectedOptions = true;

					// If using a hash serializer be sure to add the
					// correct notation for an array in the multi-select
					// context. Here the name attribute on the select element
					// might be missing the trailing bracket pair. Both names
					// "foo" and "foo[]" should be arrays.
					if (options.hash && key.slice(key.length - 2) !== '[]') {
						result = serializer(result, key + '[]', option.value);
					}
					else {
						result = serializer(result, key, option.value);
					}
				}
			}

			// Serialize if no selected options and options.empty is true
			if (!isSelectedOptions && options.empty) {
				result = serializer(result, key, '');
			}

			continue;
		}

		result = serializer(result, key, val);
	}

	// Check for all empty radio buttons and serialize them with key=""
	if (options.empty) {
		for (var key in radio_store) {
			if (!radio_store[key]) {
				result = serializer(result, key, '');
			}
		}
	}

	return result;
}

function parse_keys(string) {
	var keys = [];
	var prefix = /^([^\[\]]*)/;
	var children = new RegExp(brackets);
	var match = prefix.exec(string);

	if (match[1]) {
		keys.push(match[1]);
	}

	while ((match = children.exec(string)) !== null) {
		keys.push(match[1]);
	}

	return keys;
}

function hash_assign(result, keys, value) {
	if (keys.length === 0) {
		result = value;
		return result;
	}

	var key = keys.shift();
	var between = key.match(/^\[(.+?)\]$/);

	if (key === '[]') {
		result = result || [];

		if (Array.isArray(result)) {
			result.push(hash_assign(null, keys, value));
		}
		else {
			// This might be the result of bad name attributes like "[][foo]",
			// in this case the original `result` object will already be
			// assigned to an object literal. Rather than coerce the object to
			// an array, or cause an exception the attribute "_values" is
			// assigned as an array.
			result._values = result._values || [];
			result._values.push(hash_assign(null, keys, value));
		}

		return result;
	}

	// Key is an attribute name and can be assigned directly.
	if (!between) {
		result[key] = hash_assign(result[key], keys, value);
	}
	else {
		var string = between[1];
		var index = parseInt(string, 10);

		// If the characters between the brackets is not a number it is an
		// attribute name and can be assigned directly.
		if (isNaN(index)) {
			result = result || {};
			result[string] = hash_assign(result[string], keys, value);
		}
		else {
			result = result || [];
			result[index] = hash_assign(result[index], keys, value);
		}
	}

	return result;
}

// Object/hash encoding serializer.
function hash_serializer(result, key, value) {
	var matches = key.match(brackets);

	// Has brackets? Use the recursive assignment function to walk the keys,
	// construct any missing objects in the result tree and make the assignment
	// at the end of the chain.
	if (matches) {
		var keys = parse_keys(key);
		hash_assign(result, keys, value);
	}
	else {
		// Non bracket notation can make assignments directly.
		var existing = result[key];

		// If the value has been assigned already (for instance when a radio and
		// a checkbox have the same name attribute) convert the previous value
		// into an array before pushing into it.
		//
		// NOTE: If this requirement were removed all hash creation and
		// assignment could go through `hash_assign`.
		if (existing) {
			if (!Array.isArray(existing)) {
				result[key] = [ existing ];
			}

			result[key].push(value);
		}
		else {
			result[key] = value;
		}
	}

	return result;
}

// urlform encoding serializer
function str_serialize(result, key, value) {
	// encode newlines as \r\n cause the html spec says so
	value = value.replace(/(\r)?\n/g, '\r\n');
	value = encodeURIComponent(value);

	// spaces should be '+' rather than '%20'.
	value = value.replace(/%20/g, '+');
	return result + (result ? '&' : '') + encodeURIComponent(key) + '=' + value;
}

module.exports = serialize;
},{}],6:[function(require,module,exports){
/**
 * Copyright 2014 Craig Campbell
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * GATOR.JS
 * Simple Event Delegation
 *
 * @version 1.2.4
 *
 * Compatible with IE 9+, FF 3.6+, Safari 5+, Chrome
 *
 * Include legacy.js for compatibility with older browsers
 *
 *             .-._   _ _ _ _ _ _ _ _
 *  .-''-.__.-'00  '-' ' ' ' ' ' ' ' '-.
 * '.___ '    .   .--_'-' '-' '-' _'-' '._
 *  V: V 'vv-'   '_   '.       .'  _..' '.'.
 *    '=.____.=_.--'   :_.__.__:_   '.   : :
 *            (((____.-'        '-.  /   : :
 *                              (((-'\ .' /
 *                            _____..'  .'
 *                           '-._____.-'
 */
(function() {
    var _matcher,
        _level = 0,
        _id = 0,
        _handlers = {},
        _gatorInstances = {};

    function _addEvent(gator, type, callback) {

        // blur and focus do not bubble up but if you use event capturing
        // then you will get them
        var useCapture = type == 'blur' || type == 'focus';
        gator.element.addEventListener(type, callback, useCapture);
    }

    function _cancel(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    /**
     * returns function to use for determining if an element
     * matches a query selector
     *
     * @returns {Function}
     */
    function _getMatcher(element) {
        if (_matcher) {
            return _matcher;
        }

        if (element.matches) {
            _matcher = element.matches;
            return _matcher;
        }

        if (element.webkitMatchesSelector) {
            _matcher = element.webkitMatchesSelector;
            return _matcher;
        }

        if (element.mozMatchesSelector) {
            _matcher = element.mozMatchesSelector;
            return _matcher;
        }

        if (element.msMatchesSelector) {
            _matcher = element.msMatchesSelector;
            return _matcher;
        }

        if (element.oMatchesSelector) {
            _matcher = element.oMatchesSelector;
            return _matcher;
        }

        // if it doesn't match a native browser method
        // fall back to the gator function
        _matcher = Gator.matchesSelector;
        return _matcher;
    }

    /**
     * determines if the specified element matches a given selector
     *
     * @param {Node} element - the element to compare against the selector
     * @param {string} selector
     * @param {Node} boundElement - the element the listener was attached to
     * @returns {void|Node}
     */
    function _matchesSelector(element, selector, boundElement) {

        // no selector means this event was bound directly to this element
        if (selector == '_root') {
            return boundElement;
        }

        // if we have moved up to the element you bound the event to
        // then we have come too far
        if (element === boundElement) {
            return;
        }

        // if this is a match then we are done!
        if (_getMatcher(element).call(element, selector)) {
            return element;
        }

        // if this element did not match but has a parent we should try
        // going up the tree to see if any of the parent elements match
        // for example if you are looking for a click on an <a> tag but there
        // is a <span> inside of the a tag that it is the target,
        // it should still work
        if (element.parentNode) {
            _level++;
            return _matchesSelector(element.parentNode, selector, boundElement);
        }
    }

    function _addHandler(gator, event, selector, callback) {
        if (!_handlers[gator.id]) {
            _handlers[gator.id] = {};
        }

        if (!_handlers[gator.id][event]) {
            _handlers[gator.id][event] = {};
        }

        if (!_handlers[gator.id][event][selector]) {
            _handlers[gator.id][event][selector] = [];
        }

        _handlers[gator.id][event][selector].push(callback);
    }

    function _removeHandler(gator, event, selector, callback) {

        // if there are no events tied to this element at all
        // then don't do anything
        if (!_handlers[gator.id]) {
            return;
        }

        // if there is no event type specified then remove all events
        // example: Gator(element).off()
        if (!event) {
            for (var type in _handlers[gator.id]) {
                if (_handlers[gator.id].hasOwnProperty(type)) {
                    _handlers[gator.id][type] = {};
                }
            }
            return;
        }

        // if no callback or selector is specified remove all events of this type
        // example: Gator(element).off('click')
        if (!callback && !selector) {
            _handlers[gator.id][event] = {};
            return;
        }

        // if a selector is specified but no callback remove all events
        // for this selector
        // example: Gator(element).off('click', '.sub-element')
        if (!callback) {
            delete _handlers[gator.id][event][selector];
            return;
        }

        // if we have specified an event type, selector, and callback then we
        // need to make sure there are callbacks tied to this selector to
        // begin with.  if there aren't then we can stop here
        if (!_handlers[gator.id][event][selector]) {
            return;
        }

        // if there are then loop through all the callbacks and if we find
        // one that matches remove it from the array
        for (var i = 0; i < _handlers[gator.id][event][selector].length; i++) {
            if (_handlers[gator.id][event][selector][i] === callback) {
                _handlers[gator.id][event][selector].splice(i, 1);
                break;
            }
        }
    }

    function _handleEvent(id, e, type) {
        if (!_handlers[id][type]) {
            return;
        }

        var target = e.target || e.srcElement,
            selector,
            match,
            matches = {},
            i = 0,
            j = 0;

        // find all events that match
        _level = 0;
        for (selector in _handlers[id][type]) {
            if (_handlers[id][type].hasOwnProperty(selector)) {
                match = _matchesSelector(target, selector, _gatorInstances[id].element);

                if (match && Gator.matchesEvent(type, _gatorInstances[id].element, match, selector == '_root', e)) {
                    _level++;
                    _handlers[id][type][selector].match = match;
                    matches[_level] = _handlers[id][type][selector];
                }
            }
        }

        // stopPropagation() fails to set cancelBubble to true in Webkit
        // @see http://code.google.com/p/chromium/issues/detail?id=162270
        e.stopPropagation = function() {
            e.cancelBubble = true;
        };

        for (i = 0; i <= _level; i++) {
            if (matches[i]) {
                for (j = 0; j < matches[i].length; j++) {
                    if (matches[i][j].call(matches[i].match, e) === false) {
                        Gator.cancel(e);
                        return;
                    }

                    if (e.cancelBubble) {
                        return;
                    }
                }
            }
        }
    }

    /**
     * binds the specified events to the element
     *
     * @param {string|Array} events
     * @param {string} selector
     * @param {Function} callback
     * @param {boolean=} remove
     * @returns {Object}
     */
    function _bind(events, selector, callback, remove) {

        // fail silently if you pass null or undefined as an alement
        // in the Gator constructor
        if (!this.element) {
            return;
        }

        if (!(events instanceof Array)) {
            events = [events];
        }

        if (!callback && typeof(selector) == 'function') {
            callback = selector;
            selector = '_root';
        }

        var id = this.id,
            i;

        function _getGlobalCallback(type) {
            return function(e) {
                _handleEvent(id, e, type);
            };
        }

        for (i = 0; i < events.length; i++) {
            if (remove) {
                _removeHandler(this, events[i], selector, callback);
                continue;
            }

            if (!_handlers[id] || !_handlers[id][events[i]]) {
                Gator.addEvent(this, events[i], _getGlobalCallback(events[i]));
            }

            _addHandler(this, events[i], selector, callback);
        }

        return this;
    }

    /**
     * Gator object constructor
     *
     * @param {Node} element
     */
    function Gator(element, id) {

        // called as function
        if (!(this instanceof Gator)) {
            // only keep one Gator instance per node to make sure that
            // we don't create a ton of new objects if you want to delegate
            // multiple events from the same node
            //
            // for example: Gator(document).on(...
            for (var key in _gatorInstances) {
                if (_gatorInstances[key].element === element) {
                    return _gatorInstances[key];
                }
            }

            _id++;
            _gatorInstances[_id] = new Gator(element, _id);

            return _gatorInstances[_id];
        }

        this.element = element;
        this.id = id;
    }

    /**
     * adds an event
     *
     * @param {string|Array} events
     * @param {string} selector
     * @param {Function} callback
     * @returns {Object}
     */
    Gator.prototype.on = function(events, selector, callback) {
        return _bind.call(this, events, selector, callback);
    };

    /**
     * removes an event
     *
     * @param {string|Array} events
     * @param {string} selector
     * @param {Function} callback
     * @returns {Object}
     */
    Gator.prototype.off = function(events, selector, callback) {
        return _bind.call(this, events, selector, callback, true);
    };

    Gator.matchesSelector = function() {};
    Gator.cancel = _cancel;
    Gator.addEvent = _addEvent;
    Gator.matchesEvent = function() {
        return true;
    };

    if (typeof module !== 'undefined' && module.exports) {
        module.exports = Gator;
    }

    window.Gator = Gator;
}) ();

},{}],7:[function(require,module,exports){
/*! populate.js v1.0.2 by @dannyvankooten | MIT license */
;(function(root) {

	/**
	 * Populate form fields from a JSON object.
	 *
	 * @param form object The form element containing your input fields.
	 * @param data array JSON data to populate the fields with.
	 * @param basename string Optional basename which is added to `name` attributes
	 */
	var populate = function( form, data, basename) {

		for(var key in data) {

			if( ! data.hasOwnProperty( key ) ) {
				continue;
			}

			var name = key;
			var value = data[key];

			// handle array name attributes
			if(typeof(basename) !== "undefined") {
				name = basename + "[" + key + "]";
			}

			if(value.constructor === Array) {
				name += '[]';
			} else if(typeof value == "object") {
				populate( form, value, name);
				continue;
			}

			// only proceed if element is set
			var element = form.elements.namedItem( name );
			if( ! element ) {
				continue;
			}

			var type = element.type || element[0].type;

			switch(type ) {
				default:
					element.value = value;
					break;

				case 'radio':
				case 'checkbox':
					for( var j=0; j < element.length; j++ ) {
						element[j].checked = ( value.indexOf(element[j].value) > -1 );
					}
					break;

				case 'select-multiple':
					var values = value.constructor == Array ? value : [value];

					for(var k = 0; k < element.options.length; k++) {
						element.options[k].selected |= (values.indexOf(element.options[k].value) > -1 );
					}
					break;

				case 'select':
				case 'select-one':
					element.value = value.toString() || value;
					break;

			}

		}

	};

	// Play nice with AMD, CommonJS or a plain global object.
	if ( typeof define == 'function' && typeof define.amd == 'object' && define.amd ) {
		define(function() {
			return populate;
		});
	}	else if ( typeof module !== 'undefined' && module.exports ) {
		module.exports = populate;
	} else {
		root.populate = populate;
	}

}(this));
},{}],8:[function(require,module,exports){
/*!
 * EventEmitter v4.2.11 - git.io/ee
 * Unlicense - http://unlicense.org/
 * Oliver Caldwell - http://oli.me.uk/
 * @preserve
 */

;(function () {
    'use strict';

    /**
     * Class for managing events.
     * Can be extended to provide event functionality in other classes.
     *
     * @class EventEmitter Manages event registering and emitting.
     */
    function EventEmitter() {}

    // Shortcuts to improve speed and size
    var proto = EventEmitter.prototype;
    var exports = this;
    var originalGlobalValue = exports.EventEmitter;

    /**
     * Finds the index of the listener for the event in its storage array.
     *
     * @param {Function[]} listeners Array of listeners to search through.
     * @param {Function} listener Method to look for.
     * @return {Number} Index of the specified listener, -1 if not found
     * @api private
     */
    function indexOfListener(listeners, listener) {
        var i = listeners.length;
        while (i--) {
            if (listeners[i].listener === listener) {
                return i;
            }
        }

        return -1;
    }

    /**
     * Alias a method while keeping the context correct, to allow for overwriting of target method.
     *
     * @param {String} name The name of the target method.
     * @return {Function} The aliased method
     * @api private
     */
    function alias(name) {
        return function aliasClosure() {
            return this[name].apply(this, arguments);
        };
    }

    /**
     * Returns the listener array for the specified event.
     * Will initialise the event object and listener arrays if required.
     * Will return an object if you use a regex search. The object contains keys for each matched event. So /ba[rz]/ might return an object containing bar and baz. But only if you have either defined them with defineEvent or added some listeners to them.
     * Each property in the object response is an array of listener functions.
     *
     * @param {String|RegExp} evt Name of the event to return the listeners from.
     * @return {Function[]|Object} All listener functions for the event.
     */
    proto.getListeners = function getListeners(evt) {
        var events = this._getEvents();
        var response;
        var key;

        // Return a concatenated array of all matching events if
        // the selector is a regular expression.
        if (evt instanceof RegExp) {
            response = {};
            for (key in events) {
                if (events.hasOwnProperty(key) && evt.test(key)) {
                    response[key] = events[key];
                }
            }
        }
        else {
            response = events[evt] || (events[evt] = []);
        }

        return response;
    };

    /**
     * Takes a list of listener objects and flattens it into a list of listener functions.
     *
     * @param {Object[]} listeners Raw listener objects.
     * @return {Function[]} Just the listener functions.
     */
    proto.flattenListeners = function flattenListeners(listeners) {
        var flatListeners = [];
        var i;

        for (i = 0; i < listeners.length; i += 1) {
            flatListeners.push(listeners[i].listener);
        }

        return flatListeners;
    };

    /**
     * Fetches the requested listeners via getListeners but will always return the results inside an object. This is mainly for internal use but others may find it useful.
     *
     * @param {String|RegExp} evt Name of the event to return the listeners from.
     * @return {Object} All listener functions for an event in an object.
     */
    proto.getListenersAsObject = function getListenersAsObject(evt) {
        var listeners = this.getListeners(evt);
        var response;

        if (listeners instanceof Array) {
            response = {};
            response[evt] = listeners;
        }

        return response || listeners;
    };

    /**
     * Adds a listener function to the specified event.
     * The listener will not be added if it is a duplicate.
     * If the listener returns true then it will be removed after it is called.
     * If you pass a regular expression as the event name then the listener will be added to all events that match it.
     *
     * @param {String|RegExp} evt Name of the event to attach the listener to.
     * @param {Function} listener Method to be called when the event is emitted. If the function returns true then it will be removed after calling.
     * @return {Object} Current instance of EventEmitter for chaining.
     */
    proto.addListener = function addListener(evt, listener) {
        var listeners = this.getListenersAsObject(evt);
        var listenerIsWrapped = typeof listener === 'object';
        var key;

        for (key in listeners) {
            if (listeners.hasOwnProperty(key) && indexOfListener(listeners[key], listener) === -1) {
                listeners[key].push(listenerIsWrapped ? listener : {
                    listener: listener,
                    once: false
                });
            }
        }

        return this;
    };

    /**
     * Alias of addListener
     */
    proto.on = alias('addListener');

    /**
     * Semi-alias of addListener. It will add a listener that will be
     * automatically removed after its first execution.
     *
     * @param {String|RegExp} evt Name of the event to attach the listener to.
     * @param {Function} listener Method to be called when the event is emitted. If the function returns true then it will be removed after calling.
     * @return {Object} Current instance of EventEmitter for chaining.
     */
    proto.addOnceListener = function addOnceListener(evt, listener) {
        return this.addListener(evt, {
            listener: listener,
            once: true
        });
    };

    /**
     * Alias of addOnceListener.
     */
    proto.once = alias('addOnceListener');

    /**
     * Defines an event name. This is required if you want to use a regex to add a listener to multiple events at once. If you don't do this then how do you expect it to know what event to add to? Should it just add to every possible match for a regex? No. That is scary and bad.
     * You need to tell it what event names should be matched by a regex.
     *
     * @param {String} evt Name of the event to create.
     * @return {Object} Current instance of EventEmitter for chaining.
     */
    proto.defineEvent = function defineEvent(evt) {
        this.getListeners(evt);
        return this;
    };

    /**
     * Uses defineEvent to define multiple events.
     *
     * @param {String[]} evts An array of event names to define.
     * @return {Object} Current instance of EventEmitter for chaining.
     */
    proto.defineEvents = function defineEvents(evts) {
        for (var i = 0; i < evts.length; i += 1) {
            this.defineEvent(evts[i]);
        }
        return this;
    };

    /**
     * Removes a listener function from the specified event.
     * When passed a regular expression as the event name, it will remove the listener from all events that match it.
     *
     * @param {String|RegExp} evt Name of the event to remove the listener from.
     * @param {Function} listener Method to remove from the event.
     * @return {Object} Current instance of EventEmitter for chaining.
     */
    proto.removeListener = function removeListener(evt, listener) {
        var listeners = this.getListenersAsObject(evt);
        var index;
        var key;

        for (key in listeners) {
            if (listeners.hasOwnProperty(key)) {
                index = indexOfListener(listeners[key], listener);

                if (index !== -1) {
                    listeners[key].splice(index, 1);
                }
            }
        }

        return this;
    };

    /**
     * Alias of removeListener
     */
    proto.off = alias('removeListener');

    /**
     * Adds listeners in bulk using the manipulateListeners method.
     * If you pass an object as the second argument you can add to multiple events at once. The object should contain key value pairs of events and listeners or listener arrays. You can also pass it an event name and an array of listeners to be added.
     * You can also pass it a regular expression to add the array of listeners to all events that match it.
     * Yeah, this function does quite a bit. That's probably a bad thing.
     *
     * @param {String|Object|RegExp} evt An event name if you will pass an array of listeners next. An object if you wish to add to multiple events at once.
     * @param {Function[]} [listeners] An optional array of listener functions to add.
     * @return {Object} Current instance of EventEmitter for chaining.
     */
    proto.addListeners = function addListeners(evt, listeners) {
        // Pass through to manipulateListeners
        return this.manipulateListeners(false, evt, listeners);
    };

    /**
     * Removes listeners in bulk using the manipulateListeners method.
     * If you pass an object as the second argument you can remove from multiple events at once. The object should contain key value pairs of events and listeners or listener arrays.
     * You can also pass it an event name and an array of listeners to be removed.
     * You can also pass it a regular expression to remove the listeners from all events that match it.
     *
     * @param {String|Object|RegExp} evt An event name if you will pass an array of listeners next. An object if you wish to remove from multiple events at once.
     * @param {Function[]} [listeners] An optional array of listener functions to remove.
     * @return {Object} Current instance of EventEmitter for chaining.
     */
    proto.removeListeners = function removeListeners(evt, listeners) {
        // Pass through to manipulateListeners
        return this.manipulateListeners(true, evt, listeners);
    };

    /**
     * Edits listeners in bulk. The addListeners and removeListeners methods both use this to do their job. You should really use those instead, this is a little lower level.
     * The first argument will determine if the listeners are removed (true) or added (false).
     * If you pass an object as the second argument you can add/remove from multiple events at once. The object should contain key value pairs of events and listeners or listener arrays.
     * You can also pass it an event name and an array of listeners to be added/removed.
     * You can also pass it a regular expression to manipulate the listeners of all events that match it.
     *
     * @param {Boolean} remove True if you want to remove listeners, false if you want to add.
     * @param {String|Object|RegExp} evt An event name if you will pass an array of listeners next. An object if you wish to add/remove from multiple events at once.
     * @param {Function[]} [listeners] An optional array of listener functions to add/remove.
     * @return {Object} Current instance of EventEmitter for chaining.
     */
    proto.manipulateListeners = function manipulateListeners(remove, evt, listeners) {
        var i;
        var value;
        var single = remove ? this.removeListener : this.addListener;
        var multiple = remove ? this.removeListeners : this.addListeners;

        // If evt is an object then pass each of its properties to this method
        if (typeof evt === 'object' && !(evt instanceof RegExp)) {
            for (i in evt) {
                if (evt.hasOwnProperty(i) && (value = evt[i])) {
                    // Pass the single listener straight through to the singular method
                    if (typeof value === 'function') {
                        single.call(this, i, value);
                    }
                    else {
                        // Otherwise pass back to the multiple function
                        multiple.call(this, i, value);
                    }
                }
            }
        }
        else {
            // So evt must be a string
            // And listeners must be an array of listeners
            // Loop over it and pass each one to the multiple method
            i = listeners.length;
            while (i--) {
                single.call(this, evt, listeners[i]);
            }
        }

        return this;
    };

    /**
     * Removes all listeners from a specified event.
     * If you do not specify an event then all listeners will be removed.
     * That means every event will be emptied.
     * You can also pass a regex to remove all events that match it.
     *
     * @param {String|RegExp} [evt] Optional name of the event to remove all listeners for. Will remove from every event if not passed.
     * @return {Object} Current instance of EventEmitter for chaining.
     */
    proto.removeEvent = function removeEvent(evt) {
        var type = typeof evt;
        var events = this._getEvents();
        var key;

        // Remove different things depending on the state of evt
        if (type === 'string') {
            // Remove all listeners for the specified event
            delete events[evt];
        }
        else if (evt instanceof RegExp) {
            // Remove all events matching the regex.
            for (key in events) {
                if (events.hasOwnProperty(key) && evt.test(key)) {
                    delete events[key];
                }
            }
        }
        else {
            // Remove all listeners in all events
            delete this._events;
        }

        return this;
    };

    /**
     * Alias of removeEvent.
     *
     * Added to mirror the node API.
     */
    proto.removeAllListeners = alias('removeEvent');

    /**
     * Emits an event of your choice.
     * When emitted, every listener attached to that event will be executed.
     * If you pass the optional argument array then those arguments will be passed to every listener upon execution.
     * Because it uses `apply`, your array of arguments will be passed as if you wrote them out separately.
     * So they will not arrive within the array on the other side, they will be separate.
     * You can also pass a regular expression to emit to all events that match it.
     *
     * @param {String|RegExp} evt Name of the event to emit and execute listeners for.
     * @param {Array} [args] Optional array of arguments to be passed to each listener.
     * @return {Object} Current instance of EventEmitter for chaining.
     */
    proto.emitEvent = function emitEvent(evt, args) {
        var listenersMap = this.getListenersAsObject(evt);
        var listeners;
        var listener;
        var i;
        var key;
        var response;

        for (key in listenersMap) {
            if (listenersMap.hasOwnProperty(key)) {
                listeners = listenersMap[key].slice(0);
                i = listeners.length;

                while (i--) {
                    // If the listener returns true then it shall be removed from the event
                    // The function is executed either with a basic call or an apply if there is an args array
                    listener = listeners[i];

                    if (listener.once === true) {
                        this.removeListener(evt, listener.listener);
                    }

                    response = listener.listener.apply(this, args || []);

                    if (response === this._getOnceReturnValue()) {
                        this.removeListener(evt, listener.listener);
                    }
                }
            }
        }

        return this;
    };

    /**
     * Alias of emitEvent
     */
    proto.trigger = alias('emitEvent');

    /**
     * Subtly different from emitEvent in that it will pass its arguments on to the listeners, as opposed to taking a single array of arguments to pass on.
     * As with emitEvent, you can pass a regex in place of the event name to emit to all events that match it.
     *
     * @param {String|RegExp} evt Name of the event to emit and execute listeners for.
     * @param {...*} Optional additional arguments to be passed to each listener.
     * @return {Object} Current instance of EventEmitter for chaining.
     */
    proto.emit = function emit(evt) {
        var args = Array.prototype.slice.call(arguments, 1);
        return this.emitEvent(evt, args);
    };

    /**
     * Sets the current value to check against when executing listeners. If a
     * listeners return value matches the one set here then it will be removed
     * after execution. This value defaults to true.
     *
     * @param {*} value The new value to check for when executing listeners.
     * @return {Object} Current instance of EventEmitter for chaining.
     */
    proto.setOnceReturnValue = function setOnceReturnValue(value) {
        this._onceReturnValue = value;
        return this;
    };

    /**
     * Fetches the current value to check against when executing listeners. If
     * the listeners return value matches this one then it should be removed
     * automatically. It will return true by default.
     *
     * @return {*|Boolean} The current value to check for or the default, true.
     * @api private
     */
    proto._getOnceReturnValue = function _getOnceReturnValue() {
        if (this.hasOwnProperty('_onceReturnValue')) {
            return this._onceReturnValue;
        }
        else {
            return true;
        }
    };

    /**
     * Fetches the events object and creates one if required.
     *
     * @return {Object} The events storage object.
     * @api private
     */
    proto._getEvents = function _getEvents() {
        return this._events || (this._events = {});
    };

    /**
     * Reverts the global {@link EventEmitter} to its previous value and returns a reference to this version.
     *
     * @return {Function} Non conflicting EventEmitter class.
     */
    EventEmitter.noConflict = function noConflict() {
        exports.EventEmitter = originalGlobalValue;
        return EventEmitter;
    };

    // Expose the class either via AMD, CommonJS or the global object
    if (typeof define === 'function' && define.amd) {
        define(function () {
            return EventEmitter;
        });
    }
    else if (typeof module === 'object' && module.exports){
        module.exports = EventEmitter;
    }
    else {
        exports.EventEmitter = EventEmitter;
    }
}.call(this));

},{}]},{},[1])
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyaWZ5L25vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJhc3NldHMvYnJvd3NlcmlmeS9hcGkuanMiLCJhc3NldHMvYnJvd3NlcmlmeS9mb3Jtcy9mb3JtLmpzIiwiYXNzZXRzL2Jyb3dzZXJpZnkvZm9ybXMvZm9ybXMuanMiLCJhc3NldHMvYnJvd3NlcmlmeS90aGlyZC1wYXJ0eS9mb3JtMmpzLmpzIiwiYXNzZXRzL2Jyb3dzZXJpZnkvdGhpcmQtcGFydHkvc2VyaWFsaXplLmpzIiwibm9kZV9tb2R1bGVzL2dhdG9yL2dhdG9yLmpzIiwibm9kZV9tb2R1bGVzL3BvcHVsYXRlLmpzL3BvcHVsYXRlLmpzIiwibm9kZV9tb2R1bGVzL3dvbGZ5ODctZXZlbnRlbWl0dGVyL0V2ZW50RW1pdHRlci5qcyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTtBQ0FBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUMzREE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQzlEQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ25FQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUM1VkE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNoUUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDOVdBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNuRkE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EiLCJmaWxlIjoiZ2VuZXJhdGVkLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXNDb250ZW50IjpbIihmdW5jdGlvbiBlKHQsbixyKXtmdW5jdGlvbiBzKG8sdSl7aWYoIW5bb10pe2lmKCF0W29dKXt2YXIgYT10eXBlb2YgcmVxdWlyZT09XCJmdW5jdGlvblwiJiZyZXF1aXJlO2lmKCF1JiZhKXJldHVybiBhKG8sITApO2lmKGkpcmV0dXJuIGkobywhMCk7dmFyIGY9bmV3IEVycm9yKFwiQ2Fubm90IGZpbmQgbW9kdWxlICdcIitvK1wiJ1wiKTt0aHJvdyBmLmNvZGU9XCJNT0RVTEVfTk9UX0ZPVU5EXCIsZn12YXIgbD1uW29dPXtleHBvcnRzOnt9fTt0W29dWzBdLmNhbGwobC5leHBvcnRzLGZ1bmN0aW9uKGUpe3ZhciBuPXRbb11bMV1bZV07cmV0dXJuIHMobj9uOmUpfSxsLGwuZXhwb3J0cyxlLHQsbixyKX1yZXR1cm4gbltvXS5leHBvcnRzfXZhciBpPXR5cGVvZiByZXF1aXJlPT1cImZ1bmN0aW9uXCImJnJlcXVpcmU7Zm9yKHZhciBvPTA7bzxyLmxlbmd0aDtvKyspcyhyW29dKTtyZXR1cm4gc30pIiwiJ3VzZSBzdHJpY3QnO1xuXG4vLyBkZXBzXG52YXIgR2F0b3IgPSByZXF1aXJlKCdnYXRvcicpO1xudmFyIGZvcm1zID0gcmVxdWlyZSgnLi9mb3Jtcy9mb3Jtcy5qcycpO1xudmFyIGxpc3RlbmVycyA9IHdpbmRvdy5tYzR3cCAmJiB3aW5kb3cubWM0d3AubGlzdGVuZXJzID8gd2luZG93Lm1jNHdwLmxpc3RlbmVycyA6IFtdO1xudmFyIGNvbmZpZyA9IHdpbmRvdy5tYzR3cF9mb3Jtc19jb25maWcgfHwge307XG5cbi8vIGV4cG9zZSBzdHVmZiwgdGhpcyBvdmVycmlkZXMgZHVtbXkgamF2YXNjcmlwdFxud2luZG93Lm1jNHdwID0ge1xuXHRcImZvcm1zXCI6IGZvcm1zXG59O1xuXG4vLyByZWdpc3RlciBlYXJseSBsaXN0ZW5lcnNcbmZvcih2YXIgaT0wOyBpPGxpc3RlbmVycy5sZW5ndGg7aSsrKSB7XG5cdGZvcm1zLm9uKGxpc3RlbmVyc1tpXS5ldmVudCwgbGlzdGVuZXJzW2ldLmNhbGxiYWNrKTtcbn1cblxuLy8gd2FzIGEgZm9ybSBzdWJtaXR0ZWQ/XG5pZiggY29uZmlnLnN1Ym1pdHRlZF9mb3JtICYmIGNvbmZpZy5zdWJtaXR0ZWRfZm9ybS5pZCApIHtcblx0dmFyIGZvcm0gPSBmb3Jtcy5nZXQoY29uZmlnLnN1Ym1pdHRlZF9mb3JtLmlkKTtcblxuXHQvLyBhZGQgY2xhc3MgJiB0cmlnZ2VyIGV2ZW50XG5cdGZvcm1zLnRyaWdnZXIoICdzdWJtaXR0ZWQnLCBbZm9ybV0pO1xuXG5cdGlmKCBjb25maWcuc3VibWl0dGVkX2Zvcm0uZXJyb3JzICkge1xuXHRcdC8vIGZvcm0gaGFzIGVycm9ycywgcmVwb3B1bGF0ZSBpdC5cblx0XHRmb3JtLnNldERhdGEoY29uZmlnLnN1Ym1pdHRlZF9mb3JtLmRhdGEpO1xuXHRcdGZvcm1zLnRyaWdnZXIoJ2Vycm9yJywgW2Zvcm0sIGNvbmZpZy5zdWJtaXR0ZWRfZm9ybS5lcnJvcnNdKTtcblx0fSBlbHNlIHtcblx0XHQvLyBmb3JtIHdhcyBzdWNjZXNzZnVsbHkgc3VibWl0dGVkXG5cdFx0Zm9ybXMudHJpZ2dlcignc3VjY2VzcycsIFtmb3JtLCBjb25maWcuc3VibWl0dGVkX2Zvcm0uZGF0YV0pO1xuXHRcdGZvcm1zLnRyaWdnZXIoY29uZmlnLnN1Ym1pdHRlZF9mb3JtLmFjdGlvbiArIFwiZFwiLCBbZm9ybSwgY29uZmlnLnN1Ym1pdHRlZF9mb3JtLmRhdGFdKTtcblx0fVxufVxuXG4vLyBCaW5kIGJyb3dzZXIgZXZlbnRzIHRvIGZvcm0gZXZlbnRzICh1c2luZyBkZWxlZ2F0aW9uIHRvIHdvcmsgd2l0aCBBSkFYIGxvYWRlZCBmb3JtcyBhcyB3ZWxsKVxuR2F0b3IoZG9jdW1lbnQuYm9keSkub24oJ3N1Ym1pdCcsICcubWM0d3AtZm9ybScsIGZ1bmN0aW9uKGV2ZW50KSB7XG5cdGV2ZW50ID0gZXZlbnQgfHwgd2luZG93LmV2ZW50O1xuXHR2YXIgZm9ybSA9IGZvcm1zLmdldEJ5RWxlbWVudChldmVudC50YXJnZXQgfHwgZXZlbnQuc3JjRWxlbWVudCk7XG5cdGZvcm1zLnRyaWdnZXIoJ3N1Ym1pdCcsIFtmb3JtLCBldmVudF0pO1xufSk7XG5cbkdhdG9yKGRvY3VtZW50LmJvZHkpLm9uKCdmb2N1cycsICcubWM0d3AtZm9ybScsIGZ1bmN0aW9uKGV2ZW50KSB7XG5cdGV2ZW50ID0gZXZlbnQgfHwgd2luZG93LmV2ZW50O1xuXHR2YXIgZm9ybSA9IGZvcm1zLmdldEJ5RWxlbWVudChldmVudC50YXJnZXQgfHwgZXZlbnQuc3JjRWxlbWVudCk7XG5cdGlmKCAhIGZvcm0uc3RhcnRlZCApIHtcblx0XHRmb3Jtcy50cmlnZ2VyKCdzdGFydCcsIFtmb3JtLCBldmVudF0pO1xuXHR9XG59KTtcblxuR2F0b3IoZG9jdW1lbnQuYm9keSkub24oJ2NoYW5nZScsICcubWM0d3AtZm9ybScsIGZ1bmN0aW9uKGV2ZW50KSB7XG5cdGV2ZW50ID0gZXZlbnQgfHwgd2luZG93LmV2ZW50O1xuXHR2YXIgZm9ybSA9IGZvcm1zLmdldEJ5RWxlbWVudChldmVudC50YXJnZXQgfHwgZXZlbnQuc3JjRWxlbWVudCk7XG5cdGZvcm1zLnRyaWdnZXIoJ2NoYW5nZScsIFtmb3JtLGV2ZW50XSk7XG59KTtcblxuXG5cbiIsIid1c2Ugc3RyaWN0JztcblxudmFyIHNlcmlhbGl6ZSA9IHJlcXVpcmUoJy4uL3RoaXJkLXBhcnR5L3NlcmlhbGl6ZS5qcycpO1xudmFyIHBvcHVsYXRlID0gcmVxdWlyZSgncG9wdWxhdGUuanMnKTtcbnZhciBmb3JtVG9Kc29uID0gcmVxdWlyZSgnLi4vdGhpcmQtcGFydHkvZm9ybTJqcy5qcycpO1xuXG52YXIgRm9ybSA9IGZ1bmN0aW9uKGlkLCBlbGVtZW50KSB7XG5cblx0dmFyIGZvcm0gPSB0aGlzO1xuXG5cdHRoaXMuaWQgPSBpZDtcblx0dGhpcy5lbGVtZW50ID0gZWxlbWVudCB8fCBkb2N1bWVudC5jcmVhdGVFbGVtZW50KCdmb3JtJyk7XG5cdHRoaXMubmFtZSA9IHRoaXMuZWxlbWVudC5nZXRBdHRyaWJ1dGUoJ2RhdGEtbmFtZScpIHx8IFwiRm9ybSAjXCIgKyB0aGlzLmlkO1xuXHR0aGlzLmVycm9ycyA9IFtdO1xuXHR0aGlzLnN0YXJ0ZWQgPSBmYWxzZTtcblxuXHR0aGlzLnNldERhdGEgPSBmdW5jdGlvbihkYXRhKSB7XG5cdFx0cG9wdWxhdGUoZm9ybS5lbGVtZW50LCBkYXRhKTtcblx0fTtcblxuXHR0aGlzLmdldERhdGEgPSBmdW5jdGlvbigpIHtcblx0XHRyZXR1cm4gZm9ybVRvSnNvbihmb3JtLmVsZW1lbnQpO1xuXHR9O1xuXG5cdHRoaXMuZ2V0U2VyaWFsaXplZERhdGEgPSBmdW5jdGlvbigpIHtcblx0XHRyZXR1cm4gc2VyaWFsaXplKGZvcm0uZWxlbWVudCk7XG5cdH07XG5cblx0dGhpcy5zZXRSZXNwb25zZSA9IGZ1bmN0aW9uKCBtc2cgKSB7XG5cdFx0Zm9ybS5lbGVtZW50LnF1ZXJ5U2VsZWN0b3IoJy5tYzR3cC1yZXNwb25zZScpLmlubmVySFRNTCA9IG1zZztcblx0fTtcblxuXHR0aGlzLnBsYWNlSW50b1ZpZXcgPSBmdW5jdGlvbiggYW5pbWF0ZSApIHtcblx0XHR2YXIgc2Nyb2xsVG9IZWlnaHQgPSAwO1xuXHRcdHZhciB3aW5kb3dIZWlnaHQgPSB3aW5kb3cuaW5uZXJIZWlnaHQ7XG5cdFx0dmFyIG9iaiA9IGZvcm0uZWxlbWVudDtcblxuXHRcdGlmIChvYmoub2Zmc2V0UGFyZW50KSB7XG5cdFx0XHRkbyB7XG5cdFx0XHRcdHNjcm9sbFRvSGVpZ2h0ICs9IG9iai5vZmZzZXRUb3A7XG5cdFx0XHR9IHdoaWxlIChvYmogPSBvYmoub2Zmc2V0UGFyZW50KTtcblx0XHR9IGVsc2Uge1xuXHRcdFx0c2Nyb2xsVG9IZWlnaHQgPSBmb3JtLmVsZW1lbnQub2Zmc2V0VG9wO1xuXHRcdH1cblxuXHRcdGlmKCh3aW5kb3dIZWlnaHQgLSA4MCkgPiBmb3JtLmVsZW1lbnQuY2xpZW50SGVpZ2h0KSB7XG5cdFx0XHQvLyB2ZXJ0aWNhbGx5IGNlbnRlciB0aGUgZm9ybSwgYnV0IG9ubHkgaWYgdGhlcmUncyBlbm91Z2ggc3BhY2UgZm9yIGEgZGVjZW50IG1hcmdpblxuXHRcdFx0c2Nyb2xsVG9IZWlnaHQgPSBzY3JvbGxUb0hlaWdodCAtICgod2luZG93SGVpZ2h0IC0gZm9ybS5lbGVtZW50LmNsaWVudEhlaWdodCkgLyAyKTtcblx0XHR9IGVsc2Uge1xuXHRcdFx0Ly8gdGhlIGZvcm0gZG9lc24ndCBmaXQsIHNjcm9sbCBhIGxpdHRsZSBhYm92ZSB0aGUgZm9ybVxuXHRcdFx0c2Nyb2xsVG9IZWlnaHQgPSBzY3JvbGxUb0hlaWdodCAtIDgwO1xuXHRcdH1cblxuXHRcdC8vIHNjcm9sbCB0aGVyZS4gaWYgalF1ZXJ5IGlzIGxvYWRlZCwgZG8gaXQgd2l0aCBhbiBhbmltYXRpb24uXG5cdFx0aWYoIGFuaW1hdGUgJiYgd2luZG93LmpRdWVyeSApIHtcblx0XHRcdHdpbmRvdy5qUXVlcnkoJ2h0bWwsIGJvZHknKS5hbmltYXRlKHsgc2Nyb2xsVG9wOiBzY3JvbGxUb0hlaWdodCB9LCA4MDApO1xuXHRcdH0gZWxzZSB7XG5cdFx0XHR3aW5kb3cuc2Nyb2xsVG8oMCwgc2Nyb2xsVG9IZWlnaHQpO1xuXHRcdH1cblx0fTtcbn07XG5cbm1vZHVsZS5leHBvcnRzID0gRm9ybTsiLCIndXNlIHN0cmljdCc7XG5cbi8vIGRlcHNcbnZhciBFdmVudEVtaXR0ZXIgPSByZXF1aXJlKCd3b2xmeTg3LWV2ZW50ZW1pdHRlcicpO1xudmFyIEZvcm0gPSByZXF1aXJlKCcuL2Zvcm0uanMnKTtcblxuLy8gdmFyaWFibGVzXG52YXIgZXZlbnRzID0gbmV3IEV2ZW50RW1pdHRlcigpO1xudmFyIGZvcm1zID0gW107XG5cbi8vIGdldCBmb3JtIGJ5IGl0cyBpZFxuZnVuY3Rpb24gZ2V0KGZvcm1JZCkge1xuXHRmb3IodmFyIGk9MDsgaTxmb3Jtcy5sZW5ndGg7aSsrKSB7XG5cdFx0aWYoZm9ybXNbaV0uaWQgPT0gZm9ybUlkKSB7XG5cdFx0XHRyZXR1cm4gZm9ybXNbaV07XG5cdFx0fVxuXHR9XG5cblx0dmFyIGZvcm1FbGVtZW50ID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvcignLm1jNHdwLWZvcm0tJyArIGZvcm1JZCk7XG5cdHJldHVybiBjcmVhdGVGcm9tRWxlbWVudChmb3JtRWxlbWVudCxmb3JtSWQpO1xufVxuXG4vLyBnZXQgZm9ybSBieSA8Zm9ybT4gZWxlbWVudCAob3IgYW55IGlucHV0IGluIGZvcm0pXG5mdW5jdGlvbiBnZXRCeUVsZW1lbnQoZWxlbWVudCkge1xuXHR2YXIgZm9ybUVsZW1lbnQgPSBlbGVtZW50LmZvcm0gfHwgZWxlbWVudDtcblx0Zm9yKHZhciBpPTA7IGk8Zm9ybXMubGVuZ3RoO2krKykge1xuXHRcdGlmKGZvcm1zW2ldLmVsZW1lbnQgPT0gZm9ybUVsZW1lbnQpIHtcblx0XHRcdHJldHVybiBmb3Jtc1tpXTtcblx0XHR9XG5cdH1cblxuXHRyZXR1cm4gY3JlYXRlRnJvbUVsZW1lbnQoZWxlbWVudCk7XG59XG5cbi8vIGNyZWF0ZSBmb3JtIG9iamVjdCBmcm9tIDxmb3JtPiBlbGVtZW50XG5mdW5jdGlvbiBjcmVhdGVGcm9tRWxlbWVudChmb3JtRWxlbWVudCxpZCkge1xuXHRpZCA9IGlkIHx8IHBhcnNlSW50KCBmb3JtRWxlbWVudC5nZXRBdHRyaWJ1dGUoJ2RhdGEtaWQnKSApIHx8IDA7XG5cdHZhciBmb3JtID0gbmV3IEZvcm0oaWQsZm9ybUVsZW1lbnQpO1xuXHRmb3Jtcy5wdXNoKGZvcm0pO1xuXHRyZXR1cm4gZm9ybTtcbn1cblxuZnVuY3Rpb24gYWxsKCkge1xuXHRyZXR1cm4gZm9ybXM7XG59XG5cbmZ1bmN0aW9uIG9uKGV2ZW50LGNhbGxiYWNrKSB7XG5cdHJldHVybiBldmVudHMub24oZXZlbnQsY2FsbGJhY2spO1xufVxuXG5mdW5jdGlvbiB0cmlnZ2VyKGV2ZW50LGFyZ3MpIHtcblx0cmV0dXJuIGV2ZW50cy50cmlnZ2VyKGV2ZW50LGFyZ3MpO1xufVxuXG5mdW5jdGlvbiBvZmYoZXZlbnQsY2FsbGJhY2spIHtcblx0cmV0dXJuIGV2ZW50cy5vZmYoZXZlbnQsY2FsbGJhY2spO1xufVxuXG5tb2R1bGUuZXhwb3J0cyA9IHtcblx0XCJhbGxcIjogYWxsLFxuXHRcImdldFwiOiBnZXQsXG5cdFwiZ2V0QnlFbGVtZW50XCI6IGdldEJ5RWxlbWVudCxcblx0XCJvblwiOiBvbixcblx0XCJ0cmlnZ2VyXCI6IHRyaWdnZXIsXG5cdFwib2ZmXCI6IG9mZlxufTtcblxuIiwiLyoqXG4gKiBDb3B5cmlnaHQgKGMpIDIwMTAgTWF4aW0gVmFzaWxpZXZcbiAqXG4gKiBQZXJtaXNzaW9uIGlzIGhlcmVieSBncmFudGVkLCBmcmVlIG9mIGNoYXJnZSwgdG8gYW55IHBlcnNvbiBvYnRhaW5pbmcgYSBjb3B5XG4gKiBvZiB0aGlzIHNvZnR3YXJlIGFuZCBhc3NvY2lhdGVkIGRvY3VtZW50YXRpb24gZmlsZXMgKHRoZSBcIlNvZnR3YXJlXCIpLCB0byBkZWFsXG4gKiBpbiB0aGUgU29mdHdhcmUgd2l0aG91dCByZXN0cmljdGlvbiwgaW5jbHVkaW5nIHdpdGhvdXQgbGltaXRhdGlvbiB0aGUgcmlnaHRzXG4gKiB0byB1c2UsIGNvcHksIG1vZGlmeSwgbWVyZ2UsIHB1Ymxpc2gsIGRpc3RyaWJ1dGUsIHN1YmxpY2Vuc2UsIGFuZC9vciBzZWxsXG4gKiBjb3BpZXMgb2YgdGhlIFNvZnR3YXJlLCBhbmQgdG8gcGVybWl0IHBlcnNvbnMgdG8gd2hvbSB0aGUgU29mdHdhcmUgaXNcbiAqIGZ1cm5pc2hlZCB0byBkbyBzbywgc3ViamVjdCB0byB0aGUgZm9sbG93aW5nIGNvbmRpdGlvbnM6XG4gKlxuICogVGhlIGFib3ZlIGNvcHlyaWdodCBub3RpY2UgYW5kIHRoaXMgcGVybWlzc2lvbiBub3RpY2Ugc2hhbGwgYmUgaW5jbHVkZWQgaW5cbiAqIGFsbCBjb3BpZXMgb3Igc3Vic3RhbnRpYWwgcG9ydGlvbnMgb2YgdGhlIFNvZnR3YXJlLlxuICpcbiAqIFRIRSBTT0ZUV0FSRSBJUyBQUk9WSURFRCBcIkFTIElTXCIsIFdJVEhPVVQgV0FSUkFOVFkgT0YgQU5ZIEtJTkQsIEVYUFJFU1MgT1JcbiAqIElNUExJRUQsIElOQ0xVRElORyBCVVQgTk9UIExJTUlURUQgVE8gVEhFIFdBUlJBTlRJRVMgT0YgTUVSQ0hBTlRBQklMSVRZLFxuICogRklUTkVTUyBGT1IgQSBQQVJUSUNVTEFSIFBVUlBPU0UgQU5EIE5PTklORlJJTkdFTUVOVC4gSU4gTk8gRVZFTlQgU0hBTEwgVEhFXG4gKiBBVVRIT1JTIE9SIENPUFlSSUdIVCBIT0xERVJTIEJFIExJQUJMRSBGT1IgQU5ZIENMQUlNLCBEQU1BR0VTIE9SIE9USEVSXG4gKiBMSUFCSUxJVFksIFdIRVRIRVIgSU4gQU4gQUNUSU9OIE9GIENPTlRSQUNULCBUT1JUIE9SIE9USEVSV0lTRSwgQVJJU0lORyBGUk9NLFxuICogT1VUIE9GIE9SIElOIENPTk5FQ1RJT04gV0lUSCBUSEUgU09GVFdBUkUgT1IgVEhFIFVTRSBPUiBPVEhFUiBERUFMSU5HUyBJTlxuICogVEhFIFNPRlRXQVJFLlxuICpcbiAqIEBhdXRob3IgTWF4aW0gVmFzaWxpZXZcbiAqIERhdGU6IDA5LjA5LjIwMTBcbiAqIFRpbWU6IDE5OjAyOjMzXG4gKi9cblxuXG4oZnVuY3Rpb24gKHJvb3QsIGZhY3RvcnkpXG57XG5cdGlmICh0eXBlb2YgZXhwb3J0cyAhPT0gJ3VuZGVmaW5lZCcgJiYgdHlwZW9mIG1vZHVsZSAhPT0gJ3VuZGVmaW5lZCcgJiYgbW9kdWxlLmV4cG9ydHMpIHtcblx0XHQvLyBOb2RlSlNcblx0XHRtb2R1bGUuZXhwb3J0cyA9IGZhY3RvcnkoKTtcblx0fVxuXHRlbHNlIGlmICh0eXBlb2YgZGVmaW5lID09PSAnZnVuY3Rpb24nICYmIGRlZmluZS5hbWQpXG5cdHtcblx0XHQvLyBBTUQuIFJlZ2lzdGVyIGFzIGFuIGFub255bW91cyBtb2R1bGUuXG5cdFx0ZGVmaW5lKGZhY3RvcnkpO1xuXHR9XG5cdGVsc2Vcblx0e1xuXHRcdC8vIEJyb3dzZXIgZ2xvYmFsc1xuXHRcdHJvb3QuZm9ybTJqcyA9IGZhY3RvcnkoKTtcblx0fVxufSh0aGlzLCBmdW5jdGlvbiAoKVxue1xuXHRcInVzZSBzdHJpY3RcIjtcblxuXHQvKipcblx0ICogUmV0dXJucyBmb3JtIHZhbHVlcyByZXByZXNlbnRlZCBhcyBKYXZhc2NyaXB0IG9iamVjdFxuXHQgKiBcIm5hbWVcIiBhdHRyaWJ1dGUgZGVmaW5lcyBzdHJ1Y3R1cmUgb2YgcmVzdWx0aW5nIG9iamVjdFxuXHQgKlxuXHQgKiBAcGFyYW0gcm9vdE5vZGUge0VsZW1lbnR8U3RyaW5nfSByb290IGZvcm0gZWxlbWVudCAob3IgaXQncyBpZCkgb3IgYXJyYXkgb2Ygcm9vdCBlbGVtZW50c1xuXHQgKiBAcGFyYW0gZGVsaW1pdGVyIHtTdHJpbmd9IHN0cnVjdHVyZSBwYXJ0cyBkZWxpbWl0ZXIgZGVmYXVsdHMgdG8gJy4nXG5cdCAqIEBwYXJhbSBza2lwRW1wdHkge0Jvb2xlYW59IHNob3VsZCBza2lwIGVtcHR5IHRleHQgdmFsdWVzLCBkZWZhdWx0cyB0byB0cnVlXG5cdCAqIEBwYXJhbSBub2RlQ2FsbGJhY2sge0Z1bmN0aW9ufSBjdXN0b20gZnVuY3Rpb24gdG8gZ2V0IG5vZGUgdmFsdWVcblx0ICogQHBhcmFtIHVzZUlkSWZFbXB0eU5hbWUge0Jvb2xlYW59IGlmIHRydWUgdmFsdWUgb2YgaWQgYXR0cmlidXRlIG9mIGZpZWxkIHdpbGwgYmUgdXNlZCBpZiBuYW1lIG9mIGZpZWxkIGlzIGVtcHR5XG5cdCAqL1xuXHRmdW5jdGlvbiBmb3JtMmpzKHJvb3ROb2RlLCBkZWxpbWl0ZXIsIHNraXBFbXB0eSwgbm9kZUNhbGxiYWNrLCB1c2VJZElmRW1wdHlOYW1lLCBnZXREaXNhYmxlZClcblx0e1xuXHRcdGdldERpc2FibGVkID0gZ2V0RGlzYWJsZWQgPyB0cnVlIDogZmFsc2U7XG5cdFx0aWYgKHR5cGVvZiBza2lwRW1wdHkgPT0gJ3VuZGVmaW5lZCcgfHwgc2tpcEVtcHR5ID09IG51bGwpIHNraXBFbXB0eSA9IHRydWU7XG5cdFx0aWYgKHR5cGVvZiBkZWxpbWl0ZXIgPT0gJ3VuZGVmaW5lZCcgfHwgZGVsaW1pdGVyID09IG51bGwpIGRlbGltaXRlciA9ICcuJztcblx0XHRpZiAoYXJndW1lbnRzLmxlbmd0aCA8IDUpIHVzZUlkSWZFbXB0eU5hbWUgPSBmYWxzZTtcblxuXHRcdHJvb3ROb2RlID0gdHlwZW9mIHJvb3ROb2RlID09ICdzdHJpbmcnID8gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQocm9vdE5vZGUpIDogcm9vdE5vZGU7XG5cblx0XHR2YXIgZm9ybVZhbHVlcyA9IFtdLFxuXHRcdFx0Y3Vyck5vZGUsXG5cdFx0XHRpID0gMDtcblxuXHRcdC8qIElmIHJvb3ROb2RlIGlzIGFycmF5IC0gY29tYmluZSB2YWx1ZXMgKi9cblx0XHRpZiAocm9vdE5vZGUuY29uc3RydWN0b3IgPT0gQXJyYXkgfHwgKHR5cGVvZiBOb2RlTGlzdCAhPSBcInVuZGVmaW5lZFwiICYmIHJvb3ROb2RlLmNvbnN0cnVjdG9yID09IE5vZGVMaXN0KSlcblx0XHR7XG5cdFx0XHR3aGlsZShjdXJyTm9kZSA9IHJvb3ROb2RlW2krK10pXG5cdFx0XHR7XG5cdFx0XHRcdGZvcm1WYWx1ZXMgPSBmb3JtVmFsdWVzLmNvbmNhdChnZXRGb3JtVmFsdWVzKGN1cnJOb2RlLCBub2RlQ2FsbGJhY2ssIHVzZUlkSWZFbXB0eU5hbWUsIGdldERpc2FibGVkKSk7XG5cdFx0XHR9XG5cdFx0fVxuXHRcdGVsc2Vcblx0XHR7XG5cdFx0XHRmb3JtVmFsdWVzID0gZ2V0Rm9ybVZhbHVlcyhyb290Tm9kZSwgbm9kZUNhbGxiYWNrLCB1c2VJZElmRW1wdHlOYW1lLCBnZXREaXNhYmxlZCk7XG5cdFx0fVxuXG5cdFx0cmV0dXJuIHByb2Nlc3NOYW1lVmFsdWVzKGZvcm1WYWx1ZXMsIHNraXBFbXB0eSwgZGVsaW1pdGVyKTtcblx0fVxuXG5cdC8qKlxuXHQgKiBQcm9jZXNzZXMgY29sbGVjdGlvbiBvZiB7IG5hbWU6ICduYW1lJywgdmFsdWU6ICd2YWx1ZScgfSBvYmplY3RzLlxuXHQgKiBAcGFyYW0gbmFtZVZhbHVlc1xuXHQgKiBAcGFyYW0gc2tpcEVtcHR5IGlmIHRydWUgc2tpcHMgZWxlbWVudHMgd2l0aCB2YWx1ZSA9PSAnJyBvciB2YWx1ZSA9PSBudWxsXG5cdCAqIEBwYXJhbSBkZWxpbWl0ZXJcblx0ICovXG5cdGZ1bmN0aW9uIHByb2Nlc3NOYW1lVmFsdWVzKG5hbWVWYWx1ZXMsIHNraXBFbXB0eSwgZGVsaW1pdGVyKVxuXHR7XG5cdFx0dmFyIHJlc3VsdCA9IHt9LFxuXHRcdFx0YXJyYXlzID0ge30sXG5cdFx0XHRpLCBqLCBrLCBsLFxuXHRcdFx0dmFsdWUsXG5cdFx0XHRuYW1lUGFydHMsXG5cdFx0XHRjdXJyUmVzdWx0LFxuXHRcdFx0YXJyTmFtZUZ1bGwsXG5cdFx0XHRhcnJOYW1lLFxuXHRcdFx0YXJySWR4LFxuXHRcdFx0bmFtZVBhcnQsXG5cdFx0XHRuYW1lLFxuXHRcdFx0X25hbWVQYXJ0cztcblxuXHRcdGZvciAoaSA9IDA7IGkgPCBuYW1lVmFsdWVzLmxlbmd0aDsgaSsrKVxuXHRcdHtcblx0XHRcdHZhbHVlID0gbmFtZVZhbHVlc1tpXS52YWx1ZTtcblxuXHRcdFx0aWYgKHNraXBFbXB0eSAmJiAodmFsdWUgPT09ICcnIHx8IHZhbHVlID09PSBudWxsKSkgY29udGludWU7XG5cblx0XHRcdG5hbWUgPSBuYW1lVmFsdWVzW2ldLm5hbWU7XG5cdFx0XHRfbmFtZVBhcnRzID0gbmFtZS5zcGxpdChkZWxpbWl0ZXIpO1xuXHRcdFx0bmFtZVBhcnRzID0gW107XG5cdFx0XHRjdXJyUmVzdWx0ID0gcmVzdWx0O1xuXHRcdFx0YXJyTmFtZUZ1bGwgPSAnJztcblxuXHRcdFx0Zm9yKGogPSAwOyBqIDwgX25hbWVQYXJ0cy5sZW5ndGg7IGorKylcblx0XHRcdHtcblx0XHRcdFx0bmFtZVBhcnQgPSBfbmFtZVBhcnRzW2pdLnNwbGl0KCddWycpO1xuXHRcdFx0XHRpZiAobmFtZVBhcnQubGVuZ3RoID4gMSlcblx0XHRcdFx0e1xuXHRcdFx0XHRcdGZvcihrID0gMDsgayA8IG5hbWVQYXJ0Lmxlbmd0aDsgaysrKVxuXHRcdFx0XHRcdHtcblx0XHRcdFx0XHRcdGlmIChrID09IDApXG5cdFx0XHRcdFx0XHR7XG5cdFx0XHRcdFx0XHRcdG5hbWVQYXJ0W2tdID0gbmFtZVBhcnRba10gKyAnXSc7XG5cdFx0XHRcdFx0XHR9XG5cdFx0XHRcdFx0XHRlbHNlIGlmIChrID09IG5hbWVQYXJ0Lmxlbmd0aCAtIDEpXG5cdFx0XHRcdFx0XHR7XG5cdFx0XHRcdFx0XHRcdG5hbWVQYXJ0W2tdID0gJ1snICsgbmFtZVBhcnRba107XG5cdFx0XHRcdFx0XHR9XG5cdFx0XHRcdFx0XHRlbHNlXG5cdFx0XHRcdFx0XHR7XG5cdFx0XHRcdFx0XHRcdG5hbWVQYXJ0W2tdID0gJ1snICsgbmFtZVBhcnRba10gKyAnXSc7XG5cdFx0XHRcdFx0XHR9XG5cblx0XHRcdFx0XHRcdGFycklkeCA9IG5hbWVQYXJ0W2tdLm1hdGNoKC8oW2Etel9dKyk/XFxbKFthLXpfXVthLXowLTlfXSs/KVxcXS9pKTtcblx0XHRcdFx0XHRcdGlmIChhcnJJZHgpXG5cdFx0XHRcdFx0XHR7XG5cdFx0XHRcdFx0XHRcdGZvcihsID0gMTsgbCA8IGFycklkeC5sZW5ndGg7IGwrKylcblx0XHRcdFx0XHRcdFx0e1xuXHRcdFx0XHRcdFx0XHRcdGlmIChhcnJJZHhbbF0pIG5hbWVQYXJ0cy5wdXNoKGFycklkeFtsXSk7XG5cdFx0XHRcdFx0XHRcdH1cblx0XHRcdFx0XHRcdH1cblx0XHRcdFx0XHRcdGVsc2V7XG5cdFx0XHRcdFx0XHRcdG5hbWVQYXJ0cy5wdXNoKG5hbWVQYXJ0W2tdKTtcblx0XHRcdFx0XHRcdH1cblx0XHRcdFx0XHR9XG5cdFx0XHRcdH1cblx0XHRcdFx0ZWxzZVxuXHRcdFx0XHRcdG5hbWVQYXJ0cyA9IG5hbWVQYXJ0cy5jb25jYXQobmFtZVBhcnQpO1xuXHRcdFx0fVxuXG5cdFx0XHRmb3IgKGogPSAwOyBqIDwgbmFtZVBhcnRzLmxlbmd0aDsgaisrKVxuXHRcdFx0e1xuXHRcdFx0XHRuYW1lUGFydCA9IG5hbWVQYXJ0c1tqXTtcblxuXHRcdFx0XHRpZiAobmFtZVBhcnQuaW5kZXhPZignW10nKSA+IC0xICYmIGogPT0gbmFtZVBhcnRzLmxlbmd0aCAtIDEpXG5cdFx0XHRcdHtcblx0XHRcdFx0XHRhcnJOYW1lID0gbmFtZVBhcnQuc3Vic3RyKDAsIG5hbWVQYXJ0LmluZGV4T2YoJ1snKSk7XG5cdFx0XHRcdFx0YXJyTmFtZUZ1bGwgKz0gYXJyTmFtZTtcblxuXHRcdFx0XHRcdGlmICghY3VyclJlc3VsdFthcnJOYW1lXSkgY3VyclJlc3VsdFthcnJOYW1lXSA9IFtdO1xuXHRcdFx0XHRcdGN1cnJSZXN1bHRbYXJyTmFtZV0ucHVzaCh2YWx1ZSk7XG5cdFx0XHRcdH1cblx0XHRcdFx0ZWxzZSBpZiAobmFtZVBhcnQuaW5kZXhPZignWycpID4gLTEpXG5cdFx0XHRcdHtcblx0XHRcdFx0XHRhcnJOYW1lID0gbmFtZVBhcnQuc3Vic3RyKDAsIG5hbWVQYXJ0LmluZGV4T2YoJ1snKSk7XG5cdFx0XHRcdFx0YXJySWR4ID0gbmFtZVBhcnQucmVwbGFjZSgvKF4oW2Etel9dKyk/XFxbKXwoXFxdJCkvZ2ksICcnKTtcblxuXHRcdFx0XHRcdC8qIFVuaXF1ZSBhcnJheSBuYW1lICovXG5cdFx0XHRcdFx0YXJyTmFtZUZ1bGwgKz0gJ18nICsgYXJyTmFtZSArICdfJyArIGFycklkeDtcblxuXHRcdFx0XHRcdC8qXG5cdFx0XHRcdFx0ICogQmVjYXVzZSBhcnJJZHggaW4gZmllbGQgbmFtZSBjYW4gYmUgbm90IHplcm8tYmFzZWQgYW5kIHN0ZXAgY2FuIGJlXG5cdFx0XHRcdFx0ICogb3RoZXIgdGhhbiAxLCB3ZSBjYW4ndCB1c2UgdGhlbSBpbiB0YXJnZXQgYXJyYXkgZGlyZWN0bHkuXG5cdFx0XHRcdFx0ICogSW5zdGVhZCB3ZSdyZSBtYWtpbmcgYSBoYXNoIHdoZXJlIGtleSBpcyBhcnJJZHggYW5kIHZhbHVlIGlzIGEgcmVmZXJlbmNlIHRvXG5cdFx0XHRcdFx0ICogYWRkZWQgYXJyYXkgZWxlbWVudFxuXHRcdFx0XHRcdCAqL1xuXG5cdFx0XHRcdFx0aWYgKCFhcnJheXNbYXJyTmFtZUZ1bGxdKSBhcnJheXNbYXJyTmFtZUZ1bGxdID0ge307XG5cdFx0XHRcdFx0aWYgKGFyck5hbWUgIT0gJycgJiYgIWN1cnJSZXN1bHRbYXJyTmFtZV0pIGN1cnJSZXN1bHRbYXJyTmFtZV0gPSBbXTtcblxuXHRcdFx0XHRcdGlmIChqID09IG5hbWVQYXJ0cy5sZW5ndGggLSAxKVxuXHRcdFx0XHRcdHtcblx0XHRcdFx0XHRcdGlmIChhcnJOYW1lID09ICcnKVxuXHRcdFx0XHRcdFx0e1xuXHRcdFx0XHRcdFx0XHRjdXJyUmVzdWx0LnB1c2godmFsdWUpO1xuXHRcdFx0XHRcdFx0XHRhcnJheXNbYXJyTmFtZUZ1bGxdW2FycklkeF0gPSBjdXJyUmVzdWx0W2N1cnJSZXN1bHQubGVuZ3RoIC0gMV07XG5cdFx0XHRcdFx0XHR9XG5cdFx0XHRcdFx0XHRlbHNlXG5cdFx0XHRcdFx0XHR7XG5cdFx0XHRcdFx0XHRcdGN1cnJSZXN1bHRbYXJyTmFtZV0ucHVzaCh2YWx1ZSk7XG5cdFx0XHRcdFx0XHRcdGFycmF5c1thcnJOYW1lRnVsbF1bYXJySWR4XSA9IGN1cnJSZXN1bHRbYXJyTmFtZV1bY3VyclJlc3VsdFthcnJOYW1lXS5sZW5ndGggLSAxXTtcblx0XHRcdFx0XHRcdH1cblx0XHRcdFx0XHR9XG5cdFx0XHRcdFx0ZWxzZVxuXHRcdFx0XHRcdHtcblx0XHRcdFx0XHRcdGlmICghYXJyYXlzW2Fyck5hbWVGdWxsXVthcnJJZHhdKVxuXHRcdFx0XHRcdFx0e1xuXHRcdFx0XHRcdFx0XHRpZiAoKC9eWzAtOWEtel9dK1xcWz8vaSkudGVzdChuYW1lUGFydHNbaisxXSkpIGN1cnJSZXN1bHRbYXJyTmFtZV0ucHVzaCh7fSk7XG5cdFx0XHRcdFx0XHRcdGVsc2UgY3VyclJlc3VsdFthcnJOYW1lXS5wdXNoKFtdKTtcblxuXHRcdFx0XHRcdFx0XHRhcnJheXNbYXJyTmFtZUZ1bGxdW2FycklkeF0gPSBjdXJyUmVzdWx0W2Fyck5hbWVdW2N1cnJSZXN1bHRbYXJyTmFtZV0ubGVuZ3RoIC0gMV07XG5cdFx0XHRcdFx0XHR9XG5cdFx0XHRcdFx0fVxuXG5cdFx0XHRcdFx0Y3VyclJlc3VsdCA9IGFycmF5c1thcnJOYW1lRnVsbF1bYXJySWR4XTtcblx0XHRcdFx0fVxuXHRcdFx0XHRlbHNlXG5cdFx0XHRcdHtcblx0XHRcdFx0XHRhcnJOYW1lRnVsbCArPSBuYW1lUGFydDtcblxuXHRcdFx0XHRcdGlmIChqIDwgbmFtZVBhcnRzLmxlbmd0aCAtIDEpIC8qIE5vdCB0aGUgbGFzdCBwYXJ0IG9mIG5hbWUgLSBtZWFucyBvYmplY3QgKi9cblx0XHRcdFx0XHR7XG5cdFx0XHRcdFx0XHRpZiAoIWN1cnJSZXN1bHRbbmFtZVBhcnRdKSBjdXJyUmVzdWx0W25hbWVQYXJ0XSA9IHt9O1xuXHRcdFx0XHRcdFx0Y3VyclJlc3VsdCA9IGN1cnJSZXN1bHRbbmFtZVBhcnRdO1xuXHRcdFx0XHRcdH1cblx0XHRcdFx0XHRlbHNlXG5cdFx0XHRcdFx0e1xuXHRcdFx0XHRcdFx0Y3VyclJlc3VsdFtuYW1lUGFydF0gPSB2YWx1ZTtcblx0XHRcdFx0XHR9XG5cdFx0XHRcdH1cblx0XHRcdH1cblx0XHR9XG5cblx0XHRyZXR1cm4gcmVzdWx0O1xuXHR9XG5cblx0ZnVuY3Rpb24gZ2V0Rm9ybVZhbHVlcyhyb290Tm9kZSwgbm9kZUNhbGxiYWNrLCB1c2VJZElmRW1wdHlOYW1lLCBnZXREaXNhYmxlZClcblx0e1xuXHRcdHZhciByZXN1bHQgPSBleHRyYWN0Tm9kZVZhbHVlcyhyb290Tm9kZSwgbm9kZUNhbGxiYWNrLCB1c2VJZElmRW1wdHlOYW1lLCBnZXREaXNhYmxlZCk7XG5cdFx0cmV0dXJuIHJlc3VsdC5sZW5ndGggPiAwID8gcmVzdWx0IDogZ2V0U3ViRm9ybVZhbHVlcyhyb290Tm9kZSwgbm9kZUNhbGxiYWNrLCB1c2VJZElmRW1wdHlOYW1lLCBnZXREaXNhYmxlZCk7XG5cdH1cblxuXHRmdW5jdGlvbiBnZXRTdWJGb3JtVmFsdWVzKHJvb3ROb2RlLCBub2RlQ2FsbGJhY2ssIHVzZUlkSWZFbXB0eU5hbWUsIGdldERpc2FibGVkKVxuXHR7XG5cdFx0dmFyIHJlc3VsdCA9IFtdLFxuXHRcdFx0Y3VycmVudE5vZGUgPSByb290Tm9kZS5maXJzdENoaWxkO1xuXG5cdFx0d2hpbGUgKGN1cnJlbnROb2RlKVxuXHRcdHtcblx0XHRcdHJlc3VsdCA9IHJlc3VsdC5jb25jYXQoZXh0cmFjdE5vZGVWYWx1ZXMoY3VycmVudE5vZGUsIG5vZGVDYWxsYmFjaywgdXNlSWRJZkVtcHR5TmFtZSwgZ2V0RGlzYWJsZWQpKTtcblx0XHRcdGN1cnJlbnROb2RlID0gY3VycmVudE5vZGUubmV4dFNpYmxpbmc7XG5cdFx0fVxuXG5cdFx0cmV0dXJuIHJlc3VsdDtcblx0fVxuXG5cdGZ1bmN0aW9uIGV4dHJhY3ROb2RlVmFsdWVzKG5vZGUsIG5vZGVDYWxsYmFjaywgdXNlSWRJZkVtcHR5TmFtZSwgZ2V0RGlzYWJsZWQpIHtcblx0XHRpZiAobm9kZS5kaXNhYmxlZCAmJiAhZ2V0RGlzYWJsZWQpIHJldHVybiBbXTtcblxuXHRcdHZhciBjYWxsYmFja1Jlc3VsdCwgZmllbGRWYWx1ZSwgcmVzdWx0LCBmaWVsZE5hbWUgPSBnZXRGaWVsZE5hbWUobm9kZSwgdXNlSWRJZkVtcHR5TmFtZSk7XG5cblx0XHRjYWxsYmFja1Jlc3VsdCA9IG5vZGVDYWxsYmFjayAmJiBub2RlQ2FsbGJhY2sobm9kZSk7XG5cblx0XHRpZiAoY2FsbGJhY2tSZXN1bHQgJiYgY2FsbGJhY2tSZXN1bHQubmFtZSkge1xuXHRcdFx0cmVzdWx0ID0gW2NhbGxiYWNrUmVzdWx0XTtcblx0XHR9XG5cdFx0ZWxzZSBpZiAoZmllbGROYW1lICE9ICcnICYmIG5vZGUubm9kZU5hbWUubWF0Y2goL0lOUFVUfFRFWFRBUkVBL2kpKSB7XG5cdFx0XHRmaWVsZFZhbHVlID0gZ2V0RmllbGRWYWx1ZShub2RlLCBnZXREaXNhYmxlZCk7XG5cdFx0XHRpZiAobnVsbCA9PT0gZmllbGRWYWx1ZSkge1xuXHRcdFx0XHRyZXN1bHQgPSBbXTtcblx0XHRcdH0gZWxzZSB7XG5cdFx0XHRcdHJlc3VsdCA9IFsgeyBuYW1lOiBmaWVsZE5hbWUsIHZhbHVlOiBmaWVsZFZhbHVlfSBdO1xuXHRcdFx0fVxuXHRcdH1cblx0XHRlbHNlIGlmIChmaWVsZE5hbWUgIT0gJycgJiYgbm9kZS5ub2RlTmFtZS5tYXRjaCgvU0VMRUNUL2kpKSB7XG5cdFx0XHRmaWVsZFZhbHVlID0gZ2V0RmllbGRWYWx1ZShub2RlLCBnZXREaXNhYmxlZCk7XG5cdFx0XHRyZXN1bHQgPSBbIHsgbmFtZTogZmllbGROYW1lLnJlcGxhY2UoL1xcW1xcXSQvLCAnJyksIHZhbHVlOiBmaWVsZFZhbHVlIH0gXTtcblx0XHR9XG5cdFx0ZWxzZSB7XG5cdFx0XHRyZXN1bHQgPSBnZXRTdWJGb3JtVmFsdWVzKG5vZGUsIG5vZGVDYWxsYmFjaywgdXNlSWRJZkVtcHR5TmFtZSwgZ2V0RGlzYWJsZWQpO1xuXHRcdH1cblxuXHRcdHJldHVybiByZXN1bHQ7XG5cdH1cblxuXHRmdW5jdGlvbiBnZXRGaWVsZE5hbWUobm9kZSwgdXNlSWRJZkVtcHR5TmFtZSlcblx0e1xuXHRcdGlmIChub2RlLm5hbWUgJiYgbm9kZS5uYW1lICE9ICcnKSByZXR1cm4gbm9kZS5uYW1lO1xuXHRcdGVsc2UgaWYgKHVzZUlkSWZFbXB0eU5hbWUgJiYgbm9kZS5pZCAmJiBub2RlLmlkICE9ICcnKSByZXR1cm4gbm9kZS5pZDtcblx0XHRlbHNlIHJldHVybiAnJztcblx0fVxuXG5cblx0ZnVuY3Rpb24gZ2V0RmllbGRWYWx1ZShmaWVsZE5vZGUsIGdldERpc2FibGVkKVxuXHR7XG5cdFx0aWYgKGZpZWxkTm9kZS5kaXNhYmxlZCAmJiAhZ2V0RGlzYWJsZWQpIHJldHVybiBudWxsO1xuXG5cdFx0c3dpdGNoIChmaWVsZE5vZGUubm9kZU5hbWUpIHtcblx0XHRcdGNhc2UgJ0lOUFVUJzpcblx0XHRcdGNhc2UgJ1RFWFRBUkVBJzpcblx0XHRcdFx0c3dpdGNoIChmaWVsZE5vZGUudHlwZS50b0xvd2VyQ2FzZSgpKSB7XG5cdFx0XHRcdFx0Y2FzZSAncmFkaW8nOlxuXHRcdFx0XHRcdFx0aWYgKGZpZWxkTm9kZS5jaGVja2VkICYmIGZpZWxkTm9kZS52YWx1ZSA9PT0gXCJmYWxzZVwiKSByZXR1cm4gZmFsc2U7XG5cdFx0XHRcdFx0Y2FzZSAnY2hlY2tib3gnOlxuXHRcdFx0XHRcdFx0aWYgKGZpZWxkTm9kZS5jaGVja2VkICYmIGZpZWxkTm9kZS52YWx1ZSA9PT0gXCJ0cnVlXCIpIHJldHVybiB0cnVlO1xuXHRcdFx0XHRcdFx0aWYgKCFmaWVsZE5vZGUuY2hlY2tlZCAmJiBmaWVsZE5vZGUudmFsdWUgPT09IFwidHJ1ZVwiKSByZXR1cm4gZmFsc2U7XG5cdFx0XHRcdFx0XHRpZiAoZmllbGROb2RlLmNoZWNrZWQpIHJldHVybiBmaWVsZE5vZGUudmFsdWU7XG5cdFx0XHRcdFx0XHRicmVhaztcblxuXHRcdFx0XHRcdGNhc2UgJ2J1dHRvbic6XG5cdFx0XHRcdFx0Y2FzZSAncmVzZXQnOlxuXHRcdFx0XHRcdGNhc2UgJ3N1Ym1pdCc6XG5cdFx0XHRcdFx0Y2FzZSAnaW1hZ2UnOlxuXHRcdFx0XHRcdFx0cmV0dXJuICcnO1xuXHRcdFx0XHRcdFx0YnJlYWs7XG5cblx0XHRcdFx0XHRkZWZhdWx0OlxuXHRcdFx0XHRcdFx0cmV0dXJuIGZpZWxkTm9kZS52YWx1ZTtcblx0XHRcdFx0XHRcdGJyZWFrO1xuXHRcdFx0XHR9XG5cdFx0XHRcdGJyZWFrO1xuXG5cdFx0XHRjYXNlICdTRUxFQ1QnOlxuXHRcdFx0XHRyZXR1cm4gZ2V0U2VsZWN0ZWRPcHRpb25WYWx1ZShmaWVsZE5vZGUpO1xuXHRcdFx0XHRicmVhaztcblxuXHRcdFx0ZGVmYXVsdDpcblx0XHRcdFx0YnJlYWs7XG5cdFx0fVxuXG5cdFx0cmV0dXJuIG51bGw7XG5cdH1cblxuXHRmdW5jdGlvbiBnZXRTZWxlY3RlZE9wdGlvblZhbHVlKHNlbGVjdE5vZGUpXG5cdHtcblx0XHR2YXIgbXVsdGlwbGUgPSBzZWxlY3ROb2RlLm11bHRpcGxlLFxuXHRcdFx0cmVzdWx0ID0gW10sXG5cdFx0XHRvcHRpb25zLFxuXHRcdFx0aSwgbDtcblxuXHRcdGlmICghbXVsdGlwbGUpIHJldHVybiBzZWxlY3ROb2RlLnZhbHVlO1xuXG5cdFx0Zm9yIChvcHRpb25zID0gc2VsZWN0Tm9kZS5nZXRFbGVtZW50c0J5VGFnTmFtZShcIm9wdGlvblwiKSwgaSA9IDAsIGwgPSBvcHRpb25zLmxlbmd0aDsgaSA8IGw7IGkrKylcblx0XHR7XG5cdFx0XHRpZiAob3B0aW9uc1tpXS5zZWxlY3RlZCkgcmVzdWx0LnB1c2gob3B0aW9uc1tpXS52YWx1ZSk7XG5cdFx0fVxuXG5cdFx0cmV0dXJuIHJlc3VsdDtcblx0fVxuXG5cdHJldHVybiBmb3JtMmpzO1xuXG59KSk7IiwiLy8gZ2V0IHN1Y2Nlc3NmdWwgY29udHJvbCBmcm9tIGZvcm0gYW5kIGFzc2VtYmxlIGludG8gb2JqZWN0XG4vLyBodHRwOi8vd3d3LnczLm9yZy9UUi9odG1sNDAxL2ludGVyYWN0L2Zvcm1zLmh0bWwjaC0xNy4xMy4yXG5cbi8vIHR5cGVzIHdoaWNoIGluZGljYXRlIGEgc3VibWl0IGFjdGlvbiBhbmQgYXJlIG5vdCBzdWNjZXNzZnVsIGNvbnRyb2xzXG4vLyB0aGVzZSB3aWxsIGJlIGlnbm9yZWRcbnZhciBrX3Jfc3VibWl0dGVyID0gL14oPzpzdWJtaXR8YnV0dG9ufGltYWdlfHJlc2V0fGZpbGUpJC9pO1xuXG4vLyBub2RlIG5hbWVzIHdoaWNoIGNvdWxkIGJlIHN1Y2Nlc3NmdWwgY29udHJvbHNcbnZhciBrX3Jfc3VjY2Vzc19jb250cmxzID0gL14oPzppbnB1dHxzZWxlY3R8dGV4dGFyZWF8a2V5Z2VuKS9pO1xuXG4vLyBNYXRjaGVzIGJyYWNrZXQgbm90YXRpb24uXG52YXIgYnJhY2tldHMgPSAvKFxcW1teXFxbXFxdXSpcXF0pL2c7XG5cbi8vIHNlcmlhbGl6ZXMgZm9ybSBmaWVsZHNcbi8vIEBwYXJhbSBmb3JtIE1VU1QgYmUgYW4gSFRNTEZvcm0gZWxlbWVudFxuLy8gQHBhcmFtIG9wdGlvbnMgaXMgYW4gb3B0aW9uYWwgYXJndW1lbnQgdG8gY29uZmlndXJlIHRoZSBzZXJpYWxpemF0aW9uLiBEZWZhdWx0IG91dHB1dFxuLy8gd2l0aCBubyBvcHRpb25zIHNwZWNpZmllZCBpcyBhIHVybCBlbmNvZGVkIHN0cmluZ1xuLy8gICAgLSBoYXNoOiBbdHJ1ZSB8IGZhbHNlXSBDb25maWd1cmUgdGhlIG91dHB1dCB0eXBlLiBJZiB0cnVlLCB0aGUgb3V0cHV0IHdpbGxcbi8vICAgIGJlIGEganMgb2JqZWN0LlxuLy8gICAgLSBzZXJpYWxpemVyOiBbZnVuY3Rpb25dIE9wdGlvbmFsIHNlcmlhbGl6ZXIgZnVuY3Rpb24gdG8gb3ZlcnJpZGUgdGhlIGRlZmF1bHQgb25lLlxuLy8gICAgVGhlIGZ1bmN0aW9uIHRha2VzIDMgYXJndW1lbnRzIChyZXN1bHQsIGtleSwgdmFsdWUpIGFuZCBzaG91bGQgcmV0dXJuIG5ldyByZXN1bHRcbi8vICAgIGhhc2ggYW5kIHVybCBlbmNvZGVkIHN0ciBzZXJpYWxpemVycyBhcmUgcHJvdmlkZWQgd2l0aCB0aGlzIG1vZHVsZVxuLy8gICAgLSBkaXNhYmxlZDogW3RydWUgfCBmYWxzZV0uIElmIHRydWUgc2VyaWFsaXplIGRpc2FibGVkIGZpZWxkcy5cbi8vICAgIC0gZW1wdHk6IFt0cnVlIHwgZmFsc2VdLiBJZiB0cnVlIHNlcmlhbGl6ZSBlbXB0eSBmaWVsZHNcbmZ1bmN0aW9uIHNlcmlhbGl6ZShmb3JtLCBvcHRpb25zKSB7XG5cdGlmICh0eXBlb2Ygb3B0aW9ucyAhPSAnb2JqZWN0Jykge1xuXHRcdG9wdGlvbnMgPSB7IGhhc2g6ICEhb3B0aW9ucyB9O1xuXHR9XG5cdGVsc2UgaWYgKG9wdGlvbnMuaGFzaCA9PT0gdW5kZWZpbmVkKSB7XG5cdFx0b3B0aW9ucy5oYXNoID0gdHJ1ZTtcblx0fVxuXG5cdHZhciByZXN1bHQgPSAob3B0aW9ucy5oYXNoKSA/IHt9IDogJyc7XG5cdHZhciBzZXJpYWxpemVyID0gb3B0aW9ucy5zZXJpYWxpemVyIHx8ICgob3B0aW9ucy5oYXNoKSA/IGhhc2hfc2VyaWFsaXplciA6IHN0cl9zZXJpYWxpemUpO1xuXG5cdHZhciBlbGVtZW50cyA9IGZvcm0gJiYgZm9ybS5lbGVtZW50cyA/IGZvcm0uZWxlbWVudHMgOiBbXTtcblxuXHQvL09iamVjdCBzdG9yZSBlYWNoIHJhZGlvIGFuZCBzZXQgaWYgaXQncyBlbXB0eSBvciBub3Rcblx0dmFyIHJhZGlvX3N0b3JlID0gT2JqZWN0LmNyZWF0ZShudWxsKTtcblxuXHRmb3IgKHZhciBpPTAgOyBpPGVsZW1lbnRzLmxlbmd0aCA7ICsraSkge1xuXHRcdHZhciBlbGVtZW50ID0gZWxlbWVudHNbaV07XG5cblx0XHQvLyBpbmdvcmUgZGlzYWJsZWQgZmllbGRzXG5cdFx0aWYgKCghb3B0aW9ucy5kaXNhYmxlZCAmJiBlbGVtZW50LmRpc2FibGVkKSB8fCAhZWxlbWVudC5uYW1lKSB7XG5cdFx0XHRjb250aW51ZTtcblx0XHR9XG5cdFx0Ly8gaWdub3JlIGFueWh0aW5nIHRoYXQgaXMgbm90IGNvbnNpZGVyZWQgYSBzdWNjZXNzIGZpZWxkXG5cdFx0aWYgKCFrX3Jfc3VjY2Vzc19jb250cmxzLnRlc3QoZWxlbWVudC5ub2RlTmFtZSkgfHxcblx0XHRcdGtfcl9zdWJtaXR0ZXIudGVzdChlbGVtZW50LnR5cGUpKSB7XG5cdFx0XHRjb250aW51ZTtcblx0XHR9XG5cblx0XHR2YXIga2V5ID0gZWxlbWVudC5uYW1lO1xuXHRcdHZhciB2YWwgPSBlbGVtZW50LnZhbHVlO1xuXG5cdFx0Ly8gd2UgY2FuJ3QganVzdCB1c2UgZWxlbWVudC52YWx1ZSBmb3IgY2hlY2tib3hlcyBjYXVzZSBzb21lIGJyb3dzZXJzIGxpZSB0byB1c1xuXHRcdC8vIHRoZXkgc2F5IFwib25cIiBmb3IgdmFsdWUgd2hlbiB0aGUgYm94IGlzbid0IGNoZWNrZWRcblx0XHRpZiAoKGVsZW1lbnQudHlwZSA9PT0gJ2NoZWNrYm94JyB8fCBlbGVtZW50LnR5cGUgPT09ICdyYWRpbycpICYmICFlbGVtZW50LmNoZWNrZWQpIHtcblx0XHRcdHZhbCA9IHVuZGVmaW5lZDtcblx0XHR9XG5cblx0XHQvLyBJZiB3ZSB3YW50IGVtcHR5IGVsZW1lbnRzXG5cdFx0aWYgKG9wdGlvbnMuZW1wdHkpIHtcblx0XHRcdC8vIGZvciBjaGVja2JveFxuXHRcdFx0aWYgKGVsZW1lbnQudHlwZSA9PT0gJ2NoZWNrYm94JyAmJiAhZWxlbWVudC5jaGVja2VkKSB7XG5cdFx0XHRcdHZhbCA9ICcnO1xuXHRcdFx0fVxuXG5cdFx0XHQvLyBmb3IgcmFkaW9cblx0XHRcdGlmIChlbGVtZW50LnR5cGUgPT09ICdyYWRpbycpIHtcblx0XHRcdFx0aWYgKCFyYWRpb19zdG9yZVtlbGVtZW50Lm5hbWVdICYmICFlbGVtZW50LmNoZWNrZWQpIHtcblx0XHRcdFx0XHRyYWRpb19zdG9yZVtlbGVtZW50Lm5hbWVdID0gZmFsc2U7XG5cdFx0XHRcdH1cblx0XHRcdFx0ZWxzZSBpZiAoZWxlbWVudC5jaGVja2VkKSB7XG5cdFx0XHRcdFx0cmFkaW9fc3RvcmVbZWxlbWVudC5uYW1lXSA9IHRydWU7XG5cdFx0XHRcdH1cblx0XHRcdH1cblxuXHRcdFx0Ly8gaWYgb3B0aW9ucyBlbXB0eSBpcyB0cnVlLCBjb250aW51ZSBvbmx5IGlmIGl0cyByYWRpb1xuXHRcdFx0aWYgKCF2YWwgJiYgZWxlbWVudC50eXBlID09ICdyYWRpbycpIHtcblx0XHRcdFx0Y29udGludWU7XG5cdFx0XHR9XG5cdFx0fVxuXHRcdGVsc2Uge1xuXHRcdFx0Ly8gdmFsdWUtbGVzcyBmaWVsZHMgYXJlIGlnbm9yZWQgdW5sZXNzIG9wdGlvbnMuZW1wdHkgaXMgdHJ1ZVxuXHRcdFx0aWYgKCF2YWwpIHtcblx0XHRcdFx0Y29udGludWU7XG5cdFx0XHR9XG5cdFx0fVxuXG5cdFx0Ly8gbXVsdGkgc2VsZWN0IGJveGVzXG5cdFx0aWYgKGVsZW1lbnQudHlwZSA9PT0gJ3NlbGVjdC1tdWx0aXBsZScpIHtcblx0XHRcdHZhbCA9IFtdO1xuXG5cdFx0XHR2YXIgc2VsZWN0T3B0aW9ucyA9IGVsZW1lbnQub3B0aW9ucztcblx0XHRcdHZhciBpc1NlbGVjdGVkT3B0aW9ucyA9IGZhbHNlO1xuXHRcdFx0Zm9yICh2YXIgaj0wIDsgajxzZWxlY3RPcHRpb25zLmxlbmd0aCA7ICsraikge1xuXHRcdFx0XHR2YXIgb3B0aW9uID0gc2VsZWN0T3B0aW9uc1tqXTtcblx0XHRcdFx0dmFyIGFsbG93ZWRFbXB0eSA9IG9wdGlvbnMuZW1wdHkgJiYgIW9wdGlvbi52YWx1ZTtcblx0XHRcdFx0dmFyIGhhc1ZhbHVlID0gKG9wdGlvbi52YWx1ZSB8fCBhbGxvd2VkRW1wdHkpO1xuXHRcdFx0XHRpZiAob3B0aW9uLnNlbGVjdGVkICYmIGhhc1ZhbHVlKSB7XG5cdFx0XHRcdFx0aXNTZWxlY3RlZE9wdGlvbnMgPSB0cnVlO1xuXG5cdFx0XHRcdFx0Ly8gSWYgdXNpbmcgYSBoYXNoIHNlcmlhbGl6ZXIgYmUgc3VyZSB0byBhZGQgdGhlXG5cdFx0XHRcdFx0Ly8gY29ycmVjdCBub3RhdGlvbiBmb3IgYW4gYXJyYXkgaW4gdGhlIG11bHRpLXNlbGVjdFxuXHRcdFx0XHRcdC8vIGNvbnRleHQuIEhlcmUgdGhlIG5hbWUgYXR0cmlidXRlIG9uIHRoZSBzZWxlY3QgZWxlbWVudFxuXHRcdFx0XHRcdC8vIG1pZ2h0IGJlIG1pc3NpbmcgdGhlIHRyYWlsaW5nIGJyYWNrZXQgcGFpci4gQm90aCBuYW1lc1xuXHRcdFx0XHRcdC8vIFwiZm9vXCIgYW5kIFwiZm9vW11cIiBzaG91bGQgYmUgYXJyYXlzLlxuXHRcdFx0XHRcdGlmIChvcHRpb25zLmhhc2ggJiYga2V5LnNsaWNlKGtleS5sZW5ndGggLSAyKSAhPT0gJ1tdJykge1xuXHRcdFx0XHRcdFx0cmVzdWx0ID0gc2VyaWFsaXplcihyZXN1bHQsIGtleSArICdbXScsIG9wdGlvbi52YWx1ZSk7XG5cdFx0XHRcdFx0fVxuXHRcdFx0XHRcdGVsc2Uge1xuXHRcdFx0XHRcdFx0cmVzdWx0ID0gc2VyaWFsaXplcihyZXN1bHQsIGtleSwgb3B0aW9uLnZhbHVlKTtcblx0XHRcdFx0XHR9XG5cdFx0XHRcdH1cblx0XHRcdH1cblxuXHRcdFx0Ly8gU2VyaWFsaXplIGlmIG5vIHNlbGVjdGVkIG9wdGlvbnMgYW5kIG9wdGlvbnMuZW1wdHkgaXMgdHJ1ZVxuXHRcdFx0aWYgKCFpc1NlbGVjdGVkT3B0aW9ucyAmJiBvcHRpb25zLmVtcHR5KSB7XG5cdFx0XHRcdHJlc3VsdCA9IHNlcmlhbGl6ZXIocmVzdWx0LCBrZXksICcnKTtcblx0XHRcdH1cblxuXHRcdFx0Y29udGludWU7XG5cdFx0fVxuXG5cdFx0cmVzdWx0ID0gc2VyaWFsaXplcihyZXN1bHQsIGtleSwgdmFsKTtcblx0fVxuXG5cdC8vIENoZWNrIGZvciBhbGwgZW1wdHkgcmFkaW8gYnV0dG9ucyBhbmQgc2VyaWFsaXplIHRoZW0gd2l0aCBrZXk9XCJcIlxuXHRpZiAob3B0aW9ucy5lbXB0eSkge1xuXHRcdGZvciAodmFyIGtleSBpbiByYWRpb19zdG9yZSkge1xuXHRcdFx0aWYgKCFyYWRpb19zdG9yZVtrZXldKSB7XG5cdFx0XHRcdHJlc3VsdCA9IHNlcmlhbGl6ZXIocmVzdWx0LCBrZXksICcnKTtcblx0XHRcdH1cblx0XHR9XG5cdH1cblxuXHRyZXR1cm4gcmVzdWx0O1xufVxuXG5mdW5jdGlvbiBwYXJzZV9rZXlzKHN0cmluZykge1xuXHR2YXIga2V5cyA9IFtdO1xuXHR2YXIgcHJlZml4ID0gL14oW15cXFtcXF1dKikvO1xuXHR2YXIgY2hpbGRyZW4gPSBuZXcgUmVnRXhwKGJyYWNrZXRzKTtcblx0dmFyIG1hdGNoID0gcHJlZml4LmV4ZWMoc3RyaW5nKTtcblxuXHRpZiAobWF0Y2hbMV0pIHtcblx0XHRrZXlzLnB1c2gobWF0Y2hbMV0pO1xuXHR9XG5cblx0d2hpbGUgKChtYXRjaCA9IGNoaWxkcmVuLmV4ZWMoc3RyaW5nKSkgIT09IG51bGwpIHtcblx0XHRrZXlzLnB1c2gobWF0Y2hbMV0pO1xuXHR9XG5cblx0cmV0dXJuIGtleXM7XG59XG5cbmZ1bmN0aW9uIGhhc2hfYXNzaWduKHJlc3VsdCwga2V5cywgdmFsdWUpIHtcblx0aWYgKGtleXMubGVuZ3RoID09PSAwKSB7XG5cdFx0cmVzdWx0ID0gdmFsdWU7XG5cdFx0cmV0dXJuIHJlc3VsdDtcblx0fVxuXG5cdHZhciBrZXkgPSBrZXlzLnNoaWZ0KCk7XG5cdHZhciBiZXR3ZWVuID0ga2V5Lm1hdGNoKC9eXFxbKC4rPylcXF0kLyk7XG5cblx0aWYgKGtleSA9PT0gJ1tdJykge1xuXHRcdHJlc3VsdCA9IHJlc3VsdCB8fCBbXTtcblxuXHRcdGlmIChBcnJheS5pc0FycmF5KHJlc3VsdCkpIHtcblx0XHRcdHJlc3VsdC5wdXNoKGhhc2hfYXNzaWduKG51bGwsIGtleXMsIHZhbHVlKSk7XG5cdFx0fVxuXHRcdGVsc2Uge1xuXHRcdFx0Ly8gVGhpcyBtaWdodCBiZSB0aGUgcmVzdWx0IG9mIGJhZCBuYW1lIGF0dHJpYnV0ZXMgbGlrZSBcIltdW2Zvb11cIixcblx0XHRcdC8vIGluIHRoaXMgY2FzZSB0aGUgb3JpZ2luYWwgYHJlc3VsdGAgb2JqZWN0IHdpbGwgYWxyZWFkeSBiZVxuXHRcdFx0Ly8gYXNzaWduZWQgdG8gYW4gb2JqZWN0IGxpdGVyYWwuIFJhdGhlciB0aGFuIGNvZXJjZSB0aGUgb2JqZWN0IHRvXG5cdFx0XHQvLyBhbiBhcnJheSwgb3IgY2F1c2UgYW4gZXhjZXB0aW9uIHRoZSBhdHRyaWJ1dGUgXCJfdmFsdWVzXCIgaXNcblx0XHRcdC8vIGFzc2lnbmVkIGFzIGFuIGFycmF5LlxuXHRcdFx0cmVzdWx0Ll92YWx1ZXMgPSByZXN1bHQuX3ZhbHVlcyB8fCBbXTtcblx0XHRcdHJlc3VsdC5fdmFsdWVzLnB1c2goaGFzaF9hc3NpZ24obnVsbCwga2V5cywgdmFsdWUpKTtcblx0XHR9XG5cblx0XHRyZXR1cm4gcmVzdWx0O1xuXHR9XG5cblx0Ly8gS2V5IGlzIGFuIGF0dHJpYnV0ZSBuYW1lIGFuZCBjYW4gYmUgYXNzaWduZWQgZGlyZWN0bHkuXG5cdGlmICghYmV0d2Vlbikge1xuXHRcdHJlc3VsdFtrZXldID0gaGFzaF9hc3NpZ24ocmVzdWx0W2tleV0sIGtleXMsIHZhbHVlKTtcblx0fVxuXHRlbHNlIHtcblx0XHR2YXIgc3RyaW5nID0gYmV0d2VlblsxXTtcblx0XHR2YXIgaW5kZXggPSBwYXJzZUludChzdHJpbmcsIDEwKTtcblxuXHRcdC8vIElmIHRoZSBjaGFyYWN0ZXJzIGJldHdlZW4gdGhlIGJyYWNrZXRzIGlzIG5vdCBhIG51bWJlciBpdCBpcyBhblxuXHRcdC8vIGF0dHJpYnV0ZSBuYW1lIGFuZCBjYW4gYmUgYXNzaWduZWQgZGlyZWN0bHkuXG5cdFx0aWYgKGlzTmFOKGluZGV4KSkge1xuXHRcdFx0cmVzdWx0ID0gcmVzdWx0IHx8IHt9O1xuXHRcdFx0cmVzdWx0W3N0cmluZ10gPSBoYXNoX2Fzc2lnbihyZXN1bHRbc3RyaW5nXSwga2V5cywgdmFsdWUpO1xuXHRcdH1cblx0XHRlbHNlIHtcblx0XHRcdHJlc3VsdCA9IHJlc3VsdCB8fCBbXTtcblx0XHRcdHJlc3VsdFtpbmRleF0gPSBoYXNoX2Fzc2lnbihyZXN1bHRbaW5kZXhdLCBrZXlzLCB2YWx1ZSk7XG5cdFx0fVxuXHR9XG5cblx0cmV0dXJuIHJlc3VsdDtcbn1cblxuLy8gT2JqZWN0L2hhc2ggZW5jb2Rpbmcgc2VyaWFsaXplci5cbmZ1bmN0aW9uIGhhc2hfc2VyaWFsaXplcihyZXN1bHQsIGtleSwgdmFsdWUpIHtcblx0dmFyIG1hdGNoZXMgPSBrZXkubWF0Y2goYnJhY2tldHMpO1xuXG5cdC8vIEhhcyBicmFja2V0cz8gVXNlIHRoZSByZWN1cnNpdmUgYXNzaWdubWVudCBmdW5jdGlvbiB0byB3YWxrIHRoZSBrZXlzLFxuXHQvLyBjb25zdHJ1Y3QgYW55IG1pc3Npbmcgb2JqZWN0cyBpbiB0aGUgcmVzdWx0IHRyZWUgYW5kIG1ha2UgdGhlIGFzc2lnbm1lbnRcblx0Ly8gYXQgdGhlIGVuZCBvZiB0aGUgY2hhaW4uXG5cdGlmIChtYXRjaGVzKSB7XG5cdFx0dmFyIGtleXMgPSBwYXJzZV9rZXlzKGtleSk7XG5cdFx0aGFzaF9hc3NpZ24ocmVzdWx0LCBrZXlzLCB2YWx1ZSk7XG5cdH1cblx0ZWxzZSB7XG5cdFx0Ly8gTm9uIGJyYWNrZXQgbm90YXRpb24gY2FuIG1ha2UgYXNzaWdubWVudHMgZGlyZWN0bHkuXG5cdFx0dmFyIGV4aXN0aW5nID0gcmVzdWx0W2tleV07XG5cblx0XHQvLyBJZiB0aGUgdmFsdWUgaGFzIGJlZW4gYXNzaWduZWQgYWxyZWFkeSAoZm9yIGluc3RhbmNlIHdoZW4gYSByYWRpbyBhbmRcblx0XHQvLyBhIGNoZWNrYm94IGhhdmUgdGhlIHNhbWUgbmFtZSBhdHRyaWJ1dGUpIGNvbnZlcnQgdGhlIHByZXZpb3VzIHZhbHVlXG5cdFx0Ly8gaW50byBhbiBhcnJheSBiZWZvcmUgcHVzaGluZyBpbnRvIGl0LlxuXHRcdC8vXG5cdFx0Ly8gTk9URTogSWYgdGhpcyByZXF1aXJlbWVudCB3ZXJlIHJlbW92ZWQgYWxsIGhhc2ggY3JlYXRpb24gYW5kXG5cdFx0Ly8gYXNzaWdubWVudCBjb3VsZCBnbyB0aHJvdWdoIGBoYXNoX2Fzc2lnbmAuXG5cdFx0aWYgKGV4aXN0aW5nKSB7XG5cdFx0XHRpZiAoIUFycmF5LmlzQXJyYXkoZXhpc3RpbmcpKSB7XG5cdFx0XHRcdHJlc3VsdFtrZXldID0gWyBleGlzdGluZyBdO1xuXHRcdFx0fVxuXG5cdFx0XHRyZXN1bHRba2V5XS5wdXNoKHZhbHVlKTtcblx0XHR9XG5cdFx0ZWxzZSB7XG5cdFx0XHRyZXN1bHRba2V5XSA9IHZhbHVlO1xuXHRcdH1cblx0fVxuXG5cdHJldHVybiByZXN1bHQ7XG59XG5cbi8vIHVybGZvcm0gZW5jb2Rpbmcgc2VyaWFsaXplclxuZnVuY3Rpb24gc3RyX3NlcmlhbGl6ZShyZXN1bHQsIGtleSwgdmFsdWUpIHtcblx0Ly8gZW5jb2RlIG5ld2xpbmVzIGFzIFxcclxcbiBjYXVzZSB0aGUgaHRtbCBzcGVjIHNheXMgc29cblx0dmFsdWUgPSB2YWx1ZS5yZXBsYWNlKC8oXFxyKT9cXG4vZywgJ1xcclxcbicpO1xuXHR2YWx1ZSA9IGVuY29kZVVSSUNvbXBvbmVudCh2YWx1ZSk7XG5cblx0Ly8gc3BhY2VzIHNob3VsZCBiZSAnKycgcmF0aGVyIHRoYW4gJyUyMCcuXG5cdHZhbHVlID0gdmFsdWUucmVwbGFjZSgvJTIwL2csICcrJyk7XG5cdHJldHVybiByZXN1bHQgKyAocmVzdWx0ID8gJyYnIDogJycpICsgZW5jb2RlVVJJQ29tcG9uZW50KGtleSkgKyAnPScgKyB2YWx1ZTtcbn1cblxubW9kdWxlLmV4cG9ydHMgPSBzZXJpYWxpemU7IiwiLyoqXG4gKiBDb3B5cmlnaHQgMjAxNCBDcmFpZyBDYW1wYmVsbFxuICpcbiAqIExpY2Vuc2VkIHVuZGVyIHRoZSBBcGFjaGUgTGljZW5zZSwgVmVyc2lvbiAyLjAgKHRoZSBcIkxpY2Vuc2VcIik7XG4gKiB5b3UgbWF5IG5vdCB1c2UgdGhpcyBmaWxlIGV4Y2VwdCBpbiBjb21wbGlhbmNlIHdpdGggdGhlIExpY2Vuc2UuXG4gKiBZb3UgbWF5IG9idGFpbiBhIGNvcHkgb2YgdGhlIExpY2Vuc2UgYXRcbiAqXG4gKiBodHRwOi8vd3d3LmFwYWNoZS5vcmcvbGljZW5zZXMvTElDRU5TRS0yLjBcbiAqXG4gKiBVbmxlc3MgcmVxdWlyZWQgYnkgYXBwbGljYWJsZSBsYXcgb3IgYWdyZWVkIHRvIGluIHdyaXRpbmcsIHNvZnR3YXJlXG4gKiBkaXN0cmlidXRlZCB1bmRlciB0aGUgTGljZW5zZSBpcyBkaXN0cmlidXRlZCBvbiBhbiBcIkFTIElTXCIgQkFTSVMsXG4gKiBXSVRIT1VUIFdBUlJBTlRJRVMgT1IgQ09ORElUSU9OUyBPRiBBTlkgS0lORCwgZWl0aGVyIGV4cHJlc3Mgb3IgaW1wbGllZC5cbiAqIFNlZSB0aGUgTGljZW5zZSBmb3IgdGhlIHNwZWNpZmljIGxhbmd1YWdlIGdvdmVybmluZyBwZXJtaXNzaW9ucyBhbmRcbiAqIGxpbWl0YXRpb25zIHVuZGVyIHRoZSBMaWNlbnNlLlxuICpcbiAqIEdBVE9SLkpTXG4gKiBTaW1wbGUgRXZlbnQgRGVsZWdhdGlvblxuICpcbiAqIEB2ZXJzaW9uIDEuMi40XG4gKlxuICogQ29tcGF0aWJsZSB3aXRoIElFIDkrLCBGRiAzLjYrLCBTYWZhcmkgNSssIENocm9tZVxuICpcbiAqIEluY2x1ZGUgbGVnYWN5LmpzIGZvciBjb21wYXRpYmlsaXR5IHdpdGggb2xkZXIgYnJvd3NlcnNcbiAqXG4gKiAgICAgICAgICAgICAuLS5fICAgXyBfIF8gXyBfIF8gXyBfXG4gKiAgLi0nJy0uX18uLScwMCAgJy0nICcgJyAnICcgJyAnICcgJy0uXG4gKiAnLl9fXyAnICAgIC4gICAuLS1fJy0nICctJyAnLScgXyctJyAnLl9cbiAqICBWOiBWICd2di0nICAgJ18gICAnLiAgICAgICAuJyAgXy4uJyAnLicuXG4gKiAgICAnPS5fX19fLj1fLi0tJyAgIDpfLl9fLl9fOl8gICAnLiAgIDogOlxuICogICAgICAgICAgICAoKChfX19fLi0nICAgICAgICAnLS4gIC8gICA6IDpcbiAqICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgKCgoLSdcXCAuJyAvXG4gKiAgICAgICAgICAgICAgICAgICAgICAgICAgICBfX19fXy4uJyAgLidcbiAqICAgICAgICAgICAgICAgICAgICAgICAgICAgJy0uX19fX18uLSdcbiAqL1xuKGZ1bmN0aW9uKCkge1xuICAgIHZhciBfbWF0Y2hlcixcbiAgICAgICAgX2xldmVsID0gMCxcbiAgICAgICAgX2lkID0gMCxcbiAgICAgICAgX2hhbmRsZXJzID0ge30sXG4gICAgICAgIF9nYXRvckluc3RhbmNlcyA9IHt9O1xuXG4gICAgZnVuY3Rpb24gX2FkZEV2ZW50KGdhdG9yLCB0eXBlLCBjYWxsYmFjaykge1xuXG4gICAgICAgIC8vIGJsdXIgYW5kIGZvY3VzIGRvIG5vdCBidWJibGUgdXAgYnV0IGlmIHlvdSB1c2UgZXZlbnQgY2FwdHVyaW5nXG4gICAgICAgIC8vIHRoZW4geW91IHdpbGwgZ2V0IHRoZW1cbiAgICAgICAgdmFyIHVzZUNhcHR1cmUgPSB0eXBlID09ICdibHVyJyB8fCB0eXBlID09ICdmb2N1cyc7XG4gICAgICAgIGdhdG9yLmVsZW1lbnQuYWRkRXZlbnRMaXN0ZW5lcih0eXBlLCBjYWxsYmFjaywgdXNlQ2FwdHVyZSk7XG4gICAgfVxuXG4gICAgZnVuY3Rpb24gX2NhbmNlbChlKSB7XG4gICAgICAgIGUucHJldmVudERlZmF1bHQoKTtcbiAgICAgICAgZS5zdG9wUHJvcGFnYXRpb24oKTtcbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiByZXR1cm5zIGZ1bmN0aW9uIHRvIHVzZSBmb3IgZGV0ZXJtaW5pbmcgaWYgYW4gZWxlbWVudFxuICAgICAqIG1hdGNoZXMgYSBxdWVyeSBzZWxlY3RvclxuICAgICAqXG4gICAgICogQHJldHVybnMge0Z1bmN0aW9ufVxuICAgICAqL1xuICAgIGZ1bmN0aW9uIF9nZXRNYXRjaGVyKGVsZW1lbnQpIHtcbiAgICAgICAgaWYgKF9tYXRjaGVyKSB7XG4gICAgICAgICAgICByZXR1cm4gX21hdGNoZXI7XG4gICAgICAgIH1cblxuICAgICAgICBpZiAoZWxlbWVudC5tYXRjaGVzKSB7XG4gICAgICAgICAgICBfbWF0Y2hlciA9IGVsZW1lbnQubWF0Y2hlcztcbiAgICAgICAgICAgIHJldHVybiBfbWF0Y2hlcjtcbiAgICAgICAgfVxuXG4gICAgICAgIGlmIChlbGVtZW50LndlYmtpdE1hdGNoZXNTZWxlY3Rvcikge1xuICAgICAgICAgICAgX21hdGNoZXIgPSBlbGVtZW50LndlYmtpdE1hdGNoZXNTZWxlY3RvcjtcbiAgICAgICAgICAgIHJldHVybiBfbWF0Y2hlcjtcbiAgICAgICAgfVxuXG4gICAgICAgIGlmIChlbGVtZW50Lm1vek1hdGNoZXNTZWxlY3Rvcikge1xuICAgICAgICAgICAgX21hdGNoZXIgPSBlbGVtZW50Lm1vek1hdGNoZXNTZWxlY3RvcjtcbiAgICAgICAgICAgIHJldHVybiBfbWF0Y2hlcjtcbiAgICAgICAgfVxuXG4gICAgICAgIGlmIChlbGVtZW50Lm1zTWF0Y2hlc1NlbGVjdG9yKSB7XG4gICAgICAgICAgICBfbWF0Y2hlciA9IGVsZW1lbnQubXNNYXRjaGVzU2VsZWN0b3I7XG4gICAgICAgICAgICByZXR1cm4gX21hdGNoZXI7XG4gICAgICAgIH1cblxuICAgICAgICBpZiAoZWxlbWVudC5vTWF0Y2hlc1NlbGVjdG9yKSB7XG4gICAgICAgICAgICBfbWF0Y2hlciA9IGVsZW1lbnQub01hdGNoZXNTZWxlY3RvcjtcbiAgICAgICAgICAgIHJldHVybiBfbWF0Y2hlcjtcbiAgICAgICAgfVxuXG4gICAgICAgIC8vIGlmIGl0IGRvZXNuJ3QgbWF0Y2ggYSBuYXRpdmUgYnJvd3NlciBtZXRob2RcbiAgICAgICAgLy8gZmFsbCBiYWNrIHRvIHRoZSBnYXRvciBmdW5jdGlvblxuICAgICAgICBfbWF0Y2hlciA9IEdhdG9yLm1hdGNoZXNTZWxlY3RvcjtcbiAgICAgICAgcmV0dXJuIF9tYXRjaGVyO1xuICAgIH1cblxuICAgIC8qKlxuICAgICAqIGRldGVybWluZXMgaWYgdGhlIHNwZWNpZmllZCBlbGVtZW50IG1hdGNoZXMgYSBnaXZlbiBzZWxlY3RvclxuICAgICAqXG4gICAgICogQHBhcmFtIHtOb2RlfSBlbGVtZW50IC0gdGhlIGVsZW1lbnQgdG8gY29tcGFyZSBhZ2FpbnN0IHRoZSBzZWxlY3RvclxuICAgICAqIEBwYXJhbSB7c3RyaW5nfSBzZWxlY3RvclxuICAgICAqIEBwYXJhbSB7Tm9kZX0gYm91bmRFbGVtZW50IC0gdGhlIGVsZW1lbnQgdGhlIGxpc3RlbmVyIHdhcyBhdHRhY2hlZCB0b1xuICAgICAqIEByZXR1cm5zIHt2b2lkfE5vZGV9XG4gICAgICovXG4gICAgZnVuY3Rpb24gX21hdGNoZXNTZWxlY3RvcihlbGVtZW50LCBzZWxlY3RvciwgYm91bmRFbGVtZW50KSB7XG5cbiAgICAgICAgLy8gbm8gc2VsZWN0b3IgbWVhbnMgdGhpcyBldmVudCB3YXMgYm91bmQgZGlyZWN0bHkgdG8gdGhpcyBlbGVtZW50XG4gICAgICAgIGlmIChzZWxlY3RvciA9PSAnX3Jvb3QnKSB7XG4gICAgICAgICAgICByZXR1cm4gYm91bmRFbGVtZW50O1xuICAgICAgICB9XG5cbiAgICAgICAgLy8gaWYgd2UgaGF2ZSBtb3ZlZCB1cCB0byB0aGUgZWxlbWVudCB5b3UgYm91bmQgdGhlIGV2ZW50IHRvXG4gICAgICAgIC8vIHRoZW4gd2UgaGF2ZSBjb21lIHRvbyBmYXJcbiAgICAgICAgaWYgKGVsZW1lbnQgPT09IGJvdW5kRWxlbWVudCkge1xuICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICB9XG5cbiAgICAgICAgLy8gaWYgdGhpcyBpcyBhIG1hdGNoIHRoZW4gd2UgYXJlIGRvbmUhXG4gICAgICAgIGlmIChfZ2V0TWF0Y2hlcihlbGVtZW50KS5jYWxsKGVsZW1lbnQsIHNlbGVjdG9yKSkge1xuICAgICAgICAgICAgcmV0dXJuIGVsZW1lbnQ7XG4gICAgICAgIH1cblxuICAgICAgICAvLyBpZiB0aGlzIGVsZW1lbnQgZGlkIG5vdCBtYXRjaCBidXQgaGFzIGEgcGFyZW50IHdlIHNob3VsZCB0cnlcbiAgICAgICAgLy8gZ29pbmcgdXAgdGhlIHRyZWUgdG8gc2VlIGlmIGFueSBvZiB0aGUgcGFyZW50IGVsZW1lbnRzIG1hdGNoXG4gICAgICAgIC8vIGZvciBleGFtcGxlIGlmIHlvdSBhcmUgbG9va2luZyBmb3IgYSBjbGljayBvbiBhbiA8YT4gdGFnIGJ1dCB0aGVyZVxuICAgICAgICAvLyBpcyBhIDxzcGFuPiBpbnNpZGUgb2YgdGhlIGEgdGFnIHRoYXQgaXQgaXMgdGhlIHRhcmdldCxcbiAgICAgICAgLy8gaXQgc2hvdWxkIHN0aWxsIHdvcmtcbiAgICAgICAgaWYgKGVsZW1lbnQucGFyZW50Tm9kZSkge1xuICAgICAgICAgICAgX2xldmVsKys7XG4gICAgICAgICAgICByZXR1cm4gX21hdGNoZXNTZWxlY3RvcihlbGVtZW50LnBhcmVudE5vZGUsIHNlbGVjdG9yLCBib3VuZEVsZW1lbnQpO1xuICAgICAgICB9XG4gICAgfVxuXG4gICAgZnVuY3Rpb24gX2FkZEhhbmRsZXIoZ2F0b3IsIGV2ZW50LCBzZWxlY3RvciwgY2FsbGJhY2spIHtcbiAgICAgICAgaWYgKCFfaGFuZGxlcnNbZ2F0b3IuaWRdKSB7XG4gICAgICAgICAgICBfaGFuZGxlcnNbZ2F0b3IuaWRdID0ge307XG4gICAgICAgIH1cblxuICAgICAgICBpZiAoIV9oYW5kbGVyc1tnYXRvci5pZF1bZXZlbnRdKSB7XG4gICAgICAgICAgICBfaGFuZGxlcnNbZ2F0b3IuaWRdW2V2ZW50XSA9IHt9O1xuICAgICAgICB9XG5cbiAgICAgICAgaWYgKCFfaGFuZGxlcnNbZ2F0b3IuaWRdW2V2ZW50XVtzZWxlY3Rvcl0pIHtcbiAgICAgICAgICAgIF9oYW5kbGVyc1tnYXRvci5pZF1bZXZlbnRdW3NlbGVjdG9yXSA9IFtdO1xuICAgICAgICB9XG5cbiAgICAgICAgX2hhbmRsZXJzW2dhdG9yLmlkXVtldmVudF1bc2VsZWN0b3JdLnB1c2goY2FsbGJhY2spO1xuICAgIH1cblxuICAgIGZ1bmN0aW9uIF9yZW1vdmVIYW5kbGVyKGdhdG9yLCBldmVudCwgc2VsZWN0b3IsIGNhbGxiYWNrKSB7XG5cbiAgICAgICAgLy8gaWYgdGhlcmUgYXJlIG5vIGV2ZW50cyB0aWVkIHRvIHRoaXMgZWxlbWVudCBhdCBhbGxcbiAgICAgICAgLy8gdGhlbiBkb24ndCBkbyBhbnl0aGluZ1xuICAgICAgICBpZiAoIV9oYW5kbGVyc1tnYXRvci5pZF0pIHtcbiAgICAgICAgICAgIHJldHVybjtcbiAgICAgICAgfVxuXG4gICAgICAgIC8vIGlmIHRoZXJlIGlzIG5vIGV2ZW50IHR5cGUgc3BlY2lmaWVkIHRoZW4gcmVtb3ZlIGFsbCBldmVudHNcbiAgICAgICAgLy8gZXhhbXBsZTogR2F0b3IoZWxlbWVudCkub2ZmKClcbiAgICAgICAgaWYgKCFldmVudCkge1xuICAgICAgICAgICAgZm9yICh2YXIgdHlwZSBpbiBfaGFuZGxlcnNbZ2F0b3IuaWRdKSB7XG4gICAgICAgICAgICAgICAgaWYgKF9oYW5kbGVyc1tnYXRvci5pZF0uaGFzT3duUHJvcGVydHkodHlwZSkpIHtcbiAgICAgICAgICAgICAgICAgICAgX2hhbmRsZXJzW2dhdG9yLmlkXVt0eXBlXSA9IHt9O1xuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgIH1cbiAgICAgICAgICAgIHJldHVybjtcbiAgICAgICAgfVxuXG4gICAgICAgIC8vIGlmIG5vIGNhbGxiYWNrIG9yIHNlbGVjdG9yIGlzIHNwZWNpZmllZCByZW1vdmUgYWxsIGV2ZW50cyBvZiB0aGlzIHR5cGVcbiAgICAgICAgLy8gZXhhbXBsZTogR2F0b3IoZWxlbWVudCkub2ZmKCdjbGljaycpXG4gICAgICAgIGlmICghY2FsbGJhY2sgJiYgIXNlbGVjdG9yKSB7XG4gICAgICAgICAgICBfaGFuZGxlcnNbZ2F0b3IuaWRdW2V2ZW50XSA9IHt9O1xuICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICB9XG5cbiAgICAgICAgLy8gaWYgYSBzZWxlY3RvciBpcyBzcGVjaWZpZWQgYnV0IG5vIGNhbGxiYWNrIHJlbW92ZSBhbGwgZXZlbnRzXG4gICAgICAgIC8vIGZvciB0aGlzIHNlbGVjdG9yXG4gICAgICAgIC8vIGV4YW1wbGU6IEdhdG9yKGVsZW1lbnQpLm9mZignY2xpY2snLCAnLnN1Yi1lbGVtZW50JylcbiAgICAgICAgaWYgKCFjYWxsYmFjaykge1xuICAgICAgICAgICAgZGVsZXRlIF9oYW5kbGVyc1tnYXRvci5pZF1bZXZlbnRdW3NlbGVjdG9yXTtcbiAgICAgICAgICAgIHJldHVybjtcbiAgICAgICAgfVxuXG4gICAgICAgIC8vIGlmIHdlIGhhdmUgc3BlY2lmaWVkIGFuIGV2ZW50IHR5cGUsIHNlbGVjdG9yLCBhbmQgY2FsbGJhY2sgdGhlbiB3ZVxuICAgICAgICAvLyBuZWVkIHRvIG1ha2Ugc3VyZSB0aGVyZSBhcmUgY2FsbGJhY2tzIHRpZWQgdG8gdGhpcyBzZWxlY3RvciB0b1xuICAgICAgICAvLyBiZWdpbiB3aXRoLiAgaWYgdGhlcmUgYXJlbid0IHRoZW4gd2UgY2FuIHN0b3AgaGVyZVxuICAgICAgICBpZiAoIV9oYW5kbGVyc1tnYXRvci5pZF1bZXZlbnRdW3NlbGVjdG9yXSkge1xuICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICB9XG5cbiAgICAgICAgLy8gaWYgdGhlcmUgYXJlIHRoZW4gbG9vcCB0aHJvdWdoIGFsbCB0aGUgY2FsbGJhY2tzIGFuZCBpZiB3ZSBmaW5kXG4gICAgICAgIC8vIG9uZSB0aGF0IG1hdGNoZXMgcmVtb3ZlIGl0IGZyb20gdGhlIGFycmF5XG4gICAgICAgIGZvciAodmFyIGkgPSAwOyBpIDwgX2hhbmRsZXJzW2dhdG9yLmlkXVtldmVudF1bc2VsZWN0b3JdLmxlbmd0aDsgaSsrKSB7XG4gICAgICAgICAgICBpZiAoX2hhbmRsZXJzW2dhdG9yLmlkXVtldmVudF1bc2VsZWN0b3JdW2ldID09PSBjYWxsYmFjaykge1xuICAgICAgICAgICAgICAgIF9oYW5kbGVyc1tnYXRvci5pZF1bZXZlbnRdW3NlbGVjdG9yXS5zcGxpY2UoaSwgMSk7XG4gICAgICAgICAgICAgICAgYnJlYWs7XG4gICAgICAgICAgICB9XG4gICAgICAgIH1cbiAgICB9XG5cbiAgICBmdW5jdGlvbiBfaGFuZGxlRXZlbnQoaWQsIGUsIHR5cGUpIHtcbiAgICAgICAgaWYgKCFfaGFuZGxlcnNbaWRdW3R5cGVdKSB7XG4gICAgICAgICAgICByZXR1cm47XG4gICAgICAgIH1cblxuICAgICAgICB2YXIgdGFyZ2V0ID0gZS50YXJnZXQgfHwgZS5zcmNFbGVtZW50LFxuICAgICAgICAgICAgc2VsZWN0b3IsXG4gICAgICAgICAgICBtYXRjaCxcbiAgICAgICAgICAgIG1hdGNoZXMgPSB7fSxcbiAgICAgICAgICAgIGkgPSAwLFxuICAgICAgICAgICAgaiA9IDA7XG5cbiAgICAgICAgLy8gZmluZCBhbGwgZXZlbnRzIHRoYXQgbWF0Y2hcbiAgICAgICAgX2xldmVsID0gMDtcbiAgICAgICAgZm9yIChzZWxlY3RvciBpbiBfaGFuZGxlcnNbaWRdW3R5cGVdKSB7XG4gICAgICAgICAgICBpZiAoX2hhbmRsZXJzW2lkXVt0eXBlXS5oYXNPd25Qcm9wZXJ0eShzZWxlY3RvcikpIHtcbiAgICAgICAgICAgICAgICBtYXRjaCA9IF9tYXRjaGVzU2VsZWN0b3IodGFyZ2V0LCBzZWxlY3RvciwgX2dhdG9ySW5zdGFuY2VzW2lkXS5lbGVtZW50KTtcblxuICAgICAgICAgICAgICAgIGlmIChtYXRjaCAmJiBHYXRvci5tYXRjaGVzRXZlbnQodHlwZSwgX2dhdG9ySW5zdGFuY2VzW2lkXS5lbGVtZW50LCBtYXRjaCwgc2VsZWN0b3IgPT0gJ19yb290JywgZSkpIHtcbiAgICAgICAgICAgICAgICAgICAgX2xldmVsKys7XG4gICAgICAgICAgICAgICAgICAgIF9oYW5kbGVyc1tpZF1bdHlwZV1bc2VsZWN0b3JdLm1hdGNoID0gbWF0Y2g7XG4gICAgICAgICAgICAgICAgICAgIG1hdGNoZXNbX2xldmVsXSA9IF9oYW5kbGVyc1tpZF1bdHlwZV1bc2VsZWN0b3JdO1xuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgIH1cbiAgICAgICAgfVxuXG4gICAgICAgIC8vIHN0b3BQcm9wYWdhdGlvbigpIGZhaWxzIHRvIHNldCBjYW5jZWxCdWJibGUgdG8gdHJ1ZSBpbiBXZWJraXRcbiAgICAgICAgLy8gQHNlZSBodHRwOi8vY29kZS5nb29nbGUuY29tL3AvY2hyb21pdW0vaXNzdWVzL2RldGFpbD9pZD0xNjIyNzBcbiAgICAgICAgZS5zdG9wUHJvcGFnYXRpb24gPSBmdW5jdGlvbigpIHtcbiAgICAgICAgICAgIGUuY2FuY2VsQnViYmxlID0gdHJ1ZTtcbiAgICAgICAgfTtcblxuICAgICAgICBmb3IgKGkgPSAwOyBpIDw9IF9sZXZlbDsgaSsrKSB7XG4gICAgICAgICAgICBpZiAobWF0Y2hlc1tpXSkge1xuICAgICAgICAgICAgICAgIGZvciAoaiA9IDA7IGogPCBtYXRjaGVzW2ldLmxlbmd0aDsgaisrKSB7XG4gICAgICAgICAgICAgICAgICAgIGlmIChtYXRjaGVzW2ldW2pdLmNhbGwobWF0Y2hlc1tpXS5tYXRjaCwgZSkgPT09IGZhbHNlKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICBHYXRvci5jYW5jZWwoZSk7XG4gICAgICAgICAgICAgICAgICAgICAgICByZXR1cm47XG4gICAgICAgICAgICAgICAgICAgIH1cblxuICAgICAgICAgICAgICAgICAgICBpZiAoZS5jYW5jZWxCdWJibGUpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgIHJldHVybjtcbiAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgIH1cbiAgICAgICAgfVxuICAgIH1cblxuICAgIC8qKlxuICAgICAqIGJpbmRzIHRoZSBzcGVjaWZpZWQgZXZlbnRzIHRvIHRoZSBlbGVtZW50XG4gICAgICpcbiAgICAgKiBAcGFyYW0ge3N0cmluZ3xBcnJheX0gZXZlbnRzXG4gICAgICogQHBhcmFtIHtzdHJpbmd9IHNlbGVjdG9yXG4gICAgICogQHBhcmFtIHtGdW5jdGlvbn0gY2FsbGJhY2tcbiAgICAgKiBAcGFyYW0ge2Jvb2xlYW49fSByZW1vdmVcbiAgICAgKiBAcmV0dXJucyB7T2JqZWN0fVxuICAgICAqL1xuICAgIGZ1bmN0aW9uIF9iaW5kKGV2ZW50cywgc2VsZWN0b3IsIGNhbGxiYWNrLCByZW1vdmUpIHtcblxuICAgICAgICAvLyBmYWlsIHNpbGVudGx5IGlmIHlvdSBwYXNzIG51bGwgb3IgdW5kZWZpbmVkIGFzIGFuIGFsZW1lbnRcbiAgICAgICAgLy8gaW4gdGhlIEdhdG9yIGNvbnN0cnVjdG9yXG4gICAgICAgIGlmICghdGhpcy5lbGVtZW50KSB7XG4gICAgICAgICAgICByZXR1cm47XG4gICAgICAgIH1cblxuICAgICAgICBpZiAoIShldmVudHMgaW5zdGFuY2VvZiBBcnJheSkpIHtcbiAgICAgICAgICAgIGV2ZW50cyA9IFtldmVudHNdO1xuICAgICAgICB9XG5cbiAgICAgICAgaWYgKCFjYWxsYmFjayAmJiB0eXBlb2Yoc2VsZWN0b3IpID09ICdmdW5jdGlvbicpIHtcbiAgICAgICAgICAgIGNhbGxiYWNrID0gc2VsZWN0b3I7XG4gICAgICAgICAgICBzZWxlY3RvciA9ICdfcm9vdCc7XG4gICAgICAgIH1cblxuICAgICAgICB2YXIgaWQgPSB0aGlzLmlkLFxuICAgICAgICAgICAgaTtcblxuICAgICAgICBmdW5jdGlvbiBfZ2V0R2xvYmFsQ2FsbGJhY2sodHlwZSkge1xuICAgICAgICAgICAgcmV0dXJuIGZ1bmN0aW9uKGUpIHtcbiAgICAgICAgICAgICAgICBfaGFuZGxlRXZlbnQoaWQsIGUsIHR5cGUpO1xuICAgICAgICAgICAgfTtcbiAgICAgICAgfVxuXG4gICAgICAgIGZvciAoaSA9IDA7IGkgPCBldmVudHMubGVuZ3RoOyBpKyspIHtcbiAgICAgICAgICAgIGlmIChyZW1vdmUpIHtcbiAgICAgICAgICAgICAgICBfcmVtb3ZlSGFuZGxlcih0aGlzLCBldmVudHNbaV0sIHNlbGVjdG9yLCBjYWxsYmFjayk7XG4gICAgICAgICAgICAgICAgY29udGludWU7XG4gICAgICAgICAgICB9XG5cbiAgICAgICAgICAgIGlmICghX2hhbmRsZXJzW2lkXSB8fCAhX2hhbmRsZXJzW2lkXVtldmVudHNbaV1dKSB7XG4gICAgICAgICAgICAgICAgR2F0b3IuYWRkRXZlbnQodGhpcywgZXZlbnRzW2ldLCBfZ2V0R2xvYmFsQ2FsbGJhY2soZXZlbnRzW2ldKSk7XG4gICAgICAgICAgICB9XG5cbiAgICAgICAgICAgIF9hZGRIYW5kbGVyKHRoaXMsIGV2ZW50c1tpXSwgc2VsZWN0b3IsIGNhbGxiYWNrKTtcbiAgICAgICAgfVxuXG4gICAgICAgIHJldHVybiB0aGlzO1xuICAgIH1cblxuICAgIC8qKlxuICAgICAqIEdhdG9yIG9iamVjdCBjb25zdHJ1Y3RvclxuICAgICAqXG4gICAgICogQHBhcmFtIHtOb2RlfSBlbGVtZW50XG4gICAgICovXG4gICAgZnVuY3Rpb24gR2F0b3IoZWxlbWVudCwgaWQpIHtcblxuICAgICAgICAvLyBjYWxsZWQgYXMgZnVuY3Rpb25cbiAgICAgICAgaWYgKCEodGhpcyBpbnN0YW5jZW9mIEdhdG9yKSkge1xuICAgICAgICAgICAgLy8gb25seSBrZWVwIG9uZSBHYXRvciBpbnN0YW5jZSBwZXIgbm9kZSB0byBtYWtlIHN1cmUgdGhhdFxuICAgICAgICAgICAgLy8gd2UgZG9uJ3QgY3JlYXRlIGEgdG9uIG9mIG5ldyBvYmplY3RzIGlmIHlvdSB3YW50IHRvIGRlbGVnYXRlXG4gICAgICAgICAgICAvLyBtdWx0aXBsZSBldmVudHMgZnJvbSB0aGUgc2FtZSBub2RlXG4gICAgICAgICAgICAvL1xuICAgICAgICAgICAgLy8gZm9yIGV4YW1wbGU6IEdhdG9yKGRvY3VtZW50KS5vbiguLi5cbiAgICAgICAgICAgIGZvciAodmFyIGtleSBpbiBfZ2F0b3JJbnN0YW5jZXMpIHtcbiAgICAgICAgICAgICAgICBpZiAoX2dhdG9ySW5zdGFuY2VzW2tleV0uZWxlbWVudCA9PT0gZWxlbWVudCkge1xuICAgICAgICAgICAgICAgICAgICByZXR1cm4gX2dhdG9ySW5zdGFuY2VzW2tleV07XG4gICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICBfaWQrKztcbiAgICAgICAgICAgIF9nYXRvckluc3RhbmNlc1tfaWRdID0gbmV3IEdhdG9yKGVsZW1lbnQsIF9pZCk7XG5cbiAgICAgICAgICAgIHJldHVybiBfZ2F0b3JJbnN0YW5jZXNbX2lkXTtcbiAgICAgICAgfVxuXG4gICAgICAgIHRoaXMuZWxlbWVudCA9IGVsZW1lbnQ7XG4gICAgICAgIHRoaXMuaWQgPSBpZDtcbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBhZGRzIGFuIGV2ZW50XG4gICAgICpcbiAgICAgKiBAcGFyYW0ge3N0cmluZ3xBcnJheX0gZXZlbnRzXG4gICAgICogQHBhcmFtIHtzdHJpbmd9IHNlbGVjdG9yXG4gICAgICogQHBhcmFtIHtGdW5jdGlvbn0gY2FsbGJhY2tcbiAgICAgKiBAcmV0dXJucyB7T2JqZWN0fVxuICAgICAqL1xuICAgIEdhdG9yLnByb3RvdHlwZS5vbiA9IGZ1bmN0aW9uKGV2ZW50cywgc2VsZWN0b3IsIGNhbGxiYWNrKSB7XG4gICAgICAgIHJldHVybiBfYmluZC5jYWxsKHRoaXMsIGV2ZW50cywgc2VsZWN0b3IsIGNhbGxiYWNrKTtcbiAgICB9O1xuXG4gICAgLyoqXG4gICAgICogcmVtb3ZlcyBhbiBldmVudFxuICAgICAqXG4gICAgICogQHBhcmFtIHtzdHJpbmd8QXJyYXl9IGV2ZW50c1xuICAgICAqIEBwYXJhbSB7c3RyaW5nfSBzZWxlY3RvclxuICAgICAqIEBwYXJhbSB7RnVuY3Rpb259IGNhbGxiYWNrXG4gICAgICogQHJldHVybnMge09iamVjdH1cbiAgICAgKi9cbiAgICBHYXRvci5wcm90b3R5cGUub2ZmID0gZnVuY3Rpb24oZXZlbnRzLCBzZWxlY3RvciwgY2FsbGJhY2spIHtcbiAgICAgICAgcmV0dXJuIF9iaW5kLmNhbGwodGhpcywgZXZlbnRzLCBzZWxlY3RvciwgY2FsbGJhY2ssIHRydWUpO1xuICAgIH07XG5cbiAgICBHYXRvci5tYXRjaGVzU2VsZWN0b3IgPSBmdW5jdGlvbigpIHt9O1xuICAgIEdhdG9yLmNhbmNlbCA9IF9jYW5jZWw7XG4gICAgR2F0b3IuYWRkRXZlbnQgPSBfYWRkRXZlbnQ7XG4gICAgR2F0b3IubWF0Y2hlc0V2ZW50ID0gZnVuY3Rpb24oKSB7XG4gICAgICAgIHJldHVybiB0cnVlO1xuICAgIH07XG5cbiAgICBpZiAodHlwZW9mIG1vZHVsZSAhPT0gJ3VuZGVmaW5lZCcgJiYgbW9kdWxlLmV4cG9ydHMpIHtcbiAgICAgICAgbW9kdWxlLmV4cG9ydHMgPSBHYXRvcjtcbiAgICB9XG5cbiAgICB3aW5kb3cuR2F0b3IgPSBHYXRvcjtcbn0pICgpO1xuIiwiLyohIHBvcHVsYXRlLmpzIHYxLjAuMiBieSBAZGFubnl2YW5rb290ZW4gfCBNSVQgbGljZW5zZSAqL1xuOyhmdW5jdGlvbihyb290KSB7XG5cblx0LyoqXG5cdCAqIFBvcHVsYXRlIGZvcm0gZmllbGRzIGZyb20gYSBKU09OIG9iamVjdC5cblx0ICpcblx0ICogQHBhcmFtIGZvcm0gb2JqZWN0IFRoZSBmb3JtIGVsZW1lbnQgY29udGFpbmluZyB5b3VyIGlucHV0IGZpZWxkcy5cblx0ICogQHBhcmFtIGRhdGEgYXJyYXkgSlNPTiBkYXRhIHRvIHBvcHVsYXRlIHRoZSBmaWVsZHMgd2l0aC5cblx0ICogQHBhcmFtIGJhc2VuYW1lIHN0cmluZyBPcHRpb25hbCBiYXNlbmFtZSB3aGljaCBpcyBhZGRlZCB0byBgbmFtZWAgYXR0cmlidXRlc1xuXHQgKi9cblx0dmFyIHBvcHVsYXRlID0gZnVuY3Rpb24oIGZvcm0sIGRhdGEsIGJhc2VuYW1lKSB7XG5cblx0XHRmb3IodmFyIGtleSBpbiBkYXRhKSB7XG5cblx0XHRcdGlmKCAhIGRhdGEuaGFzT3duUHJvcGVydHkoIGtleSApICkge1xuXHRcdFx0XHRjb250aW51ZTtcblx0XHRcdH1cblxuXHRcdFx0dmFyIG5hbWUgPSBrZXk7XG5cdFx0XHR2YXIgdmFsdWUgPSBkYXRhW2tleV07XG5cblx0XHRcdC8vIGhhbmRsZSBhcnJheSBuYW1lIGF0dHJpYnV0ZXNcblx0XHRcdGlmKHR5cGVvZihiYXNlbmFtZSkgIT09IFwidW5kZWZpbmVkXCIpIHtcblx0XHRcdFx0bmFtZSA9IGJhc2VuYW1lICsgXCJbXCIgKyBrZXkgKyBcIl1cIjtcblx0XHRcdH1cblxuXHRcdFx0aWYodmFsdWUuY29uc3RydWN0b3IgPT09IEFycmF5KSB7XG5cdFx0XHRcdG5hbWUgKz0gJ1tdJztcblx0XHRcdH0gZWxzZSBpZih0eXBlb2YgdmFsdWUgPT0gXCJvYmplY3RcIikge1xuXHRcdFx0XHRwb3B1bGF0ZSggZm9ybSwgdmFsdWUsIG5hbWUpO1xuXHRcdFx0XHRjb250aW51ZTtcblx0XHRcdH1cblxuXHRcdFx0Ly8gb25seSBwcm9jZWVkIGlmIGVsZW1lbnQgaXMgc2V0XG5cdFx0XHR2YXIgZWxlbWVudCA9IGZvcm0uZWxlbWVudHMubmFtZWRJdGVtKCBuYW1lICk7XG5cdFx0XHRpZiggISBlbGVtZW50ICkge1xuXHRcdFx0XHRjb250aW51ZTtcblx0XHRcdH1cblxuXHRcdFx0dmFyIHR5cGUgPSBlbGVtZW50LnR5cGUgfHwgZWxlbWVudFswXS50eXBlO1xuXG5cdFx0XHRzd2l0Y2godHlwZSApIHtcblx0XHRcdFx0ZGVmYXVsdDpcblx0XHRcdFx0XHRlbGVtZW50LnZhbHVlID0gdmFsdWU7XG5cdFx0XHRcdFx0YnJlYWs7XG5cblx0XHRcdFx0Y2FzZSAncmFkaW8nOlxuXHRcdFx0XHRjYXNlICdjaGVja2JveCc6XG5cdFx0XHRcdFx0Zm9yKCB2YXIgaj0wOyBqIDwgZWxlbWVudC5sZW5ndGg7IGorKyApIHtcblx0XHRcdFx0XHRcdGVsZW1lbnRbal0uY2hlY2tlZCA9ICggdmFsdWUuaW5kZXhPZihlbGVtZW50W2pdLnZhbHVlKSA+IC0xICk7XG5cdFx0XHRcdFx0fVxuXHRcdFx0XHRcdGJyZWFrO1xuXG5cdFx0XHRcdGNhc2UgJ3NlbGVjdC1tdWx0aXBsZSc6XG5cdFx0XHRcdFx0dmFyIHZhbHVlcyA9IHZhbHVlLmNvbnN0cnVjdG9yID09IEFycmF5ID8gdmFsdWUgOiBbdmFsdWVdO1xuXG5cdFx0XHRcdFx0Zm9yKHZhciBrID0gMDsgayA8IGVsZW1lbnQub3B0aW9ucy5sZW5ndGg7IGsrKykge1xuXHRcdFx0XHRcdFx0ZWxlbWVudC5vcHRpb25zW2tdLnNlbGVjdGVkIHw9ICh2YWx1ZXMuaW5kZXhPZihlbGVtZW50Lm9wdGlvbnNba10udmFsdWUpID4gLTEgKTtcblx0XHRcdFx0XHR9XG5cdFx0XHRcdFx0YnJlYWs7XG5cblx0XHRcdFx0Y2FzZSAnc2VsZWN0Jzpcblx0XHRcdFx0Y2FzZSAnc2VsZWN0LW9uZSc6XG5cdFx0XHRcdFx0ZWxlbWVudC52YWx1ZSA9IHZhbHVlLnRvU3RyaW5nKCkgfHwgdmFsdWU7XG5cdFx0XHRcdFx0YnJlYWs7XG5cblx0XHRcdH1cblxuXHRcdH1cblxuXHR9O1xuXG5cdC8vIFBsYXkgbmljZSB3aXRoIEFNRCwgQ29tbW9uSlMgb3IgYSBwbGFpbiBnbG9iYWwgb2JqZWN0LlxuXHRpZiAoIHR5cGVvZiBkZWZpbmUgPT0gJ2Z1bmN0aW9uJyAmJiB0eXBlb2YgZGVmaW5lLmFtZCA9PSAnb2JqZWN0JyAmJiBkZWZpbmUuYW1kICkge1xuXHRcdGRlZmluZShmdW5jdGlvbigpIHtcblx0XHRcdHJldHVybiBwb3B1bGF0ZTtcblx0XHR9KTtcblx0fVx0ZWxzZSBpZiAoIHR5cGVvZiBtb2R1bGUgIT09ICd1bmRlZmluZWQnICYmIG1vZHVsZS5leHBvcnRzICkge1xuXHRcdG1vZHVsZS5leHBvcnRzID0gcG9wdWxhdGU7XG5cdH0gZWxzZSB7XG5cdFx0cm9vdC5wb3B1bGF0ZSA9IHBvcHVsYXRlO1xuXHR9XG5cbn0odGhpcykpOyIsIi8qIVxuICogRXZlbnRFbWl0dGVyIHY0LjIuMTEgLSBnaXQuaW8vZWVcbiAqIFVubGljZW5zZSAtIGh0dHA6Ly91bmxpY2Vuc2Uub3JnL1xuICogT2xpdmVyIENhbGR3ZWxsIC0gaHR0cDovL29saS5tZS51ay9cbiAqIEBwcmVzZXJ2ZVxuICovXG5cbjsoZnVuY3Rpb24gKCkge1xuICAgICd1c2Ugc3RyaWN0JztcblxuICAgIC8qKlxuICAgICAqIENsYXNzIGZvciBtYW5hZ2luZyBldmVudHMuXG4gICAgICogQ2FuIGJlIGV4dGVuZGVkIHRvIHByb3ZpZGUgZXZlbnQgZnVuY3Rpb25hbGl0eSBpbiBvdGhlciBjbGFzc2VzLlxuICAgICAqXG4gICAgICogQGNsYXNzIEV2ZW50RW1pdHRlciBNYW5hZ2VzIGV2ZW50IHJlZ2lzdGVyaW5nIGFuZCBlbWl0dGluZy5cbiAgICAgKi9cbiAgICBmdW5jdGlvbiBFdmVudEVtaXR0ZXIoKSB7fVxuXG4gICAgLy8gU2hvcnRjdXRzIHRvIGltcHJvdmUgc3BlZWQgYW5kIHNpemVcbiAgICB2YXIgcHJvdG8gPSBFdmVudEVtaXR0ZXIucHJvdG90eXBlO1xuICAgIHZhciBleHBvcnRzID0gdGhpcztcbiAgICB2YXIgb3JpZ2luYWxHbG9iYWxWYWx1ZSA9IGV4cG9ydHMuRXZlbnRFbWl0dGVyO1xuXG4gICAgLyoqXG4gICAgICogRmluZHMgdGhlIGluZGV4IG9mIHRoZSBsaXN0ZW5lciBmb3IgdGhlIGV2ZW50IGluIGl0cyBzdG9yYWdlIGFycmF5LlxuICAgICAqXG4gICAgICogQHBhcmFtIHtGdW5jdGlvbltdfSBsaXN0ZW5lcnMgQXJyYXkgb2YgbGlzdGVuZXJzIHRvIHNlYXJjaCB0aHJvdWdoLlxuICAgICAqIEBwYXJhbSB7RnVuY3Rpb259IGxpc3RlbmVyIE1ldGhvZCB0byBsb29rIGZvci5cbiAgICAgKiBAcmV0dXJuIHtOdW1iZXJ9IEluZGV4IG9mIHRoZSBzcGVjaWZpZWQgbGlzdGVuZXIsIC0xIGlmIG5vdCBmb3VuZFxuICAgICAqIEBhcGkgcHJpdmF0ZVxuICAgICAqL1xuICAgIGZ1bmN0aW9uIGluZGV4T2ZMaXN0ZW5lcihsaXN0ZW5lcnMsIGxpc3RlbmVyKSB7XG4gICAgICAgIHZhciBpID0gbGlzdGVuZXJzLmxlbmd0aDtcbiAgICAgICAgd2hpbGUgKGktLSkge1xuICAgICAgICAgICAgaWYgKGxpc3RlbmVyc1tpXS5saXN0ZW5lciA9PT0gbGlzdGVuZXIpIHtcbiAgICAgICAgICAgICAgICByZXR1cm4gaTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfVxuXG4gICAgICAgIHJldHVybiAtMTtcbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBBbGlhcyBhIG1ldGhvZCB3aGlsZSBrZWVwaW5nIHRoZSBjb250ZXh0IGNvcnJlY3QsIHRvIGFsbG93IGZvciBvdmVyd3JpdGluZyBvZiB0YXJnZXQgbWV0aG9kLlxuICAgICAqXG4gICAgICogQHBhcmFtIHtTdHJpbmd9IG5hbWUgVGhlIG5hbWUgb2YgdGhlIHRhcmdldCBtZXRob2QuXG4gICAgICogQHJldHVybiB7RnVuY3Rpb259IFRoZSBhbGlhc2VkIG1ldGhvZFxuICAgICAqIEBhcGkgcHJpdmF0ZVxuICAgICAqL1xuICAgIGZ1bmN0aW9uIGFsaWFzKG5hbWUpIHtcbiAgICAgICAgcmV0dXJuIGZ1bmN0aW9uIGFsaWFzQ2xvc3VyZSgpIHtcbiAgICAgICAgICAgIHJldHVybiB0aGlzW25hbWVdLmFwcGx5KHRoaXMsIGFyZ3VtZW50cyk7XG4gICAgICAgIH07XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogUmV0dXJucyB0aGUgbGlzdGVuZXIgYXJyYXkgZm9yIHRoZSBzcGVjaWZpZWQgZXZlbnQuXG4gICAgICogV2lsbCBpbml0aWFsaXNlIHRoZSBldmVudCBvYmplY3QgYW5kIGxpc3RlbmVyIGFycmF5cyBpZiByZXF1aXJlZC5cbiAgICAgKiBXaWxsIHJldHVybiBhbiBvYmplY3QgaWYgeW91IHVzZSBhIHJlZ2V4IHNlYXJjaC4gVGhlIG9iamVjdCBjb250YWlucyBrZXlzIGZvciBlYWNoIG1hdGNoZWQgZXZlbnQuIFNvIC9iYVtyel0vIG1pZ2h0IHJldHVybiBhbiBvYmplY3QgY29udGFpbmluZyBiYXIgYW5kIGJhei4gQnV0IG9ubHkgaWYgeW91IGhhdmUgZWl0aGVyIGRlZmluZWQgdGhlbSB3aXRoIGRlZmluZUV2ZW50IG9yIGFkZGVkIHNvbWUgbGlzdGVuZXJzIHRvIHRoZW0uXG4gICAgICogRWFjaCBwcm9wZXJ0eSBpbiB0aGUgb2JqZWN0IHJlc3BvbnNlIGlzIGFuIGFycmF5IG9mIGxpc3RlbmVyIGZ1bmN0aW9ucy5cbiAgICAgKlxuICAgICAqIEBwYXJhbSB7U3RyaW5nfFJlZ0V4cH0gZXZ0IE5hbWUgb2YgdGhlIGV2ZW50IHRvIHJldHVybiB0aGUgbGlzdGVuZXJzIGZyb20uXG4gICAgICogQHJldHVybiB7RnVuY3Rpb25bXXxPYmplY3R9IEFsbCBsaXN0ZW5lciBmdW5jdGlvbnMgZm9yIHRoZSBldmVudC5cbiAgICAgKi9cbiAgICBwcm90by5nZXRMaXN0ZW5lcnMgPSBmdW5jdGlvbiBnZXRMaXN0ZW5lcnMoZXZ0KSB7XG4gICAgICAgIHZhciBldmVudHMgPSB0aGlzLl9nZXRFdmVudHMoKTtcbiAgICAgICAgdmFyIHJlc3BvbnNlO1xuICAgICAgICB2YXIga2V5O1xuXG4gICAgICAgIC8vIFJldHVybiBhIGNvbmNhdGVuYXRlZCBhcnJheSBvZiBhbGwgbWF0Y2hpbmcgZXZlbnRzIGlmXG4gICAgICAgIC8vIHRoZSBzZWxlY3RvciBpcyBhIHJlZ3VsYXIgZXhwcmVzc2lvbi5cbiAgICAgICAgaWYgKGV2dCBpbnN0YW5jZW9mIFJlZ0V4cCkge1xuICAgICAgICAgICAgcmVzcG9uc2UgPSB7fTtcbiAgICAgICAgICAgIGZvciAoa2V5IGluIGV2ZW50cykge1xuICAgICAgICAgICAgICAgIGlmIChldmVudHMuaGFzT3duUHJvcGVydHkoa2V5KSAmJiBldnQudGVzdChrZXkpKSB7XG4gICAgICAgICAgICAgICAgICAgIHJlc3BvbnNlW2tleV0gPSBldmVudHNba2V5XTtcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICB9XG4gICAgICAgIH1cbiAgICAgICAgZWxzZSB7XG4gICAgICAgICAgICByZXNwb25zZSA9IGV2ZW50c1tldnRdIHx8IChldmVudHNbZXZ0XSA9IFtdKTtcbiAgICAgICAgfVxuXG4gICAgICAgIHJldHVybiByZXNwb25zZTtcbiAgICB9O1xuXG4gICAgLyoqXG4gICAgICogVGFrZXMgYSBsaXN0IG9mIGxpc3RlbmVyIG9iamVjdHMgYW5kIGZsYXR0ZW5zIGl0IGludG8gYSBsaXN0IG9mIGxpc3RlbmVyIGZ1bmN0aW9ucy5cbiAgICAgKlxuICAgICAqIEBwYXJhbSB7T2JqZWN0W119IGxpc3RlbmVycyBSYXcgbGlzdGVuZXIgb2JqZWN0cy5cbiAgICAgKiBAcmV0dXJuIHtGdW5jdGlvbltdfSBKdXN0IHRoZSBsaXN0ZW5lciBmdW5jdGlvbnMuXG4gICAgICovXG4gICAgcHJvdG8uZmxhdHRlbkxpc3RlbmVycyA9IGZ1bmN0aW9uIGZsYXR0ZW5MaXN0ZW5lcnMobGlzdGVuZXJzKSB7XG4gICAgICAgIHZhciBmbGF0TGlzdGVuZXJzID0gW107XG4gICAgICAgIHZhciBpO1xuXG4gICAgICAgIGZvciAoaSA9IDA7IGkgPCBsaXN0ZW5lcnMubGVuZ3RoOyBpICs9IDEpIHtcbiAgICAgICAgICAgIGZsYXRMaXN0ZW5lcnMucHVzaChsaXN0ZW5lcnNbaV0ubGlzdGVuZXIpO1xuICAgICAgICB9XG5cbiAgICAgICAgcmV0dXJuIGZsYXRMaXN0ZW5lcnM7XG4gICAgfTtcblxuICAgIC8qKlxuICAgICAqIEZldGNoZXMgdGhlIHJlcXVlc3RlZCBsaXN0ZW5lcnMgdmlhIGdldExpc3RlbmVycyBidXQgd2lsbCBhbHdheXMgcmV0dXJuIHRoZSByZXN1bHRzIGluc2lkZSBhbiBvYmplY3QuIFRoaXMgaXMgbWFpbmx5IGZvciBpbnRlcm5hbCB1c2UgYnV0IG90aGVycyBtYXkgZmluZCBpdCB1c2VmdWwuXG4gICAgICpcbiAgICAgKiBAcGFyYW0ge1N0cmluZ3xSZWdFeHB9IGV2dCBOYW1lIG9mIHRoZSBldmVudCB0byByZXR1cm4gdGhlIGxpc3RlbmVycyBmcm9tLlxuICAgICAqIEByZXR1cm4ge09iamVjdH0gQWxsIGxpc3RlbmVyIGZ1bmN0aW9ucyBmb3IgYW4gZXZlbnQgaW4gYW4gb2JqZWN0LlxuICAgICAqL1xuICAgIHByb3RvLmdldExpc3RlbmVyc0FzT2JqZWN0ID0gZnVuY3Rpb24gZ2V0TGlzdGVuZXJzQXNPYmplY3QoZXZ0KSB7XG4gICAgICAgIHZhciBsaXN0ZW5lcnMgPSB0aGlzLmdldExpc3RlbmVycyhldnQpO1xuICAgICAgICB2YXIgcmVzcG9uc2U7XG5cbiAgICAgICAgaWYgKGxpc3RlbmVycyBpbnN0YW5jZW9mIEFycmF5KSB7XG4gICAgICAgICAgICByZXNwb25zZSA9IHt9O1xuICAgICAgICAgICAgcmVzcG9uc2VbZXZ0XSA9IGxpc3RlbmVycztcbiAgICAgICAgfVxuXG4gICAgICAgIHJldHVybiByZXNwb25zZSB8fCBsaXN0ZW5lcnM7XG4gICAgfTtcblxuICAgIC8qKlxuICAgICAqIEFkZHMgYSBsaXN0ZW5lciBmdW5jdGlvbiB0byB0aGUgc3BlY2lmaWVkIGV2ZW50LlxuICAgICAqIFRoZSBsaXN0ZW5lciB3aWxsIG5vdCBiZSBhZGRlZCBpZiBpdCBpcyBhIGR1cGxpY2F0ZS5cbiAgICAgKiBJZiB0aGUgbGlzdGVuZXIgcmV0dXJucyB0cnVlIHRoZW4gaXQgd2lsbCBiZSByZW1vdmVkIGFmdGVyIGl0IGlzIGNhbGxlZC5cbiAgICAgKiBJZiB5b3UgcGFzcyBhIHJlZ3VsYXIgZXhwcmVzc2lvbiBhcyB0aGUgZXZlbnQgbmFtZSB0aGVuIHRoZSBsaXN0ZW5lciB3aWxsIGJlIGFkZGVkIHRvIGFsbCBldmVudHMgdGhhdCBtYXRjaCBpdC5cbiAgICAgKlxuICAgICAqIEBwYXJhbSB7U3RyaW5nfFJlZ0V4cH0gZXZ0IE5hbWUgb2YgdGhlIGV2ZW50IHRvIGF0dGFjaCB0aGUgbGlzdGVuZXIgdG8uXG4gICAgICogQHBhcmFtIHtGdW5jdGlvbn0gbGlzdGVuZXIgTWV0aG9kIHRvIGJlIGNhbGxlZCB3aGVuIHRoZSBldmVudCBpcyBlbWl0dGVkLiBJZiB0aGUgZnVuY3Rpb24gcmV0dXJucyB0cnVlIHRoZW4gaXQgd2lsbCBiZSByZW1vdmVkIGFmdGVyIGNhbGxpbmcuXG4gICAgICogQHJldHVybiB7T2JqZWN0fSBDdXJyZW50IGluc3RhbmNlIG9mIEV2ZW50RW1pdHRlciBmb3IgY2hhaW5pbmcuXG4gICAgICovXG4gICAgcHJvdG8uYWRkTGlzdGVuZXIgPSBmdW5jdGlvbiBhZGRMaXN0ZW5lcihldnQsIGxpc3RlbmVyKSB7XG4gICAgICAgIHZhciBsaXN0ZW5lcnMgPSB0aGlzLmdldExpc3RlbmVyc0FzT2JqZWN0KGV2dCk7XG4gICAgICAgIHZhciBsaXN0ZW5lcklzV3JhcHBlZCA9IHR5cGVvZiBsaXN0ZW5lciA9PT0gJ29iamVjdCc7XG4gICAgICAgIHZhciBrZXk7XG5cbiAgICAgICAgZm9yIChrZXkgaW4gbGlzdGVuZXJzKSB7XG4gICAgICAgICAgICBpZiAobGlzdGVuZXJzLmhhc093blByb3BlcnR5KGtleSkgJiYgaW5kZXhPZkxpc3RlbmVyKGxpc3RlbmVyc1trZXldLCBsaXN0ZW5lcikgPT09IC0xKSB7XG4gICAgICAgICAgICAgICAgbGlzdGVuZXJzW2tleV0ucHVzaChsaXN0ZW5lcklzV3JhcHBlZCA/IGxpc3RlbmVyIDoge1xuICAgICAgICAgICAgICAgICAgICBsaXN0ZW5lcjogbGlzdGVuZXIsXG4gICAgICAgICAgICAgICAgICAgIG9uY2U6IGZhbHNlXG4gICAgICAgICAgICAgICAgfSk7XG4gICAgICAgICAgICB9XG4gICAgICAgIH1cblxuICAgICAgICByZXR1cm4gdGhpcztcbiAgICB9O1xuXG4gICAgLyoqXG4gICAgICogQWxpYXMgb2YgYWRkTGlzdGVuZXJcbiAgICAgKi9cbiAgICBwcm90by5vbiA9IGFsaWFzKCdhZGRMaXN0ZW5lcicpO1xuXG4gICAgLyoqXG4gICAgICogU2VtaS1hbGlhcyBvZiBhZGRMaXN0ZW5lci4gSXQgd2lsbCBhZGQgYSBsaXN0ZW5lciB0aGF0IHdpbGwgYmVcbiAgICAgKiBhdXRvbWF0aWNhbGx5IHJlbW92ZWQgYWZ0ZXIgaXRzIGZpcnN0IGV4ZWN1dGlvbi5cbiAgICAgKlxuICAgICAqIEBwYXJhbSB7U3RyaW5nfFJlZ0V4cH0gZXZ0IE5hbWUgb2YgdGhlIGV2ZW50IHRvIGF0dGFjaCB0aGUgbGlzdGVuZXIgdG8uXG4gICAgICogQHBhcmFtIHtGdW5jdGlvbn0gbGlzdGVuZXIgTWV0aG9kIHRvIGJlIGNhbGxlZCB3aGVuIHRoZSBldmVudCBpcyBlbWl0dGVkLiBJZiB0aGUgZnVuY3Rpb24gcmV0dXJucyB0cnVlIHRoZW4gaXQgd2lsbCBiZSByZW1vdmVkIGFmdGVyIGNhbGxpbmcuXG4gICAgICogQHJldHVybiB7T2JqZWN0fSBDdXJyZW50IGluc3RhbmNlIG9mIEV2ZW50RW1pdHRlciBmb3IgY2hhaW5pbmcuXG4gICAgICovXG4gICAgcHJvdG8uYWRkT25jZUxpc3RlbmVyID0gZnVuY3Rpb24gYWRkT25jZUxpc3RlbmVyKGV2dCwgbGlzdGVuZXIpIHtcbiAgICAgICAgcmV0dXJuIHRoaXMuYWRkTGlzdGVuZXIoZXZ0LCB7XG4gICAgICAgICAgICBsaXN0ZW5lcjogbGlzdGVuZXIsXG4gICAgICAgICAgICBvbmNlOiB0cnVlXG4gICAgICAgIH0pO1xuICAgIH07XG5cbiAgICAvKipcbiAgICAgKiBBbGlhcyBvZiBhZGRPbmNlTGlzdGVuZXIuXG4gICAgICovXG4gICAgcHJvdG8ub25jZSA9IGFsaWFzKCdhZGRPbmNlTGlzdGVuZXInKTtcblxuICAgIC8qKlxuICAgICAqIERlZmluZXMgYW4gZXZlbnQgbmFtZS4gVGhpcyBpcyByZXF1aXJlZCBpZiB5b3Ugd2FudCB0byB1c2UgYSByZWdleCB0byBhZGQgYSBsaXN0ZW5lciB0byBtdWx0aXBsZSBldmVudHMgYXQgb25jZS4gSWYgeW91IGRvbid0IGRvIHRoaXMgdGhlbiBob3cgZG8geW91IGV4cGVjdCBpdCB0byBrbm93IHdoYXQgZXZlbnQgdG8gYWRkIHRvPyBTaG91bGQgaXQganVzdCBhZGQgdG8gZXZlcnkgcG9zc2libGUgbWF0Y2ggZm9yIGEgcmVnZXg/IE5vLiBUaGF0IGlzIHNjYXJ5IGFuZCBiYWQuXG4gICAgICogWW91IG5lZWQgdG8gdGVsbCBpdCB3aGF0IGV2ZW50IG5hbWVzIHNob3VsZCBiZSBtYXRjaGVkIGJ5IGEgcmVnZXguXG4gICAgICpcbiAgICAgKiBAcGFyYW0ge1N0cmluZ30gZXZ0IE5hbWUgb2YgdGhlIGV2ZW50IHRvIGNyZWF0ZS5cbiAgICAgKiBAcmV0dXJuIHtPYmplY3R9IEN1cnJlbnQgaW5zdGFuY2Ugb2YgRXZlbnRFbWl0dGVyIGZvciBjaGFpbmluZy5cbiAgICAgKi9cbiAgICBwcm90by5kZWZpbmVFdmVudCA9IGZ1bmN0aW9uIGRlZmluZUV2ZW50KGV2dCkge1xuICAgICAgICB0aGlzLmdldExpc3RlbmVycyhldnQpO1xuICAgICAgICByZXR1cm4gdGhpcztcbiAgICB9O1xuXG4gICAgLyoqXG4gICAgICogVXNlcyBkZWZpbmVFdmVudCB0byBkZWZpbmUgbXVsdGlwbGUgZXZlbnRzLlxuICAgICAqXG4gICAgICogQHBhcmFtIHtTdHJpbmdbXX0gZXZ0cyBBbiBhcnJheSBvZiBldmVudCBuYW1lcyB0byBkZWZpbmUuXG4gICAgICogQHJldHVybiB7T2JqZWN0fSBDdXJyZW50IGluc3RhbmNlIG9mIEV2ZW50RW1pdHRlciBmb3IgY2hhaW5pbmcuXG4gICAgICovXG4gICAgcHJvdG8uZGVmaW5lRXZlbnRzID0gZnVuY3Rpb24gZGVmaW5lRXZlbnRzKGV2dHMpIHtcbiAgICAgICAgZm9yICh2YXIgaSA9IDA7IGkgPCBldnRzLmxlbmd0aDsgaSArPSAxKSB7XG4gICAgICAgICAgICB0aGlzLmRlZmluZUV2ZW50KGV2dHNbaV0pO1xuICAgICAgICB9XG4gICAgICAgIHJldHVybiB0aGlzO1xuICAgIH07XG5cbiAgICAvKipcbiAgICAgKiBSZW1vdmVzIGEgbGlzdGVuZXIgZnVuY3Rpb24gZnJvbSB0aGUgc3BlY2lmaWVkIGV2ZW50LlxuICAgICAqIFdoZW4gcGFzc2VkIGEgcmVndWxhciBleHByZXNzaW9uIGFzIHRoZSBldmVudCBuYW1lLCBpdCB3aWxsIHJlbW92ZSB0aGUgbGlzdGVuZXIgZnJvbSBhbGwgZXZlbnRzIHRoYXQgbWF0Y2ggaXQuXG4gICAgICpcbiAgICAgKiBAcGFyYW0ge1N0cmluZ3xSZWdFeHB9IGV2dCBOYW1lIG9mIHRoZSBldmVudCB0byByZW1vdmUgdGhlIGxpc3RlbmVyIGZyb20uXG4gICAgICogQHBhcmFtIHtGdW5jdGlvbn0gbGlzdGVuZXIgTWV0aG9kIHRvIHJlbW92ZSBmcm9tIHRoZSBldmVudC5cbiAgICAgKiBAcmV0dXJuIHtPYmplY3R9IEN1cnJlbnQgaW5zdGFuY2Ugb2YgRXZlbnRFbWl0dGVyIGZvciBjaGFpbmluZy5cbiAgICAgKi9cbiAgICBwcm90by5yZW1vdmVMaXN0ZW5lciA9IGZ1bmN0aW9uIHJlbW92ZUxpc3RlbmVyKGV2dCwgbGlzdGVuZXIpIHtcbiAgICAgICAgdmFyIGxpc3RlbmVycyA9IHRoaXMuZ2V0TGlzdGVuZXJzQXNPYmplY3QoZXZ0KTtcbiAgICAgICAgdmFyIGluZGV4O1xuICAgICAgICB2YXIga2V5O1xuXG4gICAgICAgIGZvciAoa2V5IGluIGxpc3RlbmVycykge1xuICAgICAgICAgICAgaWYgKGxpc3RlbmVycy5oYXNPd25Qcm9wZXJ0eShrZXkpKSB7XG4gICAgICAgICAgICAgICAgaW5kZXggPSBpbmRleE9mTGlzdGVuZXIobGlzdGVuZXJzW2tleV0sIGxpc3RlbmVyKTtcblxuICAgICAgICAgICAgICAgIGlmIChpbmRleCAhPT0gLTEpIHtcbiAgICAgICAgICAgICAgICAgICAgbGlzdGVuZXJzW2tleV0uc3BsaWNlKGluZGV4LCAxKTtcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICB9XG4gICAgICAgIH1cblxuICAgICAgICByZXR1cm4gdGhpcztcbiAgICB9O1xuXG4gICAgLyoqXG4gICAgICogQWxpYXMgb2YgcmVtb3ZlTGlzdGVuZXJcbiAgICAgKi9cbiAgICBwcm90by5vZmYgPSBhbGlhcygncmVtb3ZlTGlzdGVuZXInKTtcblxuICAgIC8qKlxuICAgICAqIEFkZHMgbGlzdGVuZXJzIGluIGJ1bGsgdXNpbmcgdGhlIG1hbmlwdWxhdGVMaXN0ZW5lcnMgbWV0aG9kLlxuICAgICAqIElmIHlvdSBwYXNzIGFuIG9iamVjdCBhcyB0aGUgc2Vjb25kIGFyZ3VtZW50IHlvdSBjYW4gYWRkIHRvIG11bHRpcGxlIGV2ZW50cyBhdCBvbmNlLiBUaGUgb2JqZWN0IHNob3VsZCBjb250YWluIGtleSB2YWx1ZSBwYWlycyBvZiBldmVudHMgYW5kIGxpc3RlbmVycyBvciBsaXN0ZW5lciBhcnJheXMuIFlvdSBjYW4gYWxzbyBwYXNzIGl0IGFuIGV2ZW50IG5hbWUgYW5kIGFuIGFycmF5IG9mIGxpc3RlbmVycyB0byBiZSBhZGRlZC5cbiAgICAgKiBZb3UgY2FuIGFsc28gcGFzcyBpdCBhIHJlZ3VsYXIgZXhwcmVzc2lvbiB0byBhZGQgdGhlIGFycmF5IG9mIGxpc3RlbmVycyB0byBhbGwgZXZlbnRzIHRoYXQgbWF0Y2ggaXQuXG4gICAgICogWWVhaCwgdGhpcyBmdW5jdGlvbiBkb2VzIHF1aXRlIGEgYml0LiBUaGF0J3MgcHJvYmFibHkgYSBiYWQgdGhpbmcuXG4gICAgICpcbiAgICAgKiBAcGFyYW0ge1N0cmluZ3xPYmplY3R8UmVnRXhwfSBldnQgQW4gZXZlbnQgbmFtZSBpZiB5b3Ugd2lsbCBwYXNzIGFuIGFycmF5IG9mIGxpc3RlbmVycyBuZXh0LiBBbiBvYmplY3QgaWYgeW91IHdpc2ggdG8gYWRkIHRvIG11bHRpcGxlIGV2ZW50cyBhdCBvbmNlLlxuICAgICAqIEBwYXJhbSB7RnVuY3Rpb25bXX0gW2xpc3RlbmVyc10gQW4gb3B0aW9uYWwgYXJyYXkgb2YgbGlzdGVuZXIgZnVuY3Rpb25zIHRvIGFkZC5cbiAgICAgKiBAcmV0dXJuIHtPYmplY3R9IEN1cnJlbnQgaW5zdGFuY2Ugb2YgRXZlbnRFbWl0dGVyIGZvciBjaGFpbmluZy5cbiAgICAgKi9cbiAgICBwcm90by5hZGRMaXN0ZW5lcnMgPSBmdW5jdGlvbiBhZGRMaXN0ZW5lcnMoZXZ0LCBsaXN0ZW5lcnMpIHtcbiAgICAgICAgLy8gUGFzcyB0aHJvdWdoIHRvIG1hbmlwdWxhdGVMaXN0ZW5lcnNcbiAgICAgICAgcmV0dXJuIHRoaXMubWFuaXB1bGF0ZUxpc3RlbmVycyhmYWxzZSwgZXZ0LCBsaXN0ZW5lcnMpO1xuICAgIH07XG5cbiAgICAvKipcbiAgICAgKiBSZW1vdmVzIGxpc3RlbmVycyBpbiBidWxrIHVzaW5nIHRoZSBtYW5pcHVsYXRlTGlzdGVuZXJzIG1ldGhvZC5cbiAgICAgKiBJZiB5b3UgcGFzcyBhbiBvYmplY3QgYXMgdGhlIHNlY29uZCBhcmd1bWVudCB5b3UgY2FuIHJlbW92ZSBmcm9tIG11bHRpcGxlIGV2ZW50cyBhdCBvbmNlLiBUaGUgb2JqZWN0IHNob3VsZCBjb250YWluIGtleSB2YWx1ZSBwYWlycyBvZiBldmVudHMgYW5kIGxpc3RlbmVycyBvciBsaXN0ZW5lciBhcnJheXMuXG4gICAgICogWW91IGNhbiBhbHNvIHBhc3MgaXQgYW4gZXZlbnQgbmFtZSBhbmQgYW4gYXJyYXkgb2YgbGlzdGVuZXJzIHRvIGJlIHJlbW92ZWQuXG4gICAgICogWW91IGNhbiBhbHNvIHBhc3MgaXQgYSByZWd1bGFyIGV4cHJlc3Npb24gdG8gcmVtb3ZlIHRoZSBsaXN0ZW5lcnMgZnJvbSBhbGwgZXZlbnRzIHRoYXQgbWF0Y2ggaXQuXG4gICAgICpcbiAgICAgKiBAcGFyYW0ge1N0cmluZ3xPYmplY3R8UmVnRXhwfSBldnQgQW4gZXZlbnQgbmFtZSBpZiB5b3Ugd2lsbCBwYXNzIGFuIGFycmF5IG9mIGxpc3RlbmVycyBuZXh0LiBBbiBvYmplY3QgaWYgeW91IHdpc2ggdG8gcmVtb3ZlIGZyb20gbXVsdGlwbGUgZXZlbnRzIGF0IG9uY2UuXG4gICAgICogQHBhcmFtIHtGdW5jdGlvbltdfSBbbGlzdGVuZXJzXSBBbiBvcHRpb25hbCBhcnJheSBvZiBsaXN0ZW5lciBmdW5jdGlvbnMgdG8gcmVtb3ZlLlxuICAgICAqIEByZXR1cm4ge09iamVjdH0gQ3VycmVudCBpbnN0YW5jZSBvZiBFdmVudEVtaXR0ZXIgZm9yIGNoYWluaW5nLlxuICAgICAqL1xuICAgIHByb3RvLnJlbW92ZUxpc3RlbmVycyA9IGZ1bmN0aW9uIHJlbW92ZUxpc3RlbmVycyhldnQsIGxpc3RlbmVycykge1xuICAgICAgICAvLyBQYXNzIHRocm91Z2ggdG8gbWFuaXB1bGF0ZUxpc3RlbmVyc1xuICAgICAgICByZXR1cm4gdGhpcy5tYW5pcHVsYXRlTGlzdGVuZXJzKHRydWUsIGV2dCwgbGlzdGVuZXJzKTtcbiAgICB9O1xuXG4gICAgLyoqXG4gICAgICogRWRpdHMgbGlzdGVuZXJzIGluIGJ1bGsuIFRoZSBhZGRMaXN0ZW5lcnMgYW5kIHJlbW92ZUxpc3RlbmVycyBtZXRob2RzIGJvdGggdXNlIHRoaXMgdG8gZG8gdGhlaXIgam9iLiBZb3Ugc2hvdWxkIHJlYWxseSB1c2UgdGhvc2UgaW5zdGVhZCwgdGhpcyBpcyBhIGxpdHRsZSBsb3dlciBsZXZlbC5cbiAgICAgKiBUaGUgZmlyc3QgYXJndW1lbnQgd2lsbCBkZXRlcm1pbmUgaWYgdGhlIGxpc3RlbmVycyBhcmUgcmVtb3ZlZCAodHJ1ZSkgb3IgYWRkZWQgKGZhbHNlKS5cbiAgICAgKiBJZiB5b3UgcGFzcyBhbiBvYmplY3QgYXMgdGhlIHNlY29uZCBhcmd1bWVudCB5b3UgY2FuIGFkZC9yZW1vdmUgZnJvbSBtdWx0aXBsZSBldmVudHMgYXQgb25jZS4gVGhlIG9iamVjdCBzaG91bGQgY29udGFpbiBrZXkgdmFsdWUgcGFpcnMgb2YgZXZlbnRzIGFuZCBsaXN0ZW5lcnMgb3IgbGlzdGVuZXIgYXJyYXlzLlxuICAgICAqIFlvdSBjYW4gYWxzbyBwYXNzIGl0IGFuIGV2ZW50IG5hbWUgYW5kIGFuIGFycmF5IG9mIGxpc3RlbmVycyB0byBiZSBhZGRlZC9yZW1vdmVkLlxuICAgICAqIFlvdSBjYW4gYWxzbyBwYXNzIGl0IGEgcmVndWxhciBleHByZXNzaW9uIHRvIG1hbmlwdWxhdGUgdGhlIGxpc3RlbmVycyBvZiBhbGwgZXZlbnRzIHRoYXQgbWF0Y2ggaXQuXG4gICAgICpcbiAgICAgKiBAcGFyYW0ge0Jvb2xlYW59IHJlbW92ZSBUcnVlIGlmIHlvdSB3YW50IHRvIHJlbW92ZSBsaXN0ZW5lcnMsIGZhbHNlIGlmIHlvdSB3YW50IHRvIGFkZC5cbiAgICAgKiBAcGFyYW0ge1N0cmluZ3xPYmplY3R8UmVnRXhwfSBldnQgQW4gZXZlbnQgbmFtZSBpZiB5b3Ugd2lsbCBwYXNzIGFuIGFycmF5IG9mIGxpc3RlbmVycyBuZXh0LiBBbiBvYmplY3QgaWYgeW91IHdpc2ggdG8gYWRkL3JlbW92ZSBmcm9tIG11bHRpcGxlIGV2ZW50cyBhdCBvbmNlLlxuICAgICAqIEBwYXJhbSB7RnVuY3Rpb25bXX0gW2xpc3RlbmVyc10gQW4gb3B0aW9uYWwgYXJyYXkgb2YgbGlzdGVuZXIgZnVuY3Rpb25zIHRvIGFkZC9yZW1vdmUuXG4gICAgICogQHJldHVybiB7T2JqZWN0fSBDdXJyZW50IGluc3RhbmNlIG9mIEV2ZW50RW1pdHRlciBmb3IgY2hhaW5pbmcuXG4gICAgICovXG4gICAgcHJvdG8ubWFuaXB1bGF0ZUxpc3RlbmVycyA9IGZ1bmN0aW9uIG1hbmlwdWxhdGVMaXN0ZW5lcnMocmVtb3ZlLCBldnQsIGxpc3RlbmVycykge1xuICAgICAgICB2YXIgaTtcbiAgICAgICAgdmFyIHZhbHVlO1xuICAgICAgICB2YXIgc2luZ2xlID0gcmVtb3ZlID8gdGhpcy5yZW1vdmVMaXN0ZW5lciA6IHRoaXMuYWRkTGlzdGVuZXI7XG4gICAgICAgIHZhciBtdWx0aXBsZSA9IHJlbW92ZSA/IHRoaXMucmVtb3ZlTGlzdGVuZXJzIDogdGhpcy5hZGRMaXN0ZW5lcnM7XG5cbiAgICAgICAgLy8gSWYgZXZ0IGlzIGFuIG9iamVjdCB0aGVuIHBhc3MgZWFjaCBvZiBpdHMgcHJvcGVydGllcyB0byB0aGlzIG1ldGhvZFxuICAgICAgICBpZiAodHlwZW9mIGV2dCA9PT0gJ29iamVjdCcgJiYgIShldnQgaW5zdGFuY2VvZiBSZWdFeHApKSB7XG4gICAgICAgICAgICBmb3IgKGkgaW4gZXZ0KSB7XG4gICAgICAgICAgICAgICAgaWYgKGV2dC5oYXNPd25Qcm9wZXJ0eShpKSAmJiAodmFsdWUgPSBldnRbaV0pKSB7XG4gICAgICAgICAgICAgICAgICAgIC8vIFBhc3MgdGhlIHNpbmdsZSBsaXN0ZW5lciBzdHJhaWdodCB0aHJvdWdoIHRvIHRoZSBzaW5ndWxhciBtZXRob2RcbiAgICAgICAgICAgICAgICAgICAgaWYgKHR5cGVvZiB2YWx1ZSA9PT0gJ2Z1bmN0aW9uJykge1xuICAgICAgICAgICAgICAgICAgICAgICAgc2luZ2xlLmNhbGwodGhpcywgaSwgdmFsdWUpO1xuICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgICAgIGVsc2Uge1xuICAgICAgICAgICAgICAgICAgICAgICAgLy8gT3RoZXJ3aXNlIHBhc3MgYmFjayB0byB0aGUgbXVsdGlwbGUgZnVuY3Rpb25cbiAgICAgICAgICAgICAgICAgICAgICAgIG11bHRpcGxlLmNhbGwodGhpcywgaSwgdmFsdWUpO1xuICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgfVxuICAgICAgICB9XG4gICAgICAgIGVsc2Uge1xuICAgICAgICAgICAgLy8gU28gZXZ0IG11c3QgYmUgYSBzdHJpbmdcbiAgICAgICAgICAgIC8vIEFuZCBsaXN0ZW5lcnMgbXVzdCBiZSBhbiBhcnJheSBvZiBsaXN0ZW5lcnNcbiAgICAgICAgICAgIC8vIExvb3Agb3ZlciBpdCBhbmQgcGFzcyBlYWNoIG9uZSB0byB0aGUgbXVsdGlwbGUgbWV0aG9kXG4gICAgICAgICAgICBpID0gbGlzdGVuZXJzLmxlbmd0aDtcbiAgICAgICAgICAgIHdoaWxlIChpLS0pIHtcbiAgICAgICAgICAgICAgICBzaW5nbGUuY2FsbCh0aGlzLCBldnQsIGxpc3RlbmVyc1tpXSk7XG4gICAgICAgICAgICB9XG4gICAgICAgIH1cblxuICAgICAgICByZXR1cm4gdGhpcztcbiAgICB9O1xuXG4gICAgLyoqXG4gICAgICogUmVtb3ZlcyBhbGwgbGlzdGVuZXJzIGZyb20gYSBzcGVjaWZpZWQgZXZlbnQuXG4gICAgICogSWYgeW91IGRvIG5vdCBzcGVjaWZ5IGFuIGV2ZW50IHRoZW4gYWxsIGxpc3RlbmVycyB3aWxsIGJlIHJlbW92ZWQuXG4gICAgICogVGhhdCBtZWFucyBldmVyeSBldmVudCB3aWxsIGJlIGVtcHRpZWQuXG4gICAgICogWW91IGNhbiBhbHNvIHBhc3MgYSByZWdleCB0byByZW1vdmUgYWxsIGV2ZW50cyB0aGF0IG1hdGNoIGl0LlxuICAgICAqXG4gICAgICogQHBhcmFtIHtTdHJpbmd8UmVnRXhwfSBbZXZ0XSBPcHRpb25hbCBuYW1lIG9mIHRoZSBldmVudCB0byByZW1vdmUgYWxsIGxpc3RlbmVycyBmb3IuIFdpbGwgcmVtb3ZlIGZyb20gZXZlcnkgZXZlbnQgaWYgbm90IHBhc3NlZC5cbiAgICAgKiBAcmV0dXJuIHtPYmplY3R9IEN1cnJlbnQgaW5zdGFuY2Ugb2YgRXZlbnRFbWl0dGVyIGZvciBjaGFpbmluZy5cbiAgICAgKi9cbiAgICBwcm90by5yZW1vdmVFdmVudCA9IGZ1bmN0aW9uIHJlbW92ZUV2ZW50KGV2dCkge1xuICAgICAgICB2YXIgdHlwZSA9IHR5cGVvZiBldnQ7XG4gICAgICAgIHZhciBldmVudHMgPSB0aGlzLl9nZXRFdmVudHMoKTtcbiAgICAgICAgdmFyIGtleTtcblxuICAgICAgICAvLyBSZW1vdmUgZGlmZmVyZW50IHRoaW5ncyBkZXBlbmRpbmcgb24gdGhlIHN0YXRlIG9mIGV2dFxuICAgICAgICBpZiAodHlwZSA9PT0gJ3N0cmluZycpIHtcbiAgICAgICAgICAgIC8vIFJlbW92ZSBhbGwgbGlzdGVuZXJzIGZvciB0aGUgc3BlY2lmaWVkIGV2ZW50XG4gICAgICAgICAgICBkZWxldGUgZXZlbnRzW2V2dF07XG4gICAgICAgIH1cbiAgICAgICAgZWxzZSBpZiAoZXZ0IGluc3RhbmNlb2YgUmVnRXhwKSB7XG4gICAgICAgICAgICAvLyBSZW1vdmUgYWxsIGV2ZW50cyBtYXRjaGluZyB0aGUgcmVnZXguXG4gICAgICAgICAgICBmb3IgKGtleSBpbiBldmVudHMpIHtcbiAgICAgICAgICAgICAgICBpZiAoZXZlbnRzLmhhc093blByb3BlcnR5KGtleSkgJiYgZXZ0LnRlc3Qoa2V5KSkge1xuICAgICAgICAgICAgICAgICAgICBkZWxldGUgZXZlbnRzW2tleV07XG4gICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgfVxuICAgICAgICB9XG4gICAgICAgIGVsc2Uge1xuICAgICAgICAgICAgLy8gUmVtb3ZlIGFsbCBsaXN0ZW5lcnMgaW4gYWxsIGV2ZW50c1xuICAgICAgICAgICAgZGVsZXRlIHRoaXMuX2V2ZW50cztcbiAgICAgICAgfVxuXG4gICAgICAgIHJldHVybiB0aGlzO1xuICAgIH07XG5cbiAgICAvKipcbiAgICAgKiBBbGlhcyBvZiByZW1vdmVFdmVudC5cbiAgICAgKlxuICAgICAqIEFkZGVkIHRvIG1pcnJvciB0aGUgbm9kZSBBUEkuXG4gICAgICovXG4gICAgcHJvdG8ucmVtb3ZlQWxsTGlzdGVuZXJzID0gYWxpYXMoJ3JlbW92ZUV2ZW50Jyk7XG5cbiAgICAvKipcbiAgICAgKiBFbWl0cyBhbiBldmVudCBvZiB5b3VyIGNob2ljZS5cbiAgICAgKiBXaGVuIGVtaXR0ZWQsIGV2ZXJ5IGxpc3RlbmVyIGF0dGFjaGVkIHRvIHRoYXQgZXZlbnQgd2lsbCBiZSBleGVjdXRlZC5cbiAgICAgKiBJZiB5b3UgcGFzcyB0aGUgb3B0aW9uYWwgYXJndW1lbnQgYXJyYXkgdGhlbiB0aG9zZSBhcmd1bWVudHMgd2lsbCBiZSBwYXNzZWQgdG8gZXZlcnkgbGlzdGVuZXIgdXBvbiBleGVjdXRpb24uXG4gICAgICogQmVjYXVzZSBpdCB1c2VzIGBhcHBseWAsIHlvdXIgYXJyYXkgb2YgYXJndW1lbnRzIHdpbGwgYmUgcGFzc2VkIGFzIGlmIHlvdSB3cm90ZSB0aGVtIG91dCBzZXBhcmF0ZWx5LlxuICAgICAqIFNvIHRoZXkgd2lsbCBub3QgYXJyaXZlIHdpdGhpbiB0aGUgYXJyYXkgb24gdGhlIG90aGVyIHNpZGUsIHRoZXkgd2lsbCBiZSBzZXBhcmF0ZS5cbiAgICAgKiBZb3UgY2FuIGFsc28gcGFzcyBhIHJlZ3VsYXIgZXhwcmVzc2lvbiB0byBlbWl0IHRvIGFsbCBldmVudHMgdGhhdCBtYXRjaCBpdC5cbiAgICAgKlxuICAgICAqIEBwYXJhbSB7U3RyaW5nfFJlZ0V4cH0gZXZ0IE5hbWUgb2YgdGhlIGV2ZW50IHRvIGVtaXQgYW5kIGV4ZWN1dGUgbGlzdGVuZXJzIGZvci5cbiAgICAgKiBAcGFyYW0ge0FycmF5fSBbYXJnc10gT3B0aW9uYWwgYXJyYXkgb2YgYXJndW1lbnRzIHRvIGJlIHBhc3NlZCB0byBlYWNoIGxpc3RlbmVyLlxuICAgICAqIEByZXR1cm4ge09iamVjdH0gQ3VycmVudCBpbnN0YW5jZSBvZiBFdmVudEVtaXR0ZXIgZm9yIGNoYWluaW5nLlxuICAgICAqL1xuICAgIHByb3RvLmVtaXRFdmVudCA9IGZ1bmN0aW9uIGVtaXRFdmVudChldnQsIGFyZ3MpIHtcbiAgICAgICAgdmFyIGxpc3RlbmVyc01hcCA9IHRoaXMuZ2V0TGlzdGVuZXJzQXNPYmplY3QoZXZ0KTtcbiAgICAgICAgdmFyIGxpc3RlbmVycztcbiAgICAgICAgdmFyIGxpc3RlbmVyO1xuICAgICAgICB2YXIgaTtcbiAgICAgICAgdmFyIGtleTtcbiAgICAgICAgdmFyIHJlc3BvbnNlO1xuXG4gICAgICAgIGZvciAoa2V5IGluIGxpc3RlbmVyc01hcCkge1xuICAgICAgICAgICAgaWYgKGxpc3RlbmVyc01hcC5oYXNPd25Qcm9wZXJ0eShrZXkpKSB7XG4gICAgICAgICAgICAgICAgbGlzdGVuZXJzID0gbGlzdGVuZXJzTWFwW2tleV0uc2xpY2UoMCk7XG4gICAgICAgICAgICAgICAgaSA9IGxpc3RlbmVycy5sZW5ndGg7XG5cbiAgICAgICAgICAgICAgICB3aGlsZSAoaS0tKSB7XG4gICAgICAgICAgICAgICAgICAgIC8vIElmIHRoZSBsaXN0ZW5lciByZXR1cm5zIHRydWUgdGhlbiBpdCBzaGFsbCBiZSByZW1vdmVkIGZyb20gdGhlIGV2ZW50XG4gICAgICAgICAgICAgICAgICAgIC8vIFRoZSBmdW5jdGlvbiBpcyBleGVjdXRlZCBlaXRoZXIgd2l0aCBhIGJhc2ljIGNhbGwgb3IgYW4gYXBwbHkgaWYgdGhlcmUgaXMgYW4gYXJncyBhcnJheVxuICAgICAgICAgICAgICAgICAgICBsaXN0ZW5lciA9IGxpc3RlbmVyc1tpXTtcblxuICAgICAgICAgICAgICAgICAgICBpZiAobGlzdGVuZXIub25jZSA9PT0gdHJ1ZSkge1xuICAgICAgICAgICAgICAgICAgICAgICAgdGhpcy5yZW1vdmVMaXN0ZW5lcihldnQsIGxpc3RlbmVyLmxpc3RlbmVyKTtcbiAgICAgICAgICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICAgICAgICAgIHJlc3BvbnNlID0gbGlzdGVuZXIubGlzdGVuZXIuYXBwbHkodGhpcywgYXJncyB8fCBbXSk7XG5cbiAgICAgICAgICAgICAgICAgICAgaWYgKHJlc3BvbnNlID09PSB0aGlzLl9nZXRPbmNlUmV0dXJuVmFsdWUoKSkge1xuICAgICAgICAgICAgICAgICAgICAgICAgdGhpcy5yZW1vdmVMaXN0ZW5lcihldnQsIGxpc3RlbmVyLmxpc3RlbmVyKTtcbiAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgIH1cbiAgICAgICAgfVxuXG4gICAgICAgIHJldHVybiB0aGlzO1xuICAgIH07XG5cbiAgICAvKipcbiAgICAgKiBBbGlhcyBvZiBlbWl0RXZlbnRcbiAgICAgKi9cbiAgICBwcm90by50cmlnZ2VyID0gYWxpYXMoJ2VtaXRFdmVudCcpO1xuXG4gICAgLyoqXG4gICAgICogU3VidGx5IGRpZmZlcmVudCBmcm9tIGVtaXRFdmVudCBpbiB0aGF0IGl0IHdpbGwgcGFzcyBpdHMgYXJndW1lbnRzIG9uIHRvIHRoZSBsaXN0ZW5lcnMsIGFzIG9wcG9zZWQgdG8gdGFraW5nIGEgc2luZ2xlIGFycmF5IG9mIGFyZ3VtZW50cyB0byBwYXNzIG9uLlxuICAgICAqIEFzIHdpdGggZW1pdEV2ZW50LCB5b3UgY2FuIHBhc3MgYSByZWdleCBpbiBwbGFjZSBvZiB0aGUgZXZlbnQgbmFtZSB0byBlbWl0IHRvIGFsbCBldmVudHMgdGhhdCBtYXRjaCBpdC5cbiAgICAgKlxuICAgICAqIEBwYXJhbSB7U3RyaW5nfFJlZ0V4cH0gZXZ0IE5hbWUgb2YgdGhlIGV2ZW50IHRvIGVtaXQgYW5kIGV4ZWN1dGUgbGlzdGVuZXJzIGZvci5cbiAgICAgKiBAcGFyYW0gey4uLip9IE9wdGlvbmFsIGFkZGl0aW9uYWwgYXJndW1lbnRzIHRvIGJlIHBhc3NlZCB0byBlYWNoIGxpc3RlbmVyLlxuICAgICAqIEByZXR1cm4ge09iamVjdH0gQ3VycmVudCBpbnN0YW5jZSBvZiBFdmVudEVtaXR0ZXIgZm9yIGNoYWluaW5nLlxuICAgICAqL1xuICAgIHByb3RvLmVtaXQgPSBmdW5jdGlvbiBlbWl0KGV2dCkge1xuICAgICAgICB2YXIgYXJncyA9IEFycmF5LnByb3RvdHlwZS5zbGljZS5jYWxsKGFyZ3VtZW50cywgMSk7XG4gICAgICAgIHJldHVybiB0aGlzLmVtaXRFdmVudChldnQsIGFyZ3MpO1xuICAgIH07XG5cbiAgICAvKipcbiAgICAgKiBTZXRzIHRoZSBjdXJyZW50IHZhbHVlIHRvIGNoZWNrIGFnYWluc3Qgd2hlbiBleGVjdXRpbmcgbGlzdGVuZXJzLiBJZiBhXG4gICAgICogbGlzdGVuZXJzIHJldHVybiB2YWx1ZSBtYXRjaGVzIHRoZSBvbmUgc2V0IGhlcmUgdGhlbiBpdCB3aWxsIGJlIHJlbW92ZWRcbiAgICAgKiBhZnRlciBleGVjdXRpb24uIFRoaXMgdmFsdWUgZGVmYXVsdHMgdG8gdHJ1ZS5cbiAgICAgKlxuICAgICAqIEBwYXJhbSB7Kn0gdmFsdWUgVGhlIG5ldyB2YWx1ZSB0byBjaGVjayBmb3Igd2hlbiBleGVjdXRpbmcgbGlzdGVuZXJzLlxuICAgICAqIEByZXR1cm4ge09iamVjdH0gQ3VycmVudCBpbnN0YW5jZSBvZiBFdmVudEVtaXR0ZXIgZm9yIGNoYWluaW5nLlxuICAgICAqL1xuICAgIHByb3RvLnNldE9uY2VSZXR1cm5WYWx1ZSA9IGZ1bmN0aW9uIHNldE9uY2VSZXR1cm5WYWx1ZSh2YWx1ZSkge1xuICAgICAgICB0aGlzLl9vbmNlUmV0dXJuVmFsdWUgPSB2YWx1ZTtcbiAgICAgICAgcmV0dXJuIHRoaXM7XG4gICAgfTtcblxuICAgIC8qKlxuICAgICAqIEZldGNoZXMgdGhlIGN1cnJlbnQgdmFsdWUgdG8gY2hlY2sgYWdhaW5zdCB3aGVuIGV4ZWN1dGluZyBsaXN0ZW5lcnMuIElmXG4gICAgICogdGhlIGxpc3RlbmVycyByZXR1cm4gdmFsdWUgbWF0Y2hlcyB0aGlzIG9uZSB0aGVuIGl0IHNob3VsZCBiZSByZW1vdmVkXG4gICAgICogYXV0b21hdGljYWxseS4gSXQgd2lsbCByZXR1cm4gdHJ1ZSBieSBkZWZhdWx0LlxuICAgICAqXG4gICAgICogQHJldHVybiB7KnxCb29sZWFufSBUaGUgY3VycmVudCB2YWx1ZSB0byBjaGVjayBmb3Igb3IgdGhlIGRlZmF1bHQsIHRydWUuXG4gICAgICogQGFwaSBwcml2YXRlXG4gICAgICovXG4gICAgcHJvdG8uX2dldE9uY2VSZXR1cm5WYWx1ZSA9IGZ1bmN0aW9uIF9nZXRPbmNlUmV0dXJuVmFsdWUoKSB7XG4gICAgICAgIGlmICh0aGlzLmhhc093blByb3BlcnR5KCdfb25jZVJldHVyblZhbHVlJykpIHtcbiAgICAgICAgICAgIHJldHVybiB0aGlzLl9vbmNlUmV0dXJuVmFsdWU7XG4gICAgICAgIH1cbiAgICAgICAgZWxzZSB7XG4gICAgICAgICAgICByZXR1cm4gdHJ1ZTtcbiAgICAgICAgfVxuICAgIH07XG5cbiAgICAvKipcbiAgICAgKiBGZXRjaGVzIHRoZSBldmVudHMgb2JqZWN0IGFuZCBjcmVhdGVzIG9uZSBpZiByZXF1aXJlZC5cbiAgICAgKlxuICAgICAqIEByZXR1cm4ge09iamVjdH0gVGhlIGV2ZW50cyBzdG9yYWdlIG9iamVjdC5cbiAgICAgKiBAYXBpIHByaXZhdGVcbiAgICAgKi9cbiAgICBwcm90by5fZ2V0RXZlbnRzID0gZnVuY3Rpb24gX2dldEV2ZW50cygpIHtcbiAgICAgICAgcmV0dXJuIHRoaXMuX2V2ZW50cyB8fCAodGhpcy5fZXZlbnRzID0ge30pO1xuICAgIH07XG5cbiAgICAvKipcbiAgICAgKiBSZXZlcnRzIHRoZSBnbG9iYWwge0BsaW5rIEV2ZW50RW1pdHRlcn0gdG8gaXRzIHByZXZpb3VzIHZhbHVlIGFuZCByZXR1cm5zIGEgcmVmZXJlbmNlIHRvIHRoaXMgdmVyc2lvbi5cbiAgICAgKlxuICAgICAqIEByZXR1cm4ge0Z1bmN0aW9ufSBOb24gY29uZmxpY3RpbmcgRXZlbnRFbWl0dGVyIGNsYXNzLlxuICAgICAqL1xuICAgIEV2ZW50RW1pdHRlci5ub0NvbmZsaWN0ID0gZnVuY3Rpb24gbm9Db25mbGljdCgpIHtcbiAgICAgICAgZXhwb3J0cy5FdmVudEVtaXR0ZXIgPSBvcmlnaW5hbEdsb2JhbFZhbHVlO1xuICAgICAgICByZXR1cm4gRXZlbnRFbWl0dGVyO1xuICAgIH07XG5cbiAgICAvLyBFeHBvc2UgdGhlIGNsYXNzIGVpdGhlciB2aWEgQU1ELCBDb21tb25KUyBvciB0aGUgZ2xvYmFsIG9iamVjdFxuICAgIGlmICh0eXBlb2YgZGVmaW5lID09PSAnZnVuY3Rpb24nICYmIGRlZmluZS5hbWQpIHtcbiAgICAgICAgZGVmaW5lKGZ1bmN0aW9uICgpIHtcbiAgICAgICAgICAgIHJldHVybiBFdmVudEVtaXR0ZXI7XG4gICAgICAgIH0pO1xuICAgIH1cbiAgICBlbHNlIGlmICh0eXBlb2YgbW9kdWxlID09PSAnb2JqZWN0JyAmJiBtb2R1bGUuZXhwb3J0cyl7XG4gICAgICAgIG1vZHVsZS5leHBvcnRzID0gRXZlbnRFbWl0dGVyO1xuICAgIH1cbiAgICBlbHNlIHtcbiAgICAgICAgZXhwb3J0cy5FdmVudEVtaXR0ZXIgPSBFdmVudEVtaXR0ZXI7XG4gICAgfVxufS5jYWxsKHRoaXMpKTtcbiJdfQ==
