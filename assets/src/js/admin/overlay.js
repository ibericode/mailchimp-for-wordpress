const m = require('mithril')
const i18n = window.mc4wp_forms_i18n

function Overlay (vnode) {
  let element
  const onclose = vnode.attrs.onClose

  function oncreate () {
    document.addEventListener('keydown', onKeyDown)
    window.addEventListener('resize', onWindowResize)
  }

  function onremove () {
    document.removeEventListener('keydown', onKeyDown)
    window.removeEventListener('resize', onWindowResize)
  }

  function close () {
    onclose.apply(null)
  }

  function onKeyDown (evt) {
    // close overlay when pressing ESC
    if (evt.keyCode === 27) {
      close()
    }

    // prevent ENTER
    if (evt.keyCode === 13) {
      evt.preventDefault()
    }
  }

  function onWindowResize () {
    // fix for window width in IE8
    const windowWidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth
    const windowHeight = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight

    const marginLeft = (windowWidth - element.clientWidth - 40) / 2
    const marginTop = (windowHeight - element.clientHeight - 40) / 2

    element.style.left = (marginLeft > 0 ? marginLeft : 0) + 'px'
    element.style.top = (marginTop > 0 ? marginTop : 0) + 'px'
  }

  function view (vnode) {
    return [
      m('div.mc4wp-overlay-wrap',
        m('div.mc4wp-overlay',
          {
            oncreate: (vnode) => {
              element = vnode.dom
              onWindowResize()
            }
          }, [
            // close icon
            m('span', {
              class: 'close dashicons dashicons-no',
              title: i18n.close,
              onclick: close
            }),
            vnode.children
          ])
      ),
      m('div.mc4wp-overlay-background', {
        title: i18n.close,
        onclick: close
      })
    ]
  }

  return { oncreate, onremove, view }
}

module.exports = Overlay
