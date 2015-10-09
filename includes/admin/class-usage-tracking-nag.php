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
	 * The name of the option to store whether this nag was shown in
	 */
	const OPTION_NAME = 'mc4wp_usage_tracking_nag_shown';

	/**
	 * @param string $required_capability
	 */
	public function __construct( $required_capability = '' ) {
		$this->shown = get_option( self::OPTION_NAME, 0 );

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
	 *
	 */
	public function show() {

		// only show this nag if tracking is not already enabled or notice was shown before
		if( $this->shown ) {
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

		// make sure notice never appears again
		update_option( self::OPTION_NAME, 1 );
		$this->shown = 1;
	}
}