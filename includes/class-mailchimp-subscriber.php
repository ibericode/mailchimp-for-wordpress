<?php

class MC4WP_MailChimp_Subscriber
{

    /**
     * @var string Email address for this subscriber.
     */
    public $email_address = '';

    /**
     * @var array The key of this object’s properties is the ID of the interest in question.
     */
    public $interests = array();

    /**
     * @var array An individual merge var and value for a member.
     */
    public $merge_fields = array();

    /**
     * @var string Subscriber’s status.
     */
    public $status = 'pending';

    /**
     * @var string Type of email this member asked to get (‘html’ or ‘text’).
     */
    public $email_type = 'html';

    /**
     * @var string IP address the subscriber signed up from.
     */
    public $ip_signup;

    /**
     * @var string The subscriber's language
     */
    public $language;

    /**
     * @var boolean VIP status for subscriber.
     */
    public $vip;

    /**
     * @var array The tags applied to this member.
     */
    public $tags = array();

    /**
     * Retrieves member data as an array, without null values.
     *
     * @return array
     */
    public function to_array()
    {
        $array = get_object_vars($this);

        // filter out null values
        $null_values = array_filter($array, 'is_null');
        $values = array_diff_key($array, $null_values);

        return $values;
    }
}
