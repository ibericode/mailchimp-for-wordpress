const editor = require('./admin/form-editor/form-editor.js')

require('./admin/form-editor/form-watcher.js')
require('./admin/form-editor/field-helper.js')
require('./admin/form-editor/field-manager.js')
require('./admin/notices.js')

// expose to global script
window.mc4wp.forms = window.mc4wp.forms || {}
window.mc4wp.forms.editor = editor
