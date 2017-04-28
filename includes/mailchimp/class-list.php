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
	 * @param string $id
	 * @param string $name
	 * @param string $web_id (deprecated)
	 */
	public function __construct( $id, $name, $web_id = '' ) {
		$this->id = $id;
		$this->name = $name;
		$this->web_id = $web_id;

		// add email field
		$email_field = new MC4WP_MailChimp_Merge_Field( 'Email Address', 'email', 'EMAIL', true );
		$this->merge_fields[] = $email_field;
	}

	/**
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function __get( $name ) {
		// for backwards compatibility with 3.x, channel these properties to their new names
		if( $name === 'merge_vars' ) {
			return $this->merge_fields;
		} elseif( $name === 'groupings' ) {
			return $this->interest_categories;
		}
	}

	/**
	 * @param string $tag
	 * @return MC4WP_MailChimp_Merge_Field
	 *
	 * @throws Exception
	 */
	public function get_field_by_tag( $tag ) {
		// ensure uppercase tagname
		$tag = strtoupper( $tag );

		// search merge vars
		foreach( $this->merge_fields as $field ) {

			if( $field->tag !== $tag ) {
				continue;
			}

			return $field;
		}

		throw new Exception( 'Field tag not found.' );
	}

	/**
	 *
	 * @since 4.0
	 *
	 * @param string $interest_id
	 * @return MC4WP_MailChimp_Interest_Category
	 *
	 * @throws Exception
	 */
	public function get_interest_category_by_interest_id( $interest_id ) {

		foreach( $this->interest_categories as $category ) {
			foreach( $category->interests as $id => $name ) {
				if( $id != $interest_id ) {
					continue;
				}

				return $category;
			}
		}

		throw new Exception( sprintf( 'No interest with ID %s', $interest_id ) );
	}

	/**
	 * Get the interest grouping object for a given list.
	 *
	 * @param string $category_id ID of the Interest Grouping
	 *
	 * @return MC4WP_MailChimp_Interest_Category
	 *
	 * @throws Exception
	 */
	public function get_interest_category( $category_id ) {

		foreach( $this->interest_categories as $category ) {

			if( $category->id !== $category_id ) {
				continue;
			}

			return $category;
		}

		throw new Exception( sprintf( 'No interest category with ID %s', $category_id ) );
	}

	/**
	 * Get link to this list in MailChimp
	 *
	 * @return string
	 */
	public function get_web_url() {
		return 'https://admin.mailchimp.com/lists/members/?id=' . $this->web_id;
	}

	/**
	 * Get the name of an interest category by its ID
	 *
	 * @deprecated 4.0
	 * @use MC4WP_MailChimp_List::get_interest_category
	 *
	 * @param string $category_id
	 *
	 * @return string
	 */
	public function get_interest_category_name( $category_id ) {
		$category = $this->get_interest_category( $category_id );
		return $category->name;
	}

	/**
	 * @deprecated 4.0
	 * @use MC4WP_MailChimp_List::get_field_by_tag
	 *
	 * @param string $tag
	 *
	 * @return string
	 */
	public function get_field_name_by_tag( $tag ) {
		$field = $this->get_field_by_tag( $tag );
		return $field->name;
	}

}