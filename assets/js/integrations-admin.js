(function () { var require = undefined; var define = undefined; (function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
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