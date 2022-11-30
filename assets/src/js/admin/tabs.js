const URL = require('./url.js')
const context = document.getElementById('mc4wp-admin')
const tabElements = context.querySelectorAll('.mc4wp-tab')
const tabNavElements = context.querySelectorAll('.nav-tab')
const refererField = context.querySelector('input[name="_wp_http_referer"]')
const tabs = []

if (!Element.prototype.matches) {
  Element.prototype.matches = Element.prototype.msMatchesSelector ||
    Element.prototype.webkitMatchesSelector
}

[].forEach.call(tabElements, t => {
  const id = t.id.split('-').pop()
  const title = t.querySelector('h2:first-of-type').textContent

  tabs.push({
    id: id,
    title: title,
    element: t,
    nav: context.querySelectorAll('.nav-tab-' + id),
    open: open.bind(null, id)
  })
})

function get (id) {
  for (let i = 0; i < tabs.length; i++) {
    if (tabs[i].id === id) {
      return tabs[i]
    }
  }

  throw new Error('get() called with invalid tab id: ' + id)
}

function removeNavTabActiveClass (el) {
  el.className = el.className.replace('nav-tab-active', '')
}

function addNavTabActiveClass (el) {
  el.className += ' nav-tab-active'
  el.blur()
}

function hideTab (t) {
  t.className = t.className.replace('mc4wp-tab-active', '')
  t.style.display = ' none'
}

function open (tab, updateState) {
  tab = typeof tab === 'string' ? get(tab) : tab
  if (!tab) {
    return false
  }
  updateState = updateState === undefined ? true : updateState;

  // hide all tabs
  [].forEach.call(tabElements, hideTab);

  // remove .nav-tab-active from all tab navs
  [].forEach.call(tabNavElements, removeNavTabActiveClass);

  // add .nav-tab-active to current tab nav
  [].forEach.call(tab.nav, addNavTabActiveClass)

  // show target tab
  tab.element.style.display = 'block'
  tab.element.className += ' mc4wp-tab-active'

  // create new URL
  const url = URL.setParameter(window.location.href, 'tab', tab.id)

  // update hash
  if (history.pushState && updateState) {
    history.pushState(tab.id, '', url)
  }

  // update document title
  title(tab)

  // update referer field
  refererField.value = url

  // if thickbox is open, close it.
  if (typeof (window.tb_remove) === 'function') {
    window.tb_remove()
  }

  // refresh editor if open
  if (window.mc4wp && window.mc4wp.forms && window.mc4wp.forms.editor) {
    window.mc4wp.forms.editor.refresh()
  }

  return true
}

function title (tab) {
  const title = document.title.split('-')
  document.title = document.title.replace(title[0], tab.title + ' ')
}

function switchTab (evt) {
  const link = evt.target

  // get from data attribute
  let tabId = link.getAttribute('data-tab')

  // get from classname
  if (!tabId) {
    const match = link.className.match(/nav-tab-(\w+)?/)
    if (match) {
      tabId = match[1]
    }
  }

  // get from href
  if (!tabId) {
    const urlParams = URL.parse(link.href)
    if (!urlParams.tab) { return }
    tabId = urlParams.tab
  }

  const opened = open(tabId)

  if (opened) {
    evt.preventDefault()
    evt.returnValue = false
    return false
  }

  return true
}

function init () {
  // check offsetParent to determine whether tab is visible
  const activeTab = tabs.filter(t => t.element.offsetParent !== null).shift()
  if (!activeTab) {
    return
  }

  const tab = get(activeTab.id)
  if (!tab) {
    return
  }

  // check if tab is in history
  if (history.replaceState && history.state === null) {
    history.replaceState(tab.id, '')
  }

  // update document title
  title(tab)
}

function onDocumentClick (evt) {
  if (evt.target.matches('.tab-link')) {
    switchTab(evt)
  }
}

function onPopState (evt) {
  if (evt.state) {
    open(evt.state, false)
  }
}

// add event listeners
for (let i = 0; i < tabNavElements.length; i++) {
  tabNavElements[i].addEventListener('click', switchTab)
}
document.body.addEventListener('click', onDocumentClick)
if (window.addEventListener && history.pushState) {
  window.addEventListener('popstate', onPopState)
}

init()

module.exports = {
  open: open,
  get: get
}
