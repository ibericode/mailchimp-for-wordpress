<?php

/**
 * Class MC4WP_API_v3
 */
class MC4WP_API_v3 {

	/**
	 * @var MC4WP_API_v3_Client
	 */
	protected $client;

	/**
	 * @var bool Are we able to talk to the MailChimp API?
	 */
	protected $connected;
	
	/**
	 * Constructor
	 *
	 * @param string $api_key
	 */
	public function __construct( $api_key ) {
		$this->client = new MC4WP_API_v3_Client( $api_key );
	}
	
	/**
	 * Pings the MailChimp API to see if we're connected
	 *
	 * The result is cached to ensure a maximum of 1 API call per page load
	 *
	 * @return boolean
	 */
	public function is_connected() {

		if( is_null( $this->connected ) ) {
			$data = $this->client->get( '/' );
			$this->connected = is_object( $data ) && isset( $data->account_id );
		}

		return $this->connected;
	}

	/**
	 * @param $email_address
	 *
	 * @return string
	 */
	public function get_subscriber_hash( $email_address ) {
		return md5( strtolower( trim( $email_address ) ) );
	}

	/**
	 * Get recent daily, aggregated activity stats for a list.
	 *
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/lists/activity/#read-get_lists_list_id_activity
	 * @since 4.0
	 *
	 * @param string $list_id
	 *
	 * @return array
	 */
	public function get_list_activity( $list_id ) {
		$resource = sprintf( '/lists/%s/activity', $list_id );
		$data = $this->client->get( $resource );

		if( is_object( $data ) && isset( $data->activity ) ) {
			return $data->activity;
		}

		return array();
	}

	/**
	 * Gets the Groupings for a given List
	 *
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/lists/interest-categories/#read-get_lists_list_id_interest_categories
	 * @since 4.0
	 *
	 * @param string $list_id
	 * @param array $args
	 *
	 * @return array
	 */
	public function get_list_interest_categories( $list_id, array $args = array() ) {
		$resource = sprintf( '/lists/%s/interest-categories', $list_id );
		$data = $this->client->get( $resource, $args );

		if( is_object( $data ) && isset( $data->categories ) ) {
			return $data->categories;
		}

		return array();
	}

	/**
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/lists/interest-categories/interests/#read-get_lists_list_id_interest_categories_interest_category_id_interests
	 * @since 4.0
	 *
	 * @param string $list_id
	 * @param string $interest_category_id
	 * @param array $args
	 *
	 * @return array
	 */
	public function get_list_interest_category_interests( $list_id, $interest_category_id, array $args = array() ) {
		$resource = sprintf( '/lists/%s/interest-categories/%s/interests', $list_id, $interest_category_id );
		$data = $this->client->get( $resource, $args );

		if( is_object( $data ) && isset( $data->interests ) ) {
			return $data->interests;
		}

		return array();
	}

	/**
	 * Get merge vars for a given list
	 *
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/lists/merge-fields/#read-get_lists_list_id_merge_fields
	 * @since 4.0
	 *
	 * @param string $list_id
	 * @param array $args
	 *
	 * @return array
	 */
	public function get_list_merge_fields( $list_id, array $args = array() ) {
		$resource = sprintf( '/lists/%s/merge-fields', $list_id );
		$data = $this->client->get( $resource, $args );

		if( is_object( $data ) && isset( $data->merge_fields ) ) {
			return $data->merge_fields;
		}

		return array();
	}

	/**
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/lists/#read-get_lists_list_id
	 * @since 4.0
	 *
	 * @param string $list_id
	 * @param array $args
	 *
	 * @return object
	 */
	public function get_list( $list_id, array $args = array() ) {
		$resource = sprintf( '/lists/%s', $list_id );
		$data = $this->client->get( $resource, $args );
		return $data;
	}

	/**
	 *
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/lists/#read-get_lists
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function get_lists( $args = array() ) {
		$resource = '/lists';
		$data = $this->client->get( $resource, $args );

		if( is_object( $data ) && isset( $data->lists ) ) {
			return $data->lists;
		}

		return array();
	}

	/**
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/
	 * @since 4.0
	 *
	 * @param string $list_id
	 * @param string $email_address
	 * @param array $args
	 *
	 * @return object
	 */
	public function get_list_member( $list_id, $email_address, array $args = array() ) {
		$subscriber_hash = $this->get_subscriber_hash( $email_address );
		$resource = sprintf( '/lists/%s/members/%s', $list_id, $subscriber_hash );
		$data = $this->client->get( $resource, $args );
		return $data;
	}

	/**
	 * Add or update (!) a member to a MailChimp list.
	 *
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/#create-post_lists_list_id_members
	 * @since 4.0
	 *
	 * @param string $list_id
	 * @param array $args
	 *
	 * @return object
	 */
	public function add_list_member( $list_id, array $args ) {
		$subscriber_hash = $this->get_subscriber_hash( $args['email_address'] );
		$resource = sprintf( '/lists/%s/members/%s', $list_id, $subscriber_hash );

		// make sure we're sending an object as the MailChimp schema requires this
		if( isset( $args['merge_fields'] ) ) {
			$args['merge_fields'] = (object) $args['merge_fields'];
		}

		if( isset( $args['interests'] ) ) {
			$args['interests'] = (object) $args['interests'];
		}

		// "put" updates the member if it's already on the list... take notice
		$data = $this->client->put( $resource, $args );
		return $data;
	}

	/**
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/#edit-patch_lists_list_id_members_subscriber_hash
	 * @since 4.0
	 *
	 * @param $list_id
	 * @param $email_address
	 * @param array $args
	 *
	 * @return object
	 */
	public function update_list_member( $list_id, $email_address, array $args = array() ) {
		$subscriber_hash = $this->get_subscriber_hash( $email_address );
		$resource = sprintf( '/lists/%s/members/%s', $list_id, $subscriber_hash );

		// make sure we're sending an object as the MailChimp schema requires this
		if( isset( $args['merge_fields'] ) ) {
			$args['merge_fields'] = (object) $args['merge_fields'];
		}

		if( isset( $args['interests'] ) ) {
			$args['interests'] = (object) $args['interests'];
		}

		$data = $this->client->patch( $resource, $args );
		return $data;
	}

	/**
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/
	 * @since 4.0
	 *
	 * @param string $list_id
	 * @param string $email_address
	 *
	 * @return bool
	 */
	public function delete_list_member( $list_id, $email_address ) {
		$subscriber_hash = $this->get_subscriber_hash( $email_address );
		$resource = sprintf( '/lists/%s/members/%s', $list_id, $subscriber_hash );
		$data = $this->client->delete( $resource );
		return !!$data;
	}

	/**
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/#read-get_ecommerce_stores_store_id
	 * @since 4.0
	 *
	 * @param string $store_id
	 * @param array $args
	 *
	 * @return object
	 */
	public function get_ecommerce_store( $store_id, array $args = array() ) {
		$resource =  sprintf( '/ecommerce/stores/%s', $store_id );
		return $this->client->get( $resource, $args );
	}

	/**
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/#create-post_ecommerce_stores
	 * @since 4.0
	 *
	 * @param string $store_id The unique identifier for the store.
	 * @param string $list_id The unique identifier for the MailChimp List associated with the store.
	 * @param string $name The name of the store.
	 * @param string $currency_code The three-letter ISO 4217 code for the currency that the store accepts.
	 * @param array $args
	 *
	 * @return object
	 */
	public function add_ecommerce_store( $store_id, $list_id, $name, $currency_code, array $args = array() ) {
		$resource = '/ecommerce/stores';
		$args['id'] = $store_id;
		$args['list_id'] = $list_id;
		$args['name'] = $name;
		$args['currency_code'] = $currency_code;
		return $this->client->post( $resource, $args );
	}

	/**
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/#edit-patch_ecommerce_stores_store_id
	 * @since 4.0
	 *
	 * @param string $store_id
	 * @param array $args
	 *
	 * @return object
	 */
	public function update_ecommerce_store( $store_id, array $args ) {
		$resource =  sprintf( '/ecommerce/stores/%s', $store_id );
		return $this->client->patch( $resource, $args );
	}

	/**
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/#delete-delete_ecommerce_stores_store_id
	 * @since 4.0
	 *
	 * @param string $store_id
	 *
	 * @return boolean
	 */
	public function delete_ecommerce_store( $store_id ) {
		$resource = sprintf( '/ecommerce/stores/%s', $store_id );
		return $this->client->delete( $resource );
	}

	/**
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/orders/#create-post_ecommerce_stores_store_id_orders
	 * @since 4.0
	 *
	 * @param string $store_id
	 * @param array $args
	 *
	 * @return boolean
	 */
	public function add_ecommerce_store_order( $store_id, array $args ) {
		$data = $this->client->post( sprintf( '/ecommerce/stores/%s/orders', $store_id ), $args );
		return is_object( $data ) && ! empty( $data->id );
	}

	/**
	 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/ecommerce/stores/orders/#delete-delete_ecommerce_stores_store_id_orders_order_id
	 * @since 4.0
	 *
	 * @param string $store_id
	 * @param string $order_id
	 *
	 * @return bool
	 */
	public function delete_ecommerce_store_order( $store_id, $order_id ) {
		$data = $this->client->delete( sprintf( '/ecommerce/stores/%s/orders/%s', $store_id, $order_id ) );
		return !! $data;
	}

	/**
	 * @return string
	 */
	public function get_last_response_body() {
		return $this->client->get_last_response_body();
	}

	/**
	 * @return array
	 */
	public function get_last_response_headers() {
		return $this->client->get_last_response_headers();
	}


}