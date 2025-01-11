<?php

/**
 * Hat tip to Barry Kooij for providing us with this useful snippet.
 *
 * It redirects people straight to the download after filling in a "No Access" page sign-up form.
 */

// Override form redirect URL to the Download URL

function mc4wp_dlm_dynamic_url($url, $form)
{

    if (isset($_POST['mc4wp_dlm_download_id']) && ! empty($_POST['mc4wp_dlm_download_id'])) {
        try {
            /** @var DLM_Download $download */
            $download = download_monitor()->service('download_repository')->retrieve_single(absint($_POST['mc4wp_dlm_download_id']));
            $url = $download->get_the_download_link();
        } catch (Exception $exception) {
            // no download with given ID found
        }
    }

    return $url;
}

add_filter('mc4wp_form_redirect_url', 'mc4wp_dlm_dynamic_url', 20, 2);


// add hidden field to the access page on the "No Access" page.
function mc4wp_dlm_add_download_id($content)
{
    global $wp;

    if (isset($wp->query_vars) && isset($wp->query_vars['download-id'])) {
        $content .= "<input type='hidden' name='mc4wp_dlm_download_id' value='" . esc_attr($wp->query_vars['download-id']) . "' />" . PHP_EOL;
    }

    return $content;
}

add_filter('mc4wp_form_content', 'mc4wp_dlm_add_download_id');
