<?php

class MC4WP_WPForms_Field extends WPForms_Field
{
    /**
     * Primary class constructor.
     *
     * @since 1.0.0
     */
    public function init()
    {
        $this->name     = 'Mailchimp';
        $this->type     = 'mailchimp';
        $this->icon     = 'fa-envelope-o';
        $this->order    = 21;
        $this->defaults = [
            [
                'label'   => __('Sign-up to our newsletter?', 'mailchimp-for-wp'),
                'value'   => '1',
                'default' => '',
            ],
        ];
    }

    /**
     * Field options panel inside the builder.
     *
     * @since 1.0.0
     * @param array $field
     */
    public function field_options($field)
    {

        //--------------------------------------------------------------------//
        // Basic field options
        //--------------------------------------------------------------------//

        // Options open markup
        $this->field_option('basic-options', $field, [ 'markup' => 'open' ]);

        // Mailchimp list
        $this->field_option_mailchimp_list($field);

        // Choices
        $this->field_option_choices($field);

        // Description
        $this->field_option('description', $field);

        // Required toggle
        $this->field_option('required', $field);

        // Options close markup
        $this->field_option('basic-options', $field, [ 'markup' => 'close' ]);

        //--------------------------------------------------------------------//
        // Advanced field options
        //--------------------------------------------------------------------//

        // Options open markup
        $this->field_option('advanced-options', $field, [ 'markup' => 'open' ]);

        // Custom CSS classes
        $this->field_option('css', $field);

        // Options close markup
        $this->field_option('advanced-options', $field, [ 'markup' => 'close' ]);
    }

    private function field_option_mailchimp_list($field)
    {
        $mailchimp = new MC4WP_MailChimp();

        // Field option label
        $tooltip      = __('Select the Mailchimp list to subscribe to.', 'mailchimp-for-wp');
        $option_label = $this->field_element(
            'label',
            $field,
            [
                'slug'    => 'mailchimp-list',
                'value'   => __('Mailchimp list', 'mailchimp-for-wp'),
                'tooltip' => $tooltip,
            ],
            false
        );

        $option_select = sprintf('<select name="fields[%s][mailchimp_list]" data-field-id="%d" data-field-type="%s">', $field['id'], $field['id'], $this->type);
        $lists         = $mailchimp->get_lists();
        foreach ($lists as $list) {
            $option_select .= sprintf('<option value="%s" %s>%s</option>', $list->id, selected($list->id, $field['mailchimp_list'], false), $list->name);
        }
        $option_select .= '</select>';

        // Field option row (markup) including label and input.
        $output = $this->field_element(
            'row',
            $field,
            [
                'slug'    => 'mailchimp-list',
                'content' => $option_label . $option_select,
            ]
        );
    }

    private function field_option_choices($field)
    {
        $tooltip = __('Set your sign-up label text and whether it should be pre-checked.', 'mailchimp-for-wp');
        $values  = ! empty($field['choices']) ? $field['choices'] : $this->defaults;
        $class   = ! empty($field['show_values']) && (int) $field['show_values'] === 1 ? 'show-values' : '';
        $class  .= ! empty($dynamic) ? ' wpforms-hidden' : '';

        // Field option label
        $option_label = $this->field_element(
            'label',
            $field,
            [
                'slug'    => 'mailchimp-checkbox',
                'value'   => __('Sign-up checkbox', 'mailchimp-for-wp'),
                'tooltip' => $tooltip,
            ],
            false
        );

        // Field option choices inputs
        $option_choices = sprintf('<ul class="choices-list %s" data-field-id="%d" data-field-type="%s">', $class, $field['id'], $this->type);
        foreach ($values as $key => $value) {
            $default         = ! empty($value['default']) ? $value['default'] : '';
            $option_choices .= sprintf('<li data-key="%d">', $key);
            $option_choices .= sprintf('<input type="checkbox" name="fields[%s][choices][%s][default]" class="default" value="1" %s>', $field['id'], $key, checked('1', $default, false));
            $option_choices .= sprintf('<input type="text" name="fields[%s][choices][%s][label]" value="%s" class="label">', $field['id'], $key, esc_attr($value['label']));
            $option_choices .= sprintf('<input type="text" name="fields[%s][choices][%s][value]" value="%s" class="value">', $field['id'], $key, esc_attr($value['value']));
            $option_choices .= '</li>';
        }
        $option_choices .= '</ul>';

        // Field option row (markup) including label and input.
        $output = $this->field_element(
            'row',
            $field,
            [
                'slug'    => 'choices',
                'content' => $option_label . $option_choices,
            ]
        );
    }

    /**
     * Field preview inside the builder.
     *
     * @since 1.0.0
     * @param array $field
     */
    public function field_preview($field)
    {
        $values = ! empty($field['choices']) ? $field['choices'] : $this->defaults;

        // Field checkbox elements
        echo '<ul class="primary-input">';

        // Notify if currently empty
        if (empty($values)) {
            $values = [ 'label' => __('(empty)', 'wpforms') ];
        }

        // Individual checkbox options
        foreach ($values as $key => $value) {
            $default  = isset($value['default']) ? $value['default'] : '';
            $selected = checked('1', $default, false);

            printf('<li><input type="checkbox" %s disabled>%s</li>', $selected, $value['label']);
        }

        echo '</ul>';

        // Dynamic population is enabled and contains more than 20 items
        if (isset($total) && $total > 20) {
            echo '<div class="wpforms-alert-dynamic wpforms-alert wpforms-alert-warning">';
            printf(__('Showing the first 20 choices.<br> All %d choices will be displayed when viewing the form.', 'wpforms'), absint($total));
            echo '</div>';
        }

        // Description
        $this->field_preview_option('description', $field);
    }

    /**
     * Field display on the form front-end.
     *
     * @since 1.0.0
     * @param array $field
     * @param array $form_data
     */
    public function field_display($field, $field_atts, $form_data)
    {
        // Setup some defaults because WPForms broke their integration in v1.8.1.1
        $field_atts = array_merge([
            'input_class' => [],
            'input_id' => [],
        ], $field_atts);

        // Setup and sanitize the necessary data
        $field_required = ! empty($field['required']) ? ' required' : '';
        $field_class    = implode(' ', array_map('sanitize_html_class', (array) $field_atts['input_class']));
        $field_id       = implode(' ', array_map('sanitize_html_class', (array) $field_atts['input_id']));
        $form_id        = $form_data['id'];
        $choices        = (array) $field['choices'];

        // List
        printf('<ul id="%s" class="%s">', $field_id, $field_class);

        foreach ($choices as $key => $choice) {
            $selected = isset($choice['default']) ? '1' : '0';
            $depth    = isset($choice['depth']) ? absint($choice['depth']) : 1;

            printf('<li class="choice-%d depth-%d">', $key, $depth);

            // Checkbox elements
            printf(
                '<input type="checkbox" id="wpforms-%d-field_%d_%d" name="wpforms[fields][%d]" value="%s" %s %s>',
                $form_id,
                $field['id'],
                $key,
                $field['id'],
                esc_attr($choice['value']),
                checked('1', $selected, false),
                $field_required
            );

            printf('<label class="wpforms-field-label-inline" for="wpforms-%d-field_%d_%d">%s</label>', $form_id, $field['id'], $key, wp_kses_post($choice['label']));

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
    public function format($field_id, $field_submit, $form_data)
    {
        $field  = $form_data['fields'][ $field_id ];
        $choice = array_pop($field['choices']);
        $name   = sanitize_text_field($choice['label']);

        $data = [
            'name'      => $name,
            'value'     => empty($field_submit) ? __('No', 'mailchimp-for-wp') : __('Yes', 'mailchimp-for-wp'),
            'value_raw' => $field_submit,
            'id'        => absint($field_id),
            'type'      => $this->type,
        ];

        wpforms()->process->fields[ $field_id ] = $data;
    }
}
