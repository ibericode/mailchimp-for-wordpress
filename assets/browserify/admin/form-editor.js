/* Editor */
/* todo allow for CodeMirror failures */
var FormEditor = function(element) {

	// require CodeMirror & plugins
	var CodeMirror = require('codemirror');
	require('codemirror/mode/xml/xml');
	require('codemirror/mode/javascript/javascript');
	require('codemirror/mode/css/css');
	require('codemirror/mode/htmlmixed/htmlmixed');
	require('codemirror/addon/fold/xml-fold');
	require('codemirror/addon/edit/matchtags');
	require('codemirror/addon/edit/closetag.js');

	var r = {};
	var editor;

	r.editor = editor = CodeMirror.fromTextArea(element, {
		selectionPointer: true,
		matchTags: { bothTags: true },
		mode: "htmlmixed",
		htmlMode: true,
		autoCloseTags: true,
		autoRefresh: true
	});

	editor.on('change',function() {
		if(typeof(Event) === "function") {
			// Create a new 'change' event
			var event = new Event('change', { bubbles: true });
			element.dispatchEvent(event);
		}
	});

	r.getValue = function() {
		return editor.getValue();
	};

	r.insert = function( html ) {
		editor.replaceSelection( html );
		editor.focus();
	};

	r.on = function() {
		return editor.on.apply(editor,arguments);
	};

	r.refresh = function() {
		editor.refresh();
	};

	return r;
};

module.exports = FormEditor;