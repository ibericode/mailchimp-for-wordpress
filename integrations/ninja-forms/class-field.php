<?php if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class MC4WP_Ninja_Forms_Field
 */
class MC4WP_Ninja_Forms_Field extends NF_Abstracts_Input
{
    protected $_name = 'mc4wp_optin';

    protected $_nicename = 'MailChimp';

    protected $_section = 'misc';

    protected $_type = 'checkbox';

    protected $_icon = 'check-square-o';

    protected $_templates = 'checkbox';

    protected $_test_value = 0;

    protected $_settings =  array( 'checkbox_default_value', 'checked_calc_value', 'unchecked_calc_value' );

    protected $_settings_exclude = array( 'default', 'placeholder', 'input_limit_set', 'checkbox_values' );

    /**
     * NF_Fields_Checkbox constructor.
     * @since 3.0
     */
    public function __construct()
    {
        parent::__construct();

        $this->_nicename = __( 'MailChimp opt-in', 'mailchimp-for-wp' );

        $this->_settings[ 'label_pos' ][ 'value' ] = 'right';
    }

    /**
     * Admin Form Element
     * Display the checkbox on the edit submissions area.
     * @since 3.0
     *
     * @param $id Field ID.
     * @param $value Field value.
     * @return string HTML used for display of checkbox.
     */
    public function admin_form_element( $id, $value )
    {
        // If the checkboxes value is one...
        if( 1 == $value ) {
            // ...this variable to checked.
            $checked = 'checked';
        } else {
            // ...else leave the variable empty.
            $checked = '';
        }

        // Return HTML to be output to the submission edit page.
        return "<input type='hidden' name='fields[$id]' value='0' >
                <input type='checkbox' name='fields[$id]' id='' $checked>";
    }
}
