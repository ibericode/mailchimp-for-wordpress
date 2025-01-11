<?php

return [
    'subscribed'             => [
        'type' => 'success',
        'text' => esc_html__('Thank you, your sign-up request was successful! Please check your email inbox to confirm.', 'mailchimp-for-wp'),
    ],
    'updated'                => [
        'type' => 'success',
        'text' => esc_html__('Thank you, your records have been updated!', 'mailchimp-for-wp'),
    ],
    'unsubscribed'           => [
        'type' => 'success',
        'text' => esc_html__('You were successfully unsubscribed.', 'mailchimp-for-wp'),
    ],
    'not_subscribed'         => [
        'type' => 'notice',
        'text' => esc_html__('Given email address is not subscribed.', 'mailchimp-for-wp'),
    ],
    'error'                  => [
        'type' => 'error',
        'text' => esc_html__('Oops. Something went wrong. Please try again later.', 'mailchimp-for-wp'),
    ],
    'invalid_email'          => [
        'type' => 'error',
        'text' => esc_html__('Please provide a valid email address.', 'mailchimp-for-wp'),
    ],
    'already_subscribed'     => [
        'type' => 'notice',
        'text' => esc_html__('Given email address is already subscribed, thank you!', 'mailchimp-for-wp'),
    ],
    'required_field_missing' => [
        'type' => 'error',
        'text' => esc_html__('Please fill in the required fields.', 'mailchimp-for-wp'),
    ],
    'no_lists_selected'      => [
        'type' => 'error',
        'text' => esc_html__('Please select at least one list.', 'mailchimp-for-wp'),
    ],
];
