<?php

/**
 * Class MC4WP_Form_Previewer
 *
 * @todo Test this class on WP MultiSite
 */
class MC4WP_Form_Previewer {

	/**
	 * @var int
	 */
	public $form_id = 0;

	/**
	 * @const string
	 */
	const URL_PARAMETER = '_mc4wp_preview_form_id';

	/**
	 * @const string
	 */
	const PAGE_SLUG = 'mc4wp-form-preview';

	/**
	 * @return bool
	 */
	public static function init() {

		if( empty( $_GET[ self::URL_PARAMETER ] ) ) {
			return false;
		}

		$form_id = $_GET[ self::URL_PARAMETER ];
		$instance = new self( $form_id );

		if( ! $instance->at_preview_page() ) {
			$instance->go_to_preview_page();
		}

		add_filter( 'the_title', array( $instance, 'set_page_title' ) );
		add_filter( 'the_content', array( $instance, 'set_page_content' ) );
	}

	/**
	 * @param $form_id
	 */
	public function __construct( $form_id ) {
		$this->form_id = (int) $form_id;
	}

	/**
	 * @return int
	 */
	public function get_preview_page_id() {
		$page = get_page_by_path( self::PAGE_SLUG );

		if( $page instanceof WP_Post ) {
			$page_id = $page->ID;
		} else {
			$page_id = wp_insert_post(
				array(
					'post_name' =>  self::PAGE_SLUG,
					'post_type' => 'page',
					'post_status' => 'draft',
					'post_title' => 'MailChimp for WordPress: Form Preview',
					'post_content' => '[mc4wp_form]'
				)
			);
		}

		return $page_id;
	}

	/**
	 * @param bool $on_page
	 * @return string
	 */
	public function get_preview_url( $on_page = false ) {
		$base_url = $on_page ? get_permalink( $this->get_preview_page_id() ) : get_site_url();
		$preview_url = add_query_arg( array( self::URL_PARAMETER => $this->form_id ), $base_url );
		return $preview_url;
	}

	/**
	 * @return bool
	 */
	public function at_preview_page() {
		return is_page( self::PAGE_SLUG );
	}

	/**
	 * @return bool
	 */
	public function go_to_preview_page() {
		wp_redirect( $this->get_preview_url( true ) );
		exit;
	}

	/**
	 * @param string $title
	 * @return string
	 */
	public function set_page_title( $title ) {
		return __( 'Form preview', 'mailchimp-for-wp' );
	}

	/**
	 * @param string $content
	 * @return string
	 */
	public function set_page_content( $content ) {
		return sprintf(  '[mc4wp_form id="%d"]', $this->form_id );
	}

}