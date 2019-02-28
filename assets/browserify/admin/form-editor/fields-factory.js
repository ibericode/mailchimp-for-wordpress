var FieldFactory = function(fields, i18n) {
    'use strict';

    /**
     * Array of registered fields
     *
     * @type {Array}
     */
    var registeredFields = [];

    /**
     * Reset all previously registered fields
     */
    function reset() {
        // clear all of our fields
        registeredFields.forEach(fields.deregister);
    }

    /**
     * Helper function to quickly register a field and store it in local scope
     *
     * @param {object} data
     * @param {boolean} sticky
     */
    function register(category, data, sticky) {
        var field = fields.register(category, data);

        if( ! sticky ) {
            registeredFields.push(field);
        }
    }

    /**
     * Normalizes the field type which is passed by Mailchimp
     *
     * @param type
     * @returns {*}
     */
    function getFieldType(type) {

        var map = {
            'phone' : 'tel',
            'dropdown': 'select',
            'checkboxes': 'checkbox',
            'birthday': 'text'
        };

        return typeof map[ type ] !== "undefined" ? map[type] : type;
    }

    /**
     * Register the various fields for a merge var
     *
     * @param mergeField
     * @returns {boolean}
     */
    function registerMergeField(mergeField) {

        var category = i18n.listFields;
        var fieldType = getFieldType(mergeField.field_type);

        // name, type, title, value, required, label, placeholder, choices, wrap
        var data = {
            name: mergeField.tag,
            title: mergeField.name,
            required: mergeField.required,
            forceRequired: mergeField.required,
            type: fieldType,
            choices: mergeField.choices,
            acceptsMultipleValues: false // merge fields never accept multiple values.
        };

        if( data.type !== 'address' ) {
            register(category, data, false);
        } else {
            register(category, { name: data.name + '[addr1]', type: 'text', mailchimpType: 'address', title: i18n.streetAddress });
            register(category, { name: data.name + '[city]', type: 'text', mailchimpType: 'address', title: i18n.city });
            register(category, { name: data.name + '[state]', type: 'text', mailchimpType: 'address', title: i18n.state  });
            register(category, { name: data.name + '[zip]', type: 'text', mailchimpType: 'address', title: i18n.zip });
            register(category, { name: data.name + '[country]', type: 'select', mailchimpType: 'address', title: i18n.country, choices: mc4wp_vars.countries });
        }

        return true;
    }

    /**
     * Register a field for a Mailchimp grouping
     *
     * @param interestCategory
     */
    function registerInterestCategory(interestCategory){
        var category = i18n.interestCategories;
        var fieldType = getFieldType(interestCategory.field_type);

        var data = {
            title: interestCategory.name,
            name: 'INTERESTS[' + interestCategory.id + ']',
            type: fieldType,
            choices: interestCategory.interests,
            acceptsMultipleValues: fieldType === 'checkbox'
        };
        register(category, data, false);
    }

    /**
     * Register all fields belonging to a list
     *
     * @param list
     */
    function registerListFields(list) {

        // make sure EMAIL && public fields come first
        list.merge_fields = list.merge_fields.sort(function(a, b) {
            if( a.tag === 'EMAIL' || ( a.public && ! b.public ) ) {
                return -1;
            }

            if( ! a.public && b.public ) {
                return 1;
            }

            return 0;
        });

        // loop through merge vars
        list.merge_fields.forEach(registerMergeField);

        // loop through groupings
        list.interest_categories.forEach(registerInterestCategory);
    }

    /**
     * Register all lists fields
     *
     * @param lists
     */
    function registerListsFields(lists) {
        reset();
        lists.forEach(registerListFields);
    }

    function registerCustomFields(lists) {

        var choices,
            category = i18n.formFields;

        // register submit button
        register(category, {
            name: '',
            value: i18n.subscribe,
            type: "submit",
            title: i18n.submitButton
        }, true);

        // register lists choice field
        choices = {};
        for(var key in lists) {
            choices[lists[key].id] = lists[key].name;
        }

        register(category, {
            name: '_mc4wp_lists',
            type: 'checkbox',
            title: i18n.listChoice,
            choices: choices,
            help: i18n.listChoiceDescription,
            acceptsMultipleValues: true
        }, true);

        choices = {
            'subscribe': "Subscribe",
            'unsubscribe': "Unsubscribe"
        };
        register(category, {
            name: '_mc4wp_action',
            type: 'radio',
            title: i18n.formAction,
            choices: choices,
            value: 'subscribe',
            help: i18n.formActionDescription
        }, true);

        register(category, {
            name: 'AGREE_TO_TERMS',
            value: 1,
            type: "terms-checkbox",
            label: i18n.agreeToTerms,
            title: i18n.agreeToTermsShort,
            showLabel: false,
            required: true,
        }, true);
    }

    /**
     * Expose some methods
     */
    return {
        'registerCustomFields': registerCustomFields,
        'registerListFields': registerListFields,
        'registerListsFields': registerListsFields
    }

};

module.exports = FieldFactory;
