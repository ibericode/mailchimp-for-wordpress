<?php

/**
 * Class MC4WP_Remote_Content_Block
 *
 * @package MailChimp for WordPress
 * @author Danny van Kooten
 * @ignore
 */
class MC4WP_Remote_Content_Block {

	/**
	 * @var string
	 */
	protected $content = '';

	/**
	 * @var int
	 */
	protected $cache_time = 7200; // 2 hours

	/**
	 * @var string
	 */
	protected $cache_key = '';

	/**
	 * @var string
	 */
	protected $url = '';

	/**
	 * @param string $url
	 * @param int $cache_time
	 * @param string $fallback_content
	 */
	public function __construct( $url, $cache_time = 86400, $fallback_content = '' ) {
		$this->url = $url;
		$this->cache_time = $cache_time;
		$this->content = $fallback_content;

		// don't let this transient key exceed 45 characters total
		// use end of url as key because it's likely same domain is used
		$this->cache_key =  'mc4wp_remote_content_' . substr(  sanitize_key( $this->url ), -23 ); // 44 characters
	}

	/**
	 * @return bool
	 */
	public function fetch() {

		// first, try transient cache
		if( $this->fetch_from_cache() ) {
			return true;
		}

		// no? try remote url
		if( $this->fetch_from_remote() ) {
			return true;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	protected function fetch_from_cache() {
		$content = get_transient( $this->cache_key );

		if( ! is_string( $content ) ) {
			return false;
		}

		$this->content = $content;
		return true;
	}

	/**
	 * @return bool
	 */
	protected function fetch_from_remote() {

		$args = array(
			'timeout' => 5,
			'user-agent' => ''
		);

		$response = wp_remote_get( $this->url, $args );
		$response_code = (int) wp_remote_retrieve_response_code( $response );
		if( $response_code !== 200 ) {
			return false;
		}

		$content = wp_remote_retrieve_body( $response );
		if( empty( $content ) ) {
			return false;
		}

		$this->content = $content;
		$this->cache( $content );
		return true;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		$this->fetch();
		return $this->content;
	}

	/**
	 * Output the content block
	 */
	public function output() {
		echo $this;
	}

	/**
	 * Refresh content
	 */
	public function refresh() {
		delete_transient( $this->cache_key );
	}

	/**
	 * Cache content for the given cache time
	 *
	 * @param string $content
	 */
	protected function cache( $content ) {
		set_transient( $this->cache_key, $content, $this->cache_time );
	}
}