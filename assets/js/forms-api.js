(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
'use strict';

var mc4wp = window.mc4wp || {};

// deps
var Gator = require('gator');
var forms = require('./forms/forms.js');
var listeners = window.mc4wp && window.mc4wp.listeners ? window.mc4wp.listeners : [];
var config = window.mc4wp_forms_config || {};

// register early listeners
for(var i=0; i<listeners.length;i++) {
	forms.on(listeners[i].event, listeners[i].callback);
}

// was a form submitted?
// TODO: take submitted element into account here.
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

	//if( config.auto_scroll ) {
	//	form.placeIntoView( config.auto_scroll === 'animated' );
	//}
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


// expose forms object
mc4wp.forms = forms;
window.mc4wp = mc4wp;
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
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJhc3NldHMvYnJvd3NlcmlmeS9mb3Jtcy1hcGkuanMiLCJhc3NldHMvYnJvd3NlcmlmeS9mb3Jtcy9mb3JtLmpzIiwiYXNzZXRzL2Jyb3dzZXJpZnkvZm9ybXMvZm9ybXMuanMiLCJhc3NldHMvYnJvd3NlcmlmeS90aGlyZC1wYXJ0eS9mb3JtMmpzLmpzIiwiYXNzZXRzL2Jyb3dzZXJpZnkvdGhpcmQtcGFydHkvc2VyaWFsaXplLmpzIiwibm9kZV9tb2R1bGVzL2dhdG9yL2dhdG9yLmpzIiwibm9kZV9tb2R1bGVzL3BvcHVsYXRlLmpzL3BvcHVsYXRlLmpzIiwibm9kZV9tb2R1bGVzL3dvbGZ5ODctZXZlbnRlbWl0dGVyL0V2ZW50RW1pdHRlci5qcyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTtBQ0FBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUM5REE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQzlEQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ25FQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUM1VkE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNoUUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDOVdBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNuRkE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EiLCJmaWxlIjoiZ2VuZXJhdGVkLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXNDb250ZW50IjpbIihmdW5jdGlvbiBlKHQsbixyKXtmdW5jdGlvbiBzKG8sdSl7aWYoIW5bb10pe2lmKCF0W29dKXt2YXIgYT10eXBlb2YgcmVxdWlyZT09XCJmdW5jdGlvblwiJiZyZXF1aXJlO2lmKCF1JiZhKXJldHVybiBhKG8sITApO2lmKGkpcmV0dXJuIGkobywhMCk7dmFyIGY9bmV3IEVycm9yKFwiQ2Fubm90IGZpbmQgbW9kdWxlICdcIitvK1wiJ1wiKTt0aHJvdyBmLmNvZGU9XCJNT0RVTEVfTk9UX0ZPVU5EXCIsZn12YXIgbD1uW29dPXtleHBvcnRzOnt9fTt0W29dWzBdLmNhbGwobC5leHBvcnRzLGZ1bmN0aW9uKGUpe3ZhciBuPXRbb11bMV1bZV07cmV0dXJuIHMobj9uOmUpfSxsLGwuZXhwb3J0cyxlLHQsbixyKX1yZXR1cm4gbltvXS5leHBvcnRzfXZhciBpPXR5cGVvZiByZXF1aXJlPT1cImZ1bmN0aW9uXCImJnJlcXVpcmU7Zm9yKHZhciBvPTA7bzxyLmxlbmd0aDtvKyspcyhyW29dKTtyZXR1cm4gc30pIiwiJ3VzZSBzdHJpY3QnO1xuXG52YXIgbWM0d3AgPSB3aW5kb3cubWM0d3AgfHwge307XG5cbi8vIGRlcHNcbnZhciBHYXRvciA9IHJlcXVpcmUoJ2dhdG9yJyk7XG52YXIgZm9ybXMgPSByZXF1aXJlKCcuL2Zvcm1zL2Zvcm1zLmpzJyk7XG52YXIgbGlzdGVuZXJzID0gd2luZG93Lm1jNHdwICYmIHdpbmRvdy5tYzR3cC5saXN0ZW5lcnMgPyB3aW5kb3cubWM0d3AubGlzdGVuZXJzIDogW107XG52YXIgY29uZmlnID0gd2luZG93Lm1jNHdwX2Zvcm1zX2NvbmZpZyB8fCB7fTtcblxuLy8gcmVnaXN0ZXIgZWFybHkgbGlzdGVuZXJzXG5mb3IodmFyIGk9MDsgaTxsaXN0ZW5lcnMubGVuZ3RoO2krKykge1xuXHRmb3Jtcy5vbihsaXN0ZW5lcnNbaV0uZXZlbnQsIGxpc3RlbmVyc1tpXS5jYWxsYmFjayk7XG59XG5cbi8vIHdhcyBhIGZvcm0gc3VibWl0dGVkP1xuLy8gVE9ETzogdGFrZSBzdWJtaXR0ZWQgZWxlbWVudCBpbnRvIGFjY291bnQgaGVyZS5cbmlmKCBjb25maWcuc3VibWl0dGVkX2Zvcm0gJiYgY29uZmlnLnN1Ym1pdHRlZF9mb3JtLmlkICkge1xuXHR2YXIgZm9ybSA9IGZvcm1zLmdldChjb25maWcuc3VibWl0dGVkX2Zvcm0uaWQpO1xuXG5cdC8vIGFkZCBjbGFzcyAmIHRyaWdnZXIgZXZlbnRcblx0Zm9ybXMudHJpZ2dlciggJ3N1Ym1pdHRlZCcsIFtmb3JtXSk7XG5cblx0aWYoIGNvbmZpZy5zdWJtaXR0ZWRfZm9ybS5lcnJvcnMgKSB7XG5cdFx0Ly8gZm9ybSBoYXMgZXJyb3JzLCByZXBvcHVsYXRlIGl0LlxuXHRcdGZvcm0uc2V0RGF0YShjb25maWcuc3VibWl0dGVkX2Zvcm0uZGF0YSk7XG5cdFx0Zm9ybXMudHJpZ2dlcignZXJyb3InLCBbZm9ybSwgY29uZmlnLnN1Ym1pdHRlZF9mb3JtLmVycm9yc10pO1xuXHR9IGVsc2Uge1xuXHRcdC8vIGZvcm0gd2FzIHN1Y2Nlc3NmdWxseSBzdWJtaXR0ZWRcblx0XHRmb3Jtcy50cmlnZ2VyKCdzdWNjZXNzJywgW2Zvcm0sIGNvbmZpZy5zdWJtaXR0ZWRfZm9ybS5kYXRhXSk7XG5cdFx0Zm9ybXMudHJpZ2dlcihjb25maWcuc3VibWl0dGVkX2Zvcm0uYWN0aW9uICsgXCJkXCIsIFtmb3JtLCBjb25maWcuc3VibWl0dGVkX2Zvcm0uZGF0YV0pO1xuXHR9XG5cblx0Ly9pZiggY29uZmlnLmF1dG9fc2Nyb2xsICkge1xuXHQvL1x0Zm9ybS5wbGFjZUludG9WaWV3KCBjb25maWcuYXV0b19zY3JvbGwgPT09ICdhbmltYXRlZCcgKTtcblx0Ly99XG59XG5cbi8vIEJpbmQgYnJvd3NlciBldmVudHMgdG8gZm9ybSBldmVudHMgKHVzaW5nIGRlbGVnYXRpb24gdG8gd29yayB3aXRoIEFKQVggbG9hZGVkIGZvcm1zIGFzIHdlbGwpXG5HYXRvcihkb2N1bWVudC5ib2R5KS5vbignc3VibWl0JywgJy5tYzR3cC1mb3JtJywgZnVuY3Rpb24oZXZlbnQpIHtcblx0ZXZlbnQgPSBldmVudCB8fCB3aW5kb3cuZXZlbnQ7XG5cdHZhciBmb3JtID0gZm9ybXMuZ2V0QnlFbGVtZW50KGV2ZW50LnRhcmdldCB8fCBldmVudC5zcmNFbGVtZW50KTtcblx0Zm9ybXMudHJpZ2dlcignc3VibWl0JywgW2Zvcm0sIGV2ZW50XSk7XG59KTtcblxuR2F0b3IoZG9jdW1lbnQuYm9keSkub24oJ2ZvY3VzJywgJy5tYzR3cC1mb3JtJywgZnVuY3Rpb24oZXZlbnQpIHtcblx0ZXZlbnQgPSBldmVudCB8fCB3aW5kb3cuZXZlbnQ7XG5cdHZhciBmb3JtID0gZm9ybXMuZ2V0QnlFbGVtZW50KGV2ZW50LnRhcmdldCB8fCBldmVudC5zcmNFbGVtZW50KTtcblx0aWYoICEgZm9ybS5zdGFydGVkICkge1xuXHRcdGZvcm1zLnRyaWdnZXIoJ3N0YXJ0JywgW2Zvcm0sIGV2ZW50XSk7XG5cdH1cbn0pO1xuXG5HYXRvcihkb2N1bWVudC5ib2R5KS5vbignY2hhbmdlJywgJy5tYzR3cC1mb3JtJywgZnVuY3Rpb24oZXZlbnQpIHtcblx0ZXZlbnQgPSBldmVudCB8fCB3aW5kb3cuZXZlbnQ7XG5cdHZhciBmb3JtID0gZm9ybXMuZ2V0QnlFbGVtZW50KGV2ZW50LnRhcmdldCB8fCBldmVudC5zcmNFbGVtZW50KTtcblx0Zm9ybXMudHJpZ2dlcignY2hhbmdlJywgW2Zvcm0sZXZlbnRdKTtcbn0pO1xuXG5cbi8vIGV4cG9zZSBmb3JtcyBvYmplY3Rcbm1jNHdwLmZvcm1zID0gZm9ybXM7XG53aW5kb3cubWM0d3AgPSBtYzR3cDsiLCIndXNlIHN0cmljdCc7XG5cbnZhciBzZXJpYWxpemUgPSByZXF1aXJlKCcuLi90aGlyZC1wYXJ0eS9zZXJpYWxpemUuanMnKTtcbnZhciBwb3B1bGF0ZSA9IHJlcXVpcmUoJ3BvcHVsYXRlLmpzJyk7XG52YXIgZm9ybVRvSnNvbiA9IHJlcXVpcmUoJy4uL3RoaXJkLXBhcnR5L2Zvcm0yanMuanMnKTtcblxudmFyIEZvcm0gPSBmdW5jdGlvbihpZCwgZWxlbWVudCkge1xuXG5cdHZhciBmb3JtID0gdGhpcztcblxuXHR0aGlzLmlkID0gaWQ7XG5cdHRoaXMuZWxlbWVudCA9IGVsZW1lbnQgfHwgZG9jdW1lbnQuY3JlYXRlRWxlbWVudCgnZm9ybScpO1xuXHR0aGlzLm5hbWUgPSB0aGlzLmVsZW1lbnQuZ2V0QXR0cmlidXRlKCdkYXRhLW5hbWUnKSB8fCBcIkZvcm0gI1wiICsgdGhpcy5pZDtcblx0dGhpcy5lcnJvcnMgPSBbXTtcblx0dGhpcy5zdGFydGVkID0gZmFsc2U7XG5cblx0dGhpcy5zZXREYXRhID0gZnVuY3Rpb24oZGF0YSkge1xuXHRcdHBvcHVsYXRlKGZvcm0uZWxlbWVudCwgZGF0YSk7XG5cdH07XG5cblx0dGhpcy5nZXREYXRhID0gZnVuY3Rpb24oKSB7XG5cdFx0cmV0dXJuIGZvcm1Ub0pzb24oZm9ybS5lbGVtZW50KTtcblx0fTtcblxuXHR0aGlzLmdldFNlcmlhbGl6ZWREYXRhID0gZnVuY3Rpb24oKSB7XG5cdFx0cmV0dXJuIHNlcmlhbGl6ZShmb3JtLmVsZW1lbnQpO1xuXHR9O1xuXG5cdHRoaXMuc2V0UmVzcG9uc2UgPSBmdW5jdGlvbiggbXNnICkge1xuXHRcdGZvcm0uZWxlbWVudC5xdWVyeVNlbGVjdG9yKCcubWM0d3AtcmVzcG9uc2UnKS5pbm5lckhUTUwgPSBtc2c7XG5cdH07XG5cblx0dGhpcy5wbGFjZUludG9WaWV3ID0gZnVuY3Rpb24oIGFuaW1hdGUgKSB7XG5cdFx0dmFyIHNjcm9sbFRvSGVpZ2h0ID0gMDtcblx0XHR2YXIgd2luZG93SGVpZ2h0ID0gd2luZG93LmlubmVySGVpZ2h0O1xuXHRcdHZhciBvYmogPSBmb3JtLmVsZW1lbnQ7XG5cblx0XHRpZiAob2JqLm9mZnNldFBhcmVudCkge1xuXHRcdFx0ZG8ge1xuXHRcdFx0XHRzY3JvbGxUb0hlaWdodCArPSBvYmoub2Zmc2V0VG9wO1xuXHRcdFx0fSB3aGlsZSAob2JqID0gb2JqLm9mZnNldFBhcmVudCk7XG5cdFx0fSBlbHNlIHtcblx0XHRcdHNjcm9sbFRvSGVpZ2h0ID0gZm9ybS5lbGVtZW50Lm9mZnNldFRvcDtcblx0XHR9XG5cblx0XHRpZigod2luZG93SGVpZ2h0IC0gODApID4gZm9ybS5lbGVtZW50LmNsaWVudEhlaWdodCkge1xuXHRcdFx0Ly8gdmVydGljYWxseSBjZW50ZXIgdGhlIGZvcm0sIGJ1dCBvbmx5IGlmIHRoZXJlJ3MgZW5vdWdoIHNwYWNlIGZvciBhIGRlY2VudCBtYXJnaW5cblx0XHRcdHNjcm9sbFRvSGVpZ2h0ID0gc2Nyb2xsVG9IZWlnaHQgLSAoKHdpbmRvd0hlaWdodCAtIGZvcm0uZWxlbWVudC5jbGllbnRIZWlnaHQpIC8gMik7XG5cdFx0fSBlbHNlIHtcblx0XHRcdC8vIHRoZSBmb3JtIGRvZXNuJ3QgZml0LCBzY3JvbGwgYSBsaXR0bGUgYWJvdmUgdGhlIGZvcm1cblx0XHRcdHNjcm9sbFRvSGVpZ2h0ID0gc2Nyb2xsVG9IZWlnaHQgLSA4MDtcblx0XHR9XG5cblx0XHQvLyBzY3JvbGwgdGhlcmUuIGlmIGpRdWVyeSBpcyBsb2FkZWQsIGRvIGl0IHdpdGggYW4gYW5pbWF0aW9uLlxuXHRcdGlmKCBhbmltYXRlICYmIHdpbmRvdy5qUXVlcnkgKSB7XG5cdFx0XHR3aW5kb3cualF1ZXJ5KCdodG1sLCBib2R5JykuYW5pbWF0ZSh7IHNjcm9sbFRvcDogc2Nyb2xsVG9IZWlnaHQgfSwgODAwKTtcblx0XHR9IGVsc2Uge1xuXHRcdFx0d2luZG93LnNjcm9sbFRvKDAsIHNjcm9sbFRvSGVpZ2h0KTtcblx0XHR9XG5cdH07XG59O1xuXG5tb2R1bGUuZXhwb3J0cyA9IEZvcm07IiwiJ3VzZSBzdHJpY3QnO1xuXG4vLyBkZXBzXG52YXIgRXZlbnRFbWl0dGVyID0gcmVxdWlyZSgnd29sZnk4Ny1ldmVudGVtaXR0ZXInKTtcbnZhciBGb3JtID0gcmVxdWlyZSgnLi9mb3JtLmpzJyk7XG5cbi8vIHZhcmlhYmxlc1xudmFyIGV2ZW50cyA9IG5ldyBFdmVudEVtaXR0ZXIoKTtcbnZhciBmb3JtcyA9IFtdO1xuXG4vLyBnZXQgZm9ybSBieSBpdHMgaWRcbmZ1bmN0aW9uIGdldChmb3JtSWQpIHtcblx0Zm9yKHZhciBpPTA7IGk8Zm9ybXMubGVuZ3RoO2krKykge1xuXHRcdGlmKGZvcm1zW2ldLmlkID09IGZvcm1JZCkge1xuXHRcdFx0cmV0dXJuIGZvcm1zW2ldO1xuXHRcdH1cblx0fVxuXG5cdHZhciBmb3JtRWxlbWVudCA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoJy5tYzR3cC1mb3JtLScgKyBmb3JtSWQpO1xuXHRyZXR1cm4gY3JlYXRlRnJvbUVsZW1lbnQoZm9ybUVsZW1lbnQsZm9ybUlkKTtcbn1cblxuLy8gZ2V0IGZvcm0gYnkgPGZvcm0+IGVsZW1lbnQgKG9yIGFueSBpbnB1dCBpbiBmb3JtKVxuZnVuY3Rpb24gZ2V0QnlFbGVtZW50KGVsZW1lbnQpIHtcblx0dmFyIGZvcm1FbGVtZW50ID0gZWxlbWVudC5mb3JtIHx8IGVsZW1lbnQ7XG5cdGZvcih2YXIgaT0wOyBpPGZvcm1zLmxlbmd0aDtpKyspIHtcblx0XHRpZihmb3Jtc1tpXS5lbGVtZW50ID09IGZvcm1FbGVtZW50KSB7XG5cdFx0XHRyZXR1cm4gZm9ybXNbaV07XG5cdFx0fVxuXHR9XG5cblx0cmV0dXJuIGNyZWF0ZUZyb21FbGVtZW50KGVsZW1lbnQpO1xufVxuXG4vLyBjcmVhdGUgZm9ybSBvYmplY3QgZnJvbSA8Zm9ybT4gZWxlbWVudFxuZnVuY3Rpb24gY3JlYXRlRnJvbUVsZW1lbnQoZm9ybUVsZW1lbnQsaWQpIHtcblx0aWQgPSBpZCB8fCBwYXJzZUludCggZm9ybUVsZW1lbnQuZ2V0QXR0cmlidXRlKCdkYXRhLWlkJykgKSB8fCAwO1xuXHR2YXIgZm9ybSA9IG5ldyBGb3JtKGlkLGZvcm1FbGVtZW50KTtcblx0Zm9ybXMucHVzaChmb3JtKTtcblx0cmV0dXJuIGZvcm07XG59XG5cbmZ1bmN0aW9uIGFsbCgpIHtcblx0cmV0dXJuIGZvcm1zO1xufVxuXG5mdW5jdGlvbiBvbihldmVudCxjYWxsYmFjaykge1xuXHRyZXR1cm4gZXZlbnRzLm9uKGV2ZW50LGNhbGxiYWNrKTtcbn1cblxuZnVuY3Rpb24gdHJpZ2dlcihldmVudCxhcmdzKSB7XG5cdHJldHVybiBldmVudHMudHJpZ2dlcihldmVudCxhcmdzKTtcbn1cblxuZnVuY3Rpb24gb2ZmKGV2ZW50LGNhbGxiYWNrKSB7XG5cdHJldHVybiBldmVudHMub2ZmKGV2ZW50LGNhbGxiYWNrKTtcbn1cblxubW9kdWxlLmV4cG9ydHMgPSB7XG5cdFwiYWxsXCI6IGFsbCxcblx0XCJnZXRcIjogZ2V0LFxuXHRcImdldEJ5RWxlbWVudFwiOiBnZXRCeUVsZW1lbnQsXG5cdFwib25cIjogb24sXG5cdFwidHJpZ2dlclwiOiB0cmlnZ2VyLFxuXHRcIm9mZlwiOiBvZmZcbn07XG5cbiIsIi8qKlxuICogQ29weXJpZ2h0IChjKSAyMDEwIE1heGltIFZhc2lsaWV2XG4gKlxuICogUGVybWlzc2lvbiBpcyBoZXJlYnkgZ3JhbnRlZCwgZnJlZSBvZiBjaGFyZ2UsIHRvIGFueSBwZXJzb24gb2J0YWluaW5nIGEgY29weVxuICogb2YgdGhpcyBzb2Z0d2FyZSBhbmQgYXNzb2NpYXRlZCBkb2N1bWVudGF0aW9uIGZpbGVzICh0aGUgXCJTb2Z0d2FyZVwiKSwgdG8gZGVhbFxuICogaW4gdGhlIFNvZnR3YXJlIHdpdGhvdXQgcmVzdHJpY3Rpb24sIGluY2x1ZGluZyB3aXRob3V0IGxpbWl0YXRpb24gdGhlIHJpZ2h0c1xuICogdG8gdXNlLCBjb3B5LCBtb2RpZnksIG1lcmdlLCBwdWJsaXNoLCBkaXN0cmlidXRlLCBzdWJsaWNlbnNlLCBhbmQvb3Igc2VsbFxuICogY29waWVzIG9mIHRoZSBTb2Z0d2FyZSwgYW5kIHRvIHBlcm1pdCBwZXJzb25zIHRvIHdob20gdGhlIFNvZnR3YXJlIGlzXG4gKiBmdXJuaXNoZWQgdG8gZG8gc28sIHN1YmplY3QgdG8gdGhlIGZvbGxvd2luZyBjb25kaXRpb25zOlxuICpcbiAqIFRoZSBhYm92ZSBjb3B5cmlnaHQgbm90aWNlIGFuZCB0aGlzIHBlcm1pc3Npb24gbm90aWNlIHNoYWxsIGJlIGluY2x1ZGVkIGluXG4gKiBhbGwgY29waWVzIG9yIHN1YnN0YW50aWFsIHBvcnRpb25zIG9mIHRoZSBTb2Z0d2FyZS5cbiAqXG4gKiBUSEUgU09GVFdBUkUgSVMgUFJPVklERUQgXCJBUyBJU1wiLCBXSVRIT1VUIFdBUlJBTlRZIE9GIEFOWSBLSU5ELCBFWFBSRVNTIE9SXG4gKiBJTVBMSUVELCBJTkNMVURJTkcgQlVUIE5PVCBMSU1JVEVEIFRPIFRIRSBXQVJSQU5USUVTIE9GIE1FUkNIQU5UQUJJTElUWSxcbiAqIEZJVE5FU1MgRk9SIEEgUEFSVElDVUxBUiBQVVJQT1NFIEFORCBOT05JTkZSSU5HRU1FTlQuIElOIE5PIEVWRU5UIFNIQUxMIFRIRVxuICogQVVUSE9SUyBPUiBDT1BZUklHSFQgSE9MREVSUyBCRSBMSUFCTEUgRk9SIEFOWSBDTEFJTSwgREFNQUdFUyBPUiBPVEhFUlxuICogTElBQklMSVRZLCBXSEVUSEVSIElOIEFOIEFDVElPTiBPRiBDT05UUkFDVCwgVE9SVCBPUiBPVEhFUldJU0UsIEFSSVNJTkcgRlJPTSxcbiAqIE9VVCBPRiBPUiBJTiBDT05ORUNUSU9OIFdJVEggVEhFIFNPRlRXQVJFIE9SIFRIRSBVU0UgT1IgT1RIRVIgREVBTElOR1MgSU5cbiAqIFRIRSBTT0ZUV0FSRS5cbiAqXG4gKiBAYXV0aG9yIE1heGltIFZhc2lsaWV2XG4gKiBEYXRlOiAwOS4wOS4yMDEwXG4gKiBUaW1lOiAxOTowMjozM1xuICovXG5cblxuKGZ1bmN0aW9uIChyb290LCBmYWN0b3J5KVxue1xuXHRpZiAodHlwZW9mIGV4cG9ydHMgIT09ICd1bmRlZmluZWQnICYmIHR5cGVvZiBtb2R1bGUgIT09ICd1bmRlZmluZWQnICYmIG1vZHVsZS5leHBvcnRzKSB7XG5cdFx0Ly8gTm9kZUpTXG5cdFx0bW9kdWxlLmV4cG9ydHMgPSBmYWN0b3J5KCk7XG5cdH1cblx0ZWxzZSBpZiAodHlwZW9mIGRlZmluZSA9PT0gJ2Z1bmN0aW9uJyAmJiBkZWZpbmUuYW1kKVxuXHR7XG5cdFx0Ly8gQU1ELiBSZWdpc3RlciBhcyBhbiBhbm9ueW1vdXMgbW9kdWxlLlxuXHRcdGRlZmluZShmYWN0b3J5KTtcblx0fVxuXHRlbHNlXG5cdHtcblx0XHQvLyBCcm93c2VyIGdsb2JhbHNcblx0XHRyb290LmZvcm0yanMgPSBmYWN0b3J5KCk7XG5cdH1cbn0odGhpcywgZnVuY3Rpb24gKClcbntcblx0XCJ1c2Ugc3RyaWN0XCI7XG5cblx0LyoqXG5cdCAqIFJldHVybnMgZm9ybSB2YWx1ZXMgcmVwcmVzZW50ZWQgYXMgSmF2YXNjcmlwdCBvYmplY3Rcblx0ICogXCJuYW1lXCIgYXR0cmlidXRlIGRlZmluZXMgc3RydWN0dXJlIG9mIHJlc3VsdGluZyBvYmplY3Rcblx0ICpcblx0ICogQHBhcmFtIHJvb3ROb2RlIHtFbGVtZW50fFN0cmluZ30gcm9vdCBmb3JtIGVsZW1lbnQgKG9yIGl0J3MgaWQpIG9yIGFycmF5IG9mIHJvb3QgZWxlbWVudHNcblx0ICogQHBhcmFtIGRlbGltaXRlciB7U3RyaW5nfSBzdHJ1Y3R1cmUgcGFydHMgZGVsaW1pdGVyIGRlZmF1bHRzIHRvICcuJ1xuXHQgKiBAcGFyYW0gc2tpcEVtcHR5IHtCb29sZWFufSBzaG91bGQgc2tpcCBlbXB0eSB0ZXh0IHZhbHVlcywgZGVmYXVsdHMgdG8gdHJ1ZVxuXHQgKiBAcGFyYW0gbm9kZUNhbGxiYWNrIHtGdW5jdGlvbn0gY3VzdG9tIGZ1bmN0aW9uIHRvIGdldCBub2RlIHZhbHVlXG5cdCAqIEBwYXJhbSB1c2VJZElmRW1wdHlOYW1lIHtCb29sZWFufSBpZiB0cnVlIHZhbHVlIG9mIGlkIGF0dHJpYnV0ZSBvZiBmaWVsZCB3aWxsIGJlIHVzZWQgaWYgbmFtZSBvZiBmaWVsZCBpcyBlbXB0eVxuXHQgKi9cblx0ZnVuY3Rpb24gZm9ybTJqcyhyb290Tm9kZSwgZGVsaW1pdGVyLCBza2lwRW1wdHksIG5vZGVDYWxsYmFjaywgdXNlSWRJZkVtcHR5TmFtZSwgZ2V0RGlzYWJsZWQpXG5cdHtcblx0XHRnZXREaXNhYmxlZCA9IGdldERpc2FibGVkID8gdHJ1ZSA6IGZhbHNlO1xuXHRcdGlmICh0eXBlb2Ygc2tpcEVtcHR5ID09ICd1bmRlZmluZWQnIHx8IHNraXBFbXB0eSA9PSBudWxsKSBza2lwRW1wdHkgPSB0cnVlO1xuXHRcdGlmICh0eXBlb2YgZGVsaW1pdGVyID09ICd1bmRlZmluZWQnIHx8IGRlbGltaXRlciA9PSBudWxsKSBkZWxpbWl0ZXIgPSAnLic7XG5cdFx0aWYgKGFyZ3VtZW50cy5sZW5ndGggPCA1KSB1c2VJZElmRW1wdHlOYW1lID0gZmFsc2U7XG5cblx0XHRyb290Tm9kZSA9IHR5cGVvZiByb290Tm9kZSA9PSAnc3RyaW5nJyA/IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKHJvb3ROb2RlKSA6IHJvb3ROb2RlO1xuXG5cdFx0dmFyIGZvcm1WYWx1ZXMgPSBbXSxcblx0XHRcdGN1cnJOb2RlLFxuXHRcdFx0aSA9IDA7XG5cblx0XHQvKiBJZiByb290Tm9kZSBpcyBhcnJheSAtIGNvbWJpbmUgdmFsdWVzICovXG5cdFx0aWYgKHJvb3ROb2RlLmNvbnN0cnVjdG9yID09IEFycmF5IHx8ICh0eXBlb2YgTm9kZUxpc3QgIT0gXCJ1bmRlZmluZWRcIiAmJiByb290Tm9kZS5jb25zdHJ1Y3RvciA9PSBOb2RlTGlzdCkpXG5cdFx0e1xuXHRcdFx0d2hpbGUoY3Vyck5vZGUgPSByb290Tm9kZVtpKytdKVxuXHRcdFx0e1xuXHRcdFx0XHRmb3JtVmFsdWVzID0gZm9ybVZhbHVlcy5jb25jYXQoZ2V0Rm9ybVZhbHVlcyhjdXJyTm9kZSwgbm9kZUNhbGxiYWNrLCB1c2VJZElmRW1wdHlOYW1lLCBnZXREaXNhYmxlZCkpO1xuXHRcdFx0fVxuXHRcdH1cblx0XHRlbHNlXG5cdFx0e1xuXHRcdFx0Zm9ybVZhbHVlcyA9IGdldEZvcm1WYWx1ZXMocm9vdE5vZGUsIG5vZGVDYWxsYmFjaywgdXNlSWRJZkVtcHR5TmFtZSwgZ2V0RGlzYWJsZWQpO1xuXHRcdH1cblxuXHRcdHJldHVybiBwcm9jZXNzTmFtZVZhbHVlcyhmb3JtVmFsdWVzLCBza2lwRW1wdHksIGRlbGltaXRlcik7XG5cdH1cblxuXHQvKipcblx0ICogUHJvY2Vzc2VzIGNvbGxlY3Rpb24gb2YgeyBuYW1lOiAnbmFtZScsIHZhbHVlOiAndmFsdWUnIH0gb2JqZWN0cy5cblx0ICogQHBhcmFtIG5hbWVWYWx1ZXNcblx0ICogQHBhcmFtIHNraXBFbXB0eSBpZiB0cnVlIHNraXBzIGVsZW1lbnRzIHdpdGggdmFsdWUgPT0gJycgb3IgdmFsdWUgPT0gbnVsbFxuXHQgKiBAcGFyYW0gZGVsaW1pdGVyXG5cdCAqL1xuXHRmdW5jdGlvbiBwcm9jZXNzTmFtZVZhbHVlcyhuYW1lVmFsdWVzLCBza2lwRW1wdHksIGRlbGltaXRlcilcblx0e1xuXHRcdHZhciByZXN1bHQgPSB7fSxcblx0XHRcdGFycmF5cyA9IHt9LFxuXHRcdFx0aSwgaiwgaywgbCxcblx0XHRcdHZhbHVlLFxuXHRcdFx0bmFtZVBhcnRzLFxuXHRcdFx0Y3VyclJlc3VsdCxcblx0XHRcdGFyck5hbWVGdWxsLFxuXHRcdFx0YXJyTmFtZSxcblx0XHRcdGFycklkeCxcblx0XHRcdG5hbWVQYXJ0LFxuXHRcdFx0bmFtZSxcblx0XHRcdF9uYW1lUGFydHM7XG5cblx0XHRmb3IgKGkgPSAwOyBpIDwgbmFtZVZhbHVlcy5sZW5ndGg7IGkrKylcblx0XHR7XG5cdFx0XHR2YWx1ZSA9IG5hbWVWYWx1ZXNbaV0udmFsdWU7XG5cblx0XHRcdGlmIChza2lwRW1wdHkgJiYgKHZhbHVlID09PSAnJyB8fCB2YWx1ZSA9PT0gbnVsbCkpIGNvbnRpbnVlO1xuXG5cdFx0XHRuYW1lID0gbmFtZVZhbHVlc1tpXS5uYW1lO1xuXHRcdFx0X25hbWVQYXJ0cyA9IG5hbWUuc3BsaXQoZGVsaW1pdGVyKTtcblx0XHRcdG5hbWVQYXJ0cyA9IFtdO1xuXHRcdFx0Y3VyclJlc3VsdCA9IHJlc3VsdDtcblx0XHRcdGFyck5hbWVGdWxsID0gJyc7XG5cblx0XHRcdGZvcihqID0gMDsgaiA8IF9uYW1lUGFydHMubGVuZ3RoOyBqKyspXG5cdFx0XHR7XG5cdFx0XHRcdG5hbWVQYXJ0ID0gX25hbWVQYXJ0c1tqXS5zcGxpdCgnXVsnKTtcblx0XHRcdFx0aWYgKG5hbWVQYXJ0Lmxlbmd0aCA+IDEpXG5cdFx0XHRcdHtcblx0XHRcdFx0XHRmb3IoayA9IDA7IGsgPCBuYW1lUGFydC5sZW5ndGg7IGsrKylcblx0XHRcdFx0XHR7XG5cdFx0XHRcdFx0XHRpZiAoayA9PSAwKVxuXHRcdFx0XHRcdFx0e1xuXHRcdFx0XHRcdFx0XHRuYW1lUGFydFtrXSA9IG5hbWVQYXJ0W2tdICsgJ10nO1xuXHRcdFx0XHRcdFx0fVxuXHRcdFx0XHRcdFx0ZWxzZSBpZiAoayA9PSBuYW1lUGFydC5sZW5ndGggLSAxKVxuXHRcdFx0XHRcdFx0e1xuXHRcdFx0XHRcdFx0XHRuYW1lUGFydFtrXSA9ICdbJyArIG5hbWVQYXJ0W2tdO1xuXHRcdFx0XHRcdFx0fVxuXHRcdFx0XHRcdFx0ZWxzZVxuXHRcdFx0XHRcdFx0e1xuXHRcdFx0XHRcdFx0XHRuYW1lUGFydFtrXSA9ICdbJyArIG5hbWVQYXJ0W2tdICsgJ10nO1xuXHRcdFx0XHRcdFx0fVxuXG5cdFx0XHRcdFx0XHRhcnJJZHggPSBuYW1lUGFydFtrXS5tYXRjaCgvKFthLXpfXSspP1xcWyhbYS16X11bYS16MC05X10rPylcXF0vaSk7XG5cdFx0XHRcdFx0XHRpZiAoYXJySWR4KVxuXHRcdFx0XHRcdFx0e1xuXHRcdFx0XHRcdFx0XHRmb3IobCA9IDE7IGwgPCBhcnJJZHgubGVuZ3RoOyBsKyspXG5cdFx0XHRcdFx0XHRcdHtcblx0XHRcdFx0XHRcdFx0XHRpZiAoYXJySWR4W2xdKSBuYW1lUGFydHMucHVzaChhcnJJZHhbbF0pO1xuXHRcdFx0XHRcdFx0XHR9XG5cdFx0XHRcdFx0XHR9XG5cdFx0XHRcdFx0XHRlbHNle1xuXHRcdFx0XHRcdFx0XHRuYW1lUGFydHMucHVzaChuYW1lUGFydFtrXSk7XG5cdFx0XHRcdFx0XHR9XG5cdFx0XHRcdFx0fVxuXHRcdFx0XHR9XG5cdFx0XHRcdGVsc2Vcblx0XHRcdFx0XHRuYW1lUGFydHMgPSBuYW1lUGFydHMuY29uY2F0KG5hbWVQYXJ0KTtcblx0XHRcdH1cblxuXHRcdFx0Zm9yIChqID0gMDsgaiA8IG5hbWVQYXJ0cy5sZW5ndGg7IGorKylcblx0XHRcdHtcblx0XHRcdFx0bmFtZVBhcnQgPSBuYW1lUGFydHNbal07XG5cblx0XHRcdFx0aWYgKG5hbWVQYXJ0LmluZGV4T2YoJ1tdJykgPiAtMSAmJiBqID09IG5hbWVQYXJ0cy5sZW5ndGggLSAxKVxuXHRcdFx0XHR7XG5cdFx0XHRcdFx0YXJyTmFtZSA9IG5hbWVQYXJ0LnN1YnN0cigwLCBuYW1lUGFydC5pbmRleE9mKCdbJykpO1xuXHRcdFx0XHRcdGFyck5hbWVGdWxsICs9IGFyck5hbWU7XG5cblx0XHRcdFx0XHRpZiAoIWN1cnJSZXN1bHRbYXJyTmFtZV0pIGN1cnJSZXN1bHRbYXJyTmFtZV0gPSBbXTtcblx0XHRcdFx0XHRjdXJyUmVzdWx0W2Fyck5hbWVdLnB1c2godmFsdWUpO1xuXHRcdFx0XHR9XG5cdFx0XHRcdGVsc2UgaWYgKG5hbWVQYXJ0LmluZGV4T2YoJ1snKSA+IC0xKVxuXHRcdFx0XHR7XG5cdFx0XHRcdFx0YXJyTmFtZSA9IG5hbWVQYXJ0LnN1YnN0cigwLCBuYW1lUGFydC5pbmRleE9mKCdbJykpO1xuXHRcdFx0XHRcdGFycklkeCA9IG5hbWVQYXJ0LnJlcGxhY2UoLyheKFthLXpfXSspP1xcWyl8KFxcXSQpL2dpLCAnJyk7XG5cblx0XHRcdFx0XHQvKiBVbmlxdWUgYXJyYXkgbmFtZSAqL1xuXHRcdFx0XHRcdGFyck5hbWVGdWxsICs9ICdfJyArIGFyck5hbWUgKyAnXycgKyBhcnJJZHg7XG5cblx0XHRcdFx0XHQvKlxuXHRcdFx0XHRcdCAqIEJlY2F1c2UgYXJySWR4IGluIGZpZWxkIG5hbWUgY2FuIGJlIG5vdCB6ZXJvLWJhc2VkIGFuZCBzdGVwIGNhbiBiZVxuXHRcdFx0XHRcdCAqIG90aGVyIHRoYW4gMSwgd2UgY2FuJ3QgdXNlIHRoZW0gaW4gdGFyZ2V0IGFycmF5IGRpcmVjdGx5LlxuXHRcdFx0XHRcdCAqIEluc3RlYWQgd2UncmUgbWFraW5nIGEgaGFzaCB3aGVyZSBrZXkgaXMgYXJySWR4IGFuZCB2YWx1ZSBpcyBhIHJlZmVyZW5jZSB0b1xuXHRcdFx0XHRcdCAqIGFkZGVkIGFycmF5IGVsZW1lbnRcblx0XHRcdFx0XHQgKi9cblxuXHRcdFx0XHRcdGlmICghYXJyYXlzW2Fyck5hbWVGdWxsXSkgYXJyYXlzW2Fyck5hbWVGdWxsXSA9IHt9O1xuXHRcdFx0XHRcdGlmIChhcnJOYW1lICE9ICcnICYmICFjdXJyUmVzdWx0W2Fyck5hbWVdKSBjdXJyUmVzdWx0W2Fyck5hbWVdID0gW107XG5cblx0XHRcdFx0XHRpZiAoaiA9PSBuYW1lUGFydHMubGVuZ3RoIC0gMSlcblx0XHRcdFx0XHR7XG5cdFx0XHRcdFx0XHRpZiAoYXJyTmFtZSA9PSAnJylcblx0XHRcdFx0XHRcdHtcblx0XHRcdFx0XHRcdFx0Y3VyclJlc3VsdC5wdXNoKHZhbHVlKTtcblx0XHRcdFx0XHRcdFx0YXJyYXlzW2Fyck5hbWVGdWxsXVthcnJJZHhdID0gY3VyclJlc3VsdFtjdXJyUmVzdWx0Lmxlbmd0aCAtIDFdO1xuXHRcdFx0XHRcdFx0fVxuXHRcdFx0XHRcdFx0ZWxzZVxuXHRcdFx0XHRcdFx0e1xuXHRcdFx0XHRcdFx0XHRjdXJyUmVzdWx0W2Fyck5hbWVdLnB1c2godmFsdWUpO1xuXHRcdFx0XHRcdFx0XHRhcnJheXNbYXJyTmFtZUZ1bGxdW2FycklkeF0gPSBjdXJyUmVzdWx0W2Fyck5hbWVdW2N1cnJSZXN1bHRbYXJyTmFtZV0ubGVuZ3RoIC0gMV07XG5cdFx0XHRcdFx0XHR9XG5cdFx0XHRcdFx0fVxuXHRcdFx0XHRcdGVsc2Vcblx0XHRcdFx0XHR7XG5cdFx0XHRcdFx0XHRpZiAoIWFycmF5c1thcnJOYW1lRnVsbF1bYXJySWR4XSlcblx0XHRcdFx0XHRcdHtcblx0XHRcdFx0XHRcdFx0aWYgKCgvXlswLTlhLXpfXStcXFs/L2kpLnRlc3QobmFtZVBhcnRzW2orMV0pKSBjdXJyUmVzdWx0W2Fyck5hbWVdLnB1c2goe30pO1xuXHRcdFx0XHRcdFx0XHRlbHNlIGN1cnJSZXN1bHRbYXJyTmFtZV0ucHVzaChbXSk7XG5cblx0XHRcdFx0XHRcdFx0YXJyYXlzW2Fyck5hbWVGdWxsXVthcnJJZHhdID0gY3VyclJlc3VsdFthcnJOYW1lXVtjdXJyUmVzdWx0W2Fyck5hbWVdLmxlbmd0aCAtIDFdO1xuXHRcdFx0XHRcdFx0fVxuXHRcdFx0XHRcdH1cblxuXHRcdFx0XHRcdGN1cnJSZXN1bHQgPSBhcnJheXNbYXJyTmFtZUZ1bGxdW2FycklkeF07XG5cdFx0XHRcdH1cblx0XHRcdFx0ZWxzZVxuXHRcdFx0XHR7XG5cdFx0XHRcdFx0YXJyTmFtZUZ1bGwgKz0gbmFtZVBhcnQ7XG5cblx0XHRcdFx0XHRpZiAoaiA8IG5hbWVQYXJ0cy5sZW5ndGggLSAxKSAvKiBOb3QgdGhlIGxhc3QgcGFydCBvZiBuYW1lIC0gbWVhbnMgb2JqZWN0ICovXG5cdFx0XHRcdFx0e1xuXHRcdFx0XHRcdFx0aWYgKCFjdXJyUmVzdWx0W25hbWVQYXJ0XSkgY3VyclJlc3VsdFtuYW1lUGFydF0gPSB7fTtcblx0XHRcdFx0XHRcdGN1cnJSZXN1bHQgPSBjdXJyUmVzdWx0W25hbWVQYXJ0XTtcblx0XHRcdFx0XHR9XG5cdFx0XHRcdFx0ZWxzZVxuXHRcdFx0XHRcdHtcblx0XHRcdFx0XHRcdGN1cnJSZXN1bHRbbmFtZVBhcnRdID0gdmFsdWU7XG5cdFx0XHRcdFx0fVxuXHRcdFx0XHR9XG5cdFx0XHR9XG5cdFx0fVxuXG5cdFx0cmV0dXJuIHJlc3VsdDtcblx0fVxuXG5cdGZ1bmN0aW9uIGdldEZvcm1WYWx1ZXMocm9vdE5vZGUsIG5vZGVDYWxsYmFjaywgdXNlSWRJZkVtcHR5TmFtZSwgZ2V0RGlzYWJsZWQpXG5cdHtcblx0XHR2YXIgcmVzdWx0ID0gZXh0cmFjdE5vZGVWYWx1ZXMocm9vdE5vZGUsIG5vZGVDYWxsYmFjaywgdXNlSWRJZkVtcHR5TmFtZSwgZ2V0RGlzYWJsZWQpO1xuXHRcdHJldHVybiByZXN1bHQubGVuZ3RoID4gMCA/IHJlc3VsdCA6IGdldFN1YkZvcm1WYWx1ZXMocm9vdE5vZGUsIG5vZGVDYWxsYmFjaywgdXNlSWRJZkVtcHR5TmFtZSwgZ2V0RGlzYWJsZWQpO1xuXHR9XG5cblx0ZnVuY3Rpb24gZ2V0U3ViRm9ybVZhbHVlcyhyb290Tm9kZSwgbm9kZUNhbGxiYWNrLCB1c2VJZElmRW1wdHlOYW1lLCBnZXREaXNhYmxlZClcblx0e1xuXHRcdHZhciByZXN1bHQgPSBbXSxcblx0XHRcdGN1cnJlbnROb2RlID0gcm9vdE5vZGUuZmlyc3RDaGlsZDtcblxuXHRcdHdoaWxlIChjdXJyZW50Tm9kZSlcblx0XHR7XG5cdFx0XHRyZXN1bHQgPSByZXN1bHQuY29uY2F0KGV4dHJhY3ROb2RlVmFsdWVzKGN1cnJlbnROb2RlLCBub2RlQ2FsbGJhY2ssIHVzZUlkSWZFbXB0eU5hbWUsIGdldERpc2FibGVkKSk7XG5cdFx0XHRjdXJyZW50Tm9kZSA9IGN1cnJlbnROb2RlLm5leHRTaWJsaW5nO1xuXHRcdH1cblxuXHRcdHJldHVybiByZXN1bHQ7XG5cdH1cblxuXHRmdW5jdGlvbiBleHRyYWN0Tm9kZVZhbHVlcyhub2RlLCBub2RlQ2FsbGJhY2ssIHVzZUlkSWZFbXB0eU5hbWUsIGdldERpc2FibGVkKSB7XG5cdFx0aWYgKG5vZGUuZGlzYWJsZWQgJiYgIWdldERpc2FibGVkKSByZXR1cm4gW107XG5cblx0XHR2YXIgY2FsbGJhY2tSZXN1bHQsIGZpZWxkVmFsdWUsIHJlc3VsdCwgZmllbGROYW1lID0gZ2V0RmllbGROYW1lKG5vZGUsIHVzZUlkSWZFbXB0eU5hbWUpO1xuXG5cdFx0Y2FsbGJhY2tSZXN1bHQgPSBub2RlQ2FsbGJhY2sgJiYgbm9kZUNhbGxiYWNrKG5vZGUpO1xuXG5cdFx0aWYgKGNhbGxiYWNrUmVzdWx0ICYmIGNhbGxiYWNrUmVzdWx0Lm5hbWUpIHtcblx0XHRcdHJlc3VsdCA9IFtjYWxsYmFja1Jlc3VsdF07XG5cdFx0fVxuXHRcdGVsc2UgaWYgKGZpZWxkTmFtZSAhPSAnJyAmJiBub2RlLm5vZGVOYW1lLm1hdGNoKC9JTlBVVHxURVhUQVJFQS9pKSkge1xuXHRcdFx0ZmllbGRWYWx1ZSA9IGdldEZpZWxkVmFsdWUobm9kZSwgZ2V0RGlzYWJsZWQpO1xuXHRcdFx0aWYgKG51bGwgPT09IGZpZWxkVmFsdWUpIHtcblx0XHRcdFx0cmVzdWx0ID0gW107XG5cdFx0XHR9IGVsc2Uge1xuXHRcdFx0XHRyZXN1bHQgPSBbIHsgbmFtZTogZmllbGROYW1lLCB2YWx1ZTogZmllbGRWYWx1ZX0gXTtcblx0XHRcdH1cblx0XHR9XG5cdFx0ZWxzZSBpZiAoZmllbGROYW1lICE9ICcnICYmIG5vZGUubm9kZU5hbWUubWF0Y2goL1NFTEVDVC9pKSkge1xuXHRcdFx0ZmllbGRWYWx1ZSA9IGdldEZpZWxkVmFsdWUobm9kZSwgZ2V0RGlzYWJsZWQpO1xuXHRcdFx0cmVzdWx0ID0gWyB7IG5hbWU6IGZpZWxkTmFtZS5yZXBsYWNlKC9cXFtcXF0kLywgJycpLCB2YWx1ZTogZmllbGRWYWx1ZSB9IF07XG5cdFx0fVxuXHRcdGVsc2Uge1xuXHRcdFx0cmVzdWx0ID0gZ2V0U3ViRm9ybVZhbHVlcyhub2RlLCBub2RlQ2FsbGJhY2ssIHVzZUlkSWZFbXB0eU5hbWUsIGdldERpc2FibGVkKTtcblx0XHR9XG5cblx0XHRyZXR1cm4gcmVzdWx0O1xuXHR9XG5cblx0ZnVuY3Rpb24gZ2V0RmllbGROYW1lKG5vZGUsIHVzZUlkSWZFbXB0eU5hbWUpXG5cdHtcblx0XHRpZiAobm9kZS5uYW1lICYmIG5vZGUubmFtZSAhPSAnJykgcmV0dXJuIG5vZGUubmFtZTtcblx0XHRlbHNlIGlmICh1c2VJZElmRW1wdHlOYW1lICYmIG5vZGUuaWQgJiYgbm9kZS5pZCAhPSAnJykgcmV0dXJuIG5vZGUuaWQ7XG5cdFx0ZWxzZSByZXR1cm4gJyc7XG5cdH1cblxuXG5cdGZ1bmN0aW9uIGdldEZpZWxkVmFsdWUoZmllbGROb2RlLCBnZXREaXNhYmxlZClcblx0e1xuXHRcdGlmIChmaWVsZE5vZGUuZGlzYWJsZWQgJiYgIWdldERpc2FibGVkKSByZXR1cm4gbnVsbDtcblxuXHRcdHN3aXRjaCAoZmllbGROb2RlLm5vZGVOYW1lKSB7XG5cdFx0XHRjYXNlICdJTlBVVCc6XG5cdFx0XHRjYXNlICdURVhUQVJFQSc6XG5cdFx0XHRcdHN3aXRjaCAoZmllbGROb2RlLnR5cGUudG9Mb3dlckNhc2UoKSkge1xuXHRcdFx0XHRcdGNhc2UgJ3JhZGlvJzpcblx0XHRcdFx0XHRcdGlmIChmaWVsZE5vZGUuY2hlY2tlZCAmJiBmaWVsZE5vZGUudmFsdWUgPT09IFwiZmFsc2VcIikgcmV0dXJuIGZhbHNlO1xuXHRcdFx0XHRcdGNhc2UgJ2NoZWNrYm94Jzpcblx0XHRcdFx0XHRcdGlmIChmaWVsZE5vZGUuY2hlY2tlZCAmJiBmaWVsZE5vZGUudmFsdWUgPT09IFwidHJ1ZVwiKSByZXR1cm4gdHJ1ZTtcblx0XHRcdFx0XHRcdGlmICghZmllbGROb2RlLmNoZWNrZWQgJiYgZmllbGROb2RlLnZhbHVlID09PSBcInRydWVcIikgcmV0dXJuIGZhbHNlO1xuXHRcdFx0XHRcdFx0aWYgKGZpZWxkTm9kZS5jaGVja2VkKSByZXR1cm4gZmllbGROb2RlLnZhbHVlO1xuXHRcdFx0XHRcdFx0YnJlYWs7XG5cblx0XHRcdFx0XHRjYXNlICdidXR0b24nOlxuXHRcdFx0XHRcdGNhc2UgJ3Jlc2V0Jzpcblx0XHRcdFx0XHRjYXNlICdzdWJtaXQnOlxuXHRcdFx0XHRcdGNhc2UgJ2ltYWdlJzpcblx0XHRcdFx0XHRcdHJldHVybiAnJztcblx0XHRcdFx0XHRcdGJyZWFrO1xuXG5cdFx0XHRcdFx0ZGVmYXVsdDpcblx0XHRcdFx0XHRcdHJldHVybiBmaWVsZE5vZGUudmFsdWU7XG5cdFx0XHRcdFx0XHRicmVhaztcblx0XHRcdFx0fVxuXHRcdFx0XHRicmVhaztcblxuXHRcdFx0Y2FzZSAnU0VMRUNUJzpcblx0XHRcdFx0cmV0dXJuIGdldFNlbGVjdGVkT3B0aW9uVmFsdWUoZmllbGROb2RlKTtcblx0XHRcdFx0YnJlYWs7XG5cblx0XHRcdGRlZmF1bHQ6XG5cdFx0XHRcdGJyZWFrO1xuXHRcdH1cblxuXHRcdHJldHVybiBudWxsO1xuXHR9XG5cblx0ZnVuY3Rpb24gZ2V0U2VsZWN0ZWRPcHRpb25WYWx1ZShzZWxlY3ROb2RlKVxuXHR7XG5cdFx0dmFyIG11bHRpcGxlID0gc2VsZWN0Tm9kZS5tdWx0aXBsZSxcblx0XHRcdHJlc3VsdCA9IFtdLFxuXHRcdFx0b3B0aW9ucyxcblx0XHRcdGksIGw7XG5cblx0XHRpZiAoIW11bHRpcGxlKSByZXR1cm4gc2VsZWN0Tm9kZS52YWx1ZTtcblxuXHRcdGZvciAob3B0aW9ucyA9IHNlbGVjdE5vZGUuZ2V0RWxlbWVudHNCeVRhZ05hbWUoXCJvcHRpb25cIiksIGkgPSAwLCBsID0gb3B0aW9ucy5sZW5ndGg7IGkgPCBsOyBpKyspXG5cdFx0e1xuXHRcdFx0aWYgKG9wdGlvbnNbaV0uc2VsZWN0ZWQpIHJlc3VsdC5wdXNoKG9wdGlvbnNbaV0udmFsdWUpO1xuXHRcdH1cblxuXHRcdHJldHVybiByZXN1bHQ7XG5cdH1cblxuXHRyZXR1cm4gZm9ybTJqcztcblxufSkpOyIsIi8vIGdldCBzdWNjZXNzZnVsIGNvbnRyb2wgZnJvbSBmb3JtIGFuZCBhc3NlbWJsZSBpbnRvIG9iamVjdFxuLy8gaHR0cDovL3d3dy53My5vcmcvVFIvaHRtbDQwMS9pbnRlcmFjdC9mb3Jtcy5odG1sI2gtMTcuMTMuMlxuXG4vLyB0eXBlcyB3aGljaCBpbmRpY2F0ZSBhIHN1Ym1pdCBhY3Rpb24gYW5kIGFyZSBub3Qgc3VjY2Vzc2Z1bCBjb250cm9sc1xuLy8gdGhlc2Ugd2lsbCBiZSBpZ25vcmVkXG52YXIga19yX3N1Ym1pdHRlciA9IC9eKD86c3VibWl0fGJ1dHRvbnxpbWFnZXxyZXNldHxmaWxlKSQvaTtcblxuLy8gbm9kZSBuYW1lcyB3aGljaCBjb3VsZCBiZSBzdWNjZXNzZnVsIGNvbnRyb2xzXG52YXIga19yX3N1Y2Nlc3NfY29udHJscyA9IC9eKD86aW5wdXR8c2VsZWN0fHRleHRhcmVhfGtleWdlbikvaTtcblxuLy8gTWF0Y2hlcyBicmFja2V0IG5vdGF0aW9uLlxudmFyIGJyYWNrZXRzID0gLyhcXFtbXlxcW1xcXV0qXFxdKS9nO1xuXG4vLyBzZXJpYWxpemVzIGZvcm0gZmllbGRzXG4vLyBAcGFyYW0gZm9ybSBNVVNUIGJlIGFuIEhUTUxGb3JtIGVsZW1lbnRcbi8vIEBwYXJhbSBvcHRpb25zIGlzIGFuIG9wdGlvbmFsIGFyZ3VtZW50IHRvIGNvbmZpZ3VyZSB0aGUgc2VyaWFsaXphdGlvbi4gRGVmYXVsdCBvdXRwdXRcbi8vIHdpdGggbm8gb3B0aW9ucyBzcGVjaWZpZWQgaXMgYSB1cmwgZW5jb2RlZCBzdHJpbmdcbi8vICAgIC0gaGFzaDogW3RydWUgfCBmYWxzZV0gQ29uZmlndXJlIHRoZSBvdXRwdXQgdHlwZS4gSWYgdHJ1ZSwgdGhlIG91dHB1dCB3aWxsXG4vLyAgICBiZSBhIGpzIG9iamVjdC5cbi8vICAgIC0gc2VyaWFsaXplcjogW2Z1bmN0aW9uXSBPcHRpb25hbCBzZXJpYWxpemVyIGZ1bmN0aW9uIHRvIG92ZXJyaWRlIHRoZSBkZWZhdWx0IG9uZS5cbi8vICAgIFRoZSBmdW5jdGlvbiB0YWtlcyAzIGFyZ3VtZW50cyAocmVzdWx0LCBrZXksIHZhbHVlKSBhbmQgc2hvdWxkIHJldHVybiBuZXcgcmVzdWx0XG4vLyAgICBoYXNoIGFuZCB1cmwgZW5jb2RlZCBzdHIgc2VyaWFsaXplcnMgYXJlIHByb3ZpZGVkIHdpdGggdGhpcyBtb2R1bGVcbi8vICAgIC0gZGlzYWJsZWQ6IFt0cnVlIHwgZmFsc2VdLiBJZiB0cnVlIHNlcmlhbGl6ZSBkaXNhYmxlZCBmaWVsZHMuXG4vLyAgICAtIGVtcHR5OiBbdHJ1ZSB8IGZhbHNlXS4gSWYgdHJ1ZSBzZXJpYWxpemUgZW1wdHkgZmllbGRzXG5mdW5jdGlvbiBzZXJpYWxpemUoZm9ybSwgb3B0aW9ucykge1xuXHRpZiAodHlwZW9mIG9wdGlvbnMgIT0gJ29iamVjdCcpIHtcblx0XHRvcHRpb25zID0geyBoYXNoOiAhIW9wdGlvbnMgfTtcblx0fVxuXHRlbHNlIGlmIChvcHRpb25zLmhhc2ggPT09IHVuZGVmaW5lZCkge1xuXHRcdG9wdGlvbnMuaGFzaCA9IHRydWU7XG5cdH1cblxuXHR2YXIgcmVzdWx0ID0gKG9wdGlvbnMuaGFzaCkgPyB7fSA6ICcnO1xuXHR2YXIgc2VyaWFsaXplciA9IG9wdGlvbnMuc2VyaWFsaXplciB8fCAoKG9wdGlvbnMuaGFzaCkgPyBoYXNoX3NlcmlhbGl6ZXIgOiBzdHJfc2VyaWFsaXplKTtcblxuXHR2YXIgZWxlbWVudHMgPSBmb3JtICYmIGZvcm0uZWxlbWVudHMgPyBmb3JtLmVsZW1lbnRzIDogW107XG5cblx0Ly9PYmplY3Qgc3RvcmUgZWFjaCByYWRpbyBhbmQgc2V0IGlmIGl0J3MgZW1wdHkgb3Igbm90XG5cdHZhciByYWRpb19zdG9yZSA9IE9iamVjdC5jcmVhdGUobnVsbCk7XG5cblx0Zm9yICh2YXIgaT0wIDsgaTxlbGVtZW50cy5sZW5ndGggOyArK2kpIHtcblx0XHR2YXIgZWxlbWVudCA9IGVsZW1lbnRzW2ldO1xuXG5cdFx0Ly8gaW5nb3JlIGRpc2FibGVkIGZpZWxkc1xuXHRcdGlmICgoIW9wdGlvbnMuZGlzYWJsZWQgJiYgZWxlbWVudC5kaXNhYmxlZCkgfHwgIWVsZW1lbnQubmFtZSkge1xuXHRcdFx0Y29udGludWU7XG5cdFx0fVxuXHRcdC8vIGlnbm9yZSBhbnlodGluZyB0aGF0IGlzIG5vdCBjb25zaWRlcmVkIGEgc3VjY2VzcyBmaWVsZFxuXHRcdGlmICgha19yX3N1Y2Nlc3NfY29udHJscy50ZXN0KGVsZW1lbnQubm9kZU5hbWUpIHx8XG5cdFx0XHRrX3Jfc3VibWl0dGVyLnRlc3QoZWxlbWVudC50eXBlKSkge1xuXHRcdFx0Y29udGludWU7XG5cdFx0fVxuXG5cdFx0dmFyIGtleSA9IGVsZW1lbnQubmFtZTtcblx0XHR2YXIgdmFsID0gZWxlbWVudC52YWx1ZTtcblxuXHRcdC8vIHdlIGNhbid0IGp1c3QgdXNlIGVsZW1lbnQudmFsdWUgZm9yIGNoZWNrYm94ZXMgY2F1c2Ugc29tZSBicm93c2VycyBsaWUgdG8gdXNcblx0XHQvLyB0aGV5IHNheSBcIm9uXCIgZm9yIHZhbHVlIHdoZW4gdGhlIGJveCBpc24ndCBjaGVja2VkXG5cdFx0aWYgKChlbGVtZW50LnR5cGUgPT09ICdjaGVja2JveCcgfHwgZWxlbWVudC50eXBlID09PSAncmFkaW8nKSAmJiAhZWxlbWVudC5jaGVja2VkKSB7XG5cdFx0XHR2YWwgPSB1bmRlZmluZWQ7XG5cdFx0fVxuXG5cdFx0Ly8gSWYgd2Ugd2FudCBlbXB0eSBlbGVtZW50c1xuXHRcdGlmIChvcHRpb25zLmVtcHR5KSB7XG5cdFx0XHQvLyBmb3IgY2hlY2tib3hcblx0XHRcdGlmIChlbGVtZW50LnR5cGUgPT09ICdjaGVja2JveCcgJiYgIWVsZW1lbnQuY2hlY2tlZCkge1xuXHRcdFx0XHR2YWwgPSAnJztcblx0XHRcdH1cblxuXHRcdFx0Ly8gZm9yIHJhZGlvXG5cdFx0XHRpZiAoZWxlbWVudC50eXBlID09PSAncmFkaW8nKSB7XG5cdFx0XHRcdGlmICghcmFkaW9fc3RvcmVbZWxlbWVudC5uYW1lXSAmJiAhZWxlbWVudC5jaGVja2VkKSB7XG5cdFx0XHRcdFx0cmFkaW9fc3RvcmVbZWxlbWVudC5uYW1lXSA9IGZhbHNlO1xuXHRcdFx0XHR9XG5cdFx0XHRcdGVsc2UgaWYgKGVsZW1lbnQuY2hlY2tlZCkge1xuXHRcdFx0XHRcdHJhZGlvX3N0b3JlW2VsZW1lbnQubmFtZV0gPSB0cnVlO1xuXHRcdFx0XHR9XG5cdFx0XHR9XG5cblx0XHRcdC8vIGlmIG9wdGlvbnMgZW1wdHkgaXMgdHJ1ZSwgY29udGludWUgb25seSBpZiBpdHMgcmFkaW9cblx0XHRcdGlmICghdmFsICYmIGVsZW1lbnQudHlwZSA9PSAncmFkaW8nKSB7XG5cdFx0XHRcdGNvbnRpbnVlO1xuXHRcdFx0fVxuXHRcdH1cblx0XHRlbHNlIHtcblx0XHRcdC8vIHZhbHVlLWxlc3MgZmllbGRzIGFyZSBpZ25vcmVkIHVubGVzcyBvcHRpb25zLmVtcHR5IGlzIHRydWVcblx0XHRcdGlmICghdmFsKSB7XG5cdFx0XHRcdGNvbnRpbnVlO1xuXHRcdFx0fVxuXHRcdH1cblxuXHRcdC8vIG11bHRpIHNlbGVjdCBib3hlc1xuXHRcdGlmIChlbGVtZW50LnR5cGUgPT09ICdzZWxlY3QtbXVsdGlwbGUnKSB7XG5cdFx0XHR2YWwgPSBbXTtcblxuXHRcdFx0dmFyIHNlbGVjdE9wdGlvbnMgPSBlbGVtZW50Lm9wdGlvbnM7XG5cdFx0XHR2YXIgaXNTZWxlY3RlZE9wdGlvbnMgPSBmYWxzZTtcblx0XHRcdGZvciAodmFyIGo9MCA7IGo8c2VsZWN0T3B0aW9ucy5sZW5ndGggOyArK2opIHtcblx0XHRcdFx0dmFyIG9wdGlvbiA9IHNlbGVjdE9wdGlvbnNbal07XG5cdFx0XHRcdHZhciBhbGxvd2VkRW1wdHkgPSBvcHRpb25zLmVtcHR5ICYmICFvcHRpb24udmFsdWU7XG5cdFx0XHRcdHZhciBoYXNWYWx1ZSA9IChvcHRpb24udmFsdWUgfHwgYWxsb3dlZEVtcHR5KTtcblx0XHRcdFx0aWYgKG9wdGlvbi5zZWxlY3RlZCAmJiBoYXNWYWx1ZSkge1xuXHRcdFx0XHRcdGlzU2VsZWN0ZWRPcHRpb25zID0gdHJ1ZTtcblxuXHRcdFx0XHRcdC8vIElmIHVzaW5nIGEgaGFzaCBzZXJpYWxpemVyIGJlIHN1cmUgdG8gYWRkIHRoZVxuXHRcdFx0XHRcdC8vIGNvcnJlY3Qgbm90YXRpb24gZm9yIGFuIGFycmF5IGluIHRoZSBtdWx0aS1zZWxlY3Rcblx0XHRcdFx0XHQvLyBjb250ZXh0LiBIZXJlIHRoZSBuYW1lIGF0dHJpYnV0ZSBvbiB0aGUgc2VsZWN0IGVsZW1lbnRcblx0XHRcdFx0XHQvLyBtaWdodCBiZSBtaXNzaW5nIHRoZSB0cmFpbGluZyBicmFja2V0IHBhaXIuIEJvdGggbmFtZXNcblx0XHRcdFx0XHQvLyBcImZvb1wiIGFuZCBcImZvb1tdXCIgc2hvdWxkIGJlIGFycmF5cy5cblx0XHRcdFx0XHRpZiAob3B0aW9ucy5oYXNoICYmIGtleS5zbGljZShrZXkubGVuZ3RoIC0gMikgIT09ICdbXScpIHtcblx0XHRcdFx0XHRcdHJlc3VsdCA9IHNlcmlhbGl6ZXIocmVzdWx0LCBrZXkgKyAnW10nLCBvcHRpb24udmFsdWUpO1xuXHRcdFx0XHRcdH1cblx0XHRcdFx0XHRlbHNlIHtcblx0XHRcdFx0XHRcdHJlc3VsdCA9IHNlcmlhbGl6ZXIocmVzdWx0LCBrZXksIG9wdGlvbi52YWx1ZSk7XG5cdFx0XHRcdFx0fVxuXHRcdFx0XHR9XG5cdFx0XHR9XG5cblx0XHRcdC8vIFNlcmlhbGl6ZSBpZiBubyBzZWxlY3RlZCBvcHRpb25zIGFuZCBvcHRpb25zLmVtcHR5IGlzIHRydWVcblx0XHRcdGlmICghaXNTZWxlY3RlZE9wdGlvbnMgJiYgb3B0aW9ucy5lbXB0eSkge1xuXHRcdFx0XHRyZXN1bHQgPSBzZXJpYWxpemVyKHJlc3VsdCwga2V5LCAnJyk7XG5cdFx0XHR9XG5cblx0XHRcdGNvbnRpbnVlO1xuXHRcdH1cblxuXHRcdHJlc3VsdCA9IHNlcmlhbGl6ZXIocmVzdWx0LCBrZXksIHZhbCk7XG5cdH1cblxuXHQvLyBDaGVjayBmb3IgYWxsIGVtcHR5IHJhZGlvIGJ1dHRvbnMgYW5kIHNlcmlhbGl6ZSB0aGVtIHdpdGgga2V5PVwiXCJcblx0aWYgKG9wdGlvbnMuZW1wdHkpIHtcblx0XHRmb3IgKHZhciBrZXkgaW4gcmFkaW9fc3RvcmUpIHtcblx0XHRcdGlmICghcmFkaW9fc3RvcmVba2V5XSkge1xuXHRcdFx0XHRyZXN1bHQgPSBzZXJpYWxpemVyKHJlc3VsdCwga2V5LCAnJyk7XG5cdFx0XHR9XG5cdFx0fVxuXHR9XG5cblx0cmV0dXJuIHJlc3VsdDtcbn1cblxuZnVuY3Rpb24gcGFyc2Vfa2V5cyhzdHJpbmcpIHtcblx0dmFyIGtleXMgPSBbXTtcblx0dmFyIHByZWZpeCA9IC9eKFteXFxbXFxdXSopLztcblx0dmFyIGNoaWxkcmVuID0gbmV3IFJlZ0V4cChicmFja2V0cyk7XG5cdHZhciBtYXRjaCA9IHByZWZpeC5leGVjKHN0cmluZyk7XG5cblx0aWYgKG1hdGNoWzFdKSB7XG5cdFx0a2V5cy5wdXNoKG1hdGNoWzFdKTtcblx0fVxuXG5cdHdoaWxlICgobWF0Y2ggPSBjaGlsZHJlbi5leGVjKHN0cmluZykpICE9PSBudWxsKSB7XG5cdFx0a2V5cy5wdXNoKG1hdGNoWzFdKTtcblx0fVxuXG5cdHJldHVybiBrZXlzO1xufVxuXG5mdW5jdGlvbiBoYXNoX2Fzc2lnbihyZXN1bHQsIGtleXMsIHZhbHVlKSB7XG5cdGlmIChrZXlzLmxlbmd0aCA9PT0gMCkge1xuXHRcdHJlc3VsdCA9IHZhbHVlO1xuXHRcdHJldHVybiByZXN1bHQ7XG5cdH1cblxuXHR2YXIga2V5ID0ga2V5cy5zaGlmdCgpO1xuXHR2YXIgYmV0d2VlbiA9IGtleS5tYXRjaCgvXlxcWyguKz8pXFxdJC8pO1xuXG5cdGlmIChrZXkgPT09ICdbXScpIHtcblx0XHRyZXN1bHQgPSByZXN1bHQgfHwgW107XG5cblx0XHRpZiAoQXJyYXkuaXNBcnJheShyZXN1bHQpKSB7XG5cdFx0XHRyZXN1bHQucHVzaChoYXNoX2Fzc2lnbihudWxsLCBrZXlzLCB2YWx1ZSkpO1xuXHRcdH1cblx0XHRlbHNlIHtcblx0XHRcdC8vIFRoaXMgbWlnaHQgYmUgdGhlIHJlc3VsdCBvZiBiYWQgbmFtZSBhdHRyaWJ1dGVzIGxpa2UgXCJbXVtmb29dXCIsXG5cdFx0XHQvLyBpbiB0aGlzIGNhc2UgdGhlIG9yaWdpbmFsIGByZXN1bHRgIG9iamVjdCB3aWxsIGFscmVhZHkgYmVcblx0XHRcdC8vIGFzc2lnbmVkIHRvIGFuIG9iamVjdCBsaXRlcmFsLiBSYXRoZXIgdGhhbiBjb2VyY2UgdGhlIG9iamVjdCB0b1xuXHRcdFx0Ly8gYW4gYXJyYXksIG9yIGNhdXNlIGFuIGV4Y2VwdGlvbiB0aGUgYXR0cmlidXRlIFwiX3ZhbHVlc1wiIGlzXG5cdFx0XHQvLyBhc3NpZ25lZCBhcyBhbiBhcnJheS5cblx0XHRcdHJlc3VsdC5fdmFsdWVzID0gcmVzdWx0Ll92YWx1ZXMgfHwgW107XG5cdFx0XHRyZXN1bHQuX3ZhbHVlcy5wdXNoKGhhc2hfYXNzaWduKG51bGwsIGtleXMsIHZhbHVlKSk7XG5cdFx0fVxuXG5cdFx0cmV0dXJuIHJlc3VsdDtcblx0fVxuXG5cdC8vIEtleSBpcyBhbiBhdHRyaWJ1dGUgbmFtZSBhbmQgY2FuIGJlIGFzc2lnbmVkIGRpcmVjdGx5LlxuXHRpZiAoIWJldHdlZW4pIHtcblx0XHRyZXN1bHRba2V5XSA9IGhhc2hfYXNzaWduKHJlc3VsdFtrZXldLCBrZXlzLCB2YWx1ZSk7XG5cdH1cblx0ZWxzZSB7XG5cdFx0dmFyIHN0cmluZyA9IGJldHdlZW5bMV07XG5cdFx0dmFyIGluZGV4ID0gcGFyc2VJbnQoc3RyaW5nLCAxMCk7XG5cblx0XHQvLyBJZiB0aGUgY2hhcmFjdGVycyBiZXR3ZWVuIHRoZSBicmFja2V0cyBpcyBub3QgYSBudW1iZXIgaXQgaXMgYW5cblx0XHQvLyBhdHRyaWJ1dGUgbmFtZSBhbmQgY2FuIGJlIGFzc2lnbmVkIGRpcmVjdGx5LlxuXHRcdGlmIChpc05hTihpbmRleCkpIHtcblx0XHRcdHJlc3VsdCA9IHJlc3VsdCB8fCB7fTtcblx0XHRcdHJlc3VsdFtzdHJpbmddID0gaGFzaF9hc3NpZ24ocmVzdWx0W3N0cmluZ10sIGtleXMsIHZhbHVlKTtcblx0XHR9XG5cdFx0ZWxzZSB7XG5cdFx0XHRyZXN1bHQgPSByZXN1bHQgfHwgW107XG5cdFx0XHRyZXN1bHRbaW5kZXhdID0gaGFzaF9hc3NpZ24ocmVzdWx0W2luZGV4XSwga2V5cywgdmFsdWUpO1xuXHRcdH1cblx0fVxuXG5cdHJldHVybiByZXN1bHQ7XG59XG5cbi8vIE9iamVjdC9oYXNoIGVuY29kaW5nIHNlcmlhbGl6ZXIuXG5mdW5jdGlvbiBoYXNoX3NlcmlhbGl6ZXIocmVzdWx0LCBrZXksIHZhbHVlKSB7XG5cdHZhciBtYXRjaGVzID0ga2V5Lm1hdGNoKGJyYWNrZXRzKTtcblxuXHQvLyBIYXMgYnJhY2tldHM/IFVzZSB0aGUgcmVjdXJzaXZlIGFzc2lnbm1lbnQgZnVuY3Rpb24gdG8gd2FsayB0aGUga2V5cyxcblx0Ly8gY29uc3RydWN0IGFueSBtaXNzaW5nIG9iamVjdHMgaW4gdGhlIHJlc3VsdCB0cmVlIGFuZCBtYWtlIHRoZSBhc3NpZ25tZW50XG5cdC8vIGF0IHRoZSBlbmQgb2YgdGhlIGNoYWluLlxuXHRpZiAobWF0Y2hlcykge1xuXHRcdHZhciBrZXlzID0gcGFyc2Vfa2V5cyhrZXkpO1xuXHRcdGhhc2hfYXNzaWduKHJlc3VsdCwga2V5cywgdmFsdWUpO1xuXHR9XG5cdGVsc2Uge1xuXHRcdC8vIE5vbiBicmFja2V0IG5vdGF0aW9uIGNhbiBtYWtlIGFzc2lnbm1lbnRzIGRpcmVjdGx5LlxuXHRcdHZhciBleGlzdGluZyA9IHJlc3VsdFtrZXldO1xuXG5cdFx0Ly8gSWYgdGhlIHZhbHVlIGhhcyBiZWVuIGFzc2lnbmVkIGFscmVhZHkgKGZvciBpbnN0YW5jZSB3aGVuIGEgcmFkaW8gYW5kXG5cdFx0Ly8gYSBjaGVja2JveCBoYXZlIHRoZSBzYW1lIG5hbWUgYXR0cmlidXRlKSBjb252ZXJ0IHRoZSBwcmV2aW91cyB2YWx1ZVxuXHRcdC8vIGludG8gYW4gYXJyYXkgYmVmb3JlIHB1c2hpbmcgaW50byBpdC5cblx0XHQvL1xuXHRcdC8vIE5PVEU6IElmIHRoaXMgcmVxdWlyZW1lbnQgd2VyZSByZW1vdmVkIGFsbCBoYXNoIGNyZWF0aW9uIGFuZFxuXHRcdC8vIGFzc2lnbm1lbnQgY291bGQgZ28gdGhyb3VnaCBgaGFzaF9hc3NpZ25gLlxuXHRcdGlmIChleGlzdGluZykge1xuXHRcdFx0aWYgKCFBcnJheS5pc0FycmF5KGV4aXN0aW5nKSkge1xuXHRcdFx0XHRyZXN1bHRba2V5XSA9IFsgZXhpc3RpbmcgXTtcblx0XHRcdH1cblxuXHRcdFx0cmVzdWx0W2tleV0ucHVzaCh2YWx1ZSk7XG5cdFx0fVxuXHRcdGVsc2Uge1xuXHRcdFx0cmVzdWx0W2tleV0gPSB2YWx1ZTtcblx0XHR9XG5cdH1cblxuXHRyZXR1cm4gcmVzdWx0O1xufVxuXG4vLyB1cmxmb3JtIGVuY29kaW5nIHNlcmlhbGl6ZXJcbmZ1bmN0aW9uIHN0cl9zZXJpYWxpemUocmVzdWx0LCBrZXksIHZhbHVlKSB7XG5cdC8vIGVuY29kZSBuZXdsaW5lcyBhcyBcXHJcXG4gY2F1c2UgdGhlIGh0bWwgc3BlYyBzYXlzIHNvXG5cdHZhbHVlID0gdmFsdWUucmVwbGFjZSgvKFxccik/XFxuL2csICdcXHJcXG4nKTtcblx0dmFsdWUgPSBlbmNvZGVVUklDb21wb25lbnQodmFsdWUpO1xuXG5cdC8vIHNwYWNlcyBzaG91bGQgYmUgJysnIHJhdGhlciB0aGFuICclMjAnLlxuXHR2YWx1ZSA9IHZhbHVlLnJlcGxhY2UoLyUyMC9nLCAnKycpO1xuXHRyZXR1cm4gcmVzdWx0ICsgKHJlc3VsdCA/ICcmJyA6ICcnKSArIGVuY29kZVVSSUNvbXBvbmVudChrZXkpICsgJz0nICsgdmFsdWU7XG59XG5cbm1vZHVsZS5leHBvcnRzID0gc2VyaWFsaXplOyIsIi8qKlxuICogQ29weXJpZ2h0IDIwMTQgQ3JhaWcgQ2FtcGJlbGxcbiAqXG4gKiBMaWNlbnNlZCB1bmRlciB0aGUgQXBhY2hlIExpY2Vuc2UsIFZlcnNpb24gMi4wICh0aGUgXCJMaWNlbnNlXCIpO1xuICogeW91IG1heSBub3QgdXNlIHRoaXMgZmlsZSBleGNlcHQgaW4gY29tcGxpYW5jZSB3aXRoIHRoZSBMaWNlbnNlLlxuICogWW91IG1heSBvYnRhaW4gYSBjb3B5IG9mIHRoZSBMaWNlbnNlIGF0XG4gKlxuICogaHR0cDovL3d3dy5hcGFjaGUub3JnL2xpY2Vuc2VzL0xJQ0VOU0UtMi4wXG4gKlxuICogVW5sZXNzIHJlcXVpcmVkIGJ5IGFwcGxpY2FibGUgbGF3IG9yIGFncmVlZCB0byBpbiB3cml0aW5nLCBzb2Z0d2FyZVxuICogZGlzdHJpYnV0ZWQgdW5kZXIgdGhlIExpY2Vuc2UgaXMgZGlzdHJpYnV0ZWQgb24gYW4gXCJBUyBJU1wiIEJBU0lTLFxuICogV0lUSE9VVCBXQVJSQU5USUVTIE9SIENPTkRJVElPTlMgT0YgQU5ZIEtJTkQsIGVpdGhlciBleHByZXNzIG9yIGltcGxpZWQuXG4gKiBTZWUgdGhlIExpY2Vuc2UgZm9yIHRoZSBzcGVjaWZpYyBsYW5ndWFnZSBnb3Zlcm5pbmcgcGVybWlzc2lvbnMgYW5kXG4gKiBsaW1pdGF0aW9ucyB1bmRlciB0aGUgTGljZW5zZS5cbiAqXG4gKiBHQVRPUi5KU1xuICogU2ltcGxlIEV2ZW50IERlbGVnYXRpb25cbiAqXG4gKiBAdmVyc2lvbiAxLjIuNFxuICpcbiAqIENvbXBhdGlibGUgd2l0aCBJRSA5KywgRkYgMy42KywgU2FmYXJpIDUrLCBDaHJvbWVcbiAqXG4gKiBJbmNsdWRlIGxlZ2FjeS5qcyBmb3IgY29tcGF0aWJpbGl0eSB3aXRoIG9sZGVyIGJyb3dzZXJzXG4gKlxuICogICAgICAgICAgICAgLi0uXyAgIF8gXyBfIF8gXyBfIF8gX1xuICogIC4tJyctLl9fLi0nMDAgICctJyAnICcgJyAnICcgJyAnICctLlxuICogJy5fX18gJyAgICAuICAgLi0tXyctJyAnLScgJy0nIF8nLScgJy5fXG4gKiAgVjogViAndnYtJyAgICdfICAgJy4gICAgICAgLicgIF8uLicgJy4nLlxuICogICAgJz0uX19fXy49Xy4tLScgICA6Xy5fXy5fXzpfICAgJy4gICA6IDpcbiAqICAgICAgICAgICAgKCgoX19fXy4tJyAgICAgICAgJy0uICAvICAgOiA6XG4gKiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICgoKC0nXFwgLicgL1xuICogICAgICAgICAgICAgICAgICAgICAgICAgICAgX19fX18uLicgIC4nXG4gKiAgICAgICAgICAgICAgICAgICAgICAgICAgICctLl9fX19fLi0nXG4gKi9cbihmdW5jdGlvbigpIHtcbiAgICB2YXIgX21hdGNoZXIsXG4gICAgICAgIF9sZXZlbCA9IDAsXG4gICAgICAgIF9pZCA9IDAsXG4gICAgICAgIF9oYW5kbGVycyA9IHt9LFxuICAgICAgICBfZ2F0b3JJbnN0YW5jZXMgPSB7fTtcblxuICAgIGZ1bmN0aW9uIF9hZGRFdmVudChnYXRvciwgdHlwZSwgY2FsbGJhY2spIHtcblxuICAgICAgICAvLyBibHVyIGFuZCBmb2N1cyBkbyBub3QgYnViYmxlIHVwIGJ1dCBpZiB5b3UgdXNlIGV2ZW50IGNhcHR1cmluZ1xuICAgICAgICAvLyB0aGVuIHlvdSB3aWxsIGdldCB0aGVtXG4gICAgICAgIHZhciB1c2VDYXB0dXJlID0gdHlwZSA9PSAnYmx1cicgfHwgdHlwZSA9PSAnZm9jdXMnO1xuICAgICAgICBnYXRvci5lbGVtZW50LmFkZEV2ZW50TGlzdGVuZXIodHlwZSwgY2FsbGJhY2ssIHVzZUNhcHR1cmUpO1xuICAgIH1cblxuICAgIGZ1bmN0aW9uIF9jYW5jZWwoZSkge1xuICAgICAgICBlLnByZXZlbnREZWZhdWx0KCk7XG4gICAgICAgIGUuc3RvcFByb3BhZ2F0aW9uKCk7XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogcmV0dXJucyBmdW5jdGlvbiB0byB1c2UgZm9yIGRldGVybWluaW5nIGlmIGFuIGVsZW1lbnRcbiAgICAgKiBtYXRjaGVzIGEgcXVlcnkgc2VsZWN0b3JcbiAgICAgKlxuICAgICAqIEByZXR1cm5zIHtGdW5jdGlvbn1cbiAgICAgKi9cbiAgICBmdW5jdGlvbiBfZ2V0TWF0Y2hlcihlbGVtZW50KSB7XG4gICAgICAgIGlmIChfbWF0Y2hlcikge1xuICAgICAgICAgICAgcmV0dXJuIF9tYXRjaGVyO1xuICAgICAgICB9XG5cbiAgICAgICAgaWYgKGVsZW1lbnQubWF0Y2hlcykge1xuICAgICAgICAgICAgX21hdGNoZXIgPSBlbGVtZW50Lm1hdGNoZXM7XG4gICAgICAgICAgICByZXR1cm4gX21hdGNoZXI7XG4gICAgICAgIH1cblxuICAgICAgICBpZiAoZWxlbWVudC53ZWJraXRNYXRjaGVzU2VsZWN0b3IpIHtcbiAgICAgICAgICAgIF9tYXRjaGVyID0gZWxlbWVudC53ZWJraXRNYXRjaGVzU2VsZWN0b3I7XG4gICAgICAgICAgICByZXR1cm4gX21hdGNoZXI7XG4gICAgICAgIH1cblxuICAgICAgICBpZiAoZWxlbWVudC5tb3pNYXRjaGVzU2VsZWN0b3IpIHtcbiAgICAgICAgICAgIF9tYXRjaGVyID0gZWxlbWVudC5tb3pNYXRjaGVzU2VsZWN0b3I7XG4gICAgICAgICAgICByZXR1cm4gX21hdGNoZXI7XG4gICAgICAgIH1cblxuICAgICAgICBpZiAoZWxlbWVudC5tc01hdGNoZXNTZWxlY3Rvcikge1xuICAgICAgICAgICAgX21hdGNoZXIgPSBlbGVtZW50Lm1zTWF0Y2hlc1NlbGVjdG9yO1xuICAgICAgICAgICAgcmV0dXJuIF9tYXRjaGVyO1xuICAgICAgICB9XG5cbiAgICAgICAgaWYgKGVsZW1lbnQub01hdGNoZXNTZWxlY3Rvcikge1xuICAgICAgICAgICAgX21hdGNoZXIgPSBlbGVtZW50Lm9NYXRjaGVzU2VsZWN0b3I7XG4gICAgICAgICAgICByZXR1cm4gX21hdGNoZXI7XG4gICAgICAgIH1cblxuICAgICAgICAvLyBpZiBpdCBkb2Vzbid0IG1hdGNoIGEgbmF0aXZlIGJyb3dzZXIgbWV0aG9kXG4gICAgICAgIC8vIGZhbGwgYmFjayB0byB0aGUgZ2F0b3IgZnVuY3Rpb25cbiAgICAgICAgX21hdGNoZXIgPSBHYXRvci5tYXRjaGVzU2VsZWN0b3I7XG4gICAgICAgIHJldHVybiBfbWF0Y2hlcjtcbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBkZXRlcm1pbmVzIGlmIHRoZSBzcGVjaWZpZWQgZWxlbWVudCBtYXRjaGVzIGEgZ2l2ZW4gc2VsZWN0b3JcbiAgICAgKlxuICAgICAqIEBwYXJhbSB7Tm9kZX0gZWxlbWVudCAtIHRoZSBlbGVtZW50IHRvIGNvbXBhcmUgYWdhaW5zdCB0aGUgc2VsZWN0b3JcbiAgICAgKiBAcGFyYW0ge3N0cmluZ30gc2VsZWN0b3JcbiAgICAgKiBAcGFyYW0ge05vZGV9IGJvdW5kRWxlbWVudCAtIHRoZSBlbGVtZW50IHRoZSBsaXN0ZW5lciB3YXMgYXR0YWNoZWQgdG9cbiAgICAgKiBAcmV0dXJucyB7dm9pZHxOb2RlfVxuICAgICAqL1xuICAgIGZ1bmN0aW9uIF9tYXRjaGVzU2VsZWN0b3IoZWxlbWVudCwgc2VsZWN0b3IsIGJvdW5kRWxlbWVudCkge1xuXG4gICAgICAgIC8vIG5vIHNlbGVjdG9yIG1lYW5zIHRoaXMgZXZlbnQgd2FzIGJvdW5kIGRpcmVjdGx5IHRvIHRoaXMgZWxlbWVudFxuICAgICAgICBpZiAoc2VsZWN0b3IgPT0gJ19yb290Jykge1xuICAgICAgICAgICAgcmV0dXJuIGJvdW5kRWxlbWVudDtcbiAgICAgICAgfVxuXG4gICAgICAgIC8vIGlmIHdlIGhhdmUgbW92ZWQgdXAgdG8gdGhlIGVsZW1lbnQgeW91IGJvdW5kIHRoZSBldmVudCB0b1xuICAgICAgICAvLyB0aGVuIHdlIGhhdmUgY29tZSB0b28gZmFyXG4gICAgICAgIGlmIChlbGVtZW50ID09PSBib3VuZEVsZW1lbnQpIHtcbiAgICAgICAgICAgIHJldHVybjtcbiAgICAgICAgfVxuXG4gICAgICAgIC8vIGlmIHRoaXMgaXMgYSBtYXRjaCB0aGVuIHdlIGFyZSBkb25lIVxuICAgICAgICBpZiAoX2dldE1hdGNoZXIoZWxlbWVudCkuY2FsbChlbGVtZW50LCBzZWxlY3RvcikpIHtcbiAgICAgICAgICAgIHJldHVybiBlbGVtZW50O1xuICAgICAgICB9XG5cbiAgICAgICAgLy8gaWYgdGhpcyBlbGVtZW50IGRpZCBub3QgbWF0Y2ggYnV0IGhhcyBhIHBhcmVudCB3ZSBzaG91bGQgdHJ5XG4gICAgICAgIC8vIGdvaW5nIHVwIHRoZSB0cmVlIHRvIHNlZSBpZiBhbnkgb2YgdGhlIHBhcmVudCBlbGVtZW50cyBtYXRjaFxuICAgICAgICAvLyBmb3IgZXhhbXBsZSBpZiB5b3UgYXJlIGxvb2tpbmcgZm9yIGEgY2xpY2sgb24gYW4gPGE+IHRhZyBidXQgdGhlcmVcbiAgICAgICAgLy8gaXMgYSA8c3Bhbj4gaW5zaWRlIG9mIHRoZSBhIHRhZyB0aGF0IGl0IGlzIHRoZSB0YXJnZXQsXG4gICAgICAgIC8vIGl0IHNob3VsZCBzdGlsbCB3b3JrXG4gICAgICAgIGlmIChlbGVtZW50LnBhcmVudE5vZGUpIHtcbiAgICAgICAgICAgIF9sZXZlbCsrO1xuICAgICAgICAgICAgcmV0dXJuIF9tYXRjaGVzU2VsZWN0b3IoZWxlbWVudC5wYXJlbnROb2RlLCBzZWxlY3RvciwgYm91bmRFbGVtZW50KTtcbiAgICAgICAgfVxuICAgIH1cblxuICAgIGZ1bmN0aW9uIF9hZGRIYW5kbGVyKGdhdG9yLCBldmVudCwgc2VsZWN0b3IsIGNhbGxiYWNrKSB7XG4gICAgICAgIGlmICghX2hhbmRsZXJzW2dhdG9yLmlkXSkge1xuICAgICAgICAgICAgX2hhbmRsZXJzW2dhdG9yLmlkXSA9IHt9O1xuICAgICAgICB9XG5cbiAgICAgICAgaWYgKCFfaGFuZGxlcnNbZ2F0b3IuaWRdW2V2ZW50XSkge1xuICAgICAgICAgICAgX2hhbmRsZXJzW2dhdG9yLmlkXVtldmVudF0gPSB7fTtcbiAgICAgICAgfVxuXG4gICAgICAgIGlmICghX2hhbmRsZXJzW2dhdG9yLmlkXVtldmVudF1bc2VsZWN0b3JdKSB7XG4gICAgICAgICAgICBfaGFuZGxlcnNbZ2F0b3IuaWRdW2V2ZW50XVtzZWxlY3Rvcl0gPSBbXTtcbiAgICAgICAgfVxuXG4gICAgICAgIF9oYW5kbGVyc1tnYXRvci5pZF1bZXZlbnRdW3NlbGVjdG9yXS5wdXNoKGNhbGxiYWNrKTtcbiAgICB9XG5cbiAgICBmdW5jdGlvbiBfcmVtb3ZlSGFuZGxlcihnYXRvciwgZXZlbnQsIHNlbGVjdG9yLCBjYWxsYmFjaykge1xuXG4gICAgICAgIC8vIGlmIHRoZXJlIGFyZSBubyBldmVudHMgdGllZCB0byB0aGlzIGVsZW1lbnQgYXQgYWxsXG4gICAgICAgIC8vIHRoZW4gZG9uJ3QgZG8gYW55dGhpbmdcbiAgICAgICAgaWYgKCFfaGFuZGxlcnNbZ2F0b3IuaWRdKSB7XG4gICAgICAgICAgICByZXR1cm47XG4gICAgICAgIH1cblxuICAgICAgICAvLyBpZiB0aGVyZSBpcyBubyBldmVudCB0eXBlIHNwZWNpZmllZCB0aGVuIHJlbW92ZSBhbGwgZXZlbnRzXG4gICAgICAgIC8vIGV4YW1wbGU6IEdhdG9yKGVsZW1lbnQpLm9mZigpXG4gICAgICAgIGlmICghZXZlbnQpIHtcbiAgICAgICAgICAgIGZvciAodmFyIHR5cGUgaW4gX2hhbmRsZXJzW2dhdG9yLmlkXSkge1xuICAgICAgICAgICAgICAgIGlmIChfaGFuZGxlcnNbZ2F0b3IuaWRdLmhhc093blByb3BlcnR5KHR5cGUpKSB7XG4gICAgICAgICAgICAgICAgICAgIF9oYW5kbGVyc1tnYXRvci5pZF1bdHlwZV0gPSB7fTtcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICB9XG4gICAgICAgICAgICByZXR1cm47XG4gICAgICAgIH1cblxuICAgICAgICAvLyBpZiBubyBjYWxsYmFjayBvciBzZWxlY3RvciBpcyBzcGVjaWZpZWQgcmVtb3ZlIGFsbCBldmVudHMgb2YgdGhpcyB0eXBlXG4gICAgICAgIC8vIGV4YW1wbGU6IEdhdG9yKGVsZW1lbnQpLm9mZignY2xpY2snKVxuICAgICAgICBpZiAoIWNhbGxiYWNrICYmICFzZWxlY3Rvcikge1xuICAgICAgICAgICAgX2hhbmRsZXJzW2dhdG9yLmlkXVtldmVudF0gPSB7fTtcbiAgICAgICAgICAgIHJldHVybjtcbiAgICAgICAgfVxuXG4gICAgICAgIC8vIGlmIGEgc2VsZWN0b3IgaXMgc3BlY2lmaWVkIGJ1dCBubyBjYWxsYmFjayByZW1vdmUgYWxsIGV2ZW50c1xuICAgICAgICAvLyBmb3IgdGhpcyBzZWxlY3RvclxuICAgICAgICAvLyBleGFtcGxlOiBHYXRvcihlbGVtZW50KS5vZmYoJ2NsaWNrJywgJy5zdWItZWxlbWVudCcpXG4gICAgICAgIGlmICghY2FsbGJhY2spIHtcbiAgICAgICAgICAgIGRlbGV0ZSBfaGFuZGxlcnNbZ2F0b3IuaWRdW2V2ZW50XVtzZWxlY3Rvcl07XG4gICAgICAgICAgICByZXR1cm47XG4gICAgICAgIH1cblxuICAgICAgICAvLyBpZiB3ZSBoYXZlIHNwZWNpZmllZCBhbiBldmVudCB0eXBlLCBzZWxlY3RvciwgYW5kIGNhbGxiYWNrIHRoZW4gd2VcbiAgICAgICAgLy8gbmVlZCB0byBtYWtlIHN1cmUgdGhlcmUgYXJlIGNhbGxiYWNrcyB0aWVkIHRvIHRoaXMgc2VsZWN0b3IgdG9cbiAgICAgICAgLy8gYmVnaW4gd2l0aC4gIGlmIHRoZXJlIGFyZW4ndCB0aGVuIHdlIGNhbiBzdG9wIGhlcmVcbiAgICAgICAgaWYgKCFfaGFuZGxlcnNbZ2F0b3IuaWRdW2V2ZW50XVtzZWxlY3Rvcl0pIHtcbiAgICAgICAgICAgIHJldHVybjtcbiAgICAgICAgfVxuXG4gICAgICAgIC8vIGlmIHRoZXJlIGFyZSB0aGVuIGxvb3AgdGhyb3VnaCBhbGwgdGhlIGNhbGxiYWNrcyBhbmQgaWYgd2UgZmluZFxuICAgICAgICAvLyBvbmUgdGhhdCBtYXRjaGVzIHJlbW92ZSBpdCBmcm9tIHRoZSBhcnJheVxuICAgICAgICBmb3IgKHZhciBpID0gMDsgaSA8IF9oYW5kbGVyc1tnYXRvci5pZF1bZXZlbnRdW3NlbGVjdG9yXS5sZW5ndGg7IGkrKykge1xuICAgICAgICAgICAgaWYgKF9oYW5kbGVyc1tnYXRvci5pZF1bZXZlbnRdW3NlbGVjdG9yXVtpXSA9PT0gY2FsbGJhY2spIHtcbiAgICAgICAgICAgICAgICBfaGFuZGxlcnNbZ2F0b3IuaWRdW2V2ZW50XVtzZWxlY3Rvcl0uc3BsaWNlKGksIDEpO1xuICAgICAgICAgICAgICAgIGJyZWFrO1xuICAgICAgICAgICAgfVxuICAgICAgICB9XG4gICAgfVxuXG4gICAgZnVuY3Rpb24gX2hhbmRsZUV2ZW50KGlkLCBlLCB0eXBlKSB7XG4gICAgICAgIGlmICghX2hhbmRsZXJzW2lkXVt0eXBlXSkge1xuICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICB9XG5cbiAgICAgICAgdmFyIHRhcmdldCA9IGUudGFyZ2V0IHx8IGUuc3JjRWxlbWVudCxcbiAgICAgICAgICAgIHNlbGVjdG9yLFxuICAgICAgICAgICAgbWF0Y2gsXG4gICAgICAgICAgICBtYXRjaGVzID0ge30sXG4gICAgICAgICAgICBpID0gMCxcbiAgICAgICAgICAgIGogPSAwO1xuXG4gICAgICAgIC8vIGZpbmQgYWxsIGV2ZW50cyB0aGF0IG1hdGNoXG4gICAgICAgIF9sZXZlbCA9IDA7XG4gICAgICAgIGZvciAoc2VsZWN0b3IgaW4gX2hhbmRsZXJzW2lkXVt0eXBlXSkge1xuICAgICAgICAgICAgaWYgKF9oYW5kbGVyc1tpZF1bdHlwZV0uaGFzT3duUHJvcGVydHkoc2VsZWN0b3IpKSB7XG4gICAgICAgICAgICAgICAgbWF0Y2ggPSBfbWF0Y2hlc1NlbGVjdG9yKHRhcmdldCwgc2VsZWN0b3IsIF9nYXRvckluc3RhbmNlc1tpZF0uZWxlbWVudCk7XG5cbiAgICAgICAgICAgICAgICBpZiAobWF0Y2ggJiYgR2F0b3IubWF0Y2hlc0V2ZW50KHR5cGUsIF9nYXRvckluc3RhbmNlc1tpZF0uZWxlbWVudCwgbWF0Y2gsIHNlbGVjdG9yID09ICdfcm9vdCcsIGUpKSB7XG4gICAgICAgICAgICAgICAgICAgIF9sZXZlbCsrO1xuICAgICAgICAgICAgICAgICAgICBfaGFuZGxlcnNbaWRdW3R5cGVdW3NlbGVjdG9yXS5tYXRjaCA9IG1hdGNoO1xuICAgICAgICAgICAgICAgICAgICBtYXRjaGVzW19sZXZlbF0gPSBfaGFuZGxlcnNbaWRdW3R5cGVdW3NlbGVjdG9yXTtcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICB9XG4gICAgICAgIH1cblxuICAgICAgICAvLyBzdG9wUHJvcGFnYXRpb24oKSBmYWlscyB0byBzZXQgY2FuY2VsQnViYmxlIHRvIHRydWUgaW4gV2Via2l0XG4gICAgICAgIC8vIEBzZWUgaHR0cDovL2NvZGUuZ29vZ2xlLmNvbS9wL2Nocm9taXVtL2lzc3Vlcy9kZXRhaWw/aWQ9MTYyMjcwXG4gICAgICAgIGUuc3RvcFByb3BhZ2F0aW9uID0gZnVuY3Rpb24oKSB7XG4gICAgICAgICAgICBlLmNhbmNlbEJ1YmJsZSA9IHRydWU7XG4gICAgICAgIH07XG5cbiAgICAgICAgZm9yIChpID0gMDsgaSA8PSBfbGV2ZWw7IGkrKykge1xuICAgICAgICAgICAgaWYgKG1hdGNoZXNbaV0pIHtcbiAgICAgICAgICAgICAgICBmb3IgKGogPSAwOyBqIDwgbWF0Y2hlc1tpXS5sZW5ndGg7IGorKykge1xuICAgICAgICAgICAgICAgICAgICBpZiAobWF0Y2hlc1tpXVtqXS5jYWxsKG1hdGNoZXNbaV0ubWF0Y2gsIGUpID09PSBmYWxzZSkge1xuICAgICAgICAgICAgICAgICAgICAgICAgR2F0b3IuY2FuY2VsKGUpO1xuICAgICAgICAgICAgICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICAgICAgICAgICAgICB9XG5cbiAgICAgICAgICAgICAgICAgICAgaWYgKGUuY2FuY2VsQnViYmxlKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICByZXR1cm47XG4gICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICB9XG4gICAgICAgIH1cbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBiaW5kcyB0aGUgc3BlY2lmaWVkIGV2ZW50cyB0byB0aGUgZWxlbWVudFxuICAgICAqXG4gICAgICogQHBhcmFtIHtzdHJpbmd8QXJyYXl9IGV2ZW50c1xuICAgICAqIEBwYXJhbSB7c3RyaW5nfSBzZWxlY3RvclxuICAgICAqIEBwYXJhbSB7RnVuY3Rpb259IGNhbGxiYWNrXG4gICAgICogQHBhcmFtIHtib29sZWFuPX0gcmVtb3ZlXG4gICAgICogQHJldHVybnMge09iamVjdH1cbiAgICAgKi9cbiAgICBmdW5jdGlvbiBfYmluZChldmVudHMsIHNlbGVjdG9yLCBjYWxsYmFjaywgcmVtb3ZlKSB7XG5cbiAgICAgICAgLy8gZmFpbCBzaWxlbnRseSBpZiB5b3UgcGFzcyBudWxsIG9yIHVuZGVmaW5lZCBhcyBhbiBhbGVtZW50XG4gICAgICAgIC8vIGluIHRoZSBHYXRvciBjb25zdHJ1Y3RvclxuICAgICAgICBpZiAoIXRoaXMuZWxlbWVudCkge1xuICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICB9XG5cbiAgICAgICAgaWYgKCEoZXZlbnRzIGluc3RhbmNlb2YgQXJyYXkpKSB7XG4gICAgICAgICAgICBldmVudHMgPSBbZXZlbnRzXTtcbiAgICAgICAgfVxuXG4gICAgICAgIGlmICghY2FsbGJhY2sgJiYgdHlwZW9mKHNlbGVjdG9yKSA9PSAnZnVuY3Rpb24nKSB7XG4gICAgICAgICAgICBjYWxsYmFjayA9IHNlbGVjdG9yO1xuICAgICAgICAgICAgc2VsZWN0b3IgPSAnX3Jvb3QnO1xuICAgICAgICB9XG5cbiAgICAgICAgdmFyIGlkID0gdGhpcy5pZCxcbiAgICAgICAgICAgIGk7XG5cbiAgICAgICAgZnVuY3Rpb24gX2dldEdsb2JhbENhbGxiYWNrKHR5cGUpIHtcbiAgICAgICAgICAgIHJldHVybiBmdW5jdGlvbihlKSB7XG4gICAgICAgICAgICAgICAgX2hhbmRsZUV2ZW50KGlkLCBlLCB0eXBlKTtcbiAgICAgICAgICAgIH07XG4gICAgICAgIH1cblxuICAgICAgICBmb3IgKGkgPSAwOyBpIDwgZXZlbnRzLmxlbmd0aDsgaSsrKSB7XG4gICAgICAgICAgICBpZiAocmVtb3ZlKSB7XG4gICAgICAgICAgICAgICAgX3JlbW92ZUhhbmRsZXIodGhpcywgZXZlbnRzW2ldLCBzZWxlY3RvciwgY2FsbGJhY2spO1xuICAgICAgICAgICAgICAgIGNvbnRpbnVlO1xuICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICBpZiAoIV9oYW5kbGVyc1tpZF0gfHwgIV9oYW5kbGVyc1tpZF1bZXZlbnRzW2ldXSkge1xuICAgICAgICAgICAgICAgIEdhdG9yLmFkZEV2ZW50KHRoaXMsIGV2ZW50c1tpXSwgX2dldEdsb2JhbENhbGxiYWNrKGV2ZW50c1tpXSkpO1xuICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICBfYWRkSGFuZGxlcih0aGlzLCBldmVudHNbaV0sIHNlbGVjdG9yLCBjYWxsYmFjayk7XG4gICAgICAgIH1cblxuICAgICAgICByZXR1cm4gdGhpcztcbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBHYXRvciBvYmplY3QgY29uc3RydWN0b3JcbiAgICAgKlxuICAgICAqIEBwYXJhbSB7Tm9kZX0gZWxlbWVudFxuICAgICAqL1xuICAgIGZ1bmN0aW9uIEdhdG9yKGVsZW1lbnQsIGlkKSB7XG5cbiAgICAgICAgLy8gY2FsbGVkIGFzIGZ1bmN0aW9uXG4gICAgICAgIGlmICghKHRoaXMgaW5zdGFuY2VvZiBHYXRvcikpIHtcbiAgICAgICAgICAgIC8vIG9ubHkga2VlcCBvbmUgR2F0b3IgaW5zdGFuY2UgcGVyIG5vZGUgdG8gbWFrZSBzdXJlIHRoYXRcbiAgICAgICAgICAgIC8vIHdlIGRvbid0IGNyZWF0ZSBhIHRvbiBvZiBuZXcgb2JqZWN0cyBpZiB5b3Ugd2FudCB0byBkZWxlZ2F0ZVxuICAgICAgICAgICAgLy8gbXVsdGlwbGUgZXZlbnRzIGZyb20gdGhlIHNhbWUgbm9kZVxuICAgICAgICAgICAgLy9cbiAgICAgICAgICAgIC8vIGZvciBleGFtcGxlOiBHYXRvcihkb2N1bWVudCkub24oLi4uXG4gICAgICAgICAgICBmb3IgKHZhciBrZXkgaW4gX2dhdG9ySW5zdGFuY2VzKSB7XG4gICAgICAgICAgICAgICAgaWYgKF9nYXRvckluc3RhbmNlc1trZXldLmVsZW1lbnQgPT09IGVsZW1lbnQpIHtcbiAgICAgICAgICAgICAgICAgICAgcmV0dXJuIF9nYXRvckluc3RhbmNlc1trZXldO1xuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgIH1cblxuICAgICAgICAgICAgX2lkKys7XG4gICAgICAgICAgICBfZ2F0b3JJbnN0YW5jZXNbX2lkXSA9IG5ldyBHYXRvcihlbGVtZW50LCBfaWQpO1xuXG4gICAgICAgICAgICByZXR1cm4gX2dhdG9ySW5zdGFuY2VzW19pZF07XG4gICAgICAgIH1cblxuICAgICAgICB0aGlzLmVsZW1lbnQgPSBlbGVtZW50O1xuICAgICAgICB0aGlzLmlkID0gaWQ7XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogYWRkcyBhbiBldmVudFxuICAgICAqXG4gICAgICogQHBhcmFtIHtzdHJpbmd8QXJyYXl9IGV2ZW50c1xuICAgICAqIEBwYXJhbSB7c3RyaW5nfSBzZWxlY3RvclxuICAgICAqIEBwYXJhbSB7RnVuY3Rpb259IGNhbGxiYWNrXG4gICAgICogQHJldHVybnMge09iamVjdH1cbiAgICAgKi9cbiAgICBHYXRvci5wcm90b3R5cGUub24gPSBmdW5jdGlvbihldmVudHMsIHNlbGVjdG9yLCBjYWxsYmFjaykge1xuICAgICAgICByZXR1cm4gX2JpbmQuY2FsbCh0aGlzLCBldmVudHMsIHNlbGVjdG9yLCBjYWxsYmFjayk7XG4gICAgfTtcblxuICAgIC8qKlxuICAgICAqIHJlbW92ZXMgYW4gZXZlbnRcbiAgICAgKlxuICAgICAqIEBwYXJhbSB7c3RyaW5nfEFycmF5fSBldmVudHNcbiAgICAgKiBAcGFyYW0ge3N0cmluZ30gc2VsZWN0b3JcbiAgICAgKiBAcGFyYW0ge0Z1bmN0aW9ufSBjYWxsYmFja1xuICAgICAqIEByZXR1cm5zIHtPYmplY3R9XG4gICAgICovXG4gICAgR2F0b3IucHJvdG90eXBlLm9mZiA9IGZ1bmN0aW9uKGV2ZW50cywgc2VsZWN0b3IsIGNhbGxiYWNrKSB7XG4gICAgICAgIHJldHVybiBfYmluZC5jYWxsKHRoaXMsIGV2ZW50cywgc2VsZWN0b3IsIGNhbGxiYWNrLCB0cnVlKTtcbiAgICB9O1xuXG4gICAgR2F0b3IubWF0Y2hlc1NlbGVjdG9yID0gZnVuY3Rpb24oKSB7fTtcbiAgICBHYXRvci5jYW5jZWwgPSBfY2FuY2VsO1xuICAgIEdhdG9yLmFkZEV2ZW50ID0gX2FkZEV2ZW50O1xuICAgIEdhdG9yLm1hdGNoZXNFdmVudCA9IGZ1bmN0aW9uKCkge1xuICAgICAgICByZXR1cm4gdHJ1ZTtcbiAgICB9O1xuXG4gICAgaWYgKHR5cGVvZiBtb2R1bGUgIT09ICd1bmRlZmluZWQnICYmIG1vZHVsZS5leHBvcnRzKSB7XG4gICAgICAgIG1vZHVsZS5leHBvcnRzID0gR2F0b3I7XG4gICAgfVxuXG4gICAgd2luZG93LkdhdG9yID0gR2F0b3I7XG59KSAoKTtcbiIsIi8qISBwb3B1bGF0ZS5qcyB2MS4wLjIgYnkgQGRhbm55dmFua29vdGVuIHwgTUlUIGxpY2Vuc2UgKi9cbjsoZnVuY3Rpb24ocm9vdCkge1xuXG5cdC8qKlxuXHQgKiBQb3B1bGF0ZSBmb3JtIGZpZWxkcyBmcm9tIGEgSlNPTiBvYmplY3QuXG5cdCAqXG5cdCAqIEBwYXJhbSBmb3JtIG9iamVjdCBUaGUgZm9ybSBlbGVtZW50IGNvbnRhaW5pbmcgeW91ciBpbnB1dCBmaWVsZHMuXG5cdCAqIEBwYXJhbSBkYXRhIGFycmF5IEpTT04gZGF0YSB0byBwb3B1bGF0ZSB0aGUgZmllbGRzIHdpdGguXG5cdCAqIEBwYXJhbSBiYXNlbmFtZSBzdHJpbmcgT3B0aW9uYWwgYmFzZW5hbWUgd2hpY2ggaXMgYWRkZWQgdG8gYG5hbWVgIGF0dHJpYnV0ZXNcblx0ICovXG5cdHZhciBwb3B1bGF0ZSA9IGZ1bmN0aW9uKCBmb3JtLCBkYXRhLCBiYXNlbmFtZSkge1xuXG5cdFx0Zm9yKHZhciBrZXkgaW4gZGF0YSkge1xuXG5cdFx0XHRpZiggISBkYXRhLmhhc093blByb3BlcnR5KCBrZXkgKSApIHtcblx0XHRcdFx0Y29udGludWU7XG5cdFx0XHR9XG5cblx0XHRcdHZhciBuYW1lID0ga2V5O1xuXHRcdFx0dmFyIHZhbHVlID0gZGF0YVtrZXldO1xuXG5cdFx0XHQvLyBoYW5kbGUgYXJyYXkgbmFtZSBhdHRyaWJ1dGVzXG5cdFx0XHRpZih0eXBlb2YoYmFzZW5hbWUpICE9PSBcInVuZGVmaW5lZFwiKSB7XG5cdFx0XHRcdG5hbWUgPSBiYXNlbmFtZSArIFwiW1wiICsga2V5ICsgXCJdXCI7XG5cdFx0XHR9XG5cblx0XHRcdGlmKHZhbHVlLmNvbnN0cnVjdG9yID09PSBBcnJheSkge1xuXHRcdFx0XHRuYW1lICs9ICdbXSc7XG5cdFx0XHR9IGVsc2UgaWYodHlwZW9mIHZhbHVlID09IFwib2JqZWN0XCIpIHtcblx0XHRcdFx0cG9wdWxhdGUoIGZvcm0sIHZhbHVlLCBuYW1lKTtcblx0XHRcdFx0Y29udGludWU7XG5cdFx0XHR9XG5cblx0XHRcdC8vIG9ubHkgcHJvY2VlZCBpZiBlbGVtZW50IGlzIHNldFxuXHRcdFx0dmFyIGVsZW1lbnQgPSBmb3JtLmVsZW1lbnRzLm5hbWVkSXRlbSggbmFtZSApO1xuXHRcdFx0aWYoICEgZWxlbWVudCApIHtcblx0XHRcdFx0Y29udGludWU7XG5cdFx0XHR9XG5cblx0XHRcdHZhciB0eXBlID0gZWxlbWVudC50eXBlIHx8IGVsZW1lbnRbMF0udHlwZTtcblxuXHRcdFx0c3dpdGNoKHR5cGUgKSB7XG5cdFx0XHRcdGRlZmF1bHQ6XG5cdFx0XHRcdFx0ZWxlbWVudC52YWx1ZSA9IHZhbHVlO1xuXHRcdFx0XHRcdGJyZWFrO1xuXG5cdFx0XHRcdGNhc2UgJ3JhZGlvJzpcblx0XHRcdFx0Y2FzZSAnY2hlY2tib3gnOlxuXHRcdFx0XHRcdGZvciggdmFyIGo9MDsgaiA8IGVsZW1lbnQubGVuZ3RoOyBqKysgKSB7XG5cdFx0XHRcdFx0XHRlbGVtZW50W2pdLmNoZWNrZWQgPSAoIHZhbHVlLmluZGV4T2YoZWxlbWVudFtqXS52YWx1ZSkgPiAtMSApO1xuXHRcdFx0XHRcdH1cblx0XHRcdFx0XHRicmVhaztcblxuXHRcdFx0XHRjYXNlICdzZWxlY3QtbXVsdGlwbGUnOlxuXHRcdFx0XHRcdHZhciB2YWx1ZXMgPSB2YWx1ZS5jb25zdHJ1Y3RvciA9PSBBcnJheSA/IHZhbHVlIDogW3ZhbHVlXTtcblxuXHRcdFx0XHRcdGZvcih2YXIgayA9IDA7IGsgPCBlbGVtZW50Lm9wdGlvbnMubGVuZ3RoOyBrKyspIHtcblx0XHRcdFx0XHRcdGVsZW1lbnQub3B0aW9uc1trXS5zZWxlY3RlZCB8PSAodmFsdWVzLmluZGV4T2YoZWxlbWVudC5vcHRpb25zW2tdLnZhbHVlKSA+IC0xICk7XG5cdFx0XHRcdFx0fVxuXHRcdFx0XHRcdGJyZWFrO1xuXG5cdFx0XHRcdGNhc2UgJ3NlbGVjdCc6XG5cdFx0XHRcdGNhc2UgJ3NlbGVjdC1vbmUnOlxuXHRcdFx0XHRcdGVsZW1lbnQudmFsdWUgPSB2YWx1ZS50b1N0cmluZygpIHx8IHZhbHVlO1xuXHRcdFx0XHRcdGJyZWFrO1xuXG5cdFx0XHR9XG5cblx0XHR9XG5cblx0fTtcblxuXHQvLyBQbGF5IG5pY2Ugd2l0aCBBTUQsIENvbW1vbkpTIG9yIGEgcGxhaW4gZ2xvYmFsIG9iamVjdC5cblx0aWYgKCB0eXBlb2YgZGVmaW5lID09ICdmdW5jdGlvbicgJiYgdHlwZW9mIGRlZmluZS5hbWQgPT0gJ29iamVjdCcgJiYgZGVmaW5lLmFtZCApIHtcblx0XHRkZWZpbmUoZnVuY3Rpb24oKSB7XG5cdFx0XHRyZXR1cm4gcG9wdWxhdGU7XG5cdFx0fSk7XG5cdH1cdGVsc2UgaWYgKCB0eXBlb2YgbW9kdWxlICE9PSAndW5kZWZpbmVkJyAmJiBtb2R1bGUuZXhwb3J0cyApIHtcblx0XHRtb2R1bGUuZXhwb3J0cyA9IHBvcHVsYXRlO1xuXHR9IGVsc2Uge1xuXHRcdHJvb3QucG9wdWxhdGUgPSBwb3B1bGF0ZTtcblx0fVxuXG59KHRoaXMpKTsiLCIvKiFcbiAqIEV2ZW50RW1pdHRlciB2NC4yLjExIC0gZ2l0LmlvL2VlXG4gKiBVbmxpY2Vuc2UgLSBodHRwOi8vdW5saWNlbnNlLm9yZy9cbiAqIE9saXZlciBDYWxkd2VsbCAtIGh0dHA6Ly9vbGkubWUudWsvXG4gKiBAcHJlc2VydmVcbiAqL1xuXG47KGZ1bmN0aW9uICgpIHtcbiAgICAndXNlIHN0cmljdCc7XG5cbiAgICAvKipcbiAgICAgKiBDbGFzcyBmb3IgbWFuYWdpbmcgZXZlbnRzLlxuICAgICAqIENhbiBiZSBleHRlbmRlZCB0byBwcm92aWRlIGV2ZW50IGZ1bmN0aW9uYWxpdHkgaW4gb3RoZXIgY2xhc3Nlcy5cbiAgICAgKlxuICAgICAqIEBjbGFzcyBFdmVudEVtaXR0ZXIgTWFuYWdlcyBldmVudCByZWdpc3RlcmluZyBhbmQgZW1pdHRpbmcuXG4gICAgICovXG4gICAgZnVuY3Rpb24gRXZlbnRFbWl0dGVyKCkge31cblxuICAgIC8vIFNob3J0Y3V0cyB0byBpbXByb3ZlIHNwZWVkIGFuZCBzaXplXG4gICAgdmFyIHByb3RvID0gRXZlbnRFbWl0dGVyLnByb3RvdHlwZTtcbiAgICB2YXIgZXhwb3J0cyA9IHRoaXM7XG4gICAgdmFyIG9yaWdpbmFsR2xvYmFsVmFsdWUgPSBleHBvcnRzLkV2ZW50RW1pdHRlcjtcblxuICAgIC8qKlxuICAgICAqIEZpbmRzIHRoZSBpbmRleCBvZiB0aGUgbGlzdGVuZXIgZm9yIHRoZSBldmVudCBpbiBpdHMgc3RvcmFnZSBhcnJheS5cbiAgICAgKlxuICAgICAqIEBwYXJhbSB7RnVuY3Rpb25bXX0gbGlzdGVuZXJzIEFycmF5IG9mIGxpc3RlbmVycyB0byBzZWFyY2ggdGhyb3VnaC5cbiAgICAgKiBAcGFyYW0ge0Z1bmN0aW9ufSBsaXN0ZW5lciBNZXRob2QgdG8gbG9vayBmb3IuXG4gICAgICogQHJldHVybiB7TnVtYmVyfSBJbmRleCBvZiB0aGUgc3BlY2lmaWVkIGxpc3RlbmVyLCAtMSBpZiBub3QgZm91bmRcbiAgICAgKiBAYXBpIHByaXZhdGVcbiAgICAgKi9cbiAgICBmdW5jdGlvbiBpbmRleE9mTGlzdGVuZXIobGlzdGVuZXJzLCBsaXN0ZW5lcikge1xuICAgICAgICB2YXIgaSA9IGxpc3RlbmVycy5sZW5ndGg7XG4gICAgICAgIHdoaWxlIChpLS0pIHtcbiAgICAgICAgICAgIGlmIChsaXN0ZW5lcnNbaV0ubGlzdGVuZXIgPT09IGxpc3RlbmVyKSB7XG4gICAgICAgICAgICAgICAgcmV0dXJuIGk7XG4gICAgICAgICAgICB9XG4gICAgICAgIH1cblxuICAgICAgICByZXR1cm4gLTE7XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogQWxpYXMgYSBtZXRob2Qgd2hpbGUga2VlcGluZyB0aGUgY29udGV4dCBjb3JyZWN0LCB0byBhbGxvdyBmb3Igb3ZlcndyaXRpbmcgb2YgdGFyZ2V0IG1ldGhvZC5cbiAgICAgKlxuICAgICAqIEBwYXJhbSB7U3RyaW5nfSBuYW1lIFRoZSBuYW1lIG9mIHRoZSB0YXJnZXQgbWV0aG9kLlxuICAgICAqIEByZXR1cm4ge0Z1bmN0aW9ufSBUaGUgYWxpYXNlZCBtZXRob2RcbiAgICAgKiBAYXBpIHByaXZhdGVcbiAgICAgKi9cbiAgICBmdW5jdGlvbiBhbGlhcyhuYW1lKSB7XG4gICAgICAgIHJldHVybiBmdW5jdGlvbiBhbGlhc0Nsb3N1cmUoKSB7XG4gICAgICAgICAgICByZXR1cm4gdGhpc1tuYW1lXS5hcHBseSh0aGlzLCBhcmd1bWVudHMpO1xuICAgICAgICB9O1xuICAgIH1cblxuICAgIC8qKlxuICAgICAqIFJldHVybnMgdGhlIGxpc3RlbmVyIGFycmF5IGZvciB0aGUgc3BlY2lmaWVkIGV2ZW50LlxuICAgICAqIFdpbGwgaW5pdGlhbGlzZSB0aGUgZXZlbnQgb2JqZWN0IGFuZCBsaXN0ZW5lciBhcnJheXMgaWYgcmVxdWlyZWQuXG4gICAgICogV2lsbCByZXR1cm4gYW4gb2JqZWN0IGlmIHlvdSB1c2UgYSByZWdleCBzZWFyY2guIFRoZSBvYmplY3QgY29udGFpbnMga2V5cyBmb3IgZWFjaCBtYXRjaGVkIGV2ZW50LiBTbyAvYmFbcnpdLyBtaWdodCByZXR1cm4gYW4gb2JqZWN0IGNvbnRhaW5pbmcgYmFyIGFuZCBiYXouIEJ1dCBvbmx5IGlmIHlvdSBoYXZlIGVpdGhlciBkZWZpbmVkIHRoZW0gd2l0aCBkZWZpbmVFdmVudCBvciBhZGRlZCBzb21lIGxpc3RlbmVycyB0byB0aGVtLlxuICAgICAqIEVhY2ggcHJvcGVydHkgaW4gdGhlIG9iamVjdCByZXNwb25zZSBpcyBhbiBhcnJheSBvZiBsaXN0ZW5lciBmdW5jdGlvbnMuXG4gICAgICpcbiAgICAgKiBAcGFyYW0ge1N0cmluZ3xSZWdFeHB9IGV2dCBOYW1lIG9mIHRoZSBldmVudCB0byByZXR1cm4gdGhlIGxpc3RlbmVycyBmcm9tLlxuICAgICAqIEByZXR1cm4ge0Z1bmN0aW9uW118T2JqZWN0fSBBbGwgbGlzdGVuZXIgZnVuY3Rpb25zIGZvciB0aGUgZXZlbnQuXG4gICAgICovXG4gICAgcHJvdG8uZ2V0TGlzdGVuZXJzID0gZnVuY3Rpb24gZ2V0TGlzdGVuZXJzKGV2dCkge1xuICAgICAgICB2YXIgZXZlbnRzID0gdGhpcy5fZ2V0RXZlbnRzKCk7XG4gICAgICAgIHZhciByZXNwb25zZTtcbiAgICAgICAgdmFyIGtleTtcblxuICAgICAgICAvLyBSZXR1cm4gYSBjb25jYXRlbmF0ZWQgYXJyYXkgb2YgYWxsIG1hdGNoaW5nIGV2ZW50cyBpZlxuICAgICAgICAvLyB0aGUgc2VsZWN0b3IgaXMgYSByZWd1bGFyIGV4cHJlc3Npb24uXG4gICAgICAgIGlmIChldnQgaW5zdGFuY2VvZiBSZWdFeHApIHtcbiAgICAgICAgICAgIHJlc3BvbnNlID0ge307XG4gICAgICAgICAgICBmb3IgKGtleSBpbiBldmVudHMpIHtcbiAgICAgICAgICAgICAgICBpZiAoZXZlbnRzLmhhc093blByb3BlcnR5KGtleSkgJiYgZXZ0LnRlc3Qoa2V5KSkge1xuICAgICAgICAgICAgICAgICAgICByZXNwb25zZVtrZXldID0gZXZlbnRzW2tleV07XG4gICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgfVxuICAgICAgICB9XG4gICAgICAgIGVsc2Uge1xuICAgICAgICAgICAgcmVzcG9uc2UgPSBldmVudHNbZXZ0XSB8fCAoZXZlbnRzW2V2dF0gPSBbXSk7XG4gICAgICAgIH1cblxuICAgICAgICByZXR1cm4gcmVzcG9uc2U7XG4gICAgfTtcblxuICAgIC8qKlxuICAgICAqIFRha2VzIGEgbGlzdCBvZiBsaXN0ZW5lciBvYmplY3RzIGFuZCBmbGF0dGVucyBpdCBpbnRvIGEgbGlzdCBvZiBsaXN0ZW5lciBmdW5jdGlvbnMuXG4gICAgICpcbiAgICAgKiBAcGFyYW0ge09iamVjdFtdfSBsaXN0ZW5lcnMgUmF3IGxpc3RlbmVyIG9iamVjdHMuXG4gICAgICogQHJldHVybiB7RnVuY3Rpb25bXX0gSnVzdCB0aGUgbGlzdGVuZXIgZnVuY3Rpb25zLlxuICAgICAqL1xuICAgIHByb3RvLmZsYXR0ZW5MaXN0ZW5lcnMgPSBmdW5jdGlvbiBmbGF0dGVuTGlzdGVuZXJzKGxpc3RlbmVycykge1xuICAgICAgICB2YXIgZmxhdExpc3RlbmVycyA9IFtdO1xuICAgICAgICB2YXIgaTtcblxuICAgICAgICBmb3IgKGkgPSAwOyBpIDwgbGlzdGVuZXJzLmxlbmd0aDsgaSArPSAxKSB7XG4gICAgICAgICAgICBmbGF0TGlzdGVuZXJzLnB1c2gobGlzdGVuZXJzW2ldLmxpc3RlbmVyKTtcbiAgICAgICAgfVxuXG4gICAgICAgIHJldHVybiBmbGF0TGlzdGVuZXJzO1xuICAgIH07XG5cbiAgICAvKipcbiAgICAgKiBGZXRjaGVzIHRoZSByZXF1ZXN0ZWQgbGlzdGVuZXJzIHZpYSBnZXRMaXN0ZW5lcnMgYnV0IHdpbGwgYWx3YXlzIHJldHVybiB0aGUgcmVzdWx0cyBpbnNpZGUgYW4gb2JqZWN0LiBUaGlzIGlzIG1haW5seSBmb3IgaW50ZXJuYWwgdXNlIGJ1dCBvdGhlcnMgbWF5IGZpbmQgaXQgdXNlZnVsLlxuICAgICAqXG4gICAgICogQHBhcmFtIHtTdHJpbmd8UmVnRXhwfSBldnQgTmFtZSBvZiB0aGUgZXZlbnQgdG8gcmV0dXJuIHRoZSBsaXN0ZW5lcnMgZnJvbS5cbiAgICAgKiBAcmV0dXJuIHtPYmplY3R9IEFsbCBsaXN0ZW5lciBmdW5jdGlvbnMgZm9yIGFuIGV2ZW50IGluIGFuIG9iamVjdC5cbiAgICAgKi9cbiAgICBwcm90by5nZXRMaXN0ZW5lcnNBc09iamVjdCA9IGZ1bmN0aW9uIGdldExpc3RlbmVyc0FzT2JqZWN0KGV2dCkge1xuICAgICAgICB2YXIgbGlzdGVuZXJzID0gdGhpcy5nZXRMaXN0ZW5lcnMoZXZ0KTtcbiAgICAgICAgdmFyIHJlc3BvbnNlO1xuXG4gICAgICAgIGlmIChsaXN0ZW5lcnMgaW5zdGFuY2VvZiBBcnJheSkge1xuICAgICAgICAgICAgcmVzcG9uc2UgPSB7fTtcbiAgICAgICAgICAgIHJlc3BvbnNlW2V2dF0gPSBsaXN0ZW5lcnM7XG4gICAgICAgIH1cblxuICAgICAgICByZXR1cm4gcmVzcG9uc2UgfHwgbGlzdGVuZXJzO1xuICAgIH07XG5cbiAgICAvKipcbiAgICAgKiBBZGRzIGEgbGlzdGVuZXIgZnVuY3Rpb24gdG8gdGhlIHNwZWNpZmllZCBldmVudC5cbiAgICAgKiBUaGUgbGlzdGVuZXIgd2lsbCBub3QgYmUgYWRkZWQgaWYgaXQgaXMgYSBkdXBsaWNhdGUuXG4gICAgICogSWYgdGhlIGxpc3RlbmVyIHJldHVybnMgdHJ1ZSB0aGVuIGl0IHdpbGwgYmUgcmVtb3ZlZCBhZnRlciBpdCBpcyBjYWxsZWQuXG4gICAgICogSWYgeW91IHBhc3MgYSByZWd1bGFyIGV4cHJlc3Npb24gYXMgdGhlIGV2ZW50IG5hbWUgdGhlbiB0aGUgbGlzdGVuZXIgd2lsbCBiZSBhZGRlZCB0byBhbGwgZXZlbnRzIHRoYXQgbWF0Y2ggaXQuXG4gICAgICpcbiAgICAgKiBAcGFyYW0ge1N0cmluZ3xSZWdFeHB9IGV2dCBOYW1lIG9mIHRoZSBldmVudCB0byBhdHRhY2ggdGhlIGxpc3RlbmVyIHRvLlxuICAgICAqIEBwYXJhbSB7RnVuY3Rpb259IGxpc3RlbmVyIE1ldGhvZCB0byBiZSBjYWxsZWQgd2hlbiB0aGUgZXZlbnQgaXMgZW1pdHRlZC4gSWYgdGhlIGZ1bmN0aW9uIHJldHVybnMgdHJ1ZSB0aGVuIGl0IHdpbGwgYmUgcmVtb3ZlZCBhZnRlciBjYWxsaW5nLlxuICAgICAqIEByZXR1cm4ge09iamVjdH0gQ3VycmVudCBpbnN0YW5jZSBvZiBFdmVudEVtaXR0ZXIgZm9yIGNoYWluaW5nLlxuICAgICAqL1xuICAgIHByb3RvLmFkZExpc3RlbmVyID0gZnVuY3Rpb24gYWRkTGlzdGVuZXIoZXZ0LCBsaXN0ZW5lcikge1xuICAgICAgICB2YXIgbGlzdGVuZXJzID0gdGhpcy5nZXRMaXN0ZW5lcnNBc09iamVjdChldnQpO1xuICAgICAgICB2YXIgbGlzdGVuZXJJc1dyYXBwZWQgPSB0eXBlb2YgbGlzdGVuZXIgPT09ICdvYmplY3QnO1xuICAgICAgICB2YXIga2V5O1xuXG4gICAgICAgIGZvciAoa2V5IGluIGxpc3RlbmVycykge1xuICAgICAgICAgICAgaWYgKGxpc3RlbmVycy5oYXNPd25Qcm9wZXJ0eShrZXkpICYmIGluZGV4T2ZMaXN0ZW5lcihsaXN0ZW5lcnNba2V5XSwgbGlzdGVuZXIpID09PSAtMSkge1xuICAgICAgICAgICAgICAgIGxpc3RlbmVyc1trZXldLnB1c2gobGlzdGVuZXJJc1dyYXBwZWQgPyBsaXN0ZW5lciA6IHtcbiAgICAgICAgICAgICAgICAgICAgbGlzdGVuZXI6IGxpc3RlbmVyLFxuICAgICAgICAgICAgICAgICAgICBvbmNlOiBmYWxzZVxuICAgICAgICAgICAgICAgIH0pO1xuICAgICAgICAgICAgfVxuICAgICAgICB9XG5cbiAgICAgICAgcmV0dXJuIHRoaXM7XG4gICAgfTtcblxuICAgIC8qKlxuICAgICAqIEFsaWFzIG9mIGFkZExpc3RlbmVyXG4gICAgICovXG4gICAgcHJvdG8ub24gPSBhbGlhcygnYWRkTGlzdGVuZXInKTtcblxuICAgIC8qKlxuICAgICAqIFNlbWktYWxpYXMgb2YgYWRkTGlzdGVuZXIuIEl0IHdpbGwgYWRkIGEgbGlzdGVuZXIgdGhhdCB3aWxsIGJlXG4gICAgICogYXV0b21hdGljYWxseSByZW1vdmVkIGFmdGVyIGl0cyBmaXJzdCBleGVjdXRpb24uXG4gICAgICpcbiAgICAgKiBAcGFyYW0ge1N0cmluZ3xSZWdFeHB9IGV2dCBOYW1lIG9mIHRoZSBldmVudCB0byBhdHRhY2ggdGhlIGxpc3RlbmVyIHRvLlxuICAgICAqIEBwYXJhbSB7RnVuY3Rpb259IGxpc3RlbmVyIE1ldGhvZCB0byBiZSBjYWxsZWQgd2hlbiB0aGUgZXZlbnQgaXMgZW1pdHRlZC4gSWYgdGhlIGZ1bmN0aW9uIHJldHVybnMgdHJ1ZSB0aGVuIGl0IHdpbGwgYmUgcmVtb3ZlZCBhZnRlciBjYWxsaW5nLlxuICAgICAqIEByZXR1cm4ge09iamVjdH0gQ3VycmVudCBpbnN0YW5jZSBvZiBFdmVudEVtaXR0ZXIgZm9yIGNoYWluaW5nLlxuICAgICAqL1xuICAgIHByb3RvLmFkZE9uY2VMaXN0ZW5lciA9IGZ1bmN0aW9uIGFkZE9uY2VMaXN0ZW5lcihldnQsIGxpc3RlbmVyKSB7XG4gICAgICAgIHJldHVybiB0aGlzLmFkZExpc3RlbmVyKGV2dCwge1xuICAgICAgICAgICAgbGlzdGVuZXI6IGxpc3RlbmVyLFxuICAgICAgICAgICAgb25jZTogdHJ1ZVxuICAgICAgICB9KTtcbiAgICB9O1xuXG4gICAgLyoqXG4gICAgICogQWxpYXMgb2YgYWRkT25jZUxpc3RlbmVyLlxuICAgICAqL1xuICAgIHByb3RvLm9uY2UgPSBhbGlhcygnYWRkT25jZUxpc3RlbmVyJyk7XG5cbiAgICAvKipcbiAgICAgKiBEZWZpbmVzIGFuIGV2ZW50IG5hbWUuIFRoaXMgaXMgcmVxdWlyZWQgaWYgeW91IHdhbnQgdG8gdXNlIGEgcmVnZXggdG8gYWRkIGEgbGlzdGVuZXIgdG8gbXVsdGlwbGUgZXZlbnRzIGF0IG9uY2UuIElmIHlvdSBkb24ndCBkbyB0aGlzIHRoZW4gaG93IGRvIHlvdSBleHBlY3QgaXQgdG8ga25vdyB3aGF0IGV2ZW50IHRvIGFkZCB0bz8gU2hvdWxkIGl0IGp1c3QgYWRkIHRvIGV2ZXJ5IHBvc3NpYmxlIG1hdGNoIGZvciBhIHJlZ2V4PyBOby4gVGhhdCBpcyBzY2FyeSBhbmQgYmFkLlxuICAgICAqIFlvdSBuZWVkIHRvIHRlbGwgaXQgd2hhdCBldmVudCBuYW1lcyBzaG91bGQgYmUgbWF0Y2hlZCBieSBhIHJlZ2V4LlxuICAgICAqXG4gICAgICogQHBhcmFtIHtTdHJpbmd9IGV2dCBOYW1lIG9mIHRoZSBldmVudCB0byBjcmVhdGUuXG4gICAgICogQHJldHVybiB7T2JqZWN0fSBDdXJyZW50IGluc3RhbmNlIG9mIEV2ZW50RW1pdHRlciBmb3IgY2hhaW5pbmcuXG4gICAgICovXG4gICAgcHJvdG8uZGVmaW5lRXZlbnQgPSBmdW5jdGlvbiBkZWZpbmVFdmVudChldnQpIHtcbiAgICAgICAgdGhpcy5nZXRMaXN0ZW5lcnMoZXZ0KTtcbiAgICAgICAgcmV0dXJuIHRoaXM7XG4gICAgfTtcblxuICAgIC8qKlxuICAgICAqIFVzZXMgZGVmaW5lRXZlbnQgdG8gZGVmaW5lIG11bHRpcGxlIGV2ZW50cy5cbiAgICAgKlxuICAgICAqIEBwYXJhbSB7U3RyaW5nW119IGV2dHMgQW4gYXJyYXkgb2YgZXZlbnQgbmFtZXMgdG8gZGVmaW5lLlxuICAgICAqIEByZXR1cm4ge09iamVjdH0gQ3VycmVudCBpbnN0YW5jZSBvZiBFdmVudEVtaXR0ZXIgZm9yIGNoYWluaW5nLlxuICAgICAqL1xuICAgIHByb3RvLmRlZmluZUV2ZW50cyA9IGZ1bmN0aW9uIGRlZmluZUV2ZW50cyhldnRzKSB7XG4gICAgICAgIGZvciAodmFyIGkgPSAwOyBpIDwgZXZ0cy5sZW5ndGg7IGkgKz0gMSkge1xuICAgICAgICAgICAgdGhpcy5kZWZpbmVFdmVudChldnRzW2ldKTtcbiAgICAgICAgfVxuICAgICAgICByZXR1cm4gdGhpcztcbiAgICB9O1xuXG4gICAgLyoqXG4gICAgICogUmVtb3ZlcyBhIGxpc3RlbmVyIGZ1bmN0aW9uIGZyb20gdGhlIHNwZWNpZmllZCBldmVudC5cbiAgICAgKiBXaGVuIHBhc3NlZCBhIHJlZ3VsYXIgZXhwcmVzc2lvbiBhcyB0aGUgZXZlbnQgbmFtZSwgaXQgd2lsbCByZW1vdmUgdGhlIGxpc3RlbmVyIGZyb20gYWxsIGV2ZW50cyB0aGF0IG1hdGNoIGl0LlxuICAgICAqXG4gICAgICogQHBhcmFtIHtTdHJpbmd8UmVnRXhwfSBldnQgTmFtZSBvZiB0aGUgZXZlbnQgdG8gcmVtb3ZlIHRoZSBsaXN0ZW5lciBmcm9tLlxuICAgICAqIEBwYXJhbSB7RnVuY3Rpb259IGxpc3RlbmVyIE1ldGhvZCB0byByZW1vdmUgZnJvbSB0aGUgZXZlbnQuXG4gICAgICogQHJldHVybiB7T2JqZWN0fSBDdXJyZW50IGluc3RhbmNlIG9mIEV2ZW50RW1pdHRlciBmb3IgY2hhaW5pbmcuXG4gICAgICovXG4gICAgcHJvdG8ucmVtb3ZlTGlzdGVuZXIgPSBmdW5jdGlvbiByZW1vdmVMaXN0ZW5lcihldnQsIGxpc3RlbmVyKSB7XG4gICAgICAgIHZhciBsaXN0ZW5lcnMgPSB0aGlzLmdldExpc3RlbmVyc0FzT2JqZWN0KGV2dCk7XG4gICAgICAgIHZhciBpbmRleDtcbiAgICAgICAgdmFyIGtleTtcblxuICAgICAgICBmb3IgKGtleSBpbiBsaXN0ZW5lcnMpIHtcbiAgICAgICAgICAgIGlmIChsaXN0ZW5lcnMuaGFzT3duUHJvcGVydHkoa2V5KSkge1xuICAgICAgICAgICAgICAgIGluZGV4ID0gaW5kZXhPZkxpc3RlbmVyKGxpc3RlbmVyc1trZXldLCBsaXN0ZW5lcik7XG5cbiAgICAgICAgICAgICAgICBpZiAoaW5kZXggIT09IC0xKSB7XG4gICAgICAgICAgICAgICAgICAgIGxpc3RlbmVyc1trZXldLnNwbGljZShpbmRleCwgMSk7XG4gICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgfVxuICAgICAgICB9XG5cbiAgICAgICAgcmV0dXJuIHRoaXM7XG4gICAgfTtcblxuICAgIC8qKlxuICAgICAqIEFsaWFzIG9mIHJlbW92ZUxpc3RlbmVyXG4gICAgICovXG4gICAgcHJvdG8ub2ZmID0gYWxpYXMoJ3JlbW92ZUxpc3RlbmVyJyk7XG5cbiAgICAvKipcbiAgICAgKiBBZGRzIGxpc3RlbmVycyBpbiBidWxrIHVzaW5nIHRoZSBtYW5pcHVsYXRlTGlzdGVuZXJzIG1ldGhvZC5cbiAgICAgKiBJZiB5b3UgcGFzcyBhbiBvYmplY3QgYXMgdGhlIHNlY29uZCBhcmd1bWVudCB5b3UgY2FuIGFkZCB0byBtdWx0aXBsZSBldmVudHMgYXQgb25jZS4gVGhlIG9iamVjdCBzaG91bGQgY29udGFpbiBrZXkgdmFsdWUgcGFpcnMgb2YgZXZlbnRzIGFuZCBsaXN0ZW5lcnMgb3IgbGlzdGVuZXIgYXJyYXlzLiBZb3UgY2FuIGFsc28gcGFzcyBpdCBhbiBldmVudCBuYW1lIGFuZCBhbiBhcnJheSBvZiBsaXN0ZW5lcnMgdG8gYmUgYWRkZWQuXG4gICAgICogWW91IGNhbiBhbHNvIHBhc3MgaXQgYSByZWd1bGFyIGV4cHJlc3Npb24gdG8gYWRkIHRoZSBhcnJheSBvZiBsaXN0ZW5lcnMgdG8gYWxsIGV2ZW50cyB0aGF0IG1hdGNoIGl0LlxuICAgICAqIFllYWgsIHRoaXMgZnVuY3Rpb24gZG9lcyBxdWl0ZSBhIGJpdC4gVGhhdCdzIHByb2JhYmx5IGEgYmFkIHRoaW5nLlxuICAgICAqXG4gICAgICogQHBhcmFtIHtTdHJpbmd8T2JqZWN0fFJlZ0V4cH0gZXZ0IEFuIGV2ZW50IG5hbWUgaWYgeW91IHdpbGwgcGFzcyBhbiBhcnJheSBvZiBsaXN0ZW5lcnMgbmV4dC4gQW4gb2JqZWN0IGlmIHlvdSB3aXNoIHRvIGFkZCB0byBtdWx0aXBsZSBldmVudHMgYXQgb25jZS5cbiAgICAgKiBAcGFyYW0ge0Z1bmN0aW9uW119IFtsaXN0ZW5lcnNdIEFuIG9wdGlvbmFsIGFycmF5IG9mIGxpc3RlbmVyIGZ1bmN0aW9ucyB0byBhZGQuXG4gICAgICogQHJldHVybiB7T2JqZWN0fSBDdXJyZW50IGluc3RhbmNlIG9mIEV2ZW50RW1pdHRlciBmb3IgY2hhaW5pbmcuXG4gICAgICovXG4gICAgcHJvdG8uYWRkTGlzdGVuZXJzID0gZnVuY3Rpb24gYWRkTGlzdGVuZXJzKGV2dCwgbGlzdGVuZXJzKSB7XG4gICAgICAgIC8vIFBhc3MgdGhyb3VnaCB0byBtYW5pcHVsYXRlTGlzdGVuZXJzXG4gICAgICAgIHJldHVybiB0aGlzLm1hbmlwdWxhdGVMaXN0ZW5lcnMoZmFsc2UsIGV2dCwgbGlzdGVuZXJzKTtcbiAgICB9O1xuXG4gICAgLyoqXG4gICAgICogUmVtb3ZlcyBsaXN0ZW5lcnMgaW4gYnVsayB1c2luZyB0aGUgbWFuaXB1bGF0ZUxpc3RlbmVycyBtZXRob2QuXG4gICAgICogSWYgeW91IHBhc3MgYW4gb2JqZWN0IGFzIHRoZSBzZWNvbmQgYXJndW1lbnQgeW91IGNhbiByZW1vdmUgZnJvbSBtdWx0aXBsZSBldmVudHMgYXQgb25jZS4gVGhlIG9iamVjdCBzaG91bGQgY29udGFpbiBrZXkgdmFsdWUgcGFpcnMgb2YgZXZlbnRzIGFuZCBsaXN0ZW5lcnMgb3IgbGlzdGVuZXIgYXJyYXlzLlxuICAgICAqIFlvdSBjYW4gYWxzbyBwYXNzIGl0IGFuIGV2ZW50IG5hbWUgYW5kIGFuIGFycmF5IG9mIGxpc3RlbmVycyB0byBiZSByZW1vdmVkLlxuICAgICAqIFlvdSBjYW4gYWxzbyBwYXNzIGl0IGEgcmVndWxhciBleHByZXNzaW9uIHRvIHJlbW92ZSB0aGUgbGlzdGVuZXJzIGZyb20gYWxsIGV2ZW50cyB0aGF0IG1hdGNoIGl0LlxuICAgICAqXG4gICAgICogQHBhcmFtIHtTdHJpbmd8T2JqZWN0fFJlZ0V4cH0gZXZ0IEFuIGV2ZW50IG5hbWUgaWYgeW91IHdpbGwgcGFzcyBhbiBhcnJheSBvZiBsaXN0ZW5lcnMgbmV4dC4gQW4gb2JqZWN0IGlmIHlvdSB3aXNoIHRvIHJlbW92ZSBmcm9tIG11bHRpcGxlIGV2ZW50cyBhdCBvbmNlLlxuICAgICAqIEBwYXJhbSB7RnVuY3Rpb25bXX0gW2xpc3RlbmVyc10gQW4gb3B0aW9uYWwgYXJyYXkgb2YgbGlzdGVuZXIgZnVuY3Rpb25zIHRvIHJlbW92ZS5cbiAgICAgKiBAcmV0dXJuIHtPYmplY3R9IEN1cnJlbnQgaW5zdGFuY2Ugb2YgRXZlbnRFbWl0dGVyIGZvciBjaGFpbmluZy5cbiAgICAgKi9cbiAgICBwcm90by5yZW1vdmVMaXN0ZW5lcnMgPSBmdW5jdGlvbiByZW1vdmVMaXN0ZW5lcnMoZXZ0LCBsaXN0ZW5lcnMpIHtcbiAgICAgICAgLy8gUGFzcyB0aHJvdWdoIHRvIG1hbmlwdWxhdGVMaXN0ZW5lcnNcbiAgICAgICAgcmV0dXJuIHRoaXMubWFuaXB1bGF0ZUxpc3RlbmVycyh0cnVlLCBldnQsIGxpc3RlbmVycyk7XG4gICAgfTtcblxuICAgIC8qKlxuICAgICAqIEVkaXRzIGxpc3RlbmVycyBpbiBidWxrLiBUaGUgYWRkTGlzdGVuZXJzIGFuZCByZW1vdmVMaXN0ZW5lcnMgbWV0aG9kcyBib3RoIHVzZSB0aGlzIHRvIGRvIHRoZWlyIGpvYi4gWW91IHNob3VsZCByZWFsbHkgdXNlIHRob3NlIGluc3RlYWQsIHRoaXMgaXMgYSBsaXR0bGUgbG93ZXIgbGV2ZWwuXG4gICAgICogVGhlIGZpcnN0IGFyZ3VtZW50IHdpbGwgZGV0ZXJtaW5lIGlmIHRoZSBsaXN0ZW5lcnMgYXJlIHJlbW92ZWQgKHRydWUpIG9yIGFkZGVkIChmYWxzZSkuXG4gICAgICogSWYgeW91IHBhc3MgYW4gb2JqZWN0IGFzIHRoZSBzZWNvbmQgYXJndW1lbnQgeW91IGNhbiBhZGQvcmVtb3ZlIGZyb20gbXVsdGlwbGUgZXZlbnRzIGF0IG9uY2UuIFRoZSBvYmplY3Qgc2hvdWxkIGNvbnRhaW4ga2V5IHZhbHVlIHBhaXJzIG9mIGV2ZW50cyBhbmQgbGlzdGVuZXJzIG9yIGxpc3RlbmVyIGFycmF5cy5cbiAgICAgKiBZb3UgY2FuIGFsc28gcGFzcyBpdCBhbiBldmVudCBuYW1lIGFuZCBhbiBhcnJheSBvZiBsaXN0ZW5lcnMgdG8gYmUgYWRkZWQvcmVtb3ZlZC5cbiAgICAgKiBZb3UgY2FuIGFsc28gcGFzcyBpdCBhIHJlZ3VsYXIgZXhwcmVzc2lvbiB0byBtYW5pcHVsYXRlIHRoZSBsaXN0ZW5lcnMgb2YgYWxsIGV2ZW50cyB0aGF0IG1hdGNoIGl0LlxuICAgICAqXG4gICAgICogQHBhcmFtIHtCb29sZWFufSByZW1vdmUgVHJ1ZSBpZiB5b3Ugd2FudCB0byByZW1vdmUgbGlzdGVuZXJzLCBmYWxzZSBpZiB5b3Ugd2FudCB0byBhZGQuXG4gICAgICogQHBhcmFtIHtTdHJpbmd8T2JqZWN0fFJlZ0V4cH0gZXZ0IEFuIGV2ZW50IG5hbWUgaWYgeW91IHdpbGwgcGFzcyBhbiBhcnJheSBvZiBsaXN0ZW5lcnMgbmV4dC4gQW4gb2JqZWN0IGlmIHlvdSB3aXNoIHRvIGFkZC9yZW1vdmUgZnJvbSBtdWx0aXBsZSBldmVudHMgYXQgb25jZS5cbiAgICAgKiBAcGFyYW0ge0Z1bmN0aW9uW119IFtsaXN0ZW5lcnNdIEFuIG9wdGlvbmFsIGFycmF5IG9mIGxpc3RlbmVyIGZ1bmN0aW9ucyB0byBhZGQvcmVtb3ZlLlxuICAgICAqIEByZXR1cm4ge09iamVjdH0gQ3VycmVudCBpbnN0YW5jZSBvZiBFdmVudEVtaXR0ZXIgZm9yIGNoYWluaW5nLlxuICAgICAqL1xuICAgIHByb3RvLm1hbmlwdWxhdGVMaXN0ZW5lcnMgPSBmdW5jdGlvbiBtYW5pcHVsYXRlTGlzdGVuZXJzKHJlbW92ZSwgZXZ0LCBsaXN0ZW5lcnMpIHtcbiAgICAgICAgdmFyIGk7XG4gICAgICAgIHZhciB2YWx1ZTtcbiAgICAgICAgdmFyIHNpbmdsZSA9IHJlbW92ZSA/IHRoaXMucmVtb3ZlTGlzdGVuZXIgOiB0aGlzLmFkZExpc3RlbmVyO1xuICAgICAgICB2YXIgbXVsdGlwbGUgPSByZW1vdmUgPyB0aGlzLnJlbW92ZUxpc3RlbmVycyA6IHRoaXMuYWRkTGlzdGVuZXJzO1xuXG4gICAgICAgIC8vIElmIGV2dCBpcyBhbiBvYmplY3QgdGhlbiBwYXNzIGVhY2ggb2YgaXRzIHByb3BlcnRpZXMgdG8gdGhpcyBtZXRob2RcbiAgICAgICAgaWYgKHR5cGVvZiBldnQgPT09ICdvYmplY3QnICYmICEoZXZ0IGluc3RhbmNlb2YgUmVnRXhwKSkge1xuICAgICAgICAgICAgZm9yIChpIGluIGV2dCkge1xuICAgICAgICAgICAgICAgIGlmIChldnQuaGFzT3duUHJvcGVydHkoaSkgJiYgKHZhbHVlID0gZXZ0W2ldKSkge1xuICAgICAgICAgICAgICAgICAgICAvLyBQYXNzIHRoZSBzaW5nbGUgbGlzdGVuZXIgc3RyYWlnaHQgdGhyb3VnaCB0byB0aGUgc2luZ3VsYXIgbWV0aG9kXG4gICAgICAgICAgICAgICAgICAgIGlmICh0eXBlb2YgdmFsdWUgPT09ICdmdW5jdGlvbicpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgIHNpbmdsZS5jYWxsKHRoaXMsIGksIHZhbHVlKTtcbiAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgICAgICBlbHNlIHtcbiAgICAgICAgICAgICAgICAgICAgICAgIC8vIE90aGVyd2lzZSBwYXNzIGJhY2sgdG8gdGhlIG11bHRpcGxlIGZ1bmN0aW9uXG4gICAgICAgICAgICAgICAgICAgICAgICBtdWx0aXBsZS5jYWxsKHRoaXMsIGksIHZhbHVlKTtcbiAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgIH1cbiAgICAgICAgfVxuICAgICAgICBlbHNlIHtcbiAgICAgICAgICAgIC8vIFNvIGV2dCBtdXN0IGJlIGEgc3RyaW5nXG4gICAgICAgICAgICAvLyBBbmQgbGlzdGVuZXJzIG11c3QgYmUgYW4gYXJyYXkgb2YgbGlzdGVuZXJzXG4gICAgICAgICAgICAvLyBMb29wIG92ZXIgaXQgYW5kIHBhc3MgZWFjaCBvbmUgdG8gdGhlIG11bHRpcGxlIG1ldGhvZFxuICAgICAgICAgICAgaSA9IGxpc3RlbmVycy5sZW5ndGg7XG4gICAgICAgICAgICB3aGlsZSAoaS0tKSB7XG4gICAgICAgICAgICAgICAgc2luZ2xlLmNhbGwodGhpcywgZXZ0LCBsaXN0ZW5lcnNbaV0pO1xuICAgICAgICAgICAgfVxuICAgICAgICB9XG5cbiAgICAgICAgcmV0dXJuIHRoaXM7XG4gICAgfTtcblxuICAgIC8qKlxuICAgICAqIFJlbW92ZXMgYWxsIGxpc3RlbmVycyBmcm9tIGEgc3BlY2lmaWVkIGV2ZW50LlxuICAgICAqIElmIHlvdSBkbyBub3Qgc3BlY2lmeSBhbiBldmVudCB0aGVuIGFsbCBsaXN0ZW5lcnMgd2lsbCBiZSByZW1vdmVkLlxuICAgICAqIFRoYXQgbWVhbnMgZXZlcnkgZXZlbnQgd2lsbCBiZSBlbXB0aWVkLlxuICAgICAqIFlvdSBjYW4gYWxzbyBwYXNzIGEgcmVnZXggdG8gcmVtb3ZlIGFsbCBldmVudHMgdGhhdCBtYXRjaCBpdC5cbiAgICAgKlxuICAgICAqIEBwYXJhbSB7U3RyaW5nfFJlZ0V4cH0gW2V2dF0gT3B0aW9uYWwgbmFtZSBvZiB0aGUgZXZlbnQgdG8gcmVtb3ZlIGFsbCBsaXN0ZW5lcnMgZm9yLiBXaWxsIHJlbW92ZSBmcm9tIGV2ZXJ5IGV2ZW50IGlmIG5vdCBwYXNzZWQuXG4gICAgICogQHJldHVybiB7T2JqZWN0fSBDdXJyZW50IGluc3RhbmNlIG9mIEV2ZW50RW1pdHRlciBmb3IgY2hhaW5pbmcuXG4gICAgICovXG4gICAgcHJvdG8ucmVtb3ZlRXZlbnQgPSBmdW5jdGlvbiByZW1vdmVFdmVudChldnQpIHtcbiAgICAgICAgdmFyIHR5cGUgPSB0eXBlb2YgZXZ0O1xuICAgICAgICB2YXIgZXZlbnRzID0gdGhpcy5fZ2V0RXZlbnRzKCk7XG4gICAgICAgIHZhciBrZXk7XG5cbiAgICAgICAgLy8gUmVtb3ZlIGRpZmZlcmVudCB0aGluZ3MgZGVwZW5kaW5nIG9uIHRoZSBzdGF0ZSBvZiBldnRcbiAgICAgICAgaWYgKHR5cGUgPT09ICdzdHJpbmcnKSB7XG4gICAgICAgICAgICAvLyBSZW1vdmUgYWxsIGxpc3RlbmVycyBmb3IgdGhlIHNwZWNpZmllZCBldmVudFxuICAgICAgICAgICAgZGVsZXRlIGV2ZW50c1tldnRdO1xuICAgICAgICB9XG4gICAgICAgIGVsc2UgaWYgKGV2dCBpbnN0YW5jZW9mIFJlZ0V4cCkge1xuICAgICAgICAgICAgLy8gUmVtb3ZlIGFsbCBldmVudHMgbWF0Y2hpbmcgdGhlIHJlZ2V4LlxuICAgICAgICAgICAgZm9yIChrZXkgaW4gZXZlbnRzKSB7XG4gICAgICAgICAgICAgICAgaWYgKGV2ZW50cy5oYXNPd25Qcm9wZXJ0eShrZXkpICYmIGV2dC50ZXN0KGtleSkpIHtcbiAgICAgICAgICAgICAgICAgICAgZGVsZXRlIGV2ZW50c1trZXldO1xuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgIH1cbiAgICAgICAgfVxuICAgICAgICBlbHNlIHtcbiAgICAgICAgICAgIC8vIFJlbW92ZSBhbGwgbGlzdGVuZXJzIGluIGFsbCBldmVudHNcbiAgICAgICAgICAgIGRlbGV0ZSB0aGlzLl9ldmVudHM7XG4gICAgICAgIH1cblxuICAgICAgICByZXR1cm4gdGhpcztcbiAgICB9O1xuXG4gICAgLyoqXG4gICAgICogQWxpYXMgb2YgcmVtb3ZlRXZlbnQuXG4gICAgICpcbiAgICAgKiBBZGRlZCB0byBtaXJyb3IgdGhlIG5vZGUgQVBJLlxuICAgICAqL1xuICAgIHByb3RvLnJlbW92ZUFsbExpc3RlbmVycyA9IGFsaWFzKCdyZW1vdmVFdmVudCcpO1xuXG4gICAgLyoqXG4gICAgICogRW1pdHMgYW4gZXZlbnQgb2YgeW91ciBjaG9pY2UuXG4gICAgICogV2hlbiBlbWl0dGVkLCBldmVyeSBsaXN0ZW5lciBhdHRhY2hlZCB0byB0aGF0IGV2ZW50IHdpbGwgYmUgZXhlY3V0ZWQuXG4gICAgICogSWYgeW91IHBhc3MgdGhlIG9wdGlvbmFsIGFyZ3VtZW50IGFycmF5IHRoZW4gdGhvc2UgYXJndW1lbnRzIHdpbGwgYmUgcGFzc2VkIHRvIGV2ZXJ5IGxpc3RlbmVyIHVwb24gZXhlY3V0aW9uLlxuICAgICAqIEJlY2F1c2UgaXQgdXNlcyBgYXBwbHlgLCB5b3VyIGFycmF5IG9mIGFyZ3VtZW50cyB3aWxsIGJlIHBhc3NlZCBhcyBpZiB5b3Ugd3JvdGUgdGhlbSBvdXQgc2VwYXJhdGVseS5cbiAgICAgKiBTbyB0aGV5IHdpbGwgbm90IGFycml2ZSB3aXRoaW4gdGhlIGFycmF5IG9uIHRoZSBvdGhlciBzaWRlLCB0aGV5IHdpbGwgYmUgc2VwYXJhdGUuXG4gICAgICogWW91IGNhbiBhbHNvIHBhc3MgYSByZWd1bGFyIGV4cHJlc3Npb24gdG8gZW1pdCB0byBhbGwgZXZlbnRzIHRoYXQgbWF0Y2ggaXQuXG4gICAgICpcbiAgICAgKiBAcGFyYW0ge1N0cmluZ3xSZWdFeHB9IGV2dCBOYW1lIG9mIHRoZSBldmVudCB0byBlbWl0IGFuZCBleGVjdXRlIGxpc3RlbmVycyBmb3IuXG4gICAgICogQHBhcmFtIHtBcnJheX0gW2FyZ3NdIE9wdGlvbmFsIGFycmF5IG9mIGFyZ3VtZW50cyB0byBiZSBwYXNzZWQgdG8gZWFjaCBsaXN0ZW5lci5cbiAgICAgKiBAcmV0dXJuIHtPYmplY3R9IEN1cnJlbnQgaW5zdGFuY2Ugb2YgRXZlbnRFbWl0dGVyIGZvciBjaGFpbmluZy5cbiAgICAgKi9cbiAgICBwcm90by5lbWl0RXZlbnQgPSBmdW5jdGlvbiBlbWl0RXZlbnQoZXZ0LCBhcmdzKSB7XG4gICAgICAgIHZhciBsaXN0ZW5lcnNNYXAgPSB0aGlzLmdldExpc3RlbmVyc0FzT2JqZWN0KGV2dCk7XG4gICAgICAgIHZhciBsaXN0ZW5lcnM7XG4gICAgICAgIHZhciBsaXN0ZW5lcjtcbiAgICAgICAgdmFyIGk7XG4gICAgICAgIHZhciBrZXk7XG4gICAgICAgIHZhciByZXNwb25zZTtcblxuICAgICAgICBmb3IgKGtleSBpbiBsaXN0ZW5lcnNNYXApIHtcbiAgICAgICAgICAgIGlmIChsaXN0ZW5lcnNNYXAuaGFzT3duUHJvcGVydHkoa2V5KSkge1xuICAgICAgICAgICAgICAgIGxpc3RlbmVycyA9IGxpc3RlbmVyc01hcFtrZXldLnNsaWNlKDApO1xuICAgICAgICAgICAgICAgIGkgPSBsaXN0ZW5lcnMubGVuZ3RoO1xuXG4gICAgICAgICAgICAgICAgd2hpbGUgKGktLSkge1xuICAgICAgICAgICAgICAgICAgICAvLyBJZiB0aGUgbGlzdGVuZXIgcmV0dXJucyB0cnVlIHRoZW4gaXQgc2hhbGwgYmUgcmVtb3ZlZCBmcm9tIHRoZSBldmVudFxuICAgICAgICAgICAgICAgICAgICAvLyBUaGUgZnVuY3Rpb24gaXMgZXhlY3V0ZWQgZWl0aGVyIHdpdGggYSBiYXNpYyBjYWxsIG9yIGFuIGFwcGx5IGlmIHRoZXJlIGlzIGFuIGFyZ3MgYXJyYXlcbiAgICAgICAgICAgICAgICAgICAgbGlzdGVuZXIgPSBsaXN0ZW5lcnNbaV07XG5cbiAgICAgICAgICAgICAgICAgICAgaWYgKGxpc3RlbmVyLm9uY2UgPT09IHRydWUpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgIHRoaXMucmVtb3ZlTGlzdGVuZXIoZXZ0LCBsaXN0ZW5lci5saXN0ZW5lcik7XG4gICAgICAgICAgICAgICAgICAgIH1cblxuICAgICAgICAgICAgICAgICAgICByZXNwb25zZSA9IGxpc3RlbmVyLmxpc3RlbmVyLmFwcGx5KHRoaXMsIGFyZ3MgfHwgW10pO1xuXG4gICAgICAgICAgICAgICAgICAgIGlmIChyZXNwb25zZSA9PT0gdGhpcy5fZ2V0T25jZVJldHVyblZhbHVlKCkpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgIHRoaXMucmVtb3ZlTGlzdGVuZXIoZXZ0LCBsaXN0ZW5lci5saXN0ZW5lcik7XG4gICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICB9XG4gICAgICAgIH1cblxuICAgICAgICByZXR1cm4gdGhpcztcbiAgICB9O1xuXG4gICAgLyoqXG4gICAgICogQWxpYXMgb2YgZW1pdEV2ZW50XG4gICAgICovXG4gICAgcHJvdG8udHJpZ2dlciA9IGFsaWFzKCdlbWl0RXZlbnQnKTtcblxuICAgIC8qKlxuICAgICAqIFN1YnRseSBkaWZmZXJlbnQgZnJvbSBlbWl0RXZlbnQgaW4gdGhhdCBpdCB3aWxsIHBhc3MgaXRzIGFyZ3VtZW50cyBvbiB0byB0aGUgbGlzdGVuZXJzLCBhcyBvcHBvc2VkIHRvIHRha2luZyBhIHNpbmdsZSBhcnJheSBvZiBhcmd1bWVudHMgdG8gcGFzcyBvbi5cbiAgICAgKiBBcyB3aXRoIGVtaXRFdmVudCwgeW91IGNhbiBwYXNzIGEgcmVnZXggaW4gcGxhY2Ugb2YgdGhlIGV2ZW50IG5hbWUgdG8gZW1pdCB0byBhbGwgZXZlbnRzIHRoYXQgbWF0Y2ggaXQuXG4gICAgICpcbiAgICAgKiBAcGFyYW0ge1N0cmluZ3xSZWdFeHB9IGV2dCBOYW1lIG9mIHRoZSBldmVudCB0byBlbWl0IGFuZCBleGVjdXRlIGxpc3RlbmVycyBmb3IuXG4gICAgICogQHBhcmFtIHsuLi4qfSBPcHRpb25hbCBhZGRpdGlvbmFsIGFyZ3VtZW50cyB0byBiZSBwYXNzZWQgdG8gZWFjaCBsaXN0ZW5lci5cbiAgICAgKiBAcmV0dXJuIHtPYmplY3R9IEN1cnJlbnQgaW5zdGFuY2Ugb2YgRXZlbnRFbWl0dGVyIGZvciBjaGFpbmluZy5cbiAgICAgKi9cbiAgICBwcm90by5lbWl0ID0gZnVuY3Rpb24gZW1pdChldnQpIHtcbiAgICAgICAgdmFyIGFyZ3MgPSBBcnJheS5wcm90b3R5cGUuc2xpY2UuY2FsbChhcmd1bWVudHMsIDEpO1xuICAgICAgICByZXR1cm4gdGhpcy5lbWl0RXZlbnQoZXZ0LCBhcmdzKTtcbiAgICB9O1xuXG4gICAgLyoqXG4gICAgICogU2V0cyB0aGUgY3VycmVudCB2YWx1ZSB0byBjaGVjayBhZ2FpbnN0IHdoZW4gZXhlY3V0aW5nIGxpc3RlbmVycy4gSWYgYVxuICAgICAqIGxpc3RlbmVycyByZXR1cm4gdmFsdWUgbWF0Y2hlcyB0aGUgb25lIHNldCBoZXJlIHRoZW4gaXQgd2lsbCBiZSByZW1vdmVkXG4gICAgICogYWZ0ZXIgZXhlY3V0aW9uLiBUaGlzIHZhbHVlIGRlZmF1bHRzIHRvIHRydWUuXG4gICAgICpcbiAgICAgKiBAcGFyYW0geyp9IHZhbHVlIFRoZSBuZXcgdmFsdWUgdG8gY2hlY2sgZm9yIHdoZW4gZXhlY3V0aW5nIGxpc3RlbmVycy5cbiAgICAgKiBAcmV0dXJuIHtPYmplY3R9IEN1cnJlbnQgaW5zdGFuY2Ugb2YgRXZlbnRFbWl0dGVyIGZvciBjaGFpbmluZy5cbiAgICAgKi9cbiAgICBwcm90by5zZXRPbmNlUmV0dXJuVmFsdWUgPSBmdW5jdGlvbiBzZXRPbmNlUmV0dXJuVmFsdWUodmFsdWUpIHtcbiAgICAgICAgdGhpcy5fb25jZVJldHVyblZhbHVlID0gdmFsdWU7XG4gICAgICAgIHJldHVybiB0aGlzO1xuICAgIH07XG5cbiAgICAvKipcbiAgICAgKiBGZXRjaGVzIHRoZSBjdXJyZW50IHZhbHVlIHRvIGNoZWNrIGFnYWluc3Qgd2hlbiBleGVjdXRpbmcgbGlzdGVuZXJzLiBJZlxuICAgICAqIHRoZSBsaXN0ZW5lcnMgcmV0dXJuIHZhbHVlIG1hdGNoZXMgdGhpcyBvbmUgdGhlbiBpdCBzaG91bGQgYmUgcmVtb3ZlZFxuICAgICAqIGF1dG9tYXRpY2FsbHkuIEl0IHdpbGwgcmV0dXJuIHRydWUgYnkgZGVmYXVsdC5cbiAgICAgKlxuICAgICAqIEByZXR1cm4geyp8Qm9vbGVhbn0gVGhlIGN1cnJlbnQgdmFsdWUgdG8gY2hlY2sgZm9yIG9yIHRoZSBkZWZhdWx0LCB0cnVlLlxuICAgICAqIEBhcGkgcHJpdmF0ZVxuICAgICAqL1xuICAgIHByb3RvLl9nZXRPbmNlUmV0dXJuVmFsdWUgPSBmdW5jdGlvbiBfZ2V0T25jZVJldHVyblZhbHVlKCkge1xuICAgICAgICBpZiAodGhpcy5oYXNPd25Qcm9wZXJ0eSgnX29uY2VSZXR1cm5WYWx1ZScpKSB7XG4gICAgICAgICAgICByZXR1cm4gdGhpcy5fb25jZVJldHVyblZhbHVlO1xuICAgICAgICB9XG4gICAgICAgIGVsc2Uge1xuICAgICAgICAgICAgcmV0dXJuIHRydWU7XG4gICAgICAgIH1cbiAgICB9O1xuXG4gICAgLyoqXG4gICAgICogRmV0Y2hlcyB0aGUgZXZlbnRzIG9iamVjdCBhbmQgY3JlYXRlcyBvbmUgaWYgcmVxdWlyZWQuXG4gICAgICpcbiAgICAgKiBAcmV0dXJuIHtPYmplY3R9IFRoZSBldmVudHMgc3RvcmFnZSBvYmplY3QuXG4gICAgICogQGFwaSBwcml2YXRlXG4gICAgICovXG4gICAgcHJvdG8uX2dldEV2ZW50cyA9IGZ1bmN0aW9uIF9nZXRFdmVudHMoKSB7XG4gICAgICAgIHJldHVybiB0aGlzLl9ldmVudHMgfHwgKHRoaXMuX2V2ZW50cyA9IHt9KTtcbiAgICB9O1xuXG4gICAgLyoqXG4gICAgICogUmV2ZXJ0cyB0aGUgZ2xvYmFsIHtAbGluayBFdmVudEVtaXR0ZXJ9IHRvIGl0cyBwcmV2aW91cyB2YWx1ZSBhbmQgcmV0dXJucyBhIHJlZmVyZW5jZSB0byB0aGlzIHZlcnNpb24uXG4gICAgICpcbiAgICAgKiBAcmV0dXJuIHtGdW5jdGlvbn0gTm9uIGNvbmZsaWN0aW5nIEV2ZW50RW1pdHRlciBjbGFzcy5cbiAgICAgKi9cbiAgICBFdmVudEVtaXR0ZXIubm9Db25mbGljdCA9IGZ1bmN0aW9uIG5vQ29uZmxpY3QoKSB7XG4gICAgICAgIGV4cG9ydHMuRXZlbnRFbWl0dGVyID0gb3JpZ2luYWxHbG9iYWxWYWx1ZTtcbiAgICAgICAgcmV0dXJuIEV2ZW50RW1pdHRlcjtcbiAgICB9O1xuXG4gICAgLy8gRXhwb3NlIHRoZSBjbGFzcyBlaXRoZXIgdmlhIEFNRCwgQ29tbW9uSlMgb3IgdGhlIGdsb2JhbCBvYmplY3RcbiAgICBpZiAodHlwZW9mIGRlZmluZSA9PT0gJ2Z1bmN0aW9uJyAmJiBkZWZpbmUuYW1kKSB7XG4gICAgICAgIGRlZmluZShmdW5jdGlvbiAoKSB7XG4gICAgICAgICAgICByZXR1cm4gRXZlbnRFbWl0dGVyO1xuICAgICAgICB9KTtcbiAgICB9XG4gICAgZWxzZSBpZiAodHlwZW9mIG1vZHVsZSA9PT0gJ29iamVjdCcgJiYgbW9kdWxlLmV4cG9ydHMpe1xuICAgICAgICBtb2R1bGUuZXhwb3J0cyA9IEV2ZW50RW1pdHRlcjtcbiAgICB9XG4gICAgZWxzZSB7XG4gICAgICAgIGV4cG9ydHMuRXZlbnRFbWl0dGVyID0gRXZlbnRFbWl0dGVyO1xuICAgIH1cbn0uY2FsbCh0aGlzKSk7XG4iXX0=
