<?php

/**
 * Class MC4WP_MailChimp_List
 *
 * Represents a list in MailChimp
 *
 * @access public
 */
class MC4WP_MailChimp_List {

	/**
	 * @var string ID of this list for API usage
	 */
	public $id;

	/**
	 * @var string Web ID of this list in MailChimp.com
	 */
	public $web_id;

	/**
	 * @var string Name of this list
	 */
	public $name;

	/**
	 * @var int Number of subscribers on this list
	 */
	public $subscriber_count = 0;

	/**
	 * @var MC4WP_MailChimp_Merge_Var[]
	 */
	public $merge_vars = array();

	/**
	 * @var MC4WP_MailChimp_Grouping[]
	 */
	public $groupings = array();

	/**
	 * @var array Array of merge var names (tag => name) KNOWN to be on the list
	 */
	protected $default_merge_vars = array();

	/**
	 * @param string $id
	 * @param string $name
	 * @param string $web_id
	 */
	public function __construct( $id, $name, $web_id = '' ) {
		$this->id = $id;
		$this->name = $name;
		$this->web_id = $web_id;
		$this->default_merge_vars = array(
			'EMAIL' => 'Email Address',
			'OPTIN_IP' => 'IP Address',
			'MC_LANGUAGE' => 'Language'
		);
	}

	/**
	 * @param $tag
	 *
	 * @return string
	 */
	public function get_field_name_by_tag( $tag ) {

		// ensure uppercase tagname
		$tag = strtoupper( $tag );

		// search default merge vars first
		if( isset( $this->default_merge_vars[ $tag ] ) ) {
			return __( $this->default_merge_vars[ $tag ], 'mailchimp-for-wp' );
		}

		// search merge vars
		foreach( $this->merge_vars as $field ) {

			if( $field->tag !== $tag ) {
				continue;
			}

			return $field->name;
		}

		return '';
	}

	/**
	 * Get the interest grouping object for a given list.
	 *
	 * @param string $grouping_id ID of the Interest Grouping
	 *
	 * @return object|null
	 */
	public function get_grouping( $grouping_id ) {

		foreach( $this->groupings as $grouping ) {

			if( $grouping->id !== $grouping_id ) {
				continue;
			}

			return $grouping;
		}

		return null;
	}


	/**
	 * Get the name of a list grouping by its ID
	 *
	 * @param $grouping_id
	 *
	 * @return string
	 */
	public function get_grouping_name( $grouping_id ) {

		$grouping = $this->get_grouping( $grouping_id );
		if( isset( $grouping->name ) ) {
			return $grouping->name;
		}

		return '';
	}

	/**
	 * Get link to this list in MailChimp
	 *
	 * @return string
	 */
	public function get_web_url() {
		return 'https://admin.mailchimp.com/lists/members/?id=' . $this->web_id;
	}

}