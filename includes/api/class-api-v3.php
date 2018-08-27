<?php

/**
 * Class PL4WP_API_v3
 */
class PL4WP_API_v3 {

	/**
	 * @var PL4WP_API_Client
	 */
	protected $client;

	/**
	 * @var bool Are we able to talk to the PhpList API?
	 */
	protected $connected;

	/**
	 * Constructor
	 *
	 * @param string $api_key
	 */
	public function __construct( $api_url, $api_username, $api_password, $api_key ) {
		$this->client = new PL4WP_API_Client( $api_url, $api_username, $api_password, $api_key );
	}

	/**
	 * Gets the API client to perform raw API calls.
	 *
	 * @return PL4WP_API_v3_Client
	 */
	public function get_client() {
		return $this->client;
	}

	/**
	 * Pings the PhpList API to see if we're connected
	 *
	 * The result is cached to ensure a maximum of 1 API call per page load
	 *
	 * @return boolean
	 * @throws PL4WP_API_Exception
	 */
	public function is_connected() {

		if( is_null( $this->connected ) ) {
			$this->connected = $this->client->login();
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
	 * @link https://developer.phplist.com/documentation/phplist/reference/lists/activity/#read-get_lists_list_id_activity
	 *
	 * @param string $list_id
	 * @param array $args
	 *
	 * @return array
	 * @throws PL4WP_API_Exception
	 */
	public function get_list_activity( $list_id, array $args = array() ) {
		$resource = sprintf( '/lists/%s/activity', $list_id );
		$data = $this->client->get( $resource, $args );

		if( is_object( $data ) && isset( $data->activity ) ) {
			return $data->activity;
		}

		return array();
	}

	/**
	 * Gets the interest categories for a given List
	 *
	 * @link https://developer.phplist.com/documentation/phplist/reference/lists/interest-categories/#read-get_lists_list_id_interest_categories
	 *
	 * @param string $list_id
	 * @param array $args
	 *
	 * @return array
	 * @throws PL4WP_API_Exception
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
	 * @link https://developer.phplist.com/documentation/phplist/reference/lists/interest-categories/interests/#read-get_lists_list_id_interest_categories_interest_category_id_interests
	 *
	 * @param string $list_id
	 * @param string $interest_category_id
	 * @param array $args
	 *
	 * @return array
	 * @throws PL4WP_API_Exception
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
	 * @link https://developer.phplist.com/documentation/phplist/reference/lists/merge-fields/#read-get_lists_list_id_merge_fields
	 *
	 * @param string $list_id
	 * @param array $args
	 *
	 * @return array
	 * @throws PL4WP_API_Exception
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
	 * @link https://developer.phplist.com/documentation/phplist/reference/lists/#read-get_lists_list_id
	 *
	 * @param string $list_id
	 * @param array $args
	 *
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function get_list( $list_id, array $args = array() ) {
		$lists = array_filter($this->get_lists($args), function($list) use ($list_id) { return $list->id == $list_id; });
		if (!$lists) {
			throw new PL4WP_API_Exception('list not found', 'list_not_found');
		}

		return array_shift($lists);
	}

	/**
	 * @link https://developer.phplist.com/documentation/phplist/reference/lists/#read-get_lists
	 *
	 * @param array $args
	 *
	 * @return array
	 * @throws PL4WP_API_Exception
	 */
	public function get_lists( $args = array() ) {
		$data = $this->client->listsGet();

		if( is_array( $data ) ) {
			return $data;
		}

		return array();
	}

	/**
	 * @link https://developer.phplist.com/documentation/phplist/reference/lists/members/
	 *
	 * @param string $list_id
	 * @param string $email_address
	 * @param array $args
	 *
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function get_list_member( $list_id, $email_address, array $args = array() ) {
		$existing_subscriber_id = $this->client->subscriberFindByEmail($email_address);
		
		if ($existing_subscriber_id) {
			$lists = $this->client->listsSubscriber($existing_subscriber_id);
			$lists = array_filter($lists, function($list) use ($list_id) { return $list->id == $list_id; });
			if ($lists) {
				$list = array_shift($lists);
				
				return (object)[
					'id' => $existing_subscriber_id,
					'status' => $list->active==='1'?'subscribed':'pending'
				];
			}
		}
		
		return null;
		
//		$subscriber_hash = $this->get_subscriber_hash( $email_address );
//		$resource = sprintf( '/lists/%s/members/%s', $list_id, $subscriber_hash );
//		$data = $this->client->get( $resource, $args );
//		return $data;
	}

	/**
	 * Batch subscribe / unsubscribe list members.
	 *
	 * @link https://developer.phplist.com/documentation/phplist/reference/lists/#create-post_lists_list_id
	 *
	 * @param string $list_id
	 * @param array $args
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function add_list_members( $list_id, array $args ) {
		$resource = sprintf( '/lists/%s', $list_id );
		return $this->client->post( $resource, $args );
	}

	/**
	 * Add or update (!) a member to a PhpList list.
	 *
	 * @link https://developer.phplist.com/documentation/phplist/reference/lists/members/#create-post_lists_list_id_members
	 *
	 * @param string $list_id
	 * @param array $args
	 *
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function add_list_member( $list_id, array $args ) {
		$existing_subscriber_id = $this->client->subscriberFindByEmail($args['email_address']);
		
		if ($existing_subscriber_id) {
			$lists = $this->client->listsSubscriber($existing_subscriber_id);
			if (!$lists) {
				$this->client->subscriberDelete($existing_subscriber_id);
				$existing_subscriber_id = null;
			}
		}
		
		$data = null;
		if ($existing_subscriber_id) {
			$this->client->listSubscriberAdd($list_id, $existing_subscriber_id);
			$data = $existing_subscriber_id;
		} else {
			$data = $this->client->subscribe( $args['email_address'], $list_id );
		}
		
		if (!$data) {
			throw new PL4WP_API_Exception('Could not subscribe', 99);
		}
		return (object)['id' => $data];
		
//		$subscriber_hash = $this->get_subscriber_hash( $args['email_address'] );
//		$resource = sprintf( '/lists/%s/members/%s', $list_id, $subscriber_hash );
//
//		// make sure we're sending an object as the PhpList schema requires this
//		if( isset( $args['merge_fields'] ) ) {
//			$args['merge_fields'] = (object) $args['merge_fields'];
//		}
//
//		if( isset( $args['interests'] ) ) {
//			$args['interests'] = (object) $args['interests'];
//		}
//
//		// "put" updates the member if it's already on the list... take notice
//		$data = $this->client->put( $resource, $args );
//		return $data;
	}

	/**
	 * @link https://developer.phplist.com/documentation/phplist/reference/lists/members/#edit-patch_lists_list_id_members_subscriber_hash
	 *
	 * @param $list_id
	 * @param $email_address
	 * @param array $args
	 *
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function update_list_member( $list_id, $email_address, array $args ) {
		$subscriber_hash = $this->get_subscriber_hash( $email_address );
		$resource = sprintf( '/lists/%s/members/%s', $list_id, $subscriber_hash );

		// make sure we're sending an object as the PhpList schema requires this
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
	 * @link https://developer.phplist.com/documentation/phplist/reference/lists/members/
	 *
	 * @param string $list_id
	 * @param string $email_address
	 *
	 * @return bool
	 * @throws PL4WP_API_Exception
	 */
	public function delete_list_member( $list_id, $email_address ) {
		
		$existing_subscriber_id = $this->client->subscriberFindByEmail($email_address);
		
		if ($existing_subscriber_id) {
			$this->client->listSubscriberDelete($list_id, $existing_subscriber_id);
			return true;
		}
		
		return false;
		
//		$subscriber_hash = $this->get_subscriber_hash( $email_address );
//		$resource = sprintf( '/lists/%s/members/%s', $list_id, $subscriber_hash );
//		$data = $this->client->delete( $resource );
//		return !!$data;
	}

	/**
	 * @link https://developer.phplist.com/documentation/phplist/reference/ecommerce/stores/#read-get_ecommerce_stores
	 *
	 * @param array $args
	 *
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function get_ecommerce_stores( array $args = array() ) {
		$resource = '/ecommerce/stores';
		return $this->client->get( $resource, $args );
	}

	/**
	 * @link https://developer.phplist.com/documentation/phplist/reference/ecommerce/stores/#read-get_ecommerce_stores_store_id
	 *
	 * @param string $store_id
	 * @param array $args
	 *
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function get_ecommerce_store( $store_id, array $args = array() ) {
		$resource =  sprintf( '/ecommerce/stores/%s', $store_id );
		return $this->client->get( $resource, $args );
	}

	/**
	 * @link https://developer.phplist.com/documentation/phplist/reference/ecommerce/stores/#create-post_ecommerce_stores
	 *
	 * @param array $args
	 *
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function add_ecommerce_store( array $args ) {
		$resource = '/ecommerce/stores';
		return $this->client->post( $resource, $args );
	}

	/**
	 * @link https://developer.phplist.com/documentation/phplist/reference/ecommerce/stores/#edit-patch_ecommerce_stores_store_id
	 *
	 * @param string $store_id
	 * @param array $args
	 *
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function update_ecommerce_store( $store_id, array $args ) {
		$resource =  sprintf( '/ecommerce/stores/%s', $store_id );
		return $this->client->patch( $resource, $args );
	}

	/**
	 * @link https://developer.phplist.com/documentation/phplist/reference/ecommerce/stores/#delete-delete_ecommerce_stores_store_id
	 *
	 * @param string $store_id
	 *
	 * @return boolean
	 * @throws PL4WP_API_Exception
	 */
	public function delete_ecommerce_store( $store_id ) {
		$resource = sprintf( '/ecommerce/stores/%s', $store_id );
		return !!$this->client->delete( $resource );
	}

	/**
	 * @link https://developer.phplist.com/documentation/phplist/reference/ecommerce/stores/customers/#read-get_ecommerce_stores_store_id_customers
	 *
	 * @param string $store_id
	 * @param array $args
	 *
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function get_ecommerce_store_customers( $store_id, array $args = array() ) {
		$resource = sprintf( '/ecommerce/stores/%s/customers', $store_id );
		return $this->client->get( $resource, $args );
	}

	/**
	 * @link https://developer.phplist.com/documentation/phplist/reference/ecommerce/stores/customers/#read-get_ecommerce_stores_store_id_customers_customer_id
	 *
	 * @param string $store_id
	 * @param string $customer_id
	 * @param array $args
	 *
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function get_ecommerce_store_customer( $store_id, $customer_id, array $args = array() ) {
		$resource = sprintf( '/ecommerce/stores/%s/customers/%s', $store_id, $customer_id );
		return $this->client->get( $resource, $args );
	}

	/**
	 * Add OR update a store customer
	 *
	 * @link https://developer.phplist.com/documentation/phplist/reference/ecommerce/stores/customers/#edit-put_ecommerce_stores_store_id_customers_customer_id
	 *
	 * @param $store_id
	 * @param array $args
	 *
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function add_ecommerce_store_customer( $store_id, array $args ) {
		$resource = sprintf( '/ecommerce/stores/%s/customers/%s', $store_id, $args['id'] );
		return $this->client->put( $resource, $args );
	}

	/**
	 * @link https://developer.phplist.com/documentation/phplist/reference/ecommerce/stores/customers/#edit-patch_ecommerce_stores_store_id_customers_customer_id
	 *
	 * @param string $store_id
	 * @param string $customer_id
	 * @param array $args
	 *
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function update_ecommerce_store_customer( $store_id, $customer_id, array $args ) {
		$resource = sprintf( '/ecommerce/stores/%s/customers/%s', $store_id, $customer_id );
		return $this->client->patch( $resource, $args );
	}

	/**
	 * @link https://developer.phplist.com/documentation/phplist/reference/ecommerce/stores/customers/#delete-delete_ecommerce_stores_store_id_customers_customer_id
	 *
	 * @param string $store_id
	 * @param string $customer_id
	 *
	 * @return bool
	 * @throws PL4WP_API_Exception
	 */
	public function delete_ecommerce_store_customer( $store_id, $customer_id ) {
		$resource = sprintf( '/ecommerce/stores/%s/customers/%s', $store_id, $customer_id );
		return !!$this->client->delete( $resource );
	}

	/**
	 * @link https://developer.phplist.com/documentation/phplist/reference/ecommerce/stores/products/#read-get_ecommerce_stores_store_id_products
	 *
	 * @param string $store_id
	 * @param array $args
	 *
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function get_ecommerce_store_products( $store_id, array $args = array() ) {
		$resource = sprintf( '/ecommerce/stores/%s/products', $store_id );
		return $this->client->get( $resource, $args );
	}

	/**
	 * @link https://developer.phplist.com/documentation/phplist/reference/ecommerce/stores/products/#read-get_ecommerce_stores_store_id_products_product_id
	 *
	 * @param string $store_id
	 * @param string $product_id
	 * @param array $args
	 *
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function get_ecommerce_store_product( $store_id, $product_id, array $args = array() ) {
		$resource = sprintf( '/ecommerce/stores/%s/products/%s', $store_id, $product_id );
		return $this->client->get( $resource, $args );
	}

	/**
	 * Add a product to a store
	 *
	 * @link https://developer.phplist.com/documentation/phplist/reference/ecommerce/stores/products/#create-post_ecommerce_stores_store_id_products
	 *
	 * @param string $store_id
	 * @param array $args
	 *
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function add_ecommerce_store_product( $store_id, array $args ) {
		$resource = sprintf( '/ecommerce/stores/%s/products', $store_id );
		return $this->client->post( $resource, $args );
	}

	/**
	 * @link https://developer.phplist.com/documentation/phplist/reference/ecommerce/stores/products/#edit-patch_ecommerce_stores_store_id_products_product_id
	 *
	 * @param string $store_id
	 * @param string $product_id
	 * @param array $args
	 *
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function update_ecommerce_store_product( $store_id, $product_id, array $args ) {
		$resource = sprintf( '/ecommerce/stores/%s/products/%s', $store_id, $product_id );
		return $this->client->patch( $resource, $args );
	}

	/**
	 * @link https://developer.phplist.com/documentation/phplist/reference/ecommerce/stores/products/#delete-delete_ecommerce_stores_store_id_products_product_id
	 *
	 * @param string $store_id
	 * @param string $product_id
	 *
	 * @return boolean
	 * @throws PL4WP_API_Exception
	 */
	public function delete_ecommerce_store_product( $store_id, $product_id ) {
		$resource = sprintf( '/ecommerce/stores/%s/products/%s', $store_id, $product_id );
		return !!$this->client->delete( $resource );
	}

	/**
	 * @link https://developer.phplist.com/documentation/phplist/reference/ecommerce/stores/products/variants/#read-get_ecommerce_stores_store_id_products_product_id_variants
	 *
	 * @param string $store_id
	 * @param string $product_id
	 * @param array $args
	 *
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function get_ecommerce_store_product_variants( $store_id, $product_id, array $args = array() ) {
		$resource = sprintf( '/ecommerce/stores/%s/products/%s/variants', $store_id, $product_id );
		return $this->client->get( $resource, $args );
	}

	/**
	 * @link https://developer.phplist.com/documentation/phplist/reference/ecommerce/stores/products/variants/#read-get_ecommerce_stores_store_id_products_product_id_variants_variant_id
	 *
	 * @param string $store_id
	 * @param string $product_id
	 * @param string $variant_id
	 * @param array $args
	 *
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function get_ecommerce_store_product_variant( $store_id, $product_id, $variant_id, array $args = array() ) {
		$resource = sprintf( '/ecommerce/stores/%s/products/%s/variants/%s', $store_id, $product_id, $variant_id );
		return $this->client->get( $resource, $args );
	}

	/**
	 * Add OR update a product variant.
	 *
	 * @link https://developer.phplist.com/documentation/phplist/reference/ecommerce/stores/products/variants/#edit-put_ecommerce_stores_store_id_products_product_id_variants_variant_id
	 *
	 * @param string $store_id
	 * @param string $product_id
	 * @param array $args
	 *
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function add_ecommerce_store_product_variant( $store_id, $product_id, array $args ) {
		$resource = sprintf( '/ecommerce/stores/%s/products/%s/variants/%s', $store_id, $product_id, $args['id'] );
		return $this->client->put( $resource, $args );
	}

	/**
	 * @link https://developer.phplist.com/documentation/phplist/reference/ecommerce/stores/products/variants/#edit-patch_ecommerce_stores_store_id_products_product_id_variants_variant_id
	 *
	 * @param string $store_id
	 * @param string $product_id
	 * @param string $variant_id
	 * @param array $args
	 *
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function update_ecommerce_store_product_variant( $store_id, $product_id, $variant_id, array $args ) {
		$resource = sprintf( '/ecommerce/stores/%s/products/%s/variants/%s', $store_id, $product_id, $variant_id );
		return $this->client->patch( $resource, $args );
	}

	/**
	 * @link https://developer.phplist.com/documentation/phplist/reference/ecommerce/stores/products/variants/#delete-delete_ecommerce_stores_store_id_products_product_id_variants_variant_id
	 *
	 * @param string $store_id
	 * @param string $product_id
	 * @param string $variant_id
	 *
	 * @return boolean
	 * @throws PL4WP_API_Exception
	 */
	public function delete_ecommerce_store_product_variant( $store_id, $product_id, $variant_id ) {
		$resource = sprintf( '/ecommerce/stores/%s/products/%s/variants/%s', $store_id, $product_id, $variant_id );
		return !!$this->client->delete( $resource );
	}

	/**
	 * @link https://developer.phplist.com/documentation/phplist/reference/ecommerce/stores/orders/#read-get_ecommerce_stores_store_id_orders
	 *
	 * @param string $store_id
	 * @param array $args
	 *
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function get_ecommerce_store_orders( $store_id, array $args = array() ) {
		$resource = sprintf( '/ecommerce/stores/%s/orders', $store_id );
		return $this->client->get( $resource, $args );
	}

	/**
	 * @link https://developer.phplist.com/documentation/phplist/reference/ecommerce/stores/orders/#read-get_ecommerce_stores_store_id_orders_order_id
	 *
	 * @param string $store_id
	 * @param string $order_id
	 * @param array $args
	 *
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function get_ecommerce_store_order( $store_id, $order_id, array $args = array() ) {
		$resource = sprintf( '/ecommerce/stores/%s/orders/%s', $store_id, $order_id );
		return $this->client->get( $resource, $args );
	}

	/**
	 * @link https://developer.phplist.com/documentation/phplist/reference/ecommerce/stores/orders/#create-post_ecommerce_stores_store_id_orders
	 *
	 * @param string $store_id
	 * @param array $args
	 *
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function add_ecommerce_store_order( $store_id, array $args ) {
		$resource = sprintf( '/ecommerce/stores/%s/orders', $store_id );
		return $this->client->post( $resource, $args );
	}

	/**
	 * @link https://developer.phplist.com/documentation/phplist/reference/ecommerce/stores/orders/#edit-patch_ecommerce_stores_store_id_orders_order_id
	 *
	 * @param string $store_id
	 * @param string $order_id
	 * @param array $args
	 *
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function update_ecommerce_store_order( $store_id, $order_id, array $args ) {
		$resource = sprintf( '/ecommerce/stores/%s/orders/%s', $store_id, $order_id );
		return $this->client->patch( $resource, $args );
	}

	/**
	 * @link https://developer.phplist.com/documentation/phplist/reference/ecommerce/stores/orders/#delete-delete_ecommerce_stores_store_id_orders_order_id
	 *
	 * @param string $store_id
	 * @param string $order_id
	 *
	 * @return bool
	 * @throws PL4WP_API_Exception
	 */
	public function delete_ecommerce_store_order( $store_id, $order_id ) {
		return !! $this->client->delete( sprintf( '/ecommerce/stores/%s/orders/%s', $store_id, $order_id ) );
	}

	/**
	 * @link https://developer.phplist.com/documentation/phplist/reference/ecommerce/stores/orders/lines/#create-post_ecommerce_stores_store_id_orders_order_id_lines
	 *
	 * @param string $store_id
	 * @param string $order_id
	 * @param array $args
	 *
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function add_ecommerce_store_order_line( $store_id, $order_id, array $args ) {
		$resource = sprintf( '/ecommerce/stores/%s/orders/%s/lines', $store_id, $order_id );
		return $this->client->post( $resource, $args );
	}


	/**
	 * @link https://developer.phplist.com/documentation/phplist/reference/ecommerce/stores/orders/lines/#read-get_ecommerce_stores_store_id_orders_order_id_lines
	 *
	 * @param string $store_id
	 * @param string $order_id
	 * @param array $args
	 *
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function get_ecommerce_store_order_lines( $store_id, $order_id, array $args = array() ) {
		$resource = sprintf( '/ecommerce/stores/%s/orders/%s/lines', $store_id, $order_id );
		return $this->client->get( $resource, $args );
	}

	/**
	 * @link https://developer.phplist.com/documentation/phplist/reference/ecommerce/stores/orders/lines/#read-get_ecommerce_stores_store_id_orders_order_id_lines_line_id
	 *
	 * @param string $store_id
	 * @param string $order_id
	 * @param string $line_id
	 * @param array $args
	 *
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function get_ecommerce_store_order_line( $store_id, $order_id, $line_id, array $args = array() ) {
		$resource = sprintf( '/ecommerce/stores/%s/orders/%s/lines/%s', $store_id, $order_id, $line_id );
		return $this->client->get( $resource, $args );
	}

	/**
	 * @link https://developer.phplist.com/documentation/phplist/reference/ecommerce/stores/orders/lines/#edit-patch_ecommerce_stores_store_id_orders_order_id_lines_line_id
	 *
	 * @param string $store_id
	 * @param string $order_id
	 * @param string $line_id
	 * @param array $args
	 *
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function update_ecommerce_store_order_line( $store_id, $order_id, $line_id, array $args ) {
		$resource = sprintf( '/ecommerce/stores/%s/orders/%s/lines/%s', $store_id, $order_id, $line_id );
		return $this->client->patch( $resource, $args );
	}

	/**
	 * @link https://developer.phplist.com/documentation/phplist/reference/ecommerce/stores/orders/lines/#delete-delete_ecommerce_stores_store_id_orders_order_id_lines_line_id
	 *
	 * @param string $store_id
	 * @param string $order_id
	 * @param string $line_id
	 *
	 * @return bool
	 * @throws PL4WP_API_Exception
	 */
	public function delete_ecommerce_store_order_line( $store_id, $order_id, $line_id ) {
		$resource = sprintf( '/ecommerce/stores/%s/orders/%s/lines/%s', $store_id, $order_id, $line_id );
		return !! $this->client->delete( $resource );
	}

	/**
	 * @link https://developer.phplist.com/documentation/phplist/reference/ecommerce/stores/carts/#read-get_ecommerce_stores_store_id_carts
	 *
	 * @param string $store_id
	 * @param array $args
	 *
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function get_ecommerce_store_carts( $store_id, array $args = array() ) {
		$resource = sprintf( '/ecommerce/stores/%s/carts', $store_id );
		return $this->client->get( $resource, $args );
	}

	/**
	 * @link https://developer.phplist.com/documentation/phplist/reference/ecommerce/stores/carts/#read-get_ecommerce_stores_store_id_carts_cart_id
	 *
	 * @param string $store_id
	 * @param string $cart_id
	 * @param array $args
	 *
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function get_ecommerce_store_cart( $store_id, $cart_id, array $args = array() ) {
		$resource = sprintf( '/ecommerce/stores/%s/carts/%s', $store_id, $cart_id );
		return $this->client->get( $resource, $args );
	}

	/**
	 * @link https://developer.phplist.com/documentation/phplist/reference/ecommerce/stores/carts/#create-post_ecommerce_stores_store_id_carts
	 *
	 * @param string $store_id
	 * @param array $args
	 *
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function add_ecommerce_store_cart( $store_id, array $args ) {
		$resource = sprintf( '/ecommerce/stores/%s/carts', $store_id );
		return $this->client->post( $resource, $args );
	}

	/**
	 * @link https://developer.phplist.com/documentation/phplist/reference/ecommerce/stores/carts/#edit-patch_ecommerce_stores_store_id_carts_cart_id
	 *
	 * @param string $store_id
	 * @param string $cart_id
	 * @param array $args
	 *
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function update_ecommerce_store_cart( $store_id, $cart_id, array $args ) {
		$resource = sprintf( '/ecommerce/stores/%s/carts/%s', $store_id, $cart_id );
		return $this->client->patch( $resource, $args );
	}

	/**
	 * @link https://developer.phplist.com/documentation/phplist/reference/ecommerce/stores/carts/#delete-delete_ecommerce_stores_store_id_carts_cart_id
	 *
	 * @param string $store_id
	 * @param string $cart_id
	 *
	 * @return bool
	 */
	public function delete_ecommerce_store_cart( $store_id, $cart_id ) {
		return !! $this->client->delete( sprintf( '/ecommerce/stores/%s/carts/%s', $store_id, $cart_id ) );
	}

	/**
	 * @link https://developer.phplist.com/documentation/phplist/reference/ecommerce/stores/carts/lines/#read-get_ecommerce_stores_store_id_carts_cart_id_lines
	 *
	 * @param string $store_id
	 * @param string $cart_id
	 * @param array $args
	 *
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function get_ecommerce_store_cart_lines( $store_id, $cart_id, array $args = array() ) {
		$resource = sprintf( '/ecommerce/stores/%s/carts/%/lines', $store_id, $cart_id);
		return $this->client->get( $resource, $args );
	}

	/**
	 * @link https://developer.phplist.com/documentation/phplist/reference/ecommerce/stores/carts/lines/#read-get_ecommerce_stores_store_id_carts_cart_id_lines_line_id
	 *
	 * @param string $store_id
	 * @param string $cart_id
	 * @param string $line_id
	 * @param array $args
	 *
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function get_ecommerce_store_cart_line( $store_id, $cart_id, $line_id, array $args = array() ) {
		$resource = sprintf( '/ecommerce/stores/%s/carts/%s/lines/%s', $store_id, $cart_id, $line_id );
		return $this->client->get( $resource, $args );
	}

	/**
	 * @link https://developer.phplist.com/documentation/phplist/reference/ecommerce/stores/carts/lines/#create-post_ecommerce_stores_store_id_carts_cart_id_lines
	 *
	 * @param string $store_id
	 * @param string $cart_id
	 * @param array $args
	 *
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function add_ecommerce_store_cart_line( $store_id, $cart_id, array $args ) {
		$resource = sprintf( '/ecommerce/stores/%s/carts/%s/lines', $store_id, $cart_id );
		return $this->client->post( $resource, $args );
	}

	/**
	 * @link https://developer.phplist.com/documentation/phplist/reference/ecommerce/stores/carts/lines/#edit-patch_ecommerce_stores_store_id_carts_cart_id_lines_line_id
	 *
	 * @param string $store_id
	 * @param string $cart_id
	 * @param string $line_id
	 * @param array $args
	 *
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function update_ecommerce_store_cart_line( $store_id, $cart_id, $line_id, array $args ) {
		$resource = sprintf( '/ecommerce/stores/%s/carts/%s/lines/%s', $store_id, $cart_id, $line_id );
		return $this->client->patch( $resource, $args );
	}

	/**
	 * @link https://developer.phplist.com/documentation/phplist/reference/ecommerce/stores/carts/lines/#delete-delete_ecommerce_stores_store_id_carts_cart_id_lines_line_id
	 *
	 * @param string $store_id
	 * @param string $cart_id
	 * @param string $line_id
	 *
	 * @return bool
	 * @throws PL4WP_API_Exception
	 */
	public function delete_ecommerce_store_cart_line( $store_id, $cart_id, $line_id ) {
		$resource = sprintf( '/ecommerce/stores/%s/carts/%s/lines/%s', $store_id, $cart_id, $line_id );
		return !! $this->client->delete( $resource );
	}

	/**
	 * @link https://developer.phplist.com/documentation/phplist/reference/ecommerce/stores/promo-rules/#create-post_ecommerce_stores_store_id_promo_rules
	 *
	 * @param string $store_id
	 * @param array $args
	 *
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function add_ecommerce_store_promo_rule( $store_id, array $args ) {
		$resource = sprintf( '/ecommerce/stores/%s/promo-rules', $store_id );
		return $this->client->post( $resource, $args );
	}

	/**
	 * @link https://developer.phplist.com/documentation/phplist/reference/ecommerce/stores/promo-rules/#read-get_ecommerce_stores_store_id_promo_rules
	 *
	 * @param string $store_id
	 * @param array $args
	 *
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function get_ecommerce_store_promo_rules( $store_id, array $args = array() ) {
		$resource = sprintf( '/ecommerce/stores/%s/promo-rules', $store_id );
		return $this->client->get( $resource, $args );
	}

	/**
	 * @link https://developer.phplist.com/documentation/phplist/reference/ecommerce/stores/promo-rules/#read-get_ecommerce_stores_store_id_promo_rules_promo_rule_id
	 *
	 * @param string $store_id
	 * @param string $promo_rule_id
	 * @param array $args
	 *
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function get_ecommerce_store_promo_rule( $store_id, $promo_rule_id, array $args = array() ) {
		$resource = sprintf( '/ecommerce/stores/%s/promo-rules/%s', $store_id, $promo_rule_id );
		return $this->client->get( $resource, $args );
	}

	/**
	 * @link https://developer.phplist.com/documentation/phplist/reference/ecommerce/stores/promo-rules/#edit-patch_ecommerce_stores_store_id_promo_rules_promo_rule_id
	 *
	 * @param string $store_id
	 * @param string $promo_rule_id
	 * @param array $args
	 *
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function update_ecommerce_store_promo_rule( $store_id, $promo_rule_id, array $args ) {
		$resource = sprintf( '/ecommerce/stores/%s/promo-rules/%s', $store_id, $promo_rule_id );
		return $this->client->patch( $resource, $args );
	}

	/**
	 * @link https://developer.phplist.com/documentation/phplist/reference/ecommerce/stores/promo-rules/#delete-delete_ecommerce_stores_store_id_promo_rules_promo_rule_id
	 *
	 * @param string $store_id
	 * @param string $promo_rule_id
	 *
	 * @return boolean
	 * @throws PL4WP_API_Exception
	 */
	public function delete_ecommerce_store_promo_rule( $store_id, $promo_rule_id ) {
		$resource = sprintf( '/ecommerce/stores/%s/promo-rules/%s', $store_id, $promo_rule_id );
		return !! $this->client->delete( $resource );
	}

	/**
	 * @link https://developer.phplist.com/documentation/phplist/reference/ecommerce/stores/promo-rules/promo-codes/#create-post_ecommerce_stores_store_id_promo_rules_promo_rule_id_promo_codes
	 *
	 * @param string $store_id
	 * @param array $args
	 *
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function add_ecommerce_store_promo_rule_promo_code( $store_id, $promo_rule_id, array $args ) {
		$resource = sprintf( '/ecommerce/stores/%s/promo-rules/%s/promo-codes', $store_id, $promo_rule_id );
		return $this->client->post( $resource, $args );
	}

	/**
	 * @link https://developer.phplist.com/documentation/phplist/reference/ecommerce/stores/promo-rules/promo-codes/#read-get_ecommerce_stores_store_id_promo_rules_promo_rule_id_promo_codes
	 *
	 * @param string $store_id
	 * @param array $args
	 *
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function get_ecommerce_store_promo_rule_promo_codes( $store_id, $promo_rule_id, array $args = array() ) {
		$resource = sprintf( '/ecommerce/stores/%s/promo-rules/%s/promo-codes', $store_id, $promo_rule_id );
		return $this->client->get( $resource, $args );
	}

	/**
	 * @link https://developer.phplist.com/documentation/phplist/reference/ecommerce/stores/promo-rules/promo-codes/#read-get_ecommerce_stores_store_id_promo_rules_promo_rule_id_promo_codes_promo_code_id
	 *
	 * @param string $store_id
	 * @param string $promo_rule_id
	 * @param array $args
	 *
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function get_ecommerce_store_promo_rule_promo_code( $store_id, $promo_rule_id, $promo_code_id, array $args = array() ) {
		$resource = sprintf( '/ecommerce/stores/%s/promo-rules/%s/promo-codes/%s', $store_id, $promo_rule_id, $promo_code_id );
		return $this->client->get( $resource, $args );
	}

	/**
	 * @link https://developer.phplist.com/documentation/phplist/reference/ecommerce/stores/promo-rules/promo-codes/#edit-patch_ecommerce_stores_store_id_promo_rules_promo_rule_id_promo_codes_promo_code_id
	 *
	 * @param string $store_id
	 * @param string $promo_rule_id
	 * @param array $args
	 *
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function update_ecommerce_store_promo_rule_promo_code( $store_id, $promo_rule_id, $promo_code_id, array $args ) {
		$resource = sprintf( '/ecommerce/stores/%s/promo-rules/%s/promo-codes/%s', $store_id, $promo_rule_id, $promo_code_id );
		return $this->client->patch( $resource, $args );
	}

	/**
	 * @link https://developer.phplist.com/documentation/phplist/reference/ecommerce/stores/promo-rules/promo-codes/#delete-delete_ecommerce_stores_store_id_promo_rules_promo_rule_id_promo_codes_promo_code_id
	 *
	 * @param string $store_id
	 * @param string $promo_rule_id
	 *
	 * @return boolean
	 * @throws PL4WP_API_Exception
	 */
	public function delete_ecommerce_store_promo_rule_promo_code( $store_id, $promo_rule_id, $promo_code_id ) {
		$resource = sprintf( '/ecommerce/stores/%s/promo-rules/%s/promo-codes/%s', $store_id, $promo_rule_id, $promo_code_id );
		return !! $this->client->delete( $resource );
	}


	/**
	 * Get a list of an account's available templates
	 *
	 * @link https://developer.phplist.com/documentation/phplist/reference/templates/#read-get_templates
	 * @param array $args
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function get_templates( array $args = array() ) {
		$resource = '/templates';
		return $this->client->get( $resource, $args );
	}

	/**
	 * Get information about a specific template.
	 *
	 * @link https://developer.phplist.com/documentation/phplist/reference/templates/#read-get_templates_template_id
	 * @param string $template_id
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function get_template( $template_id, array $args = array() ) {
		$resource = sprintf( '/templates/%s', $template_id );
		return $this->client->get( $resource, $args );
	}

	/**
	 * @link https://developer.phplist.com/documentation/phplist/reference/templates/default-content/
	 * @param string $template_id
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function get_template_default_content( $template_id, array $args = array() ) {
		$resource = sprintf( '/templates/%s/default-content', $template_id );
		return $this->client->get( $resource, $args );
	}

	/**
	 * Create a new campaign
	 *
	 * @link https://developer.phplist.com/documentation/phplist/reference/campaigns/#create-post_campaigns
	 * @param array $args
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function add_campaign( array $args ) {
		$resource = '/campaigns';
		return $this->client->post( $resource, $args );
	}

	/**
	 * Get all campaigns in an account
	 *
	 * @link https://developer.phplist.com/documentation/phplist/reference/campaigns/#read-get_campaigns
	 * @param array $args
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function get_campaigns( array $args = array() ) {
		$resource = '/campaigns';
		return $this->client->get( $resource, $args );
	}

	/**
	 * Get information about a specific campaign.
	 *
	 * @link https://developer.phplist.com/documentation/phplist/reference/campaigns/#read-get_campaigns_campaign_id
	 * @param string $campaign_id
	 * @param array $args
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function get_campaign( $campaign_id, array $args = array() ) {
		$resource = sprintf( '/campaigns/%s', $campaign_id );
		return $this->client->get( $resource, $args );
	}

	/**
	 * Update some or all of the settings for a specific campaign.
	 *
	 * @link https://developer.phplist.com/documentation/phplist/reference/campaigns/#edit-patch_campaigns_campaign_id
	 * @param string $campaign_id
	 * @param array $args
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function update_campaign( $campaign_id, array $args ) {
		$resource = sprintf( '/campaigns/%s', $campaign_id );
		return $this->client->patch( $resource, $args );
	}

	/**
	 * Remove a campaign from the PhpList account
	 *
	 * @link https://developer.phplist.com/documentation/phplist/reference/campaigns/#delete-delete_campaigns_campaign_id
	 * @param string $campaign_id
	 * @return bool
	 * @throws PL4WP_API_Exception
	 */
	public function delete_campaign( $campaign_id ) {
		$resource = sprintf( '/campaigns/%s', $campaign_id );
		return !! $this->client->delete( $resource );
	}

	/**
	 * Perform an action on a PhpList campaign
	 *
	 * @link https://developer.phplist.com/documentation/phplist/reference/campaigns/#action-post_campaigns
	 *
	 * @param string $campaign_id
	 * @param string $action
	 * @param array $args
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function campaign_action( $campaign_id, $action, array $args = array() ) {
		$resource = sprintf( '/campaigns/%s/actions/%s', $campaign_id, $action );
		return $this->client->post( $resource, $args );
	}

	/**
	 * Get the HTML and plain-text content for a campaign
	 *
	 * @link https://developer.phplist.com/documentation/phplist/reference/campaigns/content/#read-get_campaigns_campaign_id_content
	 * @param string $campaign_id
	 * @param array $args
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function get_campaign_content( $campaign_id, array $args = array() ) {
		$resource = sprintf( '/campaigns/%s/content', $campaign_id );
		return $this->client->get( $resource, $args );
	}

	/**
	 * Set the content for a campaign
	 *
	 * @link https://developer.phplist.com/documentation/phplist/reference/campaigns/content/#edit-put_campaigns_campaign_id_content
	 * @param string $campaign_id
	 * @param array $args
	 * @return object
	 * @throws PL4WP_API_Exception
	 */
	public function update_campaign_content( $campaign_id, array $args ) {
		$resource = sprintf( '/campaigns/%s/content', $campaign_id );
		return $this->client->put( $resource, $args );
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
