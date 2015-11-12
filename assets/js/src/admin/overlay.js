var overlay = function( m ) {
	'use strict';

	var _element, _onCloseCallback;

	function onKeyDown(e) {
		if (e.keyCode == 27 && _onCloseCallback ) {
			_onCloseCallback();
		}
	}

	function position() {
		if( ! _element ) return;

		var marginLeft =  ( window.innerWidth - _element.offsetWidth ) / 2;
		var marginTop = ( window.innerHeight - _element.offsetHeight ) / 2;

		_element.style.marginLeft = marginLeft > 0 ? marginLeft + "px" : 0;
		_element.style.marginTop = marginTop > 0 ? marginTop + "px" : 0;
	}

	if (document.addEventListener) {
		document.addEventListener('keydown', onKeyDown);
		window.addEventListener('resize', position);
	} else if(document.attachEvent) {
		document.attachEvent('onkeydown', onKeyDown);
		window.addEventListener('resize', position);
	}

	return function (content, onCloseCallback) {
		_onCloseCallback = onCloseCallback;

		return [
			m("div.overlay", { config: function(el) {
				_element = el;
				position();
			}}, [

				// close icon
				m('span.close.dashicons.dashicons-no', {
					title  : "Click to close the overlay.",
					onclick: onCloseCallback
				}),

				content
			]),

			// overlay background
			m("div.overlay-background", {
				title  : "Click to close the overlay.",
				onclick: onCloseCallback
			})
		];
	};
};

module.exports = overlay;