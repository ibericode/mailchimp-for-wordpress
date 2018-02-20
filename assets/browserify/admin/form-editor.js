'use strict';

// load CodeMirror & plugins
var CodeMirror = require('codemirror');
require('codemirror/mode/xml/xml');
require('codemirror/mode/javascript/javascript');
require('codemirror/mode/css/css');
require('codemirror/mode/htmlmixed/htmlmixed');
require('codemirror/addon/fold/xml-fold.js');
require('codemirror/addon/edit/matchtags.js');
require('codemirror/addon/edit/closetag.js');
require('codemirror/addon/selection/active-line.js');
require('codemirror/addon/edit/matchbrackets.js');

/* variables */
var FormEditor = {};
var _dom = document.createElement('form');
var domDirty = false;
var editor;
var element = document.getElementById('mc4wp-form-content');
var previewFrame = document.getElementById('mc4wp-form-preview');
var previewDom;

/* functions */
function setPreviewDom() {
    let frameContent = previewFrame.contentDocument || previewFrame.contentWindow.document;
    previewDom = frameContent.querySelector('.mc4wp-form-fields');
    
    if(previewDom) { 
        updatePreview();
    }
}

function updatePreview() {
    let markup = FormEditor.getValue();

    // replace template tags
    // markup = markup.replace(templateRegex, function(s, m) {
    //     if(arguments[3]) {
    //         return arguments[3];
    //     }

    //     return '';
    // });

    // update dom
    previewDom.innerHTML = markup;
    previewDom.dispatchEvent(new Event('mc4wp-refresh'));
}

window.addEventListener('load', function() {
    CodeMirror.signal(editor, "change");
});

// set domDirty to true everytime the "change" event fires (a lot..)
element.addEventListener('change',function() {
    domDirty = true;
    updatePreview();
});

function dom() {
    if( domDirty ) {
        _dom.innerHTML = FormEditor.getValue().toLowerCase();
        domDirty = false;
    }

    return _dom;
}

FormEditor.getValue = function() {
    return editor ? editor.getValue() : element.value;
};

FormEditor.query = function(query) {
    return dom().querySelectorAll(query.toLowerCase());
};

FormEditor.containsField = function(fieldName){
    return dom().elements.namedItem(fieldName.toLowerCase()) !== null;
};

FormEditor.insert = function( html ) {
    if( editor ) {
        editor.replaceSelection( html );
        editor.focus();
    } else {
        element.value += html;
    }
};

FormEditor.on = function(event,callback) {
    if( editor ) {
        // translate "input" event for CodeMirror
        event = ( event === 'input' ) ? 'changes' : event;
        return editor.on(event,callback);
    }

    return element.addEventListener(event, callback);
};

FormEditor.refresh = function() {
    editor && editor.refresh();
};

/* bootstrap */
_dom.innerHTML = element.value.toLowerCase();

if( CodeMirror ) {
    editor = CodeMirror.fromTextArea(element, {
        selectionPointer: true,
        mode: "htmlmixed",
        htmlMode: true,
        autoCloseTags: true,
        autoRefresh: true,
        styleActiveLine: true,
        matchBrackets: true,
        matchTags: { bothTags: true },
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

previewFrame.addEventListener('load', setPreviewDom);
setPreviewDom.call();

/* exports */
module.exports = FormEditor;
