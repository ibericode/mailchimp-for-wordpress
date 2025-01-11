<?php

/*
Can be used with any integration slug, showing gravity-forms as example.
See https://github.com/ibericode/mc4wp-snippets/blob/main/integrations/integration-slugs.md

You can either return an Array with multiple list IDs or you can return a string with 1 list ID.
You can get the list IDs from MailChimp for WP > MailChimp.

This code will overwrite whatever lists you've set in the integration settings.
*/

add_filter('mc4wp_integration_gravity-forms_lists', function () {
    return ["f2415574a4","bd0c7cefa9","a53c0bf8e5"];
});
