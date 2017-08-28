<?php
/**
 * Checkbox field.
 *
 * @package    WPForms
 * @author     WPForms
 * @since      1.0.0
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2016, WPForms LLC
 */
class MC4WP_WPForms_Field extends WPForms_Field {

    /**
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    public function init() {

        // Define field type information
        $this->name     = 'MailChimp';
        $this->type     = 'mailchimp';
        $this->icon     = 'fa-envelope-o';
        $this->order    = 21;
        $this->defaults = array(
            1 => array(
                'label'   => __( 'First Choice', 'wpforms' ),
                'value'   => '1',
                'default' => '',
            )
        );
    }

    /**
     * Field options panel inside the builder.
     *
     * @since 1.0.0
     * @param array $field
     */
    public function field_options( $field ) {
        $field['label_hide'] = '1';


        //--------------------------------------------------------------------//
        // Basic field options
        //--------------------------------------------------------------------//

        // Options open markup
        $this->field_option( 'basic-options', $field, array( 'markup' => 'open' ) );

        // Label
        $this->field_option( 'label', $field );

        // Description
        $this->field_option( 'description',   $field );

        $this->field_option_checked( $field );

        // Required toggle
        $this->field_option( 'required', $field );

        // Options close markup
        $this->field_option( 'basic-options', $field, array( 'markup' => 'close' ) );

        //--------------------------------------------------------------------//
        // Advanced field options
        //--------------------------------------------------------------------//

        // Options open markup
        $this->field_option( 'advanced-options', $field, array( 'markup' => 'open' ) );

        // Custom CSS classes
        $this->field_option( 'css', $field );

        // Options close markup
        $this->field_option( 'advanced-options', $field, array( 'markup' => 'close' ) );
    }
    
    private function field_option_checked( $field ) {
        $default = ! empty( $args['default'] ) ? $args['default'] : '0';
        $value   = isset( $field['checked'] ) ? $field['checked'] : $default;
		$tooltip = __( 'Check this option to precheck the field.', 'mailchimp-for-wp' );
		$output  = $this->field_element( 'checkbox', $field, array( 'slug' => 'checked', 'value' => $value, 'desc' => __( 'Pre-check?', 'wpforms' ), 'tooltip' => $tooltip ), false );
		$output  = $this->field_element( 'row',      $field, array( 'slug' => 'checked', 'content' => $output ), false );
        echo $output;
    }

    /**
     * Field preview inside the builder.
     *
     * @since 1.0.0
     * @param array $field
     */
    public function field_preview( $field ) {

        // Field checkbox elements
        echo '<ul class="primary-input">';

        $default  = empty( $field['checked'] ) ? '' : '1';
        $selected = checked( '1', $default, false );

        printf( '<li><input type="checkbox" %s disabled>%s</li>', $selected, $field['label'] );

        echo '</ul>';

        // Description
        $this->field_preview_option( 'description', $field );
    }

    /**
     * Field display on the form front-end.
     *
     * @since 1.0.0
     * @param array $field
     * @param array $form_data
     */
    public function field_display( $field, $field_atts, $form_data ) {

        // Setup and sanitize the necessary data
        $field_required    = !empty( $field['required'] ) ? ' required' : '';
        $field_class       = implode( ' ', array_map( 'sanitize_html_class', $field_atts['input_class'] ) );
        $field_id          = implode( ' ', array_map( 'sanitize_html_class', $field_atts['input_id'] ) );
        $field_data        = '';
        $form_id           = $form_data['id'];
        if ( !empty( $field_atts['input_data'] ) ) {
            foreach ( $field_atts['input_data'] as $key => $val ) {
                $field_data .= ' data-' . $key . '="' . $val . '"';
            }
        }


        // List
        printf( '<ul id="%s" class="%s" %s>', $field_id, $field_class, $field_data );

        $selected = ! empty( $field['checked'] ) ? '1' : '0' ;
        $key = 0;

        printf( '<li>' );

        // Checkbox elements
        printf( '<input type="checkbox" id="wpforms-%d-field_%d_%d" name="wpforms[fields][%d][]" value="%s" %s %s>',
            $form_id,
            $field['id'],
            $key,
            $field['id'],
            '1',
            checked( '1', $selected, false ),
            $field_required
        );

        printf( '<label class="wpforms-field-label-inline" for="wpforms-%d-field_%d_%d">%s</label>', $form_id, $field['id'], $key, wp_kses_post( $field['label'] ) );

        echo '</li>';


        echo '</ul>';
    }

    /**
     * Formats and sanitizes field.
     *
     * @since 1.0.2
     * @param int $field_id
     * @param array $field_submit
     * @param array $form_data
     */
    public function format( $field_id, $field_submit, $form_data ) {

        $field_submit = (array) $field_submit;
        $field        = $form_data['fields'][$field_id];
        $name         = sanitize_text_field( $field['label'] );
        $value_raw    = implode( "\n", array_map( 'sanitize_text_field', $field_submit ) );
        $value        = '';

        $data = array(
            'name'      => $name,
            'value'     => '',
            'value_raw' => $value_raw,
            'id'        => absint( $field_id ),
            'type'      => $this->type,
        );



        // Normal processing, dynamic population is off

        // If show_values is true, that means values posted are the raw values
        // and not the labels. So we need to get the label values.
        if ( !empty( $field['show_values'] ) && '1' == $field['show_values'] ) {

            $value = array();

            foreach( $field_submit as $field_submit_single ) {
                foreach( $field['choices'] as $choice ) {
                    if ( $choice['value'] == $field_submit_single ) {
                        $value[] = $choice['label'];
                        break;
                    }
                }
            }

            $data['value'] = !empty( $value ) ? implode( "\n", array_map( 'sanitize_text_field', $value ) ) : '';

        } else {
            $data['value'] = $value_raw;
        }


        // Push field details to be saved
        wpforms()->process->fields[$field_id] = $data;
    }
}
