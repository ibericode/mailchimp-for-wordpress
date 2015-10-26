var overlay = function( content, onclose ) {
	'use strict';

	function onKeyDown(e) {
		if(e.keyCode !== 27) return;
		onclose();
	}

	if ( window.addEventListener) {
		window.addEventListener('keydown', onKeyDown);
	} else if (el.attachEvent)  {
		window.attachEvent('keydown', onKeyDown);
	}

	return [
		m( "div.overlay",[
			m("div.overlay-content", [

				// close icon
				m('span.close.dashicons.dashicons-no', {
					title: "Click to close the overlay.",
					onclick: onclose
				}),

				content
			])
		]),

		// overlay background
		m( "div.overlay-background", {
			title: "Click to close the overlay.",
			onclick: onclose
		})
	];
};

module.exports = overlay;