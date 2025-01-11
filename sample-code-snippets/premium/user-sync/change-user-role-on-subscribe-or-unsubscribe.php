<?php

// This snippet needs the  Webhook to be turned on under Mailchimp for WP > Usersync
// It will change the user role when someone subscribes on unsubscribes from the Audience.
// Replace 'unsubscribed' and 'subscriber' with the slugs of the roles you are using for this.

// If you want this to apply when someone subscribes through a single opt-in MC4WP form, you need to turn on API trigger in the webhook
// under Audience > Settings > Webhooks, edit the webhook and check the box for "via the API".

add_action('mc4wp_user_sync_webhook_unsubscribe', function ($data, $user) {
    $user->remove_role('subscriber');
    $user->add_role('unsubscribed');
}, 10, 2);

add_action('mc4wp_user_sync_webhook_subscribe', function ($data, $user) {
    $user->remove_role('unsubscribed');
    $user->add_role('subscriber');
}, 10, 2);
