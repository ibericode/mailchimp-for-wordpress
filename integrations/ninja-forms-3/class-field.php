<?php if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class MC4WP_Ninja_Forms_Field
 */
class MC4WP_Ninja_Forms_Field extends NF_Abstracts_Input
{
    protected $_name = 'checkbox';

    protected $_nicename = 'MailChimp';

    protected $_section = 'misc';

    protected $_icon = 'check-square-o';

    protected $_type = 'checkbox';

    protected $_templates = 'checkbox';

    protected $_test_value = 0;

    protected $_settings =  array( 'checkbox_default_value' );

    protected $_settings_exclude = array( 'default', 'placeholder', 'input_limit_set' );

    public function __construct()
    {
        parent::__construct();

        $this->_settings['mc4wp_checkbox'] = true;
        $this->_settings[ 'label_pos' ][ 'value' ] = 'right';

        add_filter( 'ninja_forms_custom_columns', array( $this, 'custom_columns' ), 10, 2 );

        add_filter( 'ninja_forms_merge_tag_value_' . $this->_name, array( $this, 'filter_merge_tag_value' ), 10, 2 );
        add_filter( 'ninja_forms_subs_export_field_value_' . $this->_type, array( $this, 'export_value' ), 10 );
    }



    public function admin_form_element( $id, $value )
    {
        $checked = ( $value ) ? "checked" : "";

        return "<input type='checkbox' name='fields[$id]' id='' $checked>";
    }

    public function custom_columns( $value, $field )
    {
        if( 'checkbox' == $field->get_setting( 'type' ) ){
            $value = ( $value ) ? __( 'checked', 'ninja-forms' ) : __( 'unchecked', 'ninja-forms' );
        }
        return $value;
    }

    public function filter_merge_tag_value( $value, $field )
    {
        if( $value ){
            if( isset( $field[ 'checked_calc_value' ] ) && '' != $field[ 'checked_calc_value' ] ) {
                return $field['checked_calc_value'];
            } else {
                return __( 'checked', 'ninja-forms' );
            }
        }

        if( ! $value ){
            if( isset( $field[ 'unchecked_calc_value' ] ) && '' != $field[ 'unchecked_calc_value' ] ) {
                return $field['unchecked_calc_value'];
            } else {
                return __( 'unchecked', 'ninja-forms' );
            }
        }

        return $value;
    }

    public function export_value( $value ) {
        if ( 1 == $value ) {
            return __( 'checked', 'ninja-forms' );
        } else {
            return __( 'unchecked', 'ninja-forms' );
        }
    }

    // TODO: fix this.
    public function process( $settings, $data ) {
//        var_dump( $settings );
//        var_dump( $data );
//        die();
    }
}
