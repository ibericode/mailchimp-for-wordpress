<?php

/**
 * Example 1
 *
 * Performing custom validation logic on forms.
 *
 * @param array $errors
 * @param MC4WP_Form $form
 * @return array
 */

add_filter('mc4wp_form_errors', function (array $errors, MC4WP_Form $form) {

    // perform logic here
    $custom_validation_passed = false;

    if (! $custom_validation_passed) {
        $errors[] = 'your_error_code';
    }

    // $errors is empty if there were no errors.
    return $errors;
}, 10, 2);


/**
 * Example 2
 *
 * Require SOME_FIELD to have a value of "Some value"
 */
add_filter('mc4wp_form_errors', function (array $errors, MC4WP_Form $form) {

    $data = $form->get_data();

    if ($data['SOME_FIELD'] !== 'Some value') {
        $errors[] = 'incorrect_value';
    }

    return $errors;
}, 10, 2);
