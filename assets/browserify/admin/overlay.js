'use strict';

const m = require('mithril');
const i18n = window.mc4wp_forms_i18n;

let _element,
	_onCloseCallback;

function close() {
	document.removeEventListener('keydown', onKeyDown);
	window.removeEventListener('resize', position);
	_onCloseCallback();
}

function onKeyDown(evt) {
	// close overlay when pressing ESC
	if(evt.keyCode === 27) {
		close();
	}

	// prevent ENTER
	if(evt.keyCode === 13 ) {
		evt.preventDefault();
	}
}

function position() {
	if( ! _element ) return;

	// fix for window width in IE8
	const windowWidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
	const windowHeight = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;

	const marginLeft = ( windowWidth - _element.clientWidth - 40 ) / 2;
	const marginTop  = ( windowHeight - _element.clientHeight - 40 ) / 2;

	_element.style.left = ( marginLeft > 0 ? marginLeft : 0 ) + "px";
	_element.style.top = ( marginTop > 0 ? marginTop : 0 ) + "px";
}

function storeElementReference(vnode) {
	_element = vnode.dom;
	position();
}


module.exports = {
	oncreate: function(vnode) {
		_onCloseCallback = vnode.attrs.onClose;
		document.addEventListener('keydown', onKeyDown);
		window.addEventListener('resize', position);
	},
	onremove: function(vnode) {
		document.removeEventListener('keydown', onKeyDown);
		window.removeEventListener('resize', position);
	},
	view: function(vnode) {
		return [
			m('div.overlay-wrap',
				m("div.overlay", { oncreate: storeElementReference },[
					// close icon
					m('span', {
						"class": 'close dashicons dashicons-no',
						title  : i18n.close,
						onclick: close
					}),
					vnode.children,
				])
			),
			m('div.overlay-background', {
				title: i18n.close,
				onclick: close
			})
		];
	}
};
