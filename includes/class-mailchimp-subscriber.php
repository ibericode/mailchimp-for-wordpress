<?php

class MC4WP_MailChimp_Subscriber
{
    /**
     * @var string Email address for this subscriber.
     */
    public $email_address = '';

    /**
     * @var null|array The key of this object’s properties is the ID of the interest in question.
     */
    public $interests = [];

    /**
     * @var array An individual merge var and value for a member.
     */
    public $merge_fields = [];

    /**
     * @var null|string Subscriber’s status.
     */
    public $status = 'pending';

    /**
     * @var null|string Type of email this member asked to get (‘html’ or ‘text’).
     */
    public $email_type = 'html';

    /**
     * @var null|string IP address the subscriber signed up from.
     */
    public $ip_signup;

    /**
     * @var null|string The subscriber's language
     */
    public $language;

    /**
     * @var null|boolean VIP status for subscriber.
     */
    public $vip;

    /**
     * @var null|array The tags applied to this member.
     */
    public $tags = [];

    /**
     * @var null|array The marketing permissions for the subscriber.
     */
    public $marketing_permissions = [];

    /**
     * Retrieves member data as an array, without null values.
     *
     * @return array
     */
    public function to_array()
    {
        $all   = get_object_vars($this);
        $array = [];

        foreach ($all as $key => $value) {
            // skip null values
            if ($value === null) {
                continue;
            }

            // skip empty marketing_permissions property
            if ($key === 'marketing_permissions' && empty($value)) {
                continue;
            }

            // otherwise, add to final array
            $array[ $key ] = $value;
        }

        return $array;
    }
}
