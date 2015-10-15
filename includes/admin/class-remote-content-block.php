<?php

/**
 * Class MC4WP_Remote_Content_Block
 *
 * @package MailChimp for WordPress
 * @author Danny van Kooten
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
	 * @param string $fallback_content
	 */
	public function __construct( $url, $fallback_content = '' ) {
		$this->url = $url;
		$this->content = $fallback_content;
		$this->cache_key =  'mc4wp_remote_content_' . substr( sanitize_key( $this->url ), 0, 25 );
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
			'timeout' => 3
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
		$this->fetch();
		echo $this->content;
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