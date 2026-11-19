<?php

defined('ABSPATH') || exit;

/**
 * Class MC4WP_Events_Manager_Integration
 *
 * Supports the booking form of Events Manager and the multiple bookings (cart) checkout of Events Manager Pro.
 *
 * @ignore
 */
class MC4WP_Events_Manager_Integration extends MC4WP_Integration
{
    /**
     * @var string
     */
    public $name = 'Events Manager';

    /**
     * @var string
     */
    public $description = 'Subscribes people from Events Manager booking forms.';


    /**
     * Add hooks
     */
    public function add_hooks()
    {
        if (! $this->options['implicit']) {
            // booking form (Events Manager 6.4+)
            add_action('em_booking_form_after_user_details', [ $this, 'output_booking_form_checkbox' ], 20, 0);

            // fallback for outdated (overridden) booking form templates without the action above
            add_action('em_booking_form_footer', [ $this, 'output_booking_form_checkbox' ], 20, 0);

            // checkout page (Events Manager Pro with multiple bookings mode)
            add_action('em_checkout_form_after_user_details', [ $this, 'output_checkbox' ], 20, 0);
        }

        add_action('em_bookings_added', [ $this, 'subscribe_from_events_manager' ], 5);
    }

    /**
     * Outputs the checkbox in the booking form of a single event.
     *
     * @hooked em_booking_form_after_user_details
     * @hooked em_booking_form_footer
     */
    public function output_booking_form_checkbox()
    {
        // in multiple bookings mode, this form only adds the event to the cart. The checkbox is shown on the checkout page instead.
        if (get_option('dbem_multiple_bookings')) {
            return;
        }

        // checkbox was already shown with the user details
        if (doing_action('em_booking_form_footer') && did_action('em_booking_form_after_user_details')) {
            return;
        }

        $this->output_checkbox();
    }

    /**
     * Subscribe from Events Manager booking forms.
     *
     * @param EM_Booking $booking Can also be an EM_Multiple_Booking when using the multiple bookings mode of Events Manager Pro.
     * @return bool
     */
    public function subscribe_from_events_manager($booking)
    {
        // Is this integration triggered? (checkbox checked or implicit)
        if (! $this->triggered()) {
            return false;
        }

        $data = $this->get_subscriber_data($booking);
        if (empty($data['EMAIL'])) {
            return false;
        }

        return $this->subscribe($data, $booking->booking_id);
    }

    /**
     * Gets the subscriber data from the person attached to the booking, falling back to the submitted form data.
     *
     * @param EM_Booking $booking
     * @return array
     */
    protected function get_subscriber_data($booking)
    {
        $person = method_exists($booking, 'get_person') ? $booking->get_person() : null;
        $form   = $this->get_data();

        $email = ! empty($person->user_email) ? $person->user_email : ( $form['user_email'] ?? '' );
        $fname = ! empty($person->first_name) ? $person->first_name : '';
        $lname = ! empty($person->last_name) ? $person->last_name : '';
        $name  = trim("{$fname} {$lname}");
        if ($name === '') {
            $name = ! empty($person->display_name) ? $person->display_name : ( $form['user_name'] ?? '' );
        }

        $data = [
            'EMAIL' => $email,
            'NAME'  => $name,
        ];

        if ($fname !== '') {
            $data['FNAME'] = $fname;
        }
        if ($lname !== '') {
            $data['LNAME'] = $lname;
        }

        return array_map('sanitize_text_field', $data);
    }

    /**
     * @return bool
     */
    public function is_installed()
    {
        return defined('EM_VERSION');
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function get_object_link($object_id)
    {
        $url = admin_url(sprintf('edit.php?post_type=event&page=events-manager-bookings&booking_id=%d', $object_id));

        // translators: %d is the Events Manager booking ID.
        return sprintf('<a href="%s">%s</a>', esc_url($url), sprintf(esc_html__('Booking #%d', 'mailchimp-for-wp'), $object_id));
    }
}
