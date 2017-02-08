'use strict';

// load CodeMirror & plugins
var CodeMirror = require('codemirror');
require('codemirror/mode/xml/xml');
require('codemirror/mode/javascript/javascript');
require('codemirror/mode/css/css');
require('codemirror/mode/htmlmixed/htmlmixed');
require('codemirror/addon/fold/xml-fold');
require('codemirror/addon/edit/matchtags');
require('codemirror/addon/edit/closetag.js');

var FormEditor = function(element) {

    // create dom representation of form
    var _dom = document.createElement('form'),
        domDirty = false,
        r = {},
        editor;

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

    window.addEventListener('load', function() {
        CodeMirror.signal(editor, "change");
    });

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
        return editor ? editor.getValue() : element.value;
    };

    r.query = function(query) {
        return dom().querySelectorAll(query.toLowerCase());
    };

    r.containsField = function(fieldName){
        return dom().elements.namedItem(fieldName.toLowerCase()) !== null;
    };

    r.insert = function( html ) {
        if( editor ) {
            editor.replaceSelection( html );
            editor.focus();
        } else {
            element.value += html;
        }
    };

    r.on = function(event,callback) {
        if( editor ) {
            // translate "input" event for CodeMirror
            event = ( event === 'input' ) ? 'changes' : event;
            return editor.on(event,callback);
        }

        return element.addEventListener(event,callback);
    };

    r.refresh = function() {
        editor && editor.refresh();
    };

    return r;
};

module.exports = FormEditor;