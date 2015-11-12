var overlay = function( m ) {
	'use strict';

	var _element,
		_onCloseCallback;

	function onKeyDown(e) {
		e = e || window.event;

		if (e.keyCode == 27 && _onCloseCallback ) {
			_onCloseCallback();
		}
	}

	function position() {
		if( ! _element ) return;

		// fix for window width in IE8
		window.innerWidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
		window.innerHeight = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;

		var marginLeft = ( window.innerWidth - _element.clientWidth ) / 2;
		var marginTop  = ( window.innerHeight - _element.clientHeight ) / 2;

		_element.style.marginLeft = marginLeft > 0 ? marginLeft + "px" : 0;
		_element.style.marginTop = marginTop > 0 ? marginTop + "px" : 0;
	}

	// bind events (IE8 compatible)
	if (document.addEventListener) {
		document.addEventListener('keydown', onKeyDown);
		window.addEventListener('resize', position);
	} else if(document.attachEvent) {
		document.attachEvent('onkeydown', onKeyDown);
		window.attachEvent('onresize', position);
	}

	return function (content, onCloseCallback) {
		_onCloseCallback = onCloseCallback;

		return [
			m("div.overlay", { config: function(el) {
				_element = el;
				position();
			}}, [

				// close icon
				m('span', {
					"class": 'close dashicons dashicons-no',
					title  : "Click to close the overlay.",
					onclick: onCloseCallback
				}),

				content
			]),

			// overlay background
			m("div", {
				"class": "overlay-background",
				title  : "Click to close the overlay.",
				onclick: onCloseCallback
			})
		];
	};
};

module.exports = overlay;