<?php

class MC4WP_Gravity_Forms_Field extends GF_Field {

    public $type = 'mailchimp';

    public function get_form_editor_field_title() {
        return esc_attr__( 'MailChimp', 'mailchimp-for-wp' );
    }

    public function get_form_editor_field_settings() {
        return array(
            'label_setting',
            'rules_setting',
            'description_setting',
            'css_class_setting',
            'mailchimp_list_setting',
        );
    }

    public function get_field_label( $force_frontend_label, $value ) {
        return '';
    }

    public function get_field_input( $form, $value = '', $entry = null ) {

        $form_id         = absint( $form['id'] );
        $is_entry_detail = $this->is_entry_detail();
        $is_form_editor  = $this->is_form_editor();

        $id            = $this->id;
        $field_id      = $is_entry_detail || $is_form_editor || $form_id == 0 ? "input_$id" : 'input_' . $form_id . "_$id";
        $disabled_text = $is_form_editor ? 'disabled="disabled"' : '';

        return sprintf( "<div class='ginput_container ginput_container_checkbox'><ul class='gfield_checkbox' id='%s'>%s</ul></div>", esc_attr( $field_id ), $this->get_checkbox_choices( $value, $disabled_text, $form_id ) );
    }


    public function get_checkbox_choices( $value, $disabled_text, $form_id = 0 ) {
        $choices = '';
        $is_entry_detail = $this->is_entry_detail();
        $is_form_editor  = $this->is_form_editor();

        $choice = array(
            'text' => $this->label,
            'value' => 1,
            'isSelected' => false,
        );

        $choice_number = 1;
        $input_id = $this->id . '.' . $choice_number;

        if ( $is_entry_detail || $is_form_editor || $form_id == 0 ){
            $id = $this->id . '_' . $choice_number ++;
        } else {
            $id = $form_id . '_' . $this->id . '_' . $choice_number ++;
        }

        if ( ! isset( $_GET['gf_token'] ) && empty( $_POST ) && rgar( $choice, 'isSelected' ) ) {
            $checked = "checked='checked'";
        } elseif ( is_array( $value ) && RGFormsModel::choice_value_match( $this, $choice, rgget( $input_id, $value ) ) ) {
            $checked = "checked='checked'";
        } elseif ( ! is_array( $value ) && RGFormsModel::choice_value_match( $this, $choice, $value ) ) {
            $checked = "checked='checked'";
        } else {
            $checked = '';
        }

        $logic_event = $this->get_conditional_logic_event( 'click' );

        $tabindex     = $this->get_tabindex();
        $choice_value = $choice['value'];
        $choice_value  = esc_attr( $choice_value );
        $choice_markup = "<li class='gchoice_{$id}'>
                        <input name='input_{$input_id}' type='checkbox' $logic_event value='{$choice_value}' {$checked} id='choice_{$id}' {$tabindex} {$disabled_text} />
                        <label for='choice_{$id}' id='label_{$id}'>{$choice['text']}</label>
                    </li>";

        $choices .= gf_apply_filters( array(
            'gform_field_choice_markup_pre_render',
            $this->formId,
            $this->id
        ), $choice_markup, $choice, $this, $value );

        return gf_apply_filters( array( 'gform_field_choices', $this->formId, $this->id ), $choices, $this );

    }
}

