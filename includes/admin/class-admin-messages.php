<?php

/**
 * Class MC4WP_Admin_Messages
 *
 * @ignore
 * @since 3.0
 */
class MC4WP_Admin_Messages {

	/**
	 * @var array
	 */
	protected $queue = array();

	/**
	 * Add hooks
	 */
	public function add_hooks() {
		add_action( 'admin_notices', array( $this, 'show' ) );
		add_action( 'shutdown', array( $this, 'save' ) );
	}

	/**
	 * Flash a message (shows on next pageload)
	 *
	 * @param        $message
	 * @param string $type
	 */
	public function flash( $message, $type = 'success' ) {
		$this->queue[] = array(
			'text' => $message,
			'type' => $type
		);
	}

	/**
	 * Show queued flash messages
	 */
	public function show() {
		$messages = get_option( 'mc4wp_flash_messages', array() );

		foreach( $messages as $message ) {
			echo sprintf( '<div class="notice notice-%s is-dismissible"><p>%s</p></div>', $message['type'], $message['text'] );
		}

		update_option( 'mc4wp_flash_messages', array() );
	}

	/**
	 * Save queued messages
	 *
	 * @hooked `shutdown`
	 */
	public function save() {

		if( ! empty( $this->queue ) ) {
			update_option( 'mc4wp_flash_messages', $this->queue );
		}

	}
}