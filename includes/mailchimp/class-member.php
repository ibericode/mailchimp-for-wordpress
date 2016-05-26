<?php

class MC4WP_MailChimp_Member {

    public $email_address = '';
    public $interests = array();
    public $merges = array();
    public $status = 'pending';
    public $email_type = 'html';
    public $ip_signup;
    public $language;

    /**
     * Retrieves member data as an array, without null values.
     *
     * @return array
     */
    public function to_array() {
        $array = get_object_vars( $this );
        $null_values = array_filter( $array, 'is_null' );
        $values = array_diff_key( $array, $null_values );
        return $values;
    }

}