'use strict';

const __ = window.wp.i18n.__;
const { registerBlockType } = window.wp.blocks;
const { SelectControl } = window.wp.components;
const forms = window.mc4wp_forms;

registerBlockType( 'mailchimp-for-wp/form', {
    title: __( 'MailChimp for WordPress Form' ),
    description: __( 'Block showing a MailChimp for WordPress sign-up form'),
    category: 'widgets',
    attributes: {
        id : {
            type: 'int',
        },
    },
    supports: {
        html: false,
    },

    edit: function(props) {
        let options = forms.map(f => {
            return {
                label: f.name,
                value: f.id,
            }
        });

        return (
            <div style={{ backgroundColor: '#f8f9f9', padding: '14px'  }}>
                <SelectControl
                    label={__('MailChimp for WordPress Sign-up Form')}
                    value={props.attributes.id}
                    options={options}
                    onChange={value => {
                        props.setAttributes( { id: value } )
                    }}
                />
            </div>
        )
    },

    // Render nothing in the saved content, because we render in PHP
    save: function(props) {
       return null;
      //return `[mc4wp_form id="${props.attributes.id}"]`;
    },
});
