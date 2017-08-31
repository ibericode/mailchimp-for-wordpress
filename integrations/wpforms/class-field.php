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
            0 => array(
                'label'   => __( 'Sign-up to our newsletter?', 'mailchimp-for-wp' ),
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

        //--------------------------------------------------------------------//
        // Basic field options
        //--------------------------------------------------------------------//

        // Options open markup
        $this->field_option( 'basic-options', $field, array( 'markup' => 'open' ) );

        // Choices
        $this->field_choices( $field );

        // Description
        $this->field_option( 'description',   $field );

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

    private function field_choices( $field ) {
        $tooltip = __( 'Add choices for the form field.', 'wpforms' );
        $dynamic = ! empty( $field['dynamic_choices'] ) ? esc_html( $field['dynamic_choices'] ) : '';
        $values  = ! empty( $field['choices'] ) ? $field['choices'] : $this->defaults;
        $class   = ! empty( $field['show_values'] ) && $field['show_values'] == '1' ? 'show-values' : '';
        $class  .= ! empty( $dynamic ) ? ' wpforms-hidden' : '';

        // Field option label
        $option_label = $this->field_element(
            'label',
            $field,
            array(
                'slug'          => 'choices',
                'value'         => __( 'Choices', 'wpforms' ),
                'tooltip'       => $tooltip,
            ),
            false
        );

        // Field option choices inputs
        $option_choices = sprintf( '<ul data-next-id="%s" class="choices-list %s" data-field-id="%d" data-field-type="%s">', max( array_keys( $values ) ) +1, $class, $field['id'], $this->type );
        foreach ( $values as $key => $value ) {
            $default = ! empty( $value['default'] ) ? $value['default'] : '';
            $option_choices .= sprintf( '<li data-key="%d">', $key );
            $option_choices .= sprintf( '<input type="checkbox" name="fields[%s][choices][%s][default]" class="default" value="1" %s>', $field['id'], $key, checked( '1', $default, false ) );
            $option_choices .= sprintf( '<input type="text" name="fields[%s][choices][%s][label]" value="%s" class="label">', $field['id'], $key, esc_attr( $value['label'] ) );
            $option_choices .= sprintf( '<input type="text" name="fields[%s][choices][%s][value]" value="%s" class="value">', $field['id'], $key, esc_attr( $value['value'] ) );
            $option_choices .= '</li>';
        }
        $option_choices .= '</ul>';

        // Field option row (markup) including label and input.
        $output = $this->field_element(
            'row',
            $field,
            array(
                'slug'    => 'choices',
                'content' => $option_label . $option_choices,
            )
        );

        echo $output;
    }
    
    /**
     * Field preview inside the builder.
     *
     * @since 1.0.0
     * @param array $field
     */
    public function field_preview( $field ) {
        $values  = !empty( $field['choices'] ) ? $field['choices'] : $this->defaults;

        // Field checkbox elements
        echo '<ul class="primary-input">';

        // Notify if currently empty
        if ( empty( $values ) ) {
            $values = array( 'label' => __( '(empty)', 'wpforms' ) );
        }

        // Individual checkbox options
        foreach ( $values as $key => $value ) {

            $default  = isset( $value['default'] ) ? $value['default'] : '';
            $selected = checked( '1', $default, false );

            printf( '<li><input type="checkbox" %s disabled>%s</li>', $selected, $value['label'] );
        }

        echo '</ul>';

        // Dynamic population is enabled and contains more than 20 items
        if ( isset( $total ) && $total > 20  ) {
            echo '<div class="wpforms-alert-dynamic wpforms-alert wpforms-alert-warning">';
            printf( __( 'Showing the first 20 choices.<br> All %d choices will be displayed when viewing the form.', 'wpforms' ), absint( $total ) );
            echo '</div>';
        }

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
        $form_id           = $form_data['id'];
        $choices           = $field['choices'];


        // List
        printf( '<ul id="%s" class="%s">', $field_id, $field_class );

        foreach( $choices as $key => $choice ) {

            $selected = isset( $choice['default'] ) ? '1' : '0' ;
            $depth    = isset( $choice['depth'] ) ? absint( $choice['depth'] ) : 1;

            printf( '<li class="choice-%d depth-%d">', $key, $depth );

            // Checkbox elements
            printf( '<input type="checkbox" id="wpforms-%d-field_%d_%d" name="wpforms[fields][%d]" value="%s" %s %s>',
                $form_id,
                $field['id'],
                $key,
                $field['id'],
                esc_attr( $choice['value'] ),
                checked( '1', $selected, false ),
                $field_required
            );

            printf( '<label class="wpforms-field-label-inline" for="wpforms-%d-field_%d_%d">%s</label>', $form_id, $field['id'], $key, wp_kses_post( $choice['label'] ) );

            echo '</li>';
        }

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

        $field        = $form_data['fields'][$field_id];
        $choice = array_pop( $field['choices' ] );
        $name         = sanitize_text_field( $choice['label'] );
        
        $data = array(
            'name'      => $name,
            'value'     => empty( $field_submit ) ? __( 'No' ) : __( 'Yes' ),
            'value_raw' => $field_submit,
            'id'        => absint( $field_id ),
            'type'      => $this->type,
        );


        // Push field details to be saved
        wpforms()->process->fields[$field_id] = $data;

        // Subscribe to MailChimp
        if( ! empty( $field_submit ) ) {
           // TODO: Find email and subscribe to MailChimp.
        }

    }
}
