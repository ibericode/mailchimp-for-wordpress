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
  icon: (<svg width="16" height="16" viewBox="0 0 16 16" version="1.1"><path opacity="1" fill="#a0a5aa" fillOpacity="1" stroke="none" d="M 8.0097656 0.052734375 A 8 8 0 0 0 0.009765625 8.0527344 A 8 8 0 0 0 8.0097656 16.052734 A 8 8 0 0 0 16.009766 8.0527344 A 8 8 0 0 0 8.0097656 0.052734375 z M 9.2597656 4.171875 C 9.3205456 4.171875 9.9296146 5.0233822 10.611328 6.0664062 C 11.293041 7.1094313 12.296018 8.5331666 12.841797 9.2285156 L 13.833984 10.492188 L 13.316406 11.041016 C 13.031321 11.342334 12.708299 11.587891 12.599609 11.587891 C 12.253798 11.587891 11.266634 10.490156 10.349609 9.0859375 C 9.8610009 8.3377415 9.4126385 7.7229 9.3515625 7.71875 C 9.2904825 7.71455 9.2402344 8.3477011 9.2402344 9.1269531 L 9.2402344 10.544922 L 8.5839844 10.982422 C 8.2233854 11.223015 7.8735746 11.418294 7.8066406 11.417969 C 7.7397106 11.417644 7.4861075 10.997223 7.2421875 10.482422 C 6.9982675 9.9676199 6.6560079 9.3946444 6.4824219 9.2089844 L 6.1679688 8.8710938 L 6.0664062 9.34375 C 5.7203313 10.974656 5.6693219 11.090791 5.0917969 11.505859 C 4.5805569 11.873288 4.2347982 12.017623 4.1914062 11.882812 C 4.1839062 11.859632 4.1482681 11.574497 4.1113281 11.25 C 3.9708341 10.015897 3.5347399 8.7602861 2.8105469 7.5019531 C 2.5672129 7.0791451 2.5711235 7.0651693 2.9765625 6.8320312 C 3.2046215 6.7008903 3.5466561 6.4845105 3.7363281 6.3515625 C 4.0587811 6.1255455 4.1076376 6.1466348 4.4941406 6.6679688 C 4.8138896 7.0992628 4.9275606 7.166285 4.9941406 6.96875 C 5.0960956 6.666263 6.181165 5.8574219 6.484375 5.8574219 C 6.600668 5.8574219 6.8857635 6.1981904 7.1171875 6.6152344 C 7.3486105 7.0322784 7.5790294 7.3728809 7.6308594 7.3730469 C 7.7759584 7.3735219 7.9383234 5.8938023 7.8339844 5.5195312 C 7.7605544 5.2561423 7.8865035 5.0831575 8.4453125 4.6796875 C 8.8327545 4.3999485 9.1989846 4.171875 9.2597656 4.171875 z " /></svg>),
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
