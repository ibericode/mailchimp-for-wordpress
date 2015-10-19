<?php

class MC4WP_Usage_Tracking_Nag {

	/**
	 * @var int
	 */
	public $shown = 0;

	/**
	 * @var string
	 */
	protected $required_capability = 'manage_options';

	/**
	 * @const string The name of the option to store whether this nag was shown in
	 */
	const OPTION_SHOWN = 'mc4wp_usage_tracking_nag_shown';

	/**
	 * @const string
	 */
	const OPTION_DELAY = 'mc4wp_usage_tracking_nag_delay_started';

	/**
	 * @const int The time to wait before showing the notice
	 */
	const DELAY_IN_SECONDS = 86400; // 1 day

	/**
	 * @param string $required_capability
	 */
	public function __construct( $required_capability = '' ) {
		$this->shown = get_option( self::OPTION_SHOWN, 0 );

		if( ! empty( $required_capability ) ) {
			$this->required_capability = $required_capability;
		}
	}

	/**
	 * Add hooks
	 */
	public function add_hooks() {

		// Don't add unneeded hooks
		if( $this->shown ) {
			return;
		}

		add_action( 'admin_notices', array( $this, 'show' ) );
		add_action( 'admin_init', array( $this, 'listen' ) );
	}

	/**
	 * @todo this should be translatable
	 */
	public function show() {

		// only show this nag notice did not show before
		if( $this->shown ) {
			return;
		}

		// don't show this option right away but start showing it after DELAY_IN_SECONDS has passed
		if( ! $this->is_delayed() ) {
			$this->delay();
			return;
		}
		?>
		<div class="updated notice">
			<p>
				<strong>Help us improve the MailChimp for WordPress plugin.</strong><br />
				Allow us to anonymously track how this plugin is used to help us make it better fit your needs. No sensitive data is tracked.
			</p>
			<form method="post">
				<p>
					<button type="submit" class="button button-primary" name="allow" value="1">Allow usage tracking</button> &nbsp;
					<button type="submit" class="button" name="allow" value="0">Dismiss</button>
				</p>

				<input type="hidden" name="mc4wp-usage-tracking-nag" value="1" />
			</form>

		</div>
		<?php
	}

	/**
	 * Listen for uses of the form in the nag notice.
	 */
	public function listen() {

		if ( ! isset( $_POST['mc4wp-usage-tracking-nag'] ) ) {
			return;
		}

		if ( ! current_user_can( $this->required_capability ) ) {
			return;
		}

		$allow = ( isset( $_POST['allow'] ) ) ? (bool) $_POST['allow'] : false;

		if ( $allow ) {
			// update plugin options
			$options                         = (array) get_option( 'mc4wp_lite', array() );
			$options['allow_usage_tracking'] = 1;
			update_option( 'mc4wp_lite', $options );

			// toggle tracking
			MC4WP_Usage_Tracking::instance()->toggle( true );
		}

		$this->disable();
	}

	/**
	 * Make sure nag never shows again and clean-up used options
	 */
	public function disable() {
		$this->shown = 1;
		update_option( self::OPTION_SHOWN, 1 );
		delete_option( self::OPTION_DELAY );
	}

	/**
	 * @return bool
	 */
	public function is_delayed() {
		$delay_started = get_option( self::OPTION_DELAY, time() );
		return time() > ( $delay_started + self::DELAY_IN_SECONDS );
	}

	/**
	 * Delay this notice (sets an option with the current time)
	 */
	public function delay() {
		add_option( self::OPTION_DELAY, time() );
	}
}