(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
'use strict';

var settings = mc4wp.settings;
var events = mc4wp.events;
var notice = document.getElementById('notice-additional-fields');

function checkRequiredListFields( ) {

	var lists = settings.getSelectedLists();

	var showNotice = false;
	var allowedFields = [ 'EMAIL', 'FNAME', 'NAME', 'LNAME' ];

	loop:
	for( var i=0; i<lists.length; i++) {
		var list = lists[i];

		for( var j=0; j<list.merge_vars.length; j++) {
			var f = list.merge_vars[j];

			if(f.required && allowedFields.indexOf(f.tag) < 0) {
				showNotice = true;
				break loop;
			}
		}
	}

	notice.style.display = showNotice ? '' : 'none';
}

if( notice ) {
	checkRequiredListFields();
	events.on('selectedLists.change', checkRequiredListFields );
}


},{}]},{},[1])
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJhc3NldHMvYnJvd3NlcmlmeS9pbnRlZ3JhdGlvbnMtYWRtaW4uanMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7QUNBQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EiLCJmaWxlIjoiZ2VuZXJhdGVkLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXNDb250ZW50IjpbIihmdW5jdGlvbiBlKHQsbixyKXtmdW5jdGlvbiBzKG8sdSl7aWYoIW5bb10pe2lmKCF0W29dKXt2YXIgYT10eXBlb2YgcmVxdWlyZT09XCJmdW5jdGlvblwiJiZyZXF1aXJlO2lmKCF1JiZhKXJldHVybiBhKG8sITApO2lmKGkpcmV0dXJuIGkobywhMCk7dmFyIGY9bmV3IEVycm9yKFwiQ2Fubm90IGZpbmQgbW9kdWxlICdcIitvK1wiJ1wiKTt0aHJvdyBmLmNvZGU9XCJNT0RVTEVfTk9UX0ZPVU5EXCIsZn12YXIgbD1uW29dPXtleHBvcnRzOnt9fTt0W29dWzBdLmNhbGwobC5leHBvcnRzLGZ1bmN0aW9uKGUpe3ZhciBuPXRbb11bMV1bZV07cmV0dXJuIHMobj9uOmUpfSxsLGwuZXhwb3J0cyxlLHQsbixyKX1yZXR1cm4gbltvXS5leHBvcnRzfXZhciBpPXR5cGVvZiByZXF1aXJlPT1cImZ1bmN0aW9uXCImJnJlcXVpcmU7Zm9yKHZhciBvPTA7bzxyLmxlbmd0aDtvKyspcyhyW29dKTtyZXR1cm4gc30pIiwiJ3VzZSBzdHJpY3QnO1xuXG52YXIgc2V0dGluZ3MgPSBtYzR3cC5zZXR0aW5ncztcbnZhciBldmVudHMgPSBtYzR3cC5ldmVudHM7XG52YXIgbm90aWNlID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ25vdGljZS1hZGRpdGlvbmFsLWZpZWxkcycpO1xuXG5mdW5jdGlvbiBjaGVja1JlcXVpcmVkTGlzdEZpZWxkcyggKSB7XG5cblx0dmFyIGxpc3RzID0gc2V0dGluZ3MuZ2V0U2VsZWN0ZWRMaXN0cygpO1xuXG5cdHZhciBzaG93Tm90aWNlID0gZmFsc2U7XG5cdHZhciBhbGxvd2VkRmllbGRzID0gWyAnRU1BSUwnLCAnRk5BTUUnLCAnTkFNRScsICdMTkFNRScgXTtcblxuXHRsb29wOlxuXHRmb3IoIHZhciBpPTA7IGk8bGlzdHMubGVuZ3RoOyBpKyspIHtcblx0XHR2YXIgbGlzdCA9IGxpc3RzW2ldO1xuXG5cdFx0Zm9yKCB2YXIgaj0wOyBqPGxpc3QubWVyZ2VfdmFycy5sZW5ndGg7IGorKykge1xuXHRcdFx0dmFyIGYgPSBsaXN0Lm1lcmdlX3ZhcnNbal07XG5cblx0XHRcdGlmKGYucmVxdWlyZWQgJiYgYWxsb3dlZEZpZWxkcy5pbmRleE9mKGYudGFnKSA8IDApIHtcblx0XHRcdFx0c2hvd05vdGljZSA9IHRydWU7XG5cdFx0XHRcdGJyZWFrIGxvb3A7XG5cdFx0XHR9XG5cdFx0fVxuXHR9XG5cblx0bm90aWNlLnN0eWxlLmRpc3BsYXkgPSBzaG93Tm90aWNlID8gJycgOiAnbm9uZSc7XG59XG5cbmlmKCBub3RpY2UgKSB7XG5cdGNoZWNrUmVxdWlyZWRMaXN0RmllbGRzKCk7XG5cdGV2ZW50cy5vbignc2VsZWN0ZWRMaXN0cy5jaGFuZ2UnLCBjaGVja1JlcXVpcmVkTGlzdEZpZWxkcyApO1xufVxuXG4iXX0=
