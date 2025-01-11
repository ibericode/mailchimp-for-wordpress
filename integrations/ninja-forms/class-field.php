<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Class MC4WP_Ninja_Forms_Field
 */
class MC4WP_Ninja_Forms_Field extends NF_Abstracts_Input
{
    protected $_name             = 'mc4wp_optin';
    protected $_nicename         = 'Mailchimp opt-in';
    protected $_section          = 'misc';
    protected $_type             = 'checkbox';
    protected $_icon             = 'check-square-o';
    protected $_templates        = 'checkbox';
    protected $_test_value       = 0;
    protected $_settings         = [ 'checkbox_default_value', 'checked_calc_value', 'unchecked_calc_value' ];
    protected $_settings_exclude = [ 'default', 'placeholder', 'input_limit_set', 'checkbox_values' ];

    /**
     * NF_Fields_Checkbox constructor.
     * @since 3.0
     */
    public function __construct()
    {
        parent::__construct();

        $this->_settings['label_pos']['value'] = 'right';

        add_filter('ninja_forms_custom_columns', [ $this, 'custom_columns' ], 10, 2);
        add_action('init', [$this, 'translate_nicename']);
    }

    public function translate_nicename()
    {
        $this->_nicename = __('Mailchimp opt-in', 'mailchimp-for-wp');
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
    public function admin_form_element($id, $value)
    {
        // If the checkboxes value is one...
        if (1 === (int) $value) {
            // ...this variable to checked.
            $checked = 'checked';
        } else {
            // ...else leave the variable empty.
            $checked = '';
        }

        // Return HTML to be output to the submission edit page.
        return "<input type='hidden' name='fields[$id]' value='0' ><input type='checkbox' name='fields[$id]' value='1' id='' $checked>";
    }

    /**
    * Custom Columns
    * Creates what is displayed in the columns on the submissions page.
    * @since 3.0
    *
    * @param string $value checkbox value
    * @param MC4WP_Ninja_Forms_Field $field field model.
    * @return $value string|void
    */
    public function custom_columns($value, $field)
    {
        // If the field type is equal to checkbox...
        if ('mc4wp_optin' === $field->get_setting('type')) {
            // Backwards compatibility check for the new checked value setting.
            if (null === $field->get_setting('checked_value') && 1 === (int) $value) {
                return __('Checked', 'ninja-forms');
            } elseif (null === $field->get_setting('unchecked_value') && 0 === (int) $value) {
                return __('Unchecked', 'ninja-forms');
            }

            // If the field value is set to 1....
            if (1 === (int) $value) {
                // Set the value to the checked value setting.
                $value = $field->get_setting('checked_value');
            } else {
                // Else set the value to the unchecked value setting.
                $value = $field->get_setting('unchecked_value');
            }
        }
        return $value;
    }
}
