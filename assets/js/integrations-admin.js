(function () { var require = undefined; var define = undefined; (function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
'use strict';

var ajaxurl = window.mc4wp_vars.ajaxurl;
var settings = window.mc4wp.settings;
var notice = document.getElementById('notice-additional-fields');

function checkRequiredListFields() {
  var ids = [].filter.call(document.querySelectorAll('.mc4wp-list-input'), function (i) {
    return i.checked;
  }).map(function (i) {
    return i.value;
  }).join(',');
  var allowedFields = ['EMAIL', 'FNAME', 'NAME', 'LNAME'];
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

},{}]},{},[1]);
 })();