(function () { var require = undefined; var define = undefined; (function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
'use strict'; // deps & vars

var _conditionalElements = _interopRequireDefault(require("./forms/conditional-elements.js"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }

var mc4wp = window.mc4wp || {};

var Gator = require('gator');

var forms = require('./forms/forms.js');

var config = window.mc4wp_forms_config || {};

var scrollToElement = require('./misc/scroll-to-element.js');

function handleFormRequest(form, eventName, errors, data) {
  var timeStart = Date.now();
  var pageHeight = document.body.clientHeight; // re-populate form if an error occurred

  if (errors) {
    form.setData(data);
  } // scroll to form


  if (window.scrollY <= 10 && config.auto_scroll) {
    scrollToElement(form.element);
  } // trigger events on window.load so all other scripts have loaded


  window.addEventListener('load', function () {
    // trigger events
    forms.trigger(form.id + '.submitted', [form]);
    forms.trigger('submitted', [form]);

    if (errors) {
      forms.trigger(form.id + '.error', [form, errors]);
      forms.trigger('error', [form, errors]);
    } else {
      // form was successfully submitted
      forms.trigger(form.id + '.success', [form, data]);
      forms.trigger('success', [form, data]); // subscribed / unsubscribed

      forms.trigger(form.id + "." + eventName, [form, data]);
      forms.trigger(eventName, [form, data]); // for BC: always trigger "subscribed" event when firing "updated_subscriber" event

      if (eventName === 'updated_subscriber') {
        forms.trigger(form.id + "." + "subscribed", [form, data, true]);
        forms.trigger('subscribed', [form, data, true]);
      }
    } // scroll to form again if page height changed since last scroll, eg because of slow loading images
    // (only if load didn't take more than 0.8 seconds to prevent overtaking user scroll)


    var timeElapsed = Date.now() - timeStart;

    if (config.auto_scroll && timeElapsed > 1000 && timeElapsed < 2000 && document.body.clientHeight !== pageHeight) {
      scrollToElement(form.element);
    }
  });
} // Bind browser events to form events (using delegation)


var gator = Gator(document.body);
gator.on('submit', '.mc4wp-form', function (event) {
  var form = forms.getByElement(event.target || event.srcElement);

  if (!event.defaultPrevented) {
    forms.trigger(form.id + '.submit', [form, event]);
  }

  if (!event.defaultPrevented) {
    forms.trigger('submit', [form, event]);
  }
});
gator.on('focus', '.mc4wp-form', function (event) {
  var form = forms.getByElement(event.target || event.srcElement);

  if (!form.started) {
    forms.trigger(form.id + '.started', [form, event]);
    forms.trigger('started', [form, event]);
    form.started = true;
  }
});
gator.on('change', '.mc4wp-form', function (event) {
  var form = forms.getByElement(event.target || event.srcElement);
  forms.trigger('change', [form, event]);
  forms.trigger(form.id + '.change', [form, event]);
}); // init conditional elements

_conditionalElements["default"].init(); // register early listeners


if (mc4wp.listeners) {
  var listeners = mc4wp.listeners;

  for (var i = 0; i < listeners.length; i++) {
    forms.on(listeners[i].event, listeners[i].callback);
  } // delete temp listeners array, so we don't bind twice


  delete mc4wp["listeners"];
} // expose forms object


mc4wp.forms = forms; // handle submitted form

if (config.submitted_form) {
  var formConfig = config.submitted_form,
      element = document.getElementById(formConfig.element_id),
      form = forms.getByElement(element);
  handleFormRequest(form, formConfig.event, formConfig.errors, formConfig.data);
} // expose mc4wp object globally


window.mc4wp = mc4wp;

},{"./forms/conditional-elements.js":2,"./forms/forms.js":4,"./misc/scroll-to-element.js":5,"gator":7}],2:[function(require,module,exports){
'use strict';

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

function getFieldValues(form, fieldName) {
  var values = [];
  var inputs = form.querySelectorAll('input[name="' + fieldName + '"], select[name="' + fieldName + '"], textarea[name="' + fieldName + '"]');

  for (var i = 0; i < inputs.length; i++) {
    var input = inputs[i];
    var type = input.getAttribute("type");

    if ((type === "radio" || type === "checkbox") && !input.checked) {
      continue;
    }

    values.push(input.value);
  }

  return values;
}

function findForm(element) {
  var bubbleElement = element;

  while (bubbleElement.parentElement) {
    bubbleElement = bubbleElement.parentElement;

    if (bubbleElement.tagName === 'FORM') {
      return bubbleElement;
    }
  }

  return null;
}

function toggleElement(el) {
  var show = !!el.getAttribute('data-show-if');
  var conditions = show ? el.getAttribute('data-show-if').split(':') : el.getAttribute('data-hide-if').split(':');
  var fieldName = conditions[0];
  var expectedValues = (conditions.length > 1 ? conditions[1] : "*").split('|');
  var form = findForm(el);
  var values = getFieldValues(form, fieldName); // determine whether condition is met

  var conditionMet = false;

  for (var i = 0; i < values.length; i++) {
    var value = values[i]; // condition is met when value is in array of expected values OR expected values contains a wildcard and value is not empty

    conditionMet = expectedValues.indexOf(value) > -1 || expectedValues.indexOf('*') > -1 && value.length > 0;

    if (conditionMet) {
      break;
    }
  } // toggle element display


  if (show) {
    el.style.display = conditionMet ? '' : 'none';
  } else {
    el.style.display = conditionMet ? 'none' : '';
  } // find all inputs inside this element and toggle [required] attr (to prevent HTML5 validation on hidden elements)


  var inputs = el.querySelectorAll('input, select, textarea');
  [].forEach.call(inputs, function (el) {
    if ((conditionMet || show) && el.getAttribute('data-was-required')) {
      el.required = true;
      el.removeAttribute('data-was-required');
    }

    if ((!conditionMet || !show) && el.required) {
      el.setAttribute('data-was-required', "true");
      el.required = false;
    }
  });
} // evaluate conditional elements globally


function evaluate() {
  var elements = document.querySelectorAll('.mc4wp-form [data-show-if], .mc4wp-form [data-hide-if]');
  [].forEach.call(elements, toggleElement);
} // re-evaluate conditional elements for change events on forms


function handleInputEvent(evt) {
  if (!evt.target || !evt.target.form || evt.target.form.className.indexOf('mc4wp-form') < 0) {
    return;
  }

  var form = evt.target.form;
  var elements = form.querySelectorAll('[data-show-if], [data-hide-if]');
  [].forEach.call(elements, toggleElement);
}

var _default = {
  'init': function init() {
    document.addEventListener('keyup', handleInputEvent, true);
    document.addEventListener('change', handleInputEvent, true);
    document.addEventListener('mc4wp-refresh', evaluate, true);
    window.addEventListener('load', evaluate);
    evaluate();
  }
};
exports["default"] = _default;

},{}],3:[function(require,module,exports){
'use strict';

var serialize = require('form-serialize');

var populate = require('populate.js');

var Form = function Form(id, element) {
  this.id = id;
  this.element = element || document.createElement('form');
  this.name = this.element.getAttribute('data-name') || "Form #" + this.id;
  this.errors = [];
  this.started = false;
};

Form.prototype.setData = function (data) {
  try {
    populate(this.element, data);
  } catch (e) {
    console.error(e);
  }
};

Form.prototype.getData = function () {
  return serialize(this.element, {
    hash: true,
    empty: true
  });
};

Form.prototype.getSerializedData = function () {
  return serialize(this.element, {
    hash: false,
    empty: true
  });
};

Form.prototype.setResponse = function (msg) {
  this.element.querySelector('.mc4wp-response').innerHTML = msg;
}; // revert back to original state


Form.prototype.reset = function () {
  this.setResponse('');
  this.element.querySelector('.mc4wp-form-fields').style.display = '';
  this.element.reset();
};

module.exports = Form;

},{"form-serialize":6,"populate.js":8}],4:[function(require,module,exports){
'use strict'; // deps

var Form = require('./form.js'); // variables


var forms = [];
var listeners = {};

function emit(event, args) {
  listeners[event] = listeners[event] || [];
  listeners[event].forEach(function (f) {
    return f.apply(null, args);
  });
}

function on(event, func) {
  listeners[event] = listeners[event] || [];
  listeners[event].push(func);
}

function off(event, func) {
  listeners[event] = listeners[event] || [];
  listeners[event] = listeners[event].filter(function (f) {
    return f !== func;
  });
} // get form by its id
// please note that this will get the FIRST occurence of the form with that ID on the page


function get(formId) {
  formId = parseInt(formId); // do we have form for this one already?

  for (var i = 0; i < forms.length; i++) {
    if (forms[i].id === formId) {
      return forms[i];
    }
  } // try to create from first occurence of this element


  var formElement = document.querySelector('.mc4wp-form-' + formId);
  return createFromElement(formElement, formId);
} // get form by <form> element (or any input in form)


function getByElement(element) {
  var formElement = element.form || element;

  for (var i = 0; i < forms.length; i++) {
    if (forms[i].element === formElement) {
      return forms[i];
    }
  }

  return createFromElement(formElement);
} // create form object from <form> element


function createFromElement(formElement, id) {
  id = id || parseInt(formElement.getAttribute('data-id')) || 0;
  var form = new Form(id, formElement);
  forms.push(form);
  return form;
}

function all() {
  return forms;
}

function triggerEvent(eventName, eventArgs) {
  if (eventName === 'submit' || eventName.indexOf('.submit') > 0) {
    // don't spin up new thread for submit event as we want to preventDefault()...
    emit(eventName, eventArgs);
  } else {
    // process in separate thread to prevent errors from breaking core functionality
    window.setTimeout(function () {
      emit(eventName, eventArgs);
    }, 1);
  }
}

module.exports = {
  "all": all,
  "get": get,
  "getByElement": getByElement,
  "on": on,
  "off": off,
  "trigger": triggerEvent
};

},{"./form.js":3}],5:[function(require,module,exports){
'use strict';

function scrollTo(element) {
  var x = window.pageXOffset || document.documentElement.scrollLeft;
  var y = calculateScrollOffset(element);
  window.scrollTo(x, y);
}

function calculateScrollOffset(elem) {
  var body = document.body,
      html = document.documentElement;
  var elemRect = elem.getBoundingClientRect();
  var clientHeight = html.clientHeight;
  var documentHeight = Math.max(body.scrollHeight, body.offsetHeight, html.clientHeight, html.scrollHeight, html.offsetHeight);
  var scrollPosition = elemRect.bottom - clientHeight / 2 - elemRect.height / 2;
  var maxScrollPosition = documentHeight - clientHeight;
  return Math.min(scrollPosition + window.pageYOffset, maxScrollPosition);
}

module.exports = scrollTo;

},{}],6:[function(require,module,exports){
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
            if (val == undefined && element.type == 'radio') {
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
        // +var converts the variable into a number
        // better than parseInt because it doesn't truncate away trailing
        // letters and actually fails if whole thing is not a number
        var index = +string;

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

},{}],7:[function(require,module,exports){
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

},{}],8:[function(require,module,exports){
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

                        if ('undefined' === typeof value) {
                            value = '';
                        }

                        if (null === value) {
                            value = '';
                        }

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
				case 'date':
          				element.value = new Date(value).toISOString().split('T')[0];	
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

},{}]},{},[1]);
 })();