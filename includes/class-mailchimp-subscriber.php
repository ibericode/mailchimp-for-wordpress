<?php

class MC4WP_MailChimp_Subscriber {


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
	 * @var array The marketing permissions for the subscriber.
	 */
	public $marketing_permissions = array();

	/**
	 * Retrieves member data as an array, without null values.
	 *
	 * @return array
	 */
	public function to_array() {
		$all = get_object_vars( $this );
		$array = array();

		foreach ( $all as $key => $value ) {
			// skip null values
			if ( $value === null ) {
				continue;
			}

			// skip empty marketing_permissions property
			if ( $key === 'marketing_permissions' && empty( $value ) ) {
				continue;
			}

			// otherwise, add to final array
			$array[ $key ] = $value;
		}

		return $array;
	}
}
