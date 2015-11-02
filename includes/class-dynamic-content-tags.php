<?php

/**
 * Class MC4WP_Dynamic_Content_Tags
 *
 * @api
 * @todo Get this to work in "text" settings (integration labels, form messages)
 * @todo Get this to work while being context-aware (subscribers_count, etc..)
 */
class MC4WP_Dynamic_Content_Tags {

	/**
	 * @var MC4WP_Dynamic_Content_Tags
	 */
	private static $instance;

	/**
	 * @var string The escape mode for replacement values.
	 */
	protected $escape_mode = 'html';

	/**
	 * @var array Array of registered dynamic content tags
	 */
	protected $tags = array();

	/**
	 * @return MC4WP_Dynamic_Content_Tags
	 */
	public static function instance() {
		
		if( self::$instance instanceof MC4WP_Dynamic_Content_Tags ) {
			return self::$instance;
		}

		return new self;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		self::$instance = $this;
	}

	/**
	 * Add hooks
	 *
	 * @todo Move out of this class
	 */
	public function add_hooks() {
		add_filter( 'mc4wp_form_message_html', array( $this, 'replace' ) );
		add_filter( 'mc4wp_integration_checkbox_label', array( $this, 'replace' ) );
		add_filter( 'mc4wp_form_content', array( $this, 'replace' ) );
		add_filter( 'mc4wp_form_redirect_url', array( $this, 'replace_in_url' ) );
	}

	/**
	 * Add a dynamic content tag
	 *
	 * @param string $tag
	 * @param string|array Replacement string or configuration array
	 * @return void
	 */
	public function add( $tag, $config ) {

		if( ! is_array( $config ) ) {
			$config = array(
				'replacement' => $config
			);
		}

		$this->tags[ $tag ] = $config;
	}

	/**
	 * Add multiple dynamic content tags at once.
	 *
	 * @param array $tags
	 * @return void
	 */
	public function add_many( array $tags ) {
		foreach( $tags as $tag => $config ) {
			$this->add( $tag, $config );
		}
	}

	/**
	 * @todo Move default tags out of this class
	 *
	 * @return array
	 */
	public function get_tags() {

		if( ! isset( $this->tags['data'] ) ) {

			$default_tags = array(
				'email'  => array(
					'description' => __( 'The email address of the current visitor (if known).', 'mailchimp-for-wp' ),
					'callback'    => array( 'MC4WP_Tools', 'get_known_email' ),
				),
				'current_url'  => array(
					'description' => __( 'The URL of the page.', 'mailchimp-for-wp' ),
					'callback'    => 'mc4wp_get_current_url',
				),
				'current_path' => array(
					'description' => __( 'The path of the page.', 'mailchimp-for-wp' ),
					'callback'    => array( $this, 'get_current_path' )
				),
				'date'         => array(
					'description' => sprintf( __( 'The current date, eg %s.', 'mailchimp-for-wp' ), date( 'Y/m/d' ) ),
					'replacement' => date( 'Y/m/d' )
				),
				'time'         => array(
					'description' => sprintf( __( 'The current time, eg %s.', 'mailchimp-for-wp' ), date( 'H:i:s' ) ),
					'replacement' => date( 'H:i:s' )
				),
				'language'     => array(
					'description' => sprintf( __( 'The site\'s language, eg %s.', 'mailchimp-for-wp' ), get_locale() ),
					'callback'    => 'get_locale',
				),
				'ip'           => array(
					'description' => __( 'The visitor\'s IP address.', 'mailchimp-for-wp' ),
					'callback'    => array( 'MC4WP_Tools', 'get_client_ip' )
				),
				'data'          => array(
					'description' => sprintf( __( "Data from the URL or a submitted form.", 'mailchimp-for-wp' ) ),
					'callback'    => array( $this, 'get_data' ),
					'example'     => 'data key=var default=\'Value..\''
				),
				'user'      => array(
					'description' => sprintf( __( "The given property of the currently logged-in user.", 'mailchimp-for-wp' ) ),
					'callback'    => array( $this, 'get_user_property' ),
					'example'     => 'user property=user_email'
				),
				'subscriber_count' => array(
					'description' => __( 'Replaced with the number of subscribers on the selected list(s)', 'mailchimp-for-wp' ),
					'callback'    => array( $this, 'get_subscriber_count' )
				),
				'response' => array(
					'description'   => __( 'Replaced with the form response (error or success messages).', 'mailchimp-for-wp' ),
					'callback'      => array( $this, 'get_response' )
				)
			);

			$this->tags = array_merge( $this->tags, $default_tags );
			$this->tags = (array) apply_filters( 'mc4wp_dynamic_content_tags', $this->tags );
		}

		return $this->tags;
	}

	/**
	 * @param $matches
	 *
	 * @return string
	 *
	 */
	protected function replace_tag( $matches ) {

		$tags = $this->get_tags();
		$tag = $matches[1];

		if( isset( $tags[ $tag ] ) ) {

			$config = $tags[ $tag ];
			$replacement = '';

			if( isset( $config['replacement'] ) ) {
				$replacement = $config['replacement'];
			} elseif( isset( $config['callback'] ) ) {

				// parse attributes
				$attributes = array();
				if( isset( $matches[2] ) ) {
					$attribute_string = $matches[2];
					$attributes       = shortcode_parse_atts( $attribute_string );
				}

				// call function
				$replacement = call_user_func( $config['callback'], $attributes );
			}

			return $this->escape_value( $replacement );
		}


		// default to not replacing it
		return $matches[0];
	}

	/**
	 * @param string $string The string containing dynamic content tags.
	 * @param string $escape_mode Escape mode for the replacement value.
	 * @return string
	 */
	public function replace( $string, $escape_mode = '' ) {
		$this->escape_mode = $escape_mode;
		$string = preg_replace_callback( '/\{(\w+)(\ +(?:[^}\n])+)*\}/', array( $this, 'replace_tag' ), $string );
		return $string;
	}

	/**
	 * @param string $string
	 *
	 * @return string
	 */
	public function replace_in_html( $string ) {
		return $this->replace( $string, 'html' );
	}

	/**
	 * @param string $string
	 *
	 * @return string
	 */
	public function replace_in_attributes( $string ) {
		return $this->replace( $string, 'attributes' );
	}

	/**
	 * @param string $string
	 *
	 * @return string
	 */
	public function replace_in_url( $string ) {
		return $this->replace( $string, 'url' );
	}

	/**
	 * @todo Move out of this class
	 *
	 * @return string
	 */
	public function get_current_path() {
		return ! empty( $_SERVER['REQUEST_URI'] ) ? esc_html( $_SERVER['REQUEST_URI'] ) : '';
	}

	/**
	 * @todo Move out of this class
	 *
	 * @param $args
	 *
	 * @return string
	 */
	public function get_data( $args = array() ) {

		$key = empty( $args['key'] ) ? '' : strtolower( $args['key'] );
		if( empty( $key ) ) {
			return '';
		}

		$default = isset( $args['default'] ) ? $args['default'] : '';
		return esc_html( MC4WP_Tools::get_request_data( $key, $default ) );
	}

	/**
	 * @todo Move out of this class
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	public function get_user_property( $args = array() ) {
		$property = empty( $args['property'] ) ? 'user_email' : $args['property'];
		$user = wp_get_current_user();

		if( $user instanceof WP_User ) {
			return $user->{$property};
		}

		return '';
	}

	/**
	 * @param $value
	 *
	 * @return string
	 */
	protected function escape_value_url( $value ) {
		return urlencode( $value );
	}

	/**
	 * @param $value
	 *
	 * @return string
	 */
	protected function escape_value_attributes( $value ) {
		return esc_attr( $value );
	}

	/**
	 * @param $value
	 *
	 * @return string
	 */
	protected function escape_value_html( $value ) {
		return esc_html( $value );
	}

	/**
	 * @param $value
	 *
	 * @return mixed
	 */
	protected function escape_value( $value ) {

		if( empty( $this->escape_mode ) ) {
			return $value;
		}

		return call_user_func( array( $this, 'escape_value_' . $this->escape_mode ), $value );
	}

	// todo: get this to work
	public function get_subscriber_count() {
		return 0;
	}

	// todo: get this to work
	public function get_response() {
		return '';
	}
}