var overlay = function(m, i18n) {
	'use strict';

	var _element,
		_onCloseCallback;

	function close() {
		document.removeEventListener('keydown', onKeyDown);
		window.removeEventListener('resize', position);
		_onCloseCallback();
	}

	function onKeyDown(e) {
		e = e || window.event;

		// close overlay when pressing ESC
		if(e.keyCode == 27) {
			close();
		}

		// prevent ENTER
		if(e.keyCode == 13 ) {
			e.preventDefault();
		}
	}

	function position() {
		if( ! _element ) return;

		// fix for window width in IE8
		var windowWidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
		var windowHeight = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;

		var marginLeft = ( windowWidth - _element.clientWidth - 40 ) / 2;
		var marginTop  = ( windowHeight - _element.clientHeight - 40 ) / 2;

		_element.style.left = ( marginLeft > 0 ? marginLeft : 0 ) + "px";
		_element.style.top = ( marginTop > 0 ? marginTop : 0 ) + "px";
	}

	return function (content, onCloseCallback) {
		_onCloseCallback = onCloseCallback;

		document.addEventListener('keydown', onKeyDown);
		window.addEventListener('resize', position);

		return [
			m('div.overlay-wrap',
				m("div.overlay", {
					config: function (el) {
						_element = el;
						position();
					}
				},[

					// close icon
					m('span', {
						"class": 'close dashicons dashicons-no',
						title  : i18n.close,
						onclick: close
					}),

					content
				])
			)
			,
			m('div.overlay-background', {
				title: i18n.close,
				onclick: close
			})
		];
	};
};

module.exports = overlay;