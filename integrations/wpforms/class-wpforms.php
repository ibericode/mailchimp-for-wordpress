<?php

defined('ABSPATH') or exit;

/**
 * Class MC4WP_WPForms_Integration
 *
 * @ignore
 */
class MC4WP_WPForms_Integration extends MC4WP_Integration
{
    /**
     * @var string
     */
    public $name = 'WPForms';

    /**
     * @var string
     */
    public $description = 'Subscribe visitors from your WPForms forms.';


    /**
     * Add hooks
     */
    public function add_hooks()
    {
        add_action('wpforms_process', [ $this, 'listen_to_wpforms' ], 20, 3);
    }

    /**
     * @return bool
     */
    public function is_installed()
    {
        return defined('WPFORMS_VERSION');
    }

    /**
     * @since 3.0
     * @return array
     */
    public function get_ui_elements()
    {
        return [];
    }

    public function listen_to_wpforms($fields, $entry, $form_data)
    {
        foreach ($fields as $field_id => $field) {
            if ($field['type'] === 'mailchimp' && (int) $field['value_raw'] === 1) {
                return $this->subscribe_from_wpforms($field_id, $fields, $form_data);
            }
        }
    }

    public function subscribe_from_wpforms($checkbox_field_id, $fields, $form_data)
    {
        foreach ($fields as $field) {
            if ($field['type'] === 'email') {
                $email_address = $field['value'];
            }
        }

        $mailchimp_list_id      = $form_data['fields'][ $checkbox_field_id ]['mailchimp_list'];
        $this->options['lists'] = [ $mailchimp_list_id ];

        if (! empty($email_address)) {
            return $this->subscribe([ 'EMAIL' => $email_address ], $form_data['id']);
        }
    }

    /**
     * @param int $form_id
     * @return string
     */
    public function get_object_link($form_id)
    {
        return '<a href="' . admin_url(sprintf('admin.php?page=wpforms-builder&view=fields&form_id=%d', $form_id)) . '">WPForms</a>';
    }
}
