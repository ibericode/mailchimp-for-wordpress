<?php

/**
 * Gets the absolute url to edit a form
 *
 * @param int $form_id ID of the form
 * @param string $tab Tab identifier to open
 *
 * @return string
 */
function mc4wp_get_edit_form_url($form_id, $tab = '')
{
    $url = admin_url(sprintf('admin.php?page=mailchimp-for-wp-forms&view=edit-form&form_id=%d', $form_id));

    if (! empty($tab)) {
        $url .= sprintf('&tab=%s', $tab);
    }

    return $url;
}

/**
 * Get absolute URL to create a new form
 *
 * @return string
 */
function mc4wp_get_add_form_url()
{
    $url = admin_url('admin.php?page=mailchimp-for-wp-forms&view=add-form');
    return $url;
}
