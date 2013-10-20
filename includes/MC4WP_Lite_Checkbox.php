<?php

class MC4WP_Lite_Checkbox
{
	private $showed_checkbox = false;

	public function __construct()
	{
		$opts = $this->get_options();

		add_action('init', array($this, 'on_init')); 

		// load checkbox css if necessary
		if ( $opts['css'] ) {
			add_filter('mc4wp_stylesheets', array($this, 'add_stylesheet'));
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

		/* Other actions... catch-all */
		if($opts['show_at_other_forms']) {
			add_action('init', array($this, 'subscribe_from_whatever'));
		}

	}

	public function get_options()
	{
		$options = MC4WP_Lite::instance()->get_options();
		return $options['checkbox'];
	}

	public function on_init()
	{
		if(function_exists("wpcf7_add_shortcode")) {
			wpcf7_add_shortcode('mc4wp_checkbox', array($this, 'get_checkbox'));
			add_action('wpcf7_mail_sent', array($this, 'subscribe_from_cf7'));
		}
	}

	public function get_checkbox($args = array())
	{
		$opts = $this->get_options();
		$label = isset($args['labels'][0]) ? $args['labels'][0] : $opts['label'];
		$checked = $opts['precheck'] ? "checked" : '';
		$content = "\n<!-- Checkbox by MailChimp for WP plugin v". MC4WP_LITE_VERSION ." - http://dannyvankooten.com/wordpress-plugins/mailchimp-for-wordpress/ -->\n";
		$content .= '<p id="mc4wp-checkbox">';
		$content .= '<input type="checkbox" name="mc4wp-do-subscribe" id="mc4wp-checkbox-input" value="1" '. $checked . ' />';
		$content .= '<label for="mc4wp-checkbox-input">'. __($label) . '</label>';
		$content .= '</p>';
		$content .= "\n<!-- / MailChimp for WP Plugin -->\n";
		return $content;
	}

	public function output_checkbox()
	{
		if($this->showed_checkbox) return;
		echo $this->get_checkbox();
		$this->showed_checkbox = true;
	}

	public function add_stylesheet($stylesheets) {
		$stylesheets['checkbox'] = 1;
		return $stylesheets;
	}


	/* Start comment form functions */
	public function subscribe_from_comment($cid, $comment_approved = '')
	{
		if(!isset($_POST['mc4wp-do-subscribe']) || $_POST['mc4wp-do-subscribe'] != 1) { return false; }
		if($comment_approved === 'spam') { return false; }

		$comment = get_comment($cid);
		
		$email = $comment->comment_author_email;
		$merge_vars = array(
			'OPTINIP' => $comment->comment_author_IP,
			'NAME' => $comment->comment_author
			);

		return $this->subscribe($email, $merge_vars);
	}

	public function add_comment_meta($comment_id)
	{
		 add_comment_meta($comment_id, 'mc4wp_subscribe', $_POST['mc4wp-do-subscribe'], true );
	}
	/* End comment form functions */

	/* Start registration form functions */
	public function subscribe_from_registration($user_id)
	{
		if(!isset($_POST['mc4wp-do-subscribe']) || $_POST['mc4wp-do-subscribe'] != 1) { return false; }
			
		// gather emailadress from user who WordPress registered
		$user = get_userdata($user_id);
		if(!$user) { return false; }

		$email = $user->user_email;
		$merge_vars = array(
			'NAME' => $user->user_login
		);

		if(isset($user->user_firstname) && !empty($user->user_firstname)) {
			$merge_vars['FNAME'] = $user->user_firstname;
		}

		if(isset($user->user_lastname) && !empty($user->user_lastname)) {
			$merge_vars['LNAME'] = $user->user_lastname;
		}
		
		$result = $this->subscribe($email, $merge_vars); 
	}
	/* End registration form functions */

	/* Start BuddyPress functions */
	public function subscribe_from_buddypress()
	{
		if(!isset($_POST['mc4wp-do-subscribe']) || $_POST['mc4wp-do-subscribe'] != 1) return;
			
		// gather emailadress and name from user who BuddyPress registered
		$email = $_POST['signup_email'];
		$merge_vars = array(
			'NAME' => $_POST['signup_username']
		);

		$result = $this->subscribe($email, $merge_vars);
	}
	/* End BuddyPress functions */

	/* Start Multisite functions */
	public function add_multisite_hidden_checkbox()
	{
		?><input type="hidden" name="mc4wp-do-subscribe" value="<?php echo (isset($_POST['mc4wp-do-subscribe'])) ? 1 : 0; ?>" /><?php
	}

	public function on_multisite_blog_signup($blog_id, $user_id, $a, $b ,$meta = null)
	{
		if(!isset($meta['mc4wp-do-subscribe']) || $meta['mc4wp-do-subscribe'] != 1) return false;
		
		return $this->subscribe_from_multisite($user_id);
	}

	public function on_multisite_user_signup($user_id, $password = NULL, $meta = NULL)
	{
		if(!isset($meta['mc4wp-do-subscribe']) || $meta['mc4wp-do-subscribe'] != 1) return false;
		
		return $this->subscribe_from_multisite($user_id);
	}

	public function add_multisite_usermeta($meta)
	{
		$meta['mc4wp-do-subscribe'] = (isset($_POST['mc4wp-do-subscribe'])) ? 1 : 0;
		return $meta;
	}

	public function subscribe_from_multisite($user_id)
	{
		$user = get_userdata($user_id);
		
		if(!is_object($user)) return false;

		$email = $user->user_email;
		$merge_vars = array(
			'NAME' => $user->first_name . ' ' . $user->last_name
		);
		$result = $this->subscribe($email, $merge_vars);
	}
	/* End Multisite functions */

	/* Start Contact Form 7 functions */
	public function subscribe_from_cf7($arg = null)
	{
		if(!isset($_POST['mc4wp-do-subscribe']) || !$_POST['mc4wp-do-subscribe']) { return false; }
		
		$_POST['mc4wp-try-subscribe'] = 1;
		unset($_POST['mc4wp-do-subscribe']);

		return $this->subscribe_from_whatever();
	}
	/* End Contact Form 7 functions */

	/* Start whatever functions */
	public function subscribe_from_whatever()
	{
		if(!isset($_POST['mc4wp-try-subscribe']) || !$_POST['mc4wp-try-subscribe']) { return false; }

		// start running..
		$email = null;
		$merge_vars = array();

		foreach($_POST as $key => $value) {

			if($key == 'mc4wp-try-subscribe') { 
				continue; 
			} elseif(strtolower(substr($key, 0, 6)) == 'mc4wp-') {
				// find extra fields which should be sent to MailChimp
				$key = strtoupper(substr($key, 6));

				if($key == 'EMAIL') {
					$email = $value;
				} elseif(!isset($merge_vars[$key])) {
					// if value is array, convert to comma-delimited string
					if(is_array($value)) { $value = implode(',', $value); }

					$merge_vars[$key] = $value;
				}

			} elseif(!$email && is_email($value)) {
				// find first email field
				$email = $value;
			} elseif(!isset($merge_vars['NAME']) && in_array(strtolower($key), array('name', 'your-name', 'username', 'fullname', 'full-name'))) {
				// find name field
				$merge_vars['NAME'] = $value;
			}
		}

		// if email has not been found by the smart field guessing, return false.. sorry
		if(!$email) { 
			return false; 
		}

		// subscribe
		$result = $this->subscribe($email, $merge_vars);
		return true;
	}
	/* End whatever functions */

	public function subscribe_from_bbpress($anonymous_data, $user_id)
	{
		if(!isset($_POST['mc4wp-do-subscribe']) || $_POST['mc4wp-do-subscribe'] != 1) { return; }

		if($anonymous_data) {

			$email = $anonymous_data['bbp_anonymous_email'];
			$merge_vars = array(
				'NAME' => $anonymous_data['bbp_anonymous_name']
			);

		} elseif($user_id) {

			$user_info = get_userdata($user_id);	
			$email = $user_info->user_email;
			$merge_vars = array(
				'NAME' => $user_info->first_name . ' ' . $user_info->last_name,
				'FNAME' => $user_info->first_name,
				'LNAME' => $user_info->last_name
			);

		} else {
			return false;
		}

		return $this->subscribe($email, $merge_vars);
	}

	public function subscribe_from_bbpress_new_topic($topic_id, $forum_id, $anonymous_data, $topic_author)
	{
		return $this->subscribe_from_bbpress($anonymous_data, $topic_author);
	}

	public function subscribe_from_bbpress_new_reply($reply_id, $topic_id, $forum_id, $anonymous_data, $reply_author)
	{
		return $this->subscribe_from_bbpress($anonymous_data, $reply_author);
	}

	public function subscribe($email, array $merge_vars = array())
	{
		$api = MC4WP_Lite::api();
		$opts = $this->get_options();

		$lists = $opts['lists'];
		
		if(empty($lists)) {
			return 'no_lists_selected';
		}

		// guess FNAME and LNAME
		if(isset($merge_vars['NAME']) && !isset($merge_vars['FNAME']) && !isset($merge_vars['LNAME'])) {
			
			$strpos = strpos($merge_vars['NAME'], ' ');

			if($strpos) {
				$merge_vars['FNAME'] = substr($merge_vars['NAME'], 0, $strpos);
				$merge_vars['LNAME'] = substr($merge_vars['NAME'], $strpos);
			} else {
				$merge_vars['FNAME'] = $merge_vars['NAME'];
			}
		}

		$merge_vars = apply_filters('mc4wp_merge_vars', $merge_vars);
		$email_type = apply_filters('mc4wp_email_type', 'html');
		
		foreach($lists as $list) {
			$result = $api->subscribe($list, $email, $merge_vars, $email_type, $opts['double_optin']);

			if($result === true) { 
				$from_url = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : '';
				do_action( 'mc4wp_subscribe_checkbox', $email, $list, $merge_vars );
			}
		}
		
		// check if result succeeded, show debug message to administrators
		if($result !== true && $api->has_error() && current_user_can('manage_options') && !defined("DOING_AJAX")) 
		{
			wp_die("
					<h3>MailChimp for WP - Error</h3>
					<p>The MailChimp server returned the following error message as a response to our sign-up request:</p>
					<pre>" . $api->get_error_message() . "</pre>
					<p>This is the data that was sent to MailChimp: </p>
					<strong>Email</strong>
					<pre>{$email}</pre>
					<strong>Merge variables</strong>
					<pre>" . print_r($merge_vars, true) . "</pre>
					<p style=\"font-style:italic; font-size:12px; \">This message is only visible to administrators for debugging purposes.</p>
					", "Error - MailChimp for WP", array('back_link' => true));
		}

		return $result;
	}



}