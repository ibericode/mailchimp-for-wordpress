<?php

/**
 * This will add the "my-class" attribute to the form wrapper element.
 *
 * @param array $classes
 * @param MC4WP_Form $form
 *
 * @return array
 */
function myprefix_add_css_class_to_form(array $classes, MC4WP_Form $form)
{
    $classes[] = 'my-class';
    return $classes;
}

add_filter('mc4wp_form_css_classes', 'myprefix_add_css_class_to_form', 10, 2);
