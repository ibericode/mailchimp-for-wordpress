<?php

// prevent direct file access
if( ! defined( 'MC4WP_LITE_VERSION' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

class MC4WP_bbPress_Integration extends MC4WP_Integration {

	/**
	 * @var string
	 */
	protected $type = 'bbpress_forms';

	public function __construct() {

		parent::__construct();

		add_action( 'bbp_theme_after_topic_form_subscriptions', array( $this, 'output_checkbox' ), 10 );
		add_action( 'bbp_theme_after_reply_form_subscription', array( $this, 'output_checkbox' ), 10 );
		add_action( 'bbp_theme_anonymous_form_extras_bottom', array( $this, 'output_checkbox' ), 10 );
		add_action( 'bbp_new_topic', array( $this, 'subscribe_from_bbpress_new_topic' ), 10, 4 );
		add_action( 'bbp_new_reply', array( $this, 'subscribe_from_bbpress_new_reply' ), 10, 5 );
	}

	/**
	* @param array $anonymous_data
	* @param int $user_id
	* @param string $trigger
	* @return boolean
	*/
	public function subscribe_from_bbpress( $anonymous_data, $user_id, $trigger ) {

		if( $this->is_spam() ) {
			return false;
		}

		if ( $this->checkbox_was_checked() === false ) {
			return false;
		}

		if ( $anonymous_data ) {

			$email = $anonymous_data['bbp_anonymous_email'];
			$merge_vars = array(
				'NAME' => $anonymous_data['bbp_anonymous_name'],
			);

		} elseif ( $user_id ) {

			$user_info = get_userdata( $user_id );
			$email = $user_info->user_email;
			$merge_vars = array(
				'NAME' => $user_info->first_name . ' ' . $user_info->last_name,
				'FNAME' => $user_info->first_name,
				'LNAME' => $user_info->last_name,
			);

		} else {
			return false;
		}

		return $this->subscribe( $email, $merge_vars, $trigger );
	}

	public function subscribe_from_bbpress_new_topic( $topic_id, $forum_id, $anonymous_data, $topic_author ) {
		return $this->subscribe_from_bbpress( $anonymous_data, $topic_author, 'bbpress_new_topic' );
	}

	public function subscribe_from_bbpress_new_reply( $reply_id, $topic_id, $forum_id, $anonymous_data, $reply_author ) {
		return $this->subscribe_from_bbpress( $anonymous_data, $reply_author, 'bbpress_new_reply' );
	}

}