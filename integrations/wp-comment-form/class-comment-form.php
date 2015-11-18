<?php

defined( 'ABSPATH' ) or exit;

/**
 * Class MC4WP_Comment_Form_Integration
 *
 * @ignore
 */
class MC4WP_Comment_Form_Integration extends MC4WP_Integration {

	/**
	 * @var bool
	 */
	protected $added_through_filter = false;

	/**
	 * @var string
	 */
	public $name = "Comment Form";

	/**
	 * @var string
	 */
	public $description = "Subscribes people from your WordPress comment form.";

	/**
	 * Add hooks
	 */
	public function add_hooks() {

		if( ! $this->options['implicit'] ) {
			// hooks for outputting the checkbox
			add_filter( 'comment_form_submit_field', array( $this, 'add_checkbox_before_submit_button' ), 90 );

			add_action( 'thesis_hook_after_comment_box', array( $this, 'maybe_output_checkbox' ), 90 );
			add_action( 'comment_form', array( $this, 'maybe_output_checkbox' ), 90 );
		}

		// hooks for checking if we should subscribe the commenter
		add_action( 'comment_post', array( $this, 'subscribe_from_comment' ), 40, 2 );
	}

	/**
	 * This adds the checkbox just before the submit button and sets a flag to prevent it from outputting twice
	 *
	 * @param $submit_button_html
	 *
	 * @return string
	 */
	public function add_checkbox_before_submit_button( $submit_button_html ) {
		$this->added_through_filter = true;
		return $this->get_checkbox_html() . $submit_button_html;
	}

	/**
	 * Output fallback
	 * Will output the checkbox if comment_form() function does not use `comment_form_submit_field` filter yet.
	 */
	public function maybe_output_checkbox() {
		if( ! $this->added_through_filter ) {
			$this->output_checkbox();
		}
	}

	/**
	 * Grabs data from WP Comment Form
	 *
	 * @param int    $comment_id
	 * @param string $comment_approved
	 *
	 * @return bool|string
	 */
	public function subscribe_from_comment( $comment_id, $comment_approved = '' ) {

		// was sign-up checkbox checked?
		if ( ! $this->triggered() ) {
			return false;
		}

		// is this a spam comment?
		if ( $comment_approved === 'spam' ) {
			return false;
		}

		$comment = get_comment( $comment_id );

		$email = $comment->comment_author_email;
		$merge_vars = array(
			'NAME' => $comment->comment_author,
			'OPTIN_IP' => $comment->comment_author_IP,
		);

		return $this->subscribe( $email, $merge_vars, $comment_id );
	}

	/**
	 * @return bool
	 */
	public function is_installed() {
		return true;
	}

	/**
	 * {@inheritdoc }
	 */
	public function get_object_link( $object_id ) {
		$comment = get_comment( $object_id );
		return sprintf( '<a href="%s">Comment #%d</a>', get_edit_comment_link( $object_id ), $object_id );
	}

}