(function () { var require = undefined; var define = undefined; (function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
'use strict';

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

var context = document.getElementById('mc4wp-admin');
var listInputs = context.querySelectorAll('.mc4wp-list-input');
var lists = window.mc4wp_vars.mailchimp.lists;
var selectedLists = [];
var listeners = {}; // functions

function getSelectedListsWhere(searchKey, searchValue) {
  return selectedLists.filter(function (el) {
    return el[searchKey] === searchValue;
  });
}

function getSelectedLists() {
  return selectedLists;
}

function updateSelectedLists() {
  selectedLists = [];
  Array.prototype.forEach.call(listInputs, function (input) {
    // skip unchecked checkboxes
    if (typeof input.checked === "boolean" && !input.checked) {
      return;
    }

    if (_typeof(lists[input.value]) === "object") {
      selectedLists.push(lists[input.value]);
    }
  });
  toggleVisibleLists();
  emit('selectedLists.change', [selectedLists]);
  return selectedLists;
}

function toggleVisibleLists() {
  var rows = document.querySelectorAll('.lists--only-selected > *');
  Array.prototype.forEach.call(rows, function (el) {
    var listId = el.getAttribute('data-list-id');
    var isSelected = getSelectedListsWhere('id', listId).length > 0;

    if (isSelected) {
      el.setAttribute('class', el.getAttribute('class').replace('hidden', ''));
    } else {
      el.setAttribute('class', el.getAttribute('class') + " hidden");
    }
  });
}

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

[].forEach.call(listInputs, function (el) {
  el.addEventListener('change', updateSelectedLists);
});
updateSelectedLists();
module.exports = {
  getSelectedLists: getSelectedLists,
  on: on
};

},{}],2:[function(require,module,exports){
'use strict';

var showIfElements = document.querySelectorAll('[data-showif]');
[].forEach.call(showIfElements, function (element) {
  var config = JSON.parse(element.getAttribute('data-showif'));
  var parentElements = document.querySelectorAll('[name="' + config.element + '"]');
  var inputs = element.querySelectorAll('input,select,textarea:not([readonly])');
  var hide = config.hide === undefined || config.hide;

  function toggleElement() {
    // do nothing with unchecked radio inputs
    if (this.getAttribute('type') === "radio" && !this.checked) {
      return;
    }

    var value = this.getAttribute("type") === "checkbox" ? this.checked : this.value;
    var conditionMet = value == config.value;

    if (hide) {
      element.style.display = conditionMet ? '' : 'none';
      element.style.visibility = conditionMet ? '' : 'hidden';
    } else {
      element.style.opacity = conditionMet ? '' : '0.4';
    } // disable input fields to stop sending their values to server


    [].forEach.call(inputs, function (inputElement) {
      conditionMet ? inputElement.removeAttribute('readonly') : inputElement.setAttribute('readonly', 'readonly');
    });
  } // find checked element and call toggleElement function


  [].forEach.call(parentElements, function (el) {
    el.addEventListener('change', toggleElement);
    toggleElement.call(el);
  });
});

},{}],3:[function(require,module,exports){
'use strict';

var settings = require('./admin/settings.js');

var notice = document.getElementById('notice-additional-fields');

require('./admin/show-if.js');

function checkRequiredListFields() {
  var allowedFields = ['EMAIL'];
  var ids = [].filter.call(document.querySelectorAll('.mc4wp-list-input'), function (i) {
    return i.checked;
  }).map(function (i) {
    return i.value;
  }).join(','); //const allowedFields = [ 'EMAIL', 'FNAME', 'NAME', 'LNAME' ];

  var showNotice = false;
  window.fetch("".concat(ajaxurl, "?action=mc4wp_get_list_details&ids=").concat(ids)).then(function (r) {
    return r.json();
  }).then(function (lists) {
    lists.forEach(function (list) {
      list.merge_fields.forEach(function (f) {
        if (f.required && allowedFields.indexOf(f.tag) < 0) {
          showNotice = true;
        }
      });
    });
  })["finally"](function () {
    notice.style.display = showNotice ? '' : 'none';
  });
}

if (notice) {
  checkRequiredListFields();
  settings.on('selectedLists.change', checkRequiredListFields);
}

},{"./admin/settings.js":1,"./admin/show-if.js":2}]},{},[3]);
 })();