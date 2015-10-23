/* Editor */
var FormEditor = function(element) {
	var editor  = CodeMirror.fromTextArea(element, {
		selectionPointer: true,
		matchTags: { bothTags: true },
		mode: "text/html",
		htmlMode: true,
		autoCloseTags: true
	});

	return editor;
};

module.exports = FormEditor;