<?php

if( ! defined("MC4WP_LITE_VERSION") ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

class MC4WP_Lite_API {
	
	private $api_url = 'https://api.mailchimp.com/2.0/';
	private $api_key = '';
	private $error_message = '';
	private $connected = null;

	public function __construct($api_key)
	{
		$this->api_key = $api_key;

		if(strpos($api_key, '-') !== false) {
			$this->api_url = 'https://' . substr($api_key, -3) . '.api.mailchimp.com/2.0/';
		}
	}

	public function is_connected()
	{
		if($this->connected === null) {
			$result = $this->call('helper/ping');
			$this->connected = ($result && isset($result->msg) && $result->msg === "Everything's Chimpy!");
		}
		
		return $this->connected;
	}

	public function subscribe($list_id, $email, array $merge_vars = array(), $email_type = 'html', $double_optin = true, $update_existing = false, $replace_interests = true, $send_welcome = false )
	{	
		$data = array(
			'id' => $list_id,
			'email' => array( 'email' => $email),
			'merge_vars' => $merge_vars,
			'email_type' => $email_type,
			'double_optin' => $double_optin,
			'update_existing' => $update_existing,
			'replace_interests' => $replace_interests,
			'send_welcome' => $send_welcome
		);

		$result = $this->call('lists/subscribe', $data);
		
		if($result) {

			if(!isset($result->error)) {
				return true;
			} else {
				// check error
				if($result->code == 214) {  return 'already_subscribed'; } 
				
				// store error message
				$this->error_message = $result->error;
				return 'error';
			}

		} else {
			return 'error';
		}
	}

	public function get_list_groupings($list_id)
	{
		$result = $this->call('lists/interest-groupings', array('id' => $list_id) );
		if($result && is_array($result)) {
			return $result;
		} else {
			return false;
		}
	}

	public function get_lists()
	{
		$result = $this->call('lists/list', array('limit' => 100));

		if($result && isset($result->data)) {
			return $result->data;
		} else {
			return false;
		}
	}

	public function get_lists_with_merge_vars($list_ids) 
	{
		$result = $this->call('lists/merge-vars', array('id' => $list_ids));
		
		if($result && isset($result->data)) {
			return $result->data;
		} else {
			return false;
		}
	}

	public function get_member_info($list_id, $emails) {
		$result = $this->call('lists/member-info', array('id' => $list_id, 'emails'  => $emails));
		
		if($result && isset($result->data)) {
			return $result->data;
		} else {
			return false;
		}
	}

	public function list_has_subscriber($list_id, $email) {
		$member_info = $this->get_member_info($list_id, array( array('email' => $email) ) );

		if( $member_info && is_array($member_info) ) {
			return ($member_info[0]->status == "subscribed");
		}

		return false;
	}

	public function call($method, array $data = array())
	{	
		// do not make request when no api key was provided.
		if(empty($this->api_key)) { return false; }

		$data['apikey'] = $this->api_key;
		$url = $this->api_url . $method . '.json';

		$response = wp_remote_post($url, array( 
			'body' => $data,
			'timeout' => 20,
			'headers' => array('Accept-Encoding' => ''),
			'sslverify' => false
			) 
		); 
	
		if(is_wp_error($response)) {
			return false;
		} else {
			// dirty fix for older WP version
			if($method == 'helper/ping' && isset($response['headers']['content-length']) && (int) $response['headers']['content-length'] == '44') { 
				return (object) array( 'msg' => "Everything's Chimpy!");
			}
			
			$body = wp_remote_retrieve_body($response);
			return json_decode($body);
		}
	}

	public function has_error()
	{
		return (!empty($this->error_message));
	}

	public function get_error_message()
	{
		return $this->error_message;
	}

}