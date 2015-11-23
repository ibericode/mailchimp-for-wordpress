/* Editor */
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
	var _dom = document.createElement('form')
		, domDirty = false;
	_dom.setAttribute('novalidate','');
	_dom.innerHTML = element.value.toLowerCase();

	if( CodeMirror ) {
		editor = CodeMirror.fromTextArea(element, {
			selectionPointer: true,
			matchTags: { bothTags: true },
			mode: "htmlmixed",
			htmlMode: true,
			autoCloseTags: true,
			autoRefresh: true
		});

		// dispatch regular "change" on element event every time editor changes (IE9+ only)
		window.dispatchEvent && editor.on('change',function() {
			if(typeof(Event) === "function") {
				// Create a new 'change' event
				var event = new Event('change', { bubbles: true });
				element.dispatchEvent(event);
			}
		});
	}

	// set domDirty to true everytime the "change" event fires (a lot..)
	element.addEventListener('change',function() {
		domDirty = true;
	});

	function dom() {
		if( domDirty ) {
			_dom.innerHTML = r.getValue().toLowerCase();
			domDirty = false;
		}

		return _dom;
	}

	r.getValue = function() {
		if( editor ) {
			return editor.getValue();
		}

		return element.value;
	};

	r.query = function(query) {
		return dom().querySelectorAll(query);
	};

	r.containsField = function(fieldName){
		return r.query('[name^="'+ fieldName.toLowerCase() +'"]').length > 0;
	};

	r.insert = function( html ) {
		if( editor ) {
			editor.replaceSelection( html );
			editor.focus();
		}

		element.value += html;
	};

	r.on = function(event,callback) {
		if( editor ) {

			// translate "input" event for CodeMirror
			if( event === 'input' ) {
				event = 'changes';
			}

			return editor.on(event,callback);
		}

		return element.addEventListener(event,callback);
	};

	r.refresh = function() {
		if( editor ) {
			editor.refresh();
		}
	};

	return r;
};

module.exports = FormEditor;