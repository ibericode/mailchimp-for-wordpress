var overlay = function( m ) {
	'use strict';

	var _onCloseCallback;

	function onKeyDown(e) {
		if (e.keyCode == 27 && _onCloseCallback ) {
			_onCloseCallback();
		}
	}

	if (window.addEventListener) {
		window.addEventListener('keydown', onKeyDown);
	} else if(window.attachEvent) {
		window.attachEvent('keydown', onKeyDown);
	}

	return function (content, onCloseCallback) {
		_onCloseCallback = onCloseCallback;

		return [
			m("div.overlay", [
				m("div.overlay-content", [

					// close icon
					m('span.close.dashicons.dashicons-no', {
						title  : "Click to close the overlay.",
						onclick: onCloseCallback
					}),

					content
				])
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