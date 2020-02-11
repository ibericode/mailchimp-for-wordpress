const __ = window.wp.i18n.__
const { registerBlockType } = window.wp.blocks
const { SelectControl } = window.wp.components // eslint-disable-line no-unused-vars
const forms = window.mc4wp_forms

registerBlockType('mailchimp-for-wp/form', {
  title: __('Mailchimp for WordPress Form'),
  description: __('Block showing a Mailchimp for WordPress sign-up form'),
  category: 'widgets',
  attributes: {
    id: {
      type: 'int'
    }
  },
  supports: {
    html: false
  },

  edit: function (props) {
    const options = forms.map(f => {
      return {
        label: f.name,
        value: f.id
      }
    })

    if (props.attributes.id === undefined && forms.length > 0) {
      props.setAttributes({ id: forms[0].id })
    }

    return (
      <div style={{ backgroundColor: '#f8f9f9', padding: '14px' }}>
        <SelectControl
          label={__('Mailchimp for WordPress Sign-up Form')}
          value={props.attributes.id}
          options={options}
          onChange={value => {
            props.setAttributes({ id: value })
          }}
        />
      </div>
    )
  },

  // Render nothing in the saved content, because we render in PHP
  save: function (props) {
    return null
    // return `[mc4wp_form id="${props.attributes.id}"]`;
  }
})
