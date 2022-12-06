<?php

add_action('mm_member_add', function($data) {
   $mailchimp = new MC4WP_MailChimp();
   $mailchimp_list_id = 'list-id-here'; // Replace with your MailChimp list ID
   $tags = array( $data['membership_level_name'] );

   $mailchimp->list_subscribe($mailchimp_list_id, $data['email'], array(
       'status' => 'pending',
       'merge_fields' => array(
           'FNAME' => $data['first_name'],
           'LNAME' => $data['last_name'],
           // 'MEMLEVEL' => $data['membership_level_name'],
       ),
       'tags' => $tags,
   ));
});