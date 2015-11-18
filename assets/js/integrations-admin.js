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

checkRequiredListFields();
events.on('selectedLists.change', checkRequiredListFields );
},{}]},{},[1])
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyaWZ5L25vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJhc3NldHMvYnJvd3NlcmlmeS9pbnRlZ3JhdGlvbnMtYWRtaW4uanMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7QUNBQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSIsImZpbGUiOiJnZW5lcmF0ZWQuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlc0NvbnRlbnQiOlsiKGZ1bmN0aW9uIGUodCxuLHIpe2Z1bmN0aW9uIHMobyx1KXtpZighbltvXSl7aWYoIXRbb10pe3ZhciBhPXR5cGVvZiByZXF1aXJlPT1cImZ1bmN0aW9uXCImJnJlcXVpcmU7aWYoIXUmJmEpcmV0dXJuIGEobywhMCk7aWYoaSlyZXR1cm4gaShvLCEwKTt2YXIgZj1uZXcgRXJyb3IoXCJDYW5ub3QgZmluZCBtb2R1bGUgJ1wiK28rXCInXCIpO3Rocm93IGYuY29kZT1cIk1PRFVMRV9OT1RfRk9VTkRcIixmfXZhciBsPW5bb109e2V4cG9ydHM6e319O3Rbb11bMF0uY2FsbChsLmV4cG9ydHMsZnVuY3Rpb24oZSl7dmFyIG49dFtvXVsxXVtlXTtyZXR1cm4gcyhuP246ZSl9LGwsbC5leHBvcnRzLGUsdCxuLHIpfXJldHVybiBuW29dLmV4cG9ydHN9dmFyIGk9dHlwZW9mIHJlcXVpcmU9PVwiZnVuY3Rpb25cIiYmcmVxdWlyZTtmb3IodmFyIG89MDtvPHIubGVuZ3RoO28rKylzKHJbb10pO3JldHVybiBzfSkiLCIndXNlIHN0cmljdCc7XG5cbnZhciBzZXR0aW5ncyA9IG1jNHdwLnNldHRpbmdzO1xudmFyIGV2ZW50cyA9IG1jNHdwLmV2ZW50cztcbnZhciBub3RpY2UgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnbm90aWNlLWFkZGl0aW9uYWwtZmllbGRzJyk7XG5cbmZ1bmN0aW9uIGNoZWNrUmVxdWlyZWRMaXN0RmllbGRzKCApIHtcblx0dmFyIGxpc3RzID0gc2V0dGluZ3MuZ2V0U2VsZWN0ZWRMaXN0cygpO1xuXG5cdHZhciBzaG93Tm90aWNlID0gZmFsc2U7XG5cdHZhciBhbGxvd2VkRmllbGRzID0gWyAnRU1BSUwnLCAnRk5BTUUnLCAnTkFNRScsICdMTkFNRScgXTtcblxuXHRsb29wOlxuXHRmb3IoIHZhciBpPTA7IGk8bGlzdHMubGVuZ3RoOyBpKyspIHtcblx0XHR2YXIgbGlzdCA9IGxpc3RzW2ldO1xuXG5cdFx0Zm9yKCB2YXIgaj0wOyBqPGxpc3QubWVyZ2VfdmFycy5sZW5ndGg7IGorKykge1xuXHRcdFx0dmFyIGYgPSBsaXN0Lm1lcmdlX3ZhcnNbal07XG5cblx0XHRcdGlmKGYucmVxdWlyZWQgJiYgYWxsb3dlZEZpZWxkcy5pbmRleE9mKGYudGFnKSA8IDApIHtcblx0XHRcdFx0c2hvd05vdGljZSA9IHRydWU7XG5cdFx0XHRcdGJyZWFrIGxvb3A7XG5cdFx0XHR9XG5cdFx0fVxuXHR9XG5cblx0bm90aWNlLnN0eWxlLmRpc3BsYXkgPSBzaG93Tm90aWNlID8gJycgOiAnbm9uZSc7XG59XG5cbmNoZWNrUmVxdWlyZWRMaXN0RmllbGRzKCk7XG5ldmVudHMub24oJ3NlbGVjdGVkTGlzdHMuY2hhbmdlJywgY2hlY2tSZXF1aXJlZExpc3RGaWVsZHMgKTsiXX0=
