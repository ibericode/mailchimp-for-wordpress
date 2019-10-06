'use strict';

const m = require('mithril');
const helpers = require('../helpers.js');
const editor = require('./form-editor.js');
const fields = require('./fields.js');
let requiredFieldsInput = document.getElementById('required-fields');

function updateFields() {
    fields.getAll().forEach(function(field) {
        // don't run for empty field names
        if(field.name.length <= 0) return;

        let fieldName = field.name;
        if( field.type === 'checkbox' ) {
            fieldName += '[]';
        }

        field.inFormContent = editor.containsField( fieldName );

        // if form contains 1 address field of group, mark all fields in this group as "required"
        if( field.mailchimpType === 'address' ) {
            field.originalRequiredValue = field.originalRequiredValue === undefined ? field.forceRequired = true : field.originalRequiredValue;

            // query other fields for this address group
            let nameGroup = field.name.replace(/\[(\w+)\]/g, '' );
            if( editor.query('[name^="' + nameGroup + '"]').length > 0 ) {
                if( field.originalRequiredValue === undefined ) {
                    field.originalRequiredValue = field.forceRequired();
                }
                field.forceRequired = true;
            } else {
                field.forceRequired = field.originalRequiredValue;
            }
        }

    });

    findRequiredFields();
    m.redraw();
}

function findRequiredFields() {

    // query fields required by Mailchimp
    let requiredFields = fields.getAllWhere('forceRequired', true).map(function(f) { return f.name.toUpperCase().replace(/\[(\w+)\]/g, '.$1' ); });

    // query fields in form with [required] attribute
    let requiredFieldElements = editor.query('[required]');
    Array.prototype.forEach.call(requiredFieldElements, function(el) {
        let name = el.name;

        // bail if name attr empty or starts with underscore
        if(!name || name.length < 0 || name[0] === '_') {
            return;
        }

        // replace array brackets with dot style notation
        name = name.replace(/\[(\w+)\]/g, '.$1' );

        // replace array-style fields
        name = name.replace(/\[\]$/, '');

        // uppercase everything before the .
        let pos = name.indexOf('.');
        pos = pos > 0 ? pos : name.length;
        name = name.substr(0, pos).toUpperCase() + name.substr(pos);

        // only add field if it's not already in it
        if( requiredFields.indexOf(name) === -1 ) {
            requiredFields.push(name);
        }
    });

    // update meta
    requiredFieldsInput.value = requiredFields.join(',');
}

// events
editor.on('change', helpers.debounce(updateFields, 500));
fields.on('change', helpers.debounce(updateFields, 500));

