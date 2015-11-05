(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
'use strict';

var forms = require('./forms/forms.js');

// expose stuff
window.mc4wp = window.mc4wp || {};
window.mc4wp.forms = forms;
},{"./forms/forms.js":3}],2:[function(require,module,exports){
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
	this.started = false;

	this.on = function(event,callback) {
		return events.on(event,callback);
	};

	this.trigger = function (event,args) {
		return events.trigger(event,args);
	};

	this.off = function(event,callback){
		return events.off(event,callback);
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

	this.setResponse = function( msg ) {
		form.element.querySelector('.mc4wp-response').innerHTML = msg;
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
};

module.exports = Form;
},{"../../third-party/populate.js":6,"../../third-party/serialize.js":7}],3:[function(require,module,exports){
var forms = function() {
	'use strict';

	// deps
	var EventEmitter = require('../../third-party/event-emitter.js');
	var Form = require('./form.js');
	var gator = require('../../third-party/gator.js');

	// variables
	var events = new EventEmitter();
	var formElements = document.querySelectorAll('.mc4wp-form');
	var config = window.mc4wp_config || {};

	// initialize Form objects
	var forms = Array.prototype.map.call(formElements,function(element) {

		// find form data
		var form = new Form(element, EventEmitter);
		var formConfig = config.forms[form.id] || {};
		form.setConfig(formConfig);

		if( config.auto_scroll && formConfig.data && formConfig.errors.length > 0 ) {
			form.placeIntoView( config.auto_scroll === 'animated' );
		}

		return form;
	});

	// Bind browser events to form events (using delegation to work with AJAX loaded forms as well)
	Gator(document.body).on('submit', '.mc4wp-form', function(event) {
		var form = getFromElement(event.target);
		if( form ) {
			events.trigger('submit', [form, event]);
		}
	});

	Gator(document.body).on('focus', '.mc4wp-form', function(event) {
		var form = getFromElement(event.target);
		if( form && ! form.started ) {
			events.trigger('started', [form, event]);
		}
	});

	Gator(document.body).on('change', '.mc4wp-form', function(event) {
		var form = getFromElement(event.target);
		if( form ) {
			events.trigger('changed', [form, event]);
		}
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
		var id = parseInt( formElement.dataset.id );
		return get(id);
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


},{"../../third-party/event-emitter.js":4,"../../third-party/gator.js":5,"./form.js":2}],4:[function(require,module,exports){
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
},{}],5:[function(require,module,exports){
/* gator v1.2.4 craig.is/riding/gators */
(function(){function t(a){return k?k:a.matches?k=a.matches:a.webkitMatchesSelector?k=a.webkitMatchesSelector:a.mozMatchesSelector?k=a.mozMatchesSelector:a.msMatchesSelector?k=a.msMatchesSelector:a.oMatchesSelector?k=a.oMatchesSelector:k=e.matchesSelector}function q(a,b,c){if("_root"==b)return c;if(a!==c){if(t(a).call(a,b))return a;if(a.parentNode)return m++,q(a.parentNode,b,c)}}function u(a,b,c,e){d[a.id]||(d[a.id]={});d[a.id][b]||(d[a.id][b]={});d[a.id][b][c]||(d[a.id][b][c]=[]);d[a.id][b][c].push(e)}
	function v(a,b,c,e){if(d[a.id])if(!b)for(var f in d[a.id])d[a.id].hasOwnProperty(f)&&(d[a.id][f]={});else if(!e&&!c)d[a.id][b]={};else if(!e)delete d[a.id][b][c];else if(d[a.id][b][c])for(f=0;f<d[a.id][b][c].length;f++)if(d[a.id][b][c][f]===e){d[a.id][b][c].splice(f,1);break}}function w(a,b,c){if(d[a][c]){var k=b.target||b.srcElement,f,g,h={},n=g=0;m=0;for(f in d[a][c])d[a][c].hasOwnProperty(f)&&(g=q(k,f,l[a].element))&&e.matchesEvent(c,l[a].element,g,"_root"==f,b)&&(m++,d[a][c][f].match=g,h[m]=d[a][c][f]);
		b.stopPropagation=function(){b.cancelBubble=!0};for(g=0;g<=m;g++)if(h[g])for(n=0;n<h[g].length;n++){if(!1===h[g][n].call(h[g].match,b)){e.cancel(b);return}if(b.cancelBubble)return}}}function r(a,b,c,k){function f(a){return function(b){w(g,b,a)}}if(this.element){a instanceof Array||(a=[a]);c||"function"!=typeof b||(c=b,b="_root");var g=this.id,h;for(h=0;h<a.length;h++)k?v(this,a[h],b,c):(d[g]&&d[g][a[h]]||e.addEvent(this,a[h],f(a[h])),u(this,a[h],b,c));return this}}function e(a,b){if(!(this instanceof
		e)){for(var c in l)if(l[c].element===a)return l[c];p++;l[p]=new e(a,p);return l[p]}this.element=a;this.id=b}var k,m=0,p=0,d={},l={};e.prototype.on=function(a,b,c){return r.call(this,a,b,c)};e.prototype.off=function(a,b,c){return r.call(this,a,b,c,!0)};e.matchesSelector=function(){};e.cancel=function(a){a.preventDefault();a.stopPropagation()};e.addEvent=function(a,b,c){a.element.addEventListener(b,c,"blur"==b||"focus"==b)};e.matchesEvent=function(){return!0};"undefined"!==typeof module&&module.exports&&
	(module.exports=e);window.Gator=e})();
},{}],6:[function(require,module,exports){
/*! populate.js v1.0 by @dannyvankooten | MIT license */
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

			// check element type
			switch(element.type || element.constructor ) {
				default:
					element.value = value;
					break;

				case RadioNodeList:
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
},{}],7:[function(require,module,exports){
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
},{}]},{},[1]);
