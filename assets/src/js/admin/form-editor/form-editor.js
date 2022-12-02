// load CodeMirror & plugins
/**
 * @type {CodeMirror}
 */
const CodeMirror = require('codemirror')
require('codemirror/mode/xml/xml')
require('codemirror/mode/javascript/javascript')
require('codemirror/mode/css/css')
require('codemirror/mode/htmlmixed/htmlmixed')
require('codemirror/addon/fold/xml-fold.js')
require('codemirror/addon/edit/matchtags.js')
require('codemirror/addon/edit/closetag.js')
require('codemirror/addon/selection/active-line.js')
require('codemirror/addon/edit/matchbrackets.js')

/* variables */
const FormEditor = {}
const _dom = document.createElement('form')
let domDirty = false
let editor
const element = document.getElementById('mc4wp-form-content')
const previewFrame = document.getElementById('mc4wp-form-preview')
let previewDom
const templateRegex = /\{[^{}]+\}/g

/* functions */
function setPreviewDom () {
  const frameContent = previewFrame.contentDocument || previewFrame.contentWindow.document
  previewDom = frameContent.querySelector('.mc4wp-form-fields')

  if (previewDom) {
    updatePreview()
  }
}

function updatePreview () {
  if (!previewDom) {
    return setPreviewDom()
  }

  let markup = FormEditor.getValue()

  // replace template tags (twice, to allow for nested tags)
  markup = markup.replace(templateRegex, '').replace(templateRegex, '')

  // update dom
  previewDom.innerHTML = markup
  previewDom.dispatchEvent(new Event('mc4wp-refresh'))
}

/**
 * @returns {HTMLFormElement}
 */
function dom () {
  if (domDirty) {
    _dom.innerHTML = FormEditor.getValue().toLowerCase()
    domDirty = false
  }

  return _dom
}

/**
 * @returns {string}
 */
FormEditor.getValue = function () {
  return editor ? editor.getValue() : element.value
}

/**
 * @param {string} query
 * @returns {NodeListOf<Element>}
 */
FormEditor.query = function (query) {
  return dom().querySelectorAll(query.toLowerCase())
}

/**
 * @param {string} fieldName
 * @returns {boolean}
 */
FormEditor.containsField = function (fieldName) {
  return dom().elements.namedItem(fieldName.toLowerCase()) !== null
}

/**
 * @param {string} html
 */
FormEditor.insert = function (html) {
  if (editor) {
    editor.replaceSelection(html)
    editor.focus()
  } else {
    element.value += html
  }
}

/**
 *
 * @param {string} event
 * @param {function} callback
 * @returns {*}
 */
FormEditor.on = function (event, callback) {
  if (editor) {
    // translate "input" event for CodeMirror
    event = (event === 'input') ? 'changes' : event
    return editor.on(event, callback)
  }

  return element.addEventListener(event, callback)
}

FormEditor.refresh = function () {
  if (editor) editor.refresh()
}

/* bootstrap */
if (element) {
  _dom.innerHTML = element.value.toLowerCase()

  // turn <textarea> element into CodeMirror editor
  editor = CodeMirror.fromTextArea(element, {
    selectionPointer: true,
    mode: 'htmlmixed',
    htmlMode: true,
    autoCloseTags: true,
    autoRefresh: true,
    styleActiveLine: true,
    matchBrackets: true,
    matchTags: { bothTags: true }
  })

  // dispatch regular "change" on element event every time editor changes (IE9+ only)
  editor.on('change', function () {
    const event = new Event('change', { bubbles: true })
    element.dispatchEvent(event)
  })

  // set domDirty to true everytime the "change" event fires (a lot..)
  element.addEventListener('change', function () {
    domDirty = true
    updatePreview()
  })

  window.addEventListener('load', function () {
    CodeMirror.signal(editor, 'change')
  })
}

if (previewFrame) {
  previewFrame.addEventListener('load', setPreviewDom)
  setPreviewDom()
}

module.exports = FormEditor
