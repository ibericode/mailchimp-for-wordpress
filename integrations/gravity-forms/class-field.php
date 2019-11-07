<?php

class MC4WP_Gravity_Forms_Field extends GF_Field
{
    public $type = 'mailchimp';

    /**
     * Returns the field markup; including field label, description, validation, and the form editor admin buttons.
     *
     * The {FIELD} placeholder will be replaced in GFFormDisplay::get_field_content with the markup returned by GF_Field::get_field_input().
     *
     * @param string|array $value                The field value. From default/dynamic population, $_POST, or a resumed incomplete submission.
     * @param bool         $force_frontend_label Should the frontend label be displayed in the admin even if an admin label is configured.
     * @param array        $form                 The Form Object currently being processed.
     *
     * @return string
     */
    public function get_field_content($value, $force_frontend_label, $form)
    {
        $validation_message = ($this->failed_validation && ! empty($this->validation_message)) ? sprintf("<div class='gfield_description validation_message'>%s</div>", $this->validation_message) : '';
        $is_form_editor  = $this->is_form_editor();
        $is_entry_detail = $this->is_entry_detail();
        $is_admin        = $is_form_editor || $is_entry_detail;

        $admin_buttons = $this->get_admin_buttons();

        $description = $this->get_description($this->description, 'gfield_description');
        if ($this->is_description_above($form)) {
            $clear         = $is_admin ? "<div class='gf_clear'></div>" : '';
            $field_content = sprintf("%s%s{FIELD}%s$clear", $admin_buttons, $description, $validation_message);
        } else {
            $field_content = sprintf("%s{FIELD}%s%s", $admin_buttons, $description, $validation_message);
        }

        return $field_content;
    }

    public function get_form_editor_field_title()
    {
        return esc_attr__('Mailchimp for WordPress', 'mailchimp-for-wp');
    }

    public function get_form_editor_field_settings()
    {
        return array(
            'label_setting',
            'description_setting',
            'css_class_setting',
            'mailchimp_list_setting',
            'mailchimp_double_optin',
            'mailchimp_precheck',
            'rules_setting',
        );
    }

    public function get_field_input($form, $value = '', $entry = null)
    {
        $form_id         = absint($form['id']);
        $is_entry_detail = $this->is_entry_detail();
        $is_form_editor  = $this->is_form_editor();

        $id            = $this->id;
        $field_id      = $is_entry_detail || $is_form_editor || $form_id == 0 ? "input_$id" : 'input_' . $form_id . "_$id";
        $disabled_text = $is_form_editor ? 'disabled="disabled"' : '';

        return sprintf("<div class='ginput_container ginput_container_checkbox'><ul class='gfield_checkbox' id='%s'>%s</ul></div>", esc_attr($field_id), $this->get_checkbox_choices($value, $disabled_text, $form_id));
    }

    private function apply_mc4wp_options_filters($options)
    {
        $options = apply_filters('mc4wp_gravity-forms_integration_options', $options);
        $options = apply_filters('mc4wp_integration_gravity-forms_options', $options);
        return $options;
    }


    public function get_checkbox_choices($value, $disabled_text, $form_id = 0)
    {
        $choices = '';
        $is_entry_detail = $this->is_entry_detail();
        $is_form_editor  = $this->is_form_editor();

        $options = array(
            'label' => $this->get_field_label(false, $value),
            'precheck' => isset($this->mailchimp_precheck) ? $this->mailchimp_precheck : false,
        );
        $options = $this->apply_mc4wp_options_filters($options);

        // generate html
        $choice = array(
            'text' => $options['label'],
            'value' => "1",
            'isSelected' => $options['precheck'],
        );
        
        $input_id = $this->id;
        if ($is_entry_detail || $is_form_editor || $form_id == 0) {
            $id = $this->id;
        } else {
            $id = $form_id . '_' . $this->id;
        }

        if (! isset($_GET['gf_token']) && empty($_POST) && rgar($choice, 'isSelected')) {
            $checked = "checked='checked'";
        } elseif (is_array($value) && RGFormsModel::choice_value_match($this, $choice, rgget($input_id, $value))) {
            $checked = "checked='checked'";
        } elseif (! is_array($value) && RGFormsModel::choice_value_match($this, $choice, $value)) {
            $checked = "checked='checked'";
        } else {
            $checked = '';
        }

        $tabindex     = $this->get_tabindex();
        $choice_value = $choice['value'];
        $choice_value  = esc_attr($choice_value);
        $choice_markup = "<li class='gchoice_{$id}'>
                        <input name='input_{$input_id}' type='checkbox' value='{$choice_value}' {$checked} id='choice_{$id}' {$tabindex} {$disabled_text} />
                        <label for='choice_{$id}' id='label_{$id}'>{$choice['text']}</label>
                    </li>";

        $choices .= gf_apply_filters(array(
            'gform_field_choice_markup_pre_render',
            $this->formId,
            $this->id
        ), $choice_markup, $choice, $this, $value);

        return gf_apply_filters(array( 'gform_field_choices', $this->formId, $this->id ), $choices, $this);
    }
}
