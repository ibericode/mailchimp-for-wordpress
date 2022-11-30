const m = require('mithril')
const i18n = window.mc4wp_forms_i18n

function Overlay () {}

Overlay.prototype.oncreate = function (vnode) {
  // bind event handlers so we can remove 'em later
  this.onDocumentKeydown = onKeyDown.bind(null, vnode.attrs.onClose)
  this.onWindowResize = onWindowResize.bind(null, vnode.dom)
  document.addEventListener('keydown', this.onDocumentKeydown)
  window.addEventListener('resize', this.onWindowResize)

  // call onWindowResize to properly center overlay
  this.onWindowResize()
}

Overlay.prototype.onremove = function () {
  document.removeEventListener('keydown', this.onDocumentKeydown)
  window.removeEventListener('resize', this.onWindowResize)
}

Overlay.prototype.view = function (vnode) {
  return [
    m('div.mc4wp-overlay-wrap',
      m('div.mc4wp-overlay', [
        // close icon
        m('span', {
          class: 'close dashicons dashicons-no',
          title: i18n.close,
          onclick: vnode.attrs.onClose
        }),
        vnode.children
      ])
    ),
    m('div.mc4wp-overlay-background', {
      title: i18n.close,
      onclick: vnode.attrs.onClose
    })
  ]
}

function onWindowResize (wrapperEl) {
  const el = wrapperEl.children[0]
  // fix for window width in IE8
  const windowWidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth
  const windowHeight = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight

  const marginLeft = (windowWidth - el.clientWidth - 40) / 2
  const marginTop = (windowHeight - el.clientHeight - 40) / 2

  el.style.left = (marginLeft > 0 ? marginLeft : 0) + 'px'
  el.style.top = (marginTop > 0 ? marginTop : 0) + 'px'
}

function onKeyDown (closeFn, evt) {
  switch (evt.keyCode) {
    // close overlay when pressing ESC
    case 27: closeFn(); break

    // prevent ENTER while overlay is open
    case 13: evt.preventDefault(); break
  }
}

module.exports = Overlay
