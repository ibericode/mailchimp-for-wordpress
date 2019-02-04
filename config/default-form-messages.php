<?php
return array(
    'subscribed'               => array(
        'type' => 'success',
        'text' => __('Thank you, your sign-up request was successful! Please check your email inbox to confirm.', 'mailchimp-for-wp')
    ),
    'updated' 				   => array(
        'type' => 'success',
        'text' => __('Thank you, your records have been updated!', 'mailchimp-for-wp'),
    ),
    'unsubscribed'             => array(
        'type' => 'success',
        'text' => __('You were successfully unsubscribed.', 'mailchimp-for-wp'),
    ),
    'not_subscribed'           => array(
        'type' => 'notice',
        'text' => __('Given email address is not subscribed.', 'mailchimp-for-wp'),
    ),
    'error'                    => array(
        'type' => 'error',
        'text' => __('Oops. Something went wrong. Please try again later.', 'mailchimp-for-wp'),
    ),
    'invalid_email'            => array(
        'type' => 'error',
        'text' => __('Please provide a valid email address.', 'mailchimp-for-wp'),
    ),
    'already_subscribed'       => array(
        'type' => 'notice',
        'text' => __('Given email address is already subscribed, thank you!', 'mailchimp-for-wp'),
    ),
    'required_field_missing'   => array(
        'type' => 'error',
        'text' => __('Please fill in the required fields.', 'mailchimp-for-wp'),
    ),
    'no_lists_selected'        => array(
        'type' => 'error',
        'text' => __('Please select at least one list.', 'mailchimp-for-wp')
    ),
);
