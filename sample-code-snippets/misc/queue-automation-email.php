<?php

$api = mc4wp_get_api_v3();
$client = $api->get_client();
$workflow_id = 'abcdef';
$workflow_email_id = 'ghijklm';
$url = sprintf('/automations/%s/emails/%s/queue', $workflow_id, $workflow_email_id);
$data = [
  'email_address' => 'johndoe@email.com',
];
$response = $client->post($url, $data);
