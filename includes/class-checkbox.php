<?php

if( ! defined("MC4WP_LITE_VERSION") ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

class MC4WP_Lite_Checkbox
{	
	/**
	* @var boolean
	*/
	private $showed_checkbox = false;
	
	/**
	* Constructor
	*/
	public function __construct()
	{
		$opts = mc4wp_get_options('checkbox');

		add_action('init', array( $this, 'initialize' ) ); 

		// load checkbox css if necessary
		if ( $opts['css'] ) {
			add_filter( 'mc4wp_stylesheets', array( $this, 'add_stylesheet' ) );
		}

		/* Comment Form Actions */
		if($opts['show_at_comment_form']) {
			// hooks for checking if we should subscribe the commenter
			add_action('comment_post', array($this, 'subscribe_from_comment'), 20, 2);

			// hooks for outputting the checkbox
			add_action('thesis_hook_after_comment_box', array($this,'output_checkbox'), 20);
			add_action('comment_form', array($this,'output_checkbox'), 20);
		}

		/* Registration Form Actions */
		if($opts['show_at_registration_form']) {
			add_action('register_form',array($this, 'output_checkbox'),20);
			add_action('user_register',array($this, 'subscribe_from_registration'), 80, 1);
		}

		/* BuddyPress Form Actions */
		if($opts['show_at_buddypress_form']) {
			add_action('bp_before_registration_submit_buttons', array($this, 'output_checkbox'), 20);
			add_action('bp_complete_signup', array($this, 'subscribe_from_buddypress'), 20);
		}

		/* Multisite Form Actions */
		if($opts['show_at_multisite_form']) {
			add_action('signup_extra_fields', array($this, 'output_checkbox'), 20);
			add_action('signup_blogform', array($this, 'add_multisite_hidden_checkbox'), 20);
			add_action('wpmu_activate_blog', array($this, 'on_multisite_blog_signup'), 20, 5);
			add_action('wpmu_activate_user', array($this, 'on_multisite_user_signup'), 20, 3);

			add_filter('add_signup_meta', array($this, 'add_multisite_usermeta'));
		}

		/* bbPress actions */
		if($opts['show_at_bbpress_forms']) {
			add_action('bbp_theme_after_topic_form_subscriptions', array($this, 'output_checkbox'), 10);
			add_action('bbp_theme_after_reply_form_subscription', array($this, 'output_checkbox'), 10);
			add_action('bbp_theme_anonymous_form_extras_bottom', array($this, 'output_checkbox'), 10);
			add_action('bbp_new_topic', array($this, 'subscribe_from_bbpress_new_topic'), 10, 4);
			add_action('bbp_new_reply', array($this, 'subscribe_from_bbpress_new_reply'), 10, 5);
		}

	}

	public function initialize()
	{
		if( function_exists( "wpcf7_add_shortcode" ) ) {
			wpcf7_add_shortcode( 'mc4wp_checkbox', array( $this, 'get_checkbox') );
			add_action( 'wpcf7_posted_data', array( $this, 'alter_cf7_data') );
			add_action( 'wpcf7_mail_sent', array( $this, 'subscribe_from_cf7' ) );
		}

		// catch-all (for manual integrations with third-party forms)
		if( isset( $_POST['mc4wp-try-subscribe'] ) && $_POST['mc4wp-try-subscribe'] ) {
			$this->subscribe_from_whatever();
		}
	}

	public function get_checkbox( $args = array() )
	{
		$opts = mc4wp_get_options('checkbox');

		$label = isset( $args['labels'][0] ) ? $args['labels'][0] : $opts['label'];
		$checked = $opts['precheck'] ? "checked" : '';

		// CF7 checkbox?
		if( is_array( $args ) && isset( $args['type'] ) ) {

			// check for default:0 or default:1 to set the checked attribute
		 	if( in_array( 'default:1', $args['options'] ) ) {
		 		$checked = 'checked';
		 	} else if( in_array( 'default:0', $args['options'] ) ) {
		 		$checked = '';
		 	}

		}

		$content = "\n<!-- Checkbox by MailChimp for WordPress plugin v". MC4WP_LITE_VERSION ." - http://dannyvankooten.com/mailchimp-for-wordpress/ -->\n";
		
		do_action('mc4wp_before_checkbox');

		$content .= '<p id="mc4wp-checkbox">';
		$content .= '<label><input type="checkbox" name="mc4wp-do-subscribe" value="1" '. $checked . ' /> ' . __($label) . '</label>';
		$content .= '</p>';
		
		do_action('mc4wp_after_checkbox');

		$content .= "\n<!-- / MailChimp for WP Plugin -->\n";
		return $content;
	}

	/**
	* Outputs a sign-up checkbox
	*/
	public function output_checkbox()
	{
		if( $this->showed_checkbox ) {
			return;
		}

		// echo the sign-up checkbox
		echo $this->get_checkbox();

		$this->showed_checkbox = true;
	}

	/**
	* Adds the checkbox stylesheet to the array
	* @param array $stylesheets
	* @return array
	*/
	public function add_stylesheet( $stylesheets ) {
		$stylesheets['checkbox'] = 1;
		return $stylesheets;
	}


	/* Start comment form functions */
	public function subscribe_from_comment( $cid, $comment_approved = '' ) {
		if( ! isset( $_POST['mc4wp-do-subscribe'] ) || $_POST['mc4wp-do-subscribe'] != 1 ) { 
			return false; 
		}

		if( $comment_approved === 'spam' ) { 
			return false; 
		}

		$comment = get_comment( $cid );
		
		$email = $comment->comment_author_email;
		$merge_vars = array(
			'OPTINIP' => $comment->comment_author_IP,
			'NAME' => $comment->comment_author
		);

		return $this->subscribe( $email, $merge_vars );
	}

	public function add_comment_meta( $comment_id ) {
		 add_comment_meta($comment_id, 'mc4wp_subscribe', $_POST['mc4wp-do-subscribe'], true );
	}
	/* End comment form functions */

	/* Start registration form functions */
	public function subscribe_from_registration( $user_id ) {

		if( ! isset( $_POST['mc4wp-do-subscribe'] ) || $_POST['mc4wp-do-subscribe'] != 1 ) { 
			return false; 
		}
			
		// gather emailadress from user who WordPress registered
		$user = get_userdata( $user_id );
		if( $user == false ) { 
			return false; 
		}

		$email = $user->user_email;
		$merge_vars = array(
			'NAME' => $user->user_login
		);

		if( isset( $user->user_firstname ) && ! empty( $user->user_firstname ) ) {
			$merge_vars['FNAME'] = $user->user_firstname;
		}

		if( isset( $user->user_lastname ) && ! empty( $user->user_lastname ) ) {
			$merge_vars['LNAME'] = $user->user_lastname;
		}
		
		$result = $this->subscribe( $email, $merge_vars ); 
	}
	/* End registration form functions */

	/* Start BuddyPress functions */
	public function subscribe_from_buddypress()
	{
		if( ! isset( $_POST['mc4wp-do-subscribe'] ) || $_POST['mc4wp-do-subscribe'] != 1 ) {
			return false;
		}
			
		// gather emailadress and name from user who BuddyPress registered
		$email = $_POST['signup_email'];
		$merge_vars = array(
			'NAME' => $_POST['signup_username']
		);

		return $this->subscribe( $email, $merge_vars );
	}
	/* End BuddyPress functions */

	/* Start Multisite functions */
	public function add_multisite_hidden_checkbox()
	{
		?><input type="hidden" name="mc4wp-do-subscribe" value="<?php echo ( isset( $_POST['mc4wp-do-subscribe'] ) ) ? 1 : 0; ?>" /><?php
	}

	public function on_multisite_blog_signup( $blog_id, $user_id, $a, $b ,$meta = null )
	{
		if( ! isset($meta['mc4wp-do-subscribe'] ) || $meta['mc4wp-do-subscribe'] != 1) {
			return false;
		}

		return $this->subscribe_from_multisite($user_id);
	}

	public function on_multisite_user_signup($user_id, $password = NULL, $meta = NULL)
	{
		if( ! isset( $meta['mc4wp-do-subscribe'] ) || $meta['mc4wp-do-subscribe'] != 1 ) {
			return false;
		}

		return $this->subscribe_from_multisite($user_id);
	}

	/**
	* Adds the checked state of the sign-up checkbox to the $meta array of Multisite sign-ups
	*
	* @param array $meta
	* @return array
	*/
	public function add_multisite_usermeta( $meta = array() )
	{
		$meta['mc4wp-do-subscribe'] = ( isset( $_POST['mc4wp-do-subscribe'] ) ) ? 1 : 0;
		return $meta;
	}

	/**
	* Subscribes from Multisite sign-ups
	* @param int $user_id
	*/
	public function subscribe_from_multisite( $user_id )
	{
		$user = get_userdata( $user_id );
		
		if( ! is_object( $user ) ) {
			return false;
		}

		$email = $user->user_email;
		$merge_vars = array(
			'NAME' => $user->first_name . ' ' . $user->last_name
		);
		$result = $this->subscribe( $email, $merge_vars );
	}
	/* End Multisite functions */

	/* Start Contact Form 7 functions */

	/**
	* Adds the checkbox state to CF7 email data
	* @param array $data
	* @return array
	*/
	public function alter_cf7_data( $data = array() ) {
		$data['mc4wp_checkbox'] = ( isset( $_POST['mc4wp-do-subscribe'] ) && $_POST['mc4wp-do-subscribe'] == 1 ) ? __("Yes") : __("No");
		return $data;
	}

	/**
	* Subscribe from Contact Form 7 submissions
	* @param array $args (optional)
	*/
	public function subscribe_from_cf7( $args = null )
	{
		// check if CF7 "mc4wp" checkbox was checked
		if( ! isset( $_POST['mc4wp-do-subscribe'] ) || ! $_POST['mc4wp-do-subscribe'] ) { 
			return false; 
		}
		
		$_POST['mc4wp-try-subscribe'] = 1;
		unset( $_POST['mc4wp-do-subscribe'] );

		return $this->subscribe_from_whatever();
	}
	/* End Contact Form 7 functions */

	/* Start whatever functions */
	public function subscribe_from_whatever()
	{
		if(! isset( $_POST['mc4wp-try-subscribe'] ) || ! $_POST['mc4wp-try-subscribe'] ) { 
			return false; 
		}

		// start running..
		$email = null;
		$merge_vars = array(
			'GROUPINGS' => array()
		);

		foreach( $_POST as $key => $value ) {

			if( $key == 'mc4wp-try-subscribe' ) { 
				continue; 
			} elseif( strtolower( substr( $key, 0, 6 ) ) == 'mc4wp-' ) {
				// find extra fields which should be sent to MailChimp
				$key = strtoupper( substr( $key, 6 ) );

				if( $key == 'EMAIL' ) {
					$email = $value;
				} elseif( $key == 'GROUPINGS' && is_array( $value ) ) {

					$groupings = $value;

					foreach( $groupings as $grouping_id_or_name => $groups ) {

						$grouping = array();

						// group ID or group name given?
						if( is_numeric( $grouping_id_or_name ) ) {
							$grouping['id'] = $grouping_id_or_name;
						} else {
							$grouping['name'] = $grouping_id_or_name;
						}

						// comma separated list should become an array
						if( ! is_array( $groups ) ) {
							$grouping['groups'] = explode( ',', $groups );
						} else {
							$grouping['groups'] = $groups;
						}

						// add grouping to array
						$merge_vars['GROUPINGS'][] = $grouping;

					} // end foreach

				} elseif( ! isset( $merge_vars[$key] ) ) {

					// if value is array, convert to comma-delimited string
					if( is_array( $value ) ) { 
						$value = implode( ',', $value ); 
					}

					$merge_vars[$key] = $value;
				}

			} elseif( ! $email && is_email( $value ) ) {
				// find first email field
				$email = $value;
			} else {
				$simple_key = str_replace( array( '-', '_' ), '', strtolower( $key ) );

				if( ! isset( $merge_vars['NAME'] ) && in_array( $simple_key, array( 'name', 'yourname', 'username', 'fullname' ) ) ) {
					// find name field
					$merge_vars['NAME'] = $value;
				} 

				if( ! isset( $merge_vars['FNAME'] ) && in_array( $simple_key, array( 'firstname', 'fname', "givenname", "forename" ) ) ) {
					// find first name field
					$merge_vars['FNAME'] = $value;
				}

				if( ! isset($merge_vars['LNAME']) && in_array( $simple_key, array( 'lastname', 'lname', 'surname', 'familyname' ) ) ) {
					// find last name field
					$merge_vars['LNAME'] = $value;
				}
			} 
		} // end foreach $_POST


		// unset groupings if not used
		if( empty( $merge_vars['GROUPINGS'] ) ) { 
			unset( $merge_vars['GROUPINGS'] ); 
		}

		// if email has not been found by the smart field guessing, return false.. sorry
		if( ! $email ) { 
			return false; 
		}

		// subscribe
		$result = $this->subscribe( $email, $merge_vars );
		return true;
	}
	/* End whatever functions */

	/**
	* @param array $anonymous_data
	* @param int $user_id
	* @return boolean
	*/
	public function subscribe_from_bbpress( $anonymous_data, $user_id ) {
		if( ! isset($_POST['mc4wp-do-subscribe'] ) || $_POST['mc4wp-do-subscribe'] != 1 ) { 
			return; 
		}

		if( $anonymous_data ) {

			$email = $anonymous_data['bbp_anonymous_email'];
			$merge_vars = array(
				'NAME' => $anonymous_data['bbp_anonymous_name']
			);

		} elseif( $user_id ) {

			$user_info = get_userdata( $user_id );	
			$email = $user_info->user_email;
			$merge_vars = array(
				'NAME' => $user_info->first_name . ' ' . $user_info->last_name,
				'FNAME' => $user_info->first_name,
				'LNAME' => $user_info->last_name
			);

		} else {
			return false;
		}

		return $this->subscribe( $email, $merge_vars );
	}

	public function subscribe_from_bbpress_new_topic( $topic_id, $forum_id, $anonymous_data, $topic_author ) {
		return $this->subscribe_from_bbpress( $anonymous_data, $topic_author );
	}

	public function subscribe_from_bbpress_new_reply( $reply_id, $topic_id, $forum_id, $anonymous_data, $reply_author )
	{
		return $this->subscribe_from_bbpress( $anonymous_data, $reply_author );
	}

	/**
	* Sets up the required data and calls the API Subscribe method
	*
	* @param string $email
	* @param array $merge_vars
	* @return boolean
	*/
	public function subscribe( $email, array $merge_vars = array() )
	{
		$api = mc4wp_get_api();
		$opts = mc4wp_get_options('checkbox');

		$lists = $opts['lists'];
		
		if( ! $lists || empty( $lists ) ) {

			if( ( ! defined("DOING_AJAX") || ! DOING_AJAX ) && current_user_can( 'manage_options' ) ) {
				wp_die('
					<h3>MailChimp for WP - Error</h3>
					<p>Please select a list to subscribe to in the <a href="'. admin_url('admin.php?page=mc4wp-lite-checkbox-settings') .'">checkbox settings</a>.</p>
					<p style="font-style:italic; font-size:12px;">This message is only visible to administrators for debugging purposes.</p>
					', "Error - MailChimp for WP", array( 'back_link' => true ) );
			}

			return 'no_lists_selected';
		}

		
		// guess FNAME and LNAME
		if ( isset( $merge_vars['NAME'] ) && ! isset( $merge_vars['FNAME'] ) && ! isset( $merge_vars['LNAME'] ) ) {

			$strpos = strpos( $merge_vars['NAME'], ' ' );

			if ( $strpos ) {
				$merge_vars['FNAME'] = trim( substr( $merge_vars['NAME'], 0, $strpos ) );
				$merge_vars['LNAME'] = trim( substr( $merge_vars['NAME'], $strpos ) );
			} else {
				$merge_vars['FNAME'] = $merge_vars['NAME'];
			}
		}

		$merge_vars = apply_filters( 'mc4wp_merge_vars', $merge_vars, '' );
		$email_type = apply_filters( 'mc4wp_email_type', 'html' );
		$lists = apply_filters( 'mc4wp_lists', $lists, $merge_vars );
		
		foreach( $lists as $list ) {
			$result = $api->subscribe( $list, $email, $merge_vars, $email_type, $opts['double_optin'] );

			if( $result === true ) { 
				$from_url = ( isset( $_SERVER['HTTP_REFERER'] ) ) ? $_SERVER['HTTP_REFERER'] : '';
				do_action( 'mc4wp_subscribe_checkbox', $email, $list, $merge_vars );
			}
		}
		
		// check if result succeeded, show debug message to administrators
		if( $result !== true && $api->has_error() && current_user_can( 'manage_options' ) && ! defined( "DOING_AJAX" ) ) 
		{
			wp_die("
					<h3>MailChimp for WP - Error</h3>
					<p>The MailChimp server returned the following error message as a response to our sign-up request:</p>
					<pre>" . $api->get_error_message() . "</pre>
					<p>This is the data that was sent to MailChimp: </p>
					<strong>Email</strong>
					<pre>{$email}</pre>
					<strong>Merge variables</strong>
					<pre>" . print_r( $merge_vars, true ) . "</pre>
					<p style=\"font-style:italic; font-size:12px; \">This message is only visible to administrators for debugging purposes.</p>
					", "Error - MailChimp for WP", array( 'back_link' => true ) );
		}

		return $result;
	}



}