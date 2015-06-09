<?php

class MC4WP_Comment_Form_Integration extends MC4WP_Integration_Base {

	public $type = 'comment_form';

	public $name = 'Comment Form';

	public function add_hooks() {
		// hooks for outputting the checkbox
		add_action( 'thesis_hook_after_comment_box', array( $this, 'output_checkbox' ), 10 );
		add_action( 'comment_form', array( $this, 'output_checkbox' ), 10 );

		// hooks for checking if we should subscribe the commenter
		add_action( 'comment_post', array( $this, 'subscribe_from_comment' ), 40, 2 );
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

		return $this->subscribe( $email, $merge_vars, $comment_id );
	}
}