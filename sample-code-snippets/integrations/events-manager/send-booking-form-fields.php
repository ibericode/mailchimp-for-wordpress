<?php

/**
 * This sends additional Events Manager booking form fields to Mailchimp.
 *
 * By default, the Events Manager integration sends the email address and name of the person making the booking.
 * Any other field from the booking form (including custom fields added through the Events Manager Pro form editor) can be added here.
 *
 * The array keys on the left are the Mailchimp field tags (Audience > Settings > Audience fields and *|MERGE|* tags).
 * The array keys on the right are the field IDs from the booking form (Events > Forms Editor in Events Manager Pro).
 *
 * @return array
 */

add_filter('mc4wp_integration_events-manager_data', function ($data, $booking_id) {
    $booking = em_get_booking($booking_id);
    if (empty($booking->booking_id)) {
        return $data;
    }

    // fields about the person making the booking (eg. address fields, or Pro fields of the type "user field")
    $registration = $booking->booking_meta['registration'] ?? [];

    // fields specific to this booking (eg. a comment, or Pro fields that are not user fields)
    $fields = $booking->booking_meta['booking'] ?? [];

    // this sends the "dbem_phone" field from Events Manager to a Mailchimp field called "PHONE".
    // Mailchimp's phone field type is strict about the number format, so a text field is recommended to avoid failed sign-ups.
    $phone = ! empty($registration['dbem_phone']) ? $registration['dbem_phone'] : $booking->get_person()->phone;
    if (! empty($phone)) {
        $data['PHONE'] = $phone;
    }

    // this sends the "dbem_city" field from Events Manager to a Mailchimp field called "CITY"
    if (! empty($registration['dbem_city'])) {
        $data['CITY'] = $registration['dbem_city'];
    }

    // a custom field you added in the Events Manager Pro form editor, with field ID "company"
    if (! empty($fields['company'])) {
        $data['COMPANY'] = $fields['company'];
    }

    // this sends the address fields to a Mailchimp field of the type "address" with tag "ADDRESS"
    if (! empty($registration['dbem_address'])) {
        $data['ADDRESS'] = [
            'addr1'   => $registration['dbem_address'],
            'city'    => $registration['dbem_city'] ?? '',
            'state'   => $registration['dbem_state'] ?? '',
            'zip'     => $registration['dbem_zip'] ?? '',
            'country' => $registration['dbem_country'] ?? '',
        ];
    }

    // this sends the name of the booked event to a Mailchimp field called "EVENT"
    // note: with the multiple bookings mode of Events Manager Pro, one booking can contain several events
    $event = $booking->get_event();
    if (! empty($event->event_name)) {
        $data['EVENT'] = $event->event_name;
    }

    return $data;
}, 10, 2);
