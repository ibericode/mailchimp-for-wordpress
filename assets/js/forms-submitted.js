(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
'use strict';

var _scrollToElement = _interopRequireDefault(require("./misc/scroll-to-element.js"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }

var submittedForm = window.mc4wp_submitted_form;
var forms = window.mc4wp.forms;

function trigger(event, args) {
  forms.trigger(args[0].id + '.' + event, args);
  forms.trigger(event, args);
}

function handleFormRequest(form, eventName, errors, data) {
  var timeStart = Date.now();
  var pageHeight = document.body.clientHeight; // re-populate form if an error occurred

  if (errors) {
    form.setData(data);
  } // scroll to form


  if (window.scrollY <= 10 && submittedForm.auto_scroll) {
    (0, _scrollToElement["default"])(form.element);
  } // trigger events on window.load so all other scripts have loaded


  window.addEventListener('load', function () {
    trigger('submitted', [form]);

    if (errors) {
      trigger('error', [form, errors]);
    } else {
      // form was successfully submitted
      trigger('success', [form, data]); // subscribed / unsubscribed

      trigger(eventName, [form, data]); // for BC: always trigger "subscribed" event when firing "updated_subscriber" event

      if (eventName === 'updated_subscriber') {
        trigger('subscribed', [form, data, true]);
      }
    } // scroll to form again if page height changed since last scroll, eg because of slow loading images
    // (only if load didn't take too long to prevent overtaking user scroll)


    var timeElapsed = Date.now() - timeStart;

    if (submittedForm.auto_scroll && timeElapsed > 1000 && timeElapsed < 2000 && document.body.clientHeight !== pageHeight) {
      (0, _scrollToElement["default"])(form.element);
    }
  });
}

if (submittedForm) {
  var element = document.getElementById(submittedForm.element_id);
  var form = forms.getByElement(element);
  handleFormRequest(form, submittedForm.event, submittedForm.errors, submittedForm.data);
}

},{"./misc/scroll-to-element.js":2}],2:[function(require,module,exports){
'use strict';

function scrollTo(element) {
  var x = window.pageXOffset || document.documentElement.scrollLeft;
  var y = calculateScrollOffset(element);
  window.scrollTo(x, y);
}

function calculateScrollOffset(elem) {
  var body = document.body;
  var html = document.documentElement;
  var elemRect = elem.getBoundingClientRect();
  var clientHeight = html.clientHeight;
  var documentHeight = Math.max(body.scrollHeight, body.offsetHeight, html.clientHeight, html.scrollHeight, html.offsetHeight);
  var scrollPosition = elemRect.bottom - clientHeight / 2 - elemRect.height / 2;
  var maxScrollPosition = documentHeight - clientHeight;
  return Math.min(scrollPosition + window.pageYOffset, maxScrollPosition);
}

module.exports = scrollTo;

},{}]},{},[1]);
