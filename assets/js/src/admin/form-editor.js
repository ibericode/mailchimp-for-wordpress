/* Editor */
/* todo allow for CodeMirror failures */
var FormEditor = function(element) {

	var r = {};
	var editor;

	r.editor = editor = CodeMirror.fromTextArea(element, {
		selectionPointer: true,
		matchTags: { bothTags: true },
		mode: "text/html",
		htmlMode: true,
		autoCloseTags: true,
		autoRefresh: true
	});

	r.getValue = function() {
		return editor.getValue();
	};

	r.insert = function( html ) {
		editor.replaceSelection( html );
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