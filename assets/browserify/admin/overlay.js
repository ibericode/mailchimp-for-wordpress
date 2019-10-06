'use strict';

const m = require('mithril');
const i18n = window.mc4wp_forms_i18n;

class Overlay {
	constructor(vnode) {
		this.onclose = vnode.attrs.onClose;

		this.close = this.close.bind(this);
		this.onKeyDown = this.onKeyDown.bind(this);
		this.onWindowResize = this.onWindowResize.bind(this);
	}

	oncreate() {
		document.addEventListener('keydown', this.onKeyDown);
		window.addEventListener('resize', this.onWindowResize);
	}

	onremove() {
		document.removeEventListener('keydown', this.onKeyDown);
		window.removeEventListener('resize', this.onWindowResize);
	}

	close() {
		this.onclose();
	}

	onKeyDown(evt) {
		// close overlay when pressing ESC
		if(evt.keyCode === 27) {
			this.close();
		}

		// prevent ENTER
		if(evt.keyCode === 13 ) {
			evt.preventDefault();
		}
	}

	onWindowResize() {
		// fix for window width in IE8
		const windowWidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
		const windowHeight = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;

		const marginLeft = ( windowWidth - this.element.clientWidth - 40 ) / 2;
		const marginTop  = ( windowHeight - this.element.clientHeight - 40 ) / 2;

		this.element.style.left = ( marginLeft > 0 ? marginLeft : 0 ) + "px";
		this.element.style.top = ( marginTop > 0 ? marginTop : 0 ) + "px";
	}

	view(vnode) {
		return [
			m('div.overlay-wrap',
				m("div.overlay",
					{
						oncreate: (vnode) => {
							this.element = vnode.dom;
							this.onWindowResize();
						}
					}, [
					// close icon
					m('span', {
						"class": 'close dashicons dashicons-no',
						title  : i18n.close,
						onclick: this.close
					}),
					vnode.children,
				])
			),
			m('div.overlay-background', {
				title: i18n.close,
				onclick: this.close
			})
		];
	}
}

module.exports = Overlay;
