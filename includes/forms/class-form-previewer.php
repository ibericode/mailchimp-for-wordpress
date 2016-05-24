<?php

/**
 * Class MC4WP_Form_Previewer
 *
 * @access private
 * @since 3.0
 * @ignore
 */
class MC4WP_Form_Previewer {

	/**
	 * @var int The form ID to show in the preview page.
	 */
	public $form_id = 0;

	/**
	 * @var bool
	 */
	public $is_preview = false;

	/**
	 * @var int
	 */
	public $preview_form_id = 0;

	/**
	 * @var MC4WP_Form
	 */
	public $form;

	/**
	 * @const string
	 */
	const PAGE_SLUG = 'mc4wp-form-preview';

	/**
	 * Listens for requests to our form preview page
	 *
	 * @return bool
	 */
	public static function init() {

		if( ! is_page( self::PAGE_SLUG ) || empty( $_GET['form_id'] ) ) {
			return false;
		}

		if( ! current_user_can( 'read_private_pages' ) ) {
			return false;
		}

		define( 'MC4WP_FORM_IS_PREVIEW', true );

		$form_id        = (int) $_GET[ 'form_id' ];
		$is_preview     = isset( $_GET['preview'] );
		$instance = new self( $form_id, $is_preview );

		add_filter( 'mc4wp_form_stylesheets', array( $instance, 'set_stylesheet' ) );
		add_filter( 'mc4wp_form_css_classes', array( $instance, 'add_css_class' ), 10, 2 );
		add_filter( 'the_title', array( $instance, 'set_page_title' ) );
		add_filter( 'the_content', array( $instance, 'set_page_content' ) );
	}

	/**
	 * @param int $form_id
	 * @param bool $is_preview
	 */
	public function __construct( $form_id, $is_preview = false ) {
		$this->form_id = $form_id;
		$this->is_preview = $is_preview;

		// get the preview form
		if( $is_preview ) {
			$this->preview_form_id = $this->get_preview_id();
		}

		// if that failed, get the real form
		if( empty( $this->preview_form_id ) ) {
			$this->preview_form_id = $this->form_id;
		}

		// get form instance
		$this->form = mc4wp_get_form( $this->preview_form_id );
	}

	/**
	 * Gets the ID of the preview form page
	 *
	 * @return int
	 */
	public function get_preview_page_id() {
		$page = get_page_by_path( self::PAGE_SLUG );

		if( $page instanceof WP_Post && in_array( $page->post_status, array( 'draft', 'publish' ) ) ) {
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
	 * Sets or updates the ID for the preview form
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	public function set_preview_id( $id ) {
		return update_option( 'mc4wp_form_preview_id', $id, false );
	}

	/**
	 * Gets the ID of the preview form
	 *
	 * @return int
	 */
	public function get_preview_id() {
		return get_option( 'mc4wp_form_preview_id', 0 );
	}

	/**
	 * Gets the full URL for the form preview page
	 *
	 * @return string
	 */
	public function get_preview_url() {
		$base_url = get_permalink( $this->get_preview_page_id() );
		$args = array(
			'form_id' => $this->form_id
		);

		if( $this->is_preview ) {
			$args['preview'] = '';
		}

		$preview_url = add_query_arg( $args, $base_url );
		return $preview_url;
	}

	/**
	 * We only need the stylesheet of this form for this preview page.
	 *
	 * @param $stylesheets
	 *
	 * @return string
	 */
	public function set_stylesheet( $stylesheets ) {
		return array( $this->form->get_stylesheet() );
	}

	/**
	 * @param string $title
	 * @return string
	 */
	public function set_page_title( $title ) {
		if( ! in_the_loop() ) {
			return $title;
		}

		return __( 'Form preview', 'mailchimp-for-wp' );
	}

	/**
	 * @param string $content
	 * @return string
	 */
	public function set_page_content( $content ) {
		if( ! in_the_loop() ) {
			return $content;
		}

		return $this->form;
	}

	/**
	 * Adds the real CSS class to the preview form-id
	 *
	 * @param array $classes
	 * @return array
	 */
	public function add_css_class( $classes, $form ) {

		// only act on our preview form
		if( $form !== $this->form ) {
			return $classes;
		}

		// replace preview ID with actual form ID in all classes
		foreach( $classes as $key => $class ) {
			$classes[ $key ] = str_replace( $this->preview_form_id, $this->form_id, $class );
		}

		return $classes;
	}
}