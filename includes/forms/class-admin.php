<?php

/**
 * Class MC4WP_Forms_Admin
 *
 * @ignore
 */
class MC4WP_Forms_Admin {

	/**
	 * @var MC4WP_Admin_Messages
	 */
	protected $messages;

	/**
	 * @var MC4WP_MailChimp
	 */
	protected $mailchimp;

	/**
	 * @param MC4WP_Admin_Messages $messages
	 * @param MC4WP_MailChimp $mailchimp
	 */
	public function __construct( MC4WP_Admin_Messages $messages, MC4WP_MailChimp $mailchimp ) {
		$this->messages = $messages;
		$this->mailchimp = $mailchimp;

		require dirname( __FILE__ ) . '/admin-functions.php';
	}

	/**
	 * Add hooks
	 */
	public function add_hooks() {
		add_action( 'mc4wp_save_form', array( $this, 'update_form_stylesheets' ) );
		add_action( 'mc4wp_admin_edit_form', array( $this, 'process_save_form' ) );
		add_action( 'mc4wp_admin_add_form', array( $this, 'process_add_form' ) );
		add_filter( 'mc4wp_admin_menu_items', array( $this, 'add_menu_item' ), 5 );

		add_action( 'mc4wp_admin_show_forms_page-edit-form', array( $this, 'show_edit_page' ) );
		add_action( 'mc4wp_admin_show_forms_page-add-form', array( $this, 'show_add_page' ) );

		//todo decouple admin assets
		//add_action( 'mc4wp_admin_enqueue_assets', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * @param $items
	 *
	 * @return mixed
	 */
	public function add_menu_item( $items ) {

		$items['forms'] = array(
			'title' => __( 'Forms', 'mailchimp-for-wp' ),
			'text' => __( 'Forms', 'mailchimp-for-wp' ),
			'slug' => 'forms',
			'callback' => array( $this, 'show_forms_page' ),
			'load_callback' => array( $this, 'redirect_to_form_action' )
		);

		return $items;
	}

	/**
	 * Act on the "add form" form
	 */
	public function process_add_form() {

		check_admin_referer( 'add_form', '_mc4wp_nonce' );

		$form_data = stripslashes_deep( $_POST['mc4wp_form'] );
		$form_content = include MC4WP_PLUGIN_DIR . 'config/default-form-content.php';
		$form_id = wp_insert_post(
			array(
				'post_type' => 'mc4wp-form',
				'post_status' => 'publish',
				'post_title' => $form_data['name'],
				'post_content' => $form_content,
			)
		);

		update_post_meta( $form_id, '_mc4wp_settings', $form_data['settings'] );

		// @todo allow for easy way to get admin url's
		$this->messages->flash( __( "<strong>Success!</strong> Form successfully saved.", 'mailchimp-for-wp' ) );
		wp_safe_redirect( mc4wp_get_edit_form_url( $form_id ) );
		exit;
	}

	/**
	 * Saves a form
	 */
	public function process_save_form( ) {

		check_admin_referer( 'edit_form', '_mc4wp_nonce' );
		$form_id = (int) $_POST['mc4wp_form_id'];

		// check if attempted post is of post_type `mc4wp_form`
		$post = get_post( $form_id );
		if( ! is_object( $post ) || $post->post_type !== 'mc4wp-form' ) {
			wp_nonce_ays( '' );
		}

		$form_data = stripslashes_deep( $_POST['mc4wp_form'] );
		$form_settings = $form_data['settings'];

		// @todo sanitize data

		$form_id = wp_insert_post(
			array(
				'ID' => $form_id,
				'post_type' => 'mc4wp-form',
				'post_status' => 'publish',
				'post_title' => $form_data['name'],
				'post_content' => $form_data['content']
			)
		);

		update_post_meta( $form_id, '_mc4wp_settings', $form_settings );

		// save form messages in individual meta keys
		foreach( $form_data['messages'] as $key => $message ) {
			update_post_meta( $form_id, $key, $message );
		}

		// update default form id?
		// @todo should this be here?
		$default_form_id = (int) get_option( 'mc4wp_default_form_id', 0 );
		if( empty( $default_form_id ) ) {
			update_option( 'mc4wp_default_form_id', $form_id );
		}

		/**
		 * Runs right after a form is updated.
		 *
		 * @param int $form_id
		 */
		do_action( 'mc4wp_save_form', $form_id );

		$this->messages->flash( __( "<strong>Success!</strong> Form successfully saved.", 'mailchimp-for-wp' ) );
	}

	/**
	 * Goes through each form and aggregates array of stylesheet slugs to load.
	 *
	 * @hooked `mc4wp_save_form`
	 */
	public function update_form_stylesheets() {
		$stylesheets = array();

		$forms = mc4wp_get_forms();
		foreach( $forms as $form ) {

			if( empty( $form->settings['css'] ) ) {
				continue;
			}

			$stylesheet = $form->settings['css'];

			// form themes live in the same stylesheet
			if( strpos( $stylesheet, 'form-theme-' ) !== false ) {
				$stylesheet = 'form-themes';
			}

			if( ! in_array( $stylesheet, $stylesheets ) ) {
				$stylesheets[] = $stylesheet;
			}
		}

		update_option( 'mc4wp_form_stylesheets', $stylesheets );
	}

	/**
	 * Redirect to correct form action
	 *
	 * @ignore
	 */
	public function redirect_to_form_action() {

		if( ! empty( $_GET['view'] ) ) {
			return;
		}

		// query first available form and go there
		$posts = get_posts(
			array(
				'post_type' => 'mc4wp-form',
				'post_status' => 'publish',
				'numberposts' => 1
			)
		);

		// if we have a post, go to the "edit form" screen
		if( $posts ) {
			$post = array_pop( $posts );
			wp_safe_redirect( mc4wp_get_edit_form_url( $post->ID ) );
			exit;
		}

		// we don't have a form yet, go to "add new" screen
		wp_safe_redirect( mc4wp_get_add_form_url() );
		exit;
	}

	/**
	 * Show the Forms Settings page
	 *
	 * @internal
	 */
	public function show_forms_page() {

		$view = ! empty( $_GET['view'] ) ? $_GET['view'] : '';

		/**
		 * @ignore
		 */
		do_action( 'mc4wp_admin_show_forms_page', $view );

		/**
		 * @ignore
		 */
		do_action( 'mc4wp_admin_show_forms_page-' . $view );
	}

	/**
	 * Show the "Edit Form" page
	 *
	 * @internal
	 */
	public function show_edit_page() {
		$form_id = ( ! empty( $_GET['form_id'] ) ) ? (int) $_GET['form_id'] : 0;
		$lists = $this->mailchimp->get_lists();

		try{
			$form = mc4wp_get_form( $form_id );
		} catch( Exception $e ) {
			echo '<h3>' . __( "Form not found.", 'mailchimp-for-wp' ) . '</h3>';
			echo '<p>' . $e->getMessage() . '</p>';
			return;
		}

		$opts = $form->settings;
		$active_tab = ( isset( $_GET['tab'] ) ) ? $_GET['tab'] : 'fields';
		$previewer = new MC4WP_Form_Previewer( $form->ID );

		require dirname( __FILE__ ) . '/views/edit-form.php';
	}

	/**
	 * Shows the "Add Form" page
	 *
	 * @internal
	 */
	public function show_add_page() {
		$lists = $this->mailchimp->get_lists();
		require dirname( __FILE__ ) . '/views/add-form.php';
	}

	/**
	 * Get URL for a tab on the current page.
	 *
	 * @since 3.0
	 * @internal
	 * @param $tab
	 * @return string
	 */
	public function tab_url( $tab ) {
		return add_query_arg( array( 'tab' => $tab ), remove_query_arg( 'tab' ) );
	}

}