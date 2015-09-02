<?php

defined( 'ABSPATH' ) or exit;

class MC4WP_Comment_Form_Integration extends MC4WP_Integration {

	/**
	 * @var string
	 */
	protected $type = 'comment_form';

	/**
	 * @var bool
	 */
	protected $added_through_filter = false;

	/**
	 * Constructor
	 */
	public function __construct() {

		parent::__construct();

		// hooks for outputting the checkbox
		add_filter( 'comment_form_submit_field', array( $this, 'add_checkbox_before_submit_button' ), 90 );

		add_action( 'thesis_hook_after_comment_box', array( $this, 'maybe_output_checkbox' ), 90 );
		add_action( 'comment_form', array( $this, 'maybe_output_checkbox' ), 90 );

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
		return $this->get_checkbox() . $submit_button_html;
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

		if( $this->is_spam() ) {
			return false;
		}

		// was sign-up checkbox checked?
		if ( $this->checkbox_was_checked() === false ) {
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

		return $this->subscribe( $email, $merge_vars, $this->type, $comment_id );
	}
}