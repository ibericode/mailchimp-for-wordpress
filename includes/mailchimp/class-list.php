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
	public $web_id = '';

	/**
	 * @var string Name of this list
	 */
	public $name;

	/**
	 * @var int Number of subscribers on this list
	 */
	public $subscriber_count = 0;

	/**
	 * @var MC4WP_MailChimp_Merge_Field[]
	 */
	public $merge_fields = array();

	/**
	 * @var MC4WP_MailChimp_Interest_Category[]
	 */
	public $interest_categories = array();

	/**
	 * @var array Array of merge var names (tag => name) KNOWN to be on the list
	 */
	protected $default_merge_fields = array();

	/**
	 * @param string $id
	 * @param string $name
	 * @param string $web_id (deprecated)
	 */
	public function __construct( $id, $name, $web_id = '' ) {
		$this->id = $id;
		$this->name = $name;
		$this->web_id = $web_id;
		$this->default_merge_fields = array(
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
		if( isset( $this->default_merge_fields[ $tag ] ) ) {
			return __( $this->default_merge_fields[ $tag ], 'mailchimp-for-wp' );
		}

		// search merge vars
		foreach( $this->merge_fields as $field ) {

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
	 * @param string $category_id ID of the Interest Grouping
	 *
	 * @return MC4WP_MailChimp_Interest_Category|null
	 */
	public function get_interest_category( $category_id ) {

		foreach( $this->interest_categories as $category ) {

			if( $category->id !== $category_id ) {
				continue;
			}

			return $category;
		}

		return null;
	}


	/**
	 * Get the name of an interest category by its ID
	 *
	 * @param $category_id
	 *
	 * @return string
	 */
	public function get_interest_category_name( $category_id ) {

		$category = $this->get_interest_category( $category_id );

		if( isset( $category->name ) ) {
			return $category->name;
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