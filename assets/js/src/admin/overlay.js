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
		var windowWidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
		var windowHeight = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;

		var marginLeft = ( windowWidth - _element.clientWidth - 80 ) / 2;
		var marginTop  = ( windowHeight - _element.clientHeight - 80 ) / 2;

		_element.style.marginLeft = ( marginLeft > 0 ? marginLeft : 0 ) + "px";
		_element.style.marginTop = ( marginTop > 0 ? marginTop : 0 ) + "px";
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

		return m("div", {
				"class": "overlay-wrapper",
				title  : "Click to close the overlay.",
				onclick: onCloseCallback
			}, [
				m("div.overlay", {
					config: function(el) {
						_element = el;
						position();
					},
					"onclick": function(event) {
						event.stopPropagation();
					}}, [

					// close icon
					m('span', {
						"class": 'close dashicons dashicons-no',
						title  : "Click to close the overlay.",
						onclick: onCloseCallback
					}),

					content
				])

			]);
	};
};

module.exports = overlay;