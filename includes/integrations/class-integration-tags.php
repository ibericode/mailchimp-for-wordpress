<?php

/**
 * Class MC4WP_Integration_Tags
 *
 * @ignore
 * @access private
 */
class MC4WP_Integration_Tags{

	/**
	 * @var MC4WP_Dynamic_Content_Tags
	 */
	protected $tags;

	/**
	 * @var MC4WP_Integration
	 */
	protected $integration;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->tags = new MC4WP_Dynamic_Content_Tags( 'integrations' );
	}

	/**
	 * Add hooks
	 */
	public function add_hooks() {
		add_filter( 'mc4wp_dynamic_content_tags_integrations', array( $this, 'register' ) );
		add_filter( 'mc4wp_integration_checkbox_label', array( $this, 'replace' ), 10, 2 );
	}

	/**
	 * Register dynamic content tags for integrations
	 *
	 * @hooked `mc4wp_dynamic_content_tags_integrations`
	 * @param array $tags
	 * @return array
	 */
	public function register( array $tags ) {
		$tags['subscriber_count'] = array(
			'description' => __( 'Replaced with the number of subscribers on the selected list(s)', 'mailchimp-for-wp' ),
			'callback'    => array( $this, 'get_subscriber_count' )
		);
		return $tags;
	}

	/**
	 * @hooked `mc4wp_integration_checkbox_label`
	 * @param string $string
	 * @param MC4WP_Integration $integration
	 * @return string
	 */
	public function replace( $string, MC4WP_Integration $integration ) {
		$this->integration = $integration;
		$string = $this->tags->replace( $string );
		return $string;
	}

    /**
     * Returns the number of subscribers on the selected lists (for the form context)
     *
     * @return int
     */
    public function get_subscriber_count() {
        $mailchimp = new MC4WP_MailChimp();
        $count = $mailchimp->get_subscriber_count( $this->integration->get_lists() );
        return number_format( $count );
    }
}