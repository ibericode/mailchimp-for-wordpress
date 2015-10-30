<?php

class MC4WP_Update_Control {

	/**
	 * @var string
	 */
	protected $plugin_file = '';

	/**
	 * @param string $plugin_file
	 */
	public function __construct( $plugin_file ) {
		$this->plugin_file = $plugin_file;
	}

	/**
	 * Add hooks
	 */
	public function add_hooks() {
		// hide major plugin updates for everyone
		add_filter( 'site_transient_update_plugins', array( $this, 'hide_major_plugin_updates' ) );
	}

	/**
	 * Prevents v3.x updates from showing
	 *
	 */
	public function hide_major_plugin_updates( $data ) {

		// fake set new version to 3.0 (for testing)
		$data->response[ $this->plugin_file ] = $data->no_update[ $this->plugin_file ];
		$data->response[ $this->plugin_file ]->new_version = "3.0.0";

		// don't act if there's nothing to act upon
		if( empty( $data->response[ $this->plugin_file ]->new_version ) ) {
			return $data;
		}

		$wordpress_org_data = $data->response[ $this->plugin_file ];

		// is there a major update for this plugin?
		if( ! version_compare( $wordpress_org_data->new_version, '3.0.0', '>=' ) ) {
			return $data;
		}

		// did user opt-in to 3.0? if so, show the update.
		$opted_in = get_option( 'mc4wp_update_to_3x_optin', false );
		if( $opted_in ) {
			return $data;
		}

		// user did NOT opt-in, check for minor version updates
		$minor_update_data = $this->get_latest_minor_update();

		// if something in the custom update check failed, just unset the data.
		if ( ! is_object( $minor_update_data ) ) {
			unset( $data->response[ $this->plugin_file ] );
			return $data;
		}

		// return modified updates data
		$data->response[ $this->plugin_file ] = $this->merge_update_data( $wordpress_org_data, $minor_update_data );
		return $data;
	}

	/**
	 *
	 *
	 * @return array|mixed|object
	 */
	protected function get_latest_minor_update() {

		static $json;

		if( ! $json ) {
			// get latest 2x version
			$response = wp_remote_get( 'https://s3.amazonaws.com/ibericode/mailchimp-for-wp-update-info-2.x.json' );
			$body     = wp_remote_retrieve_body( $response );

			if( empty( $body ) ) {
				return false;
			}

			$json = json_decode( $body );
		}

		return $json;
	}

	/**
	 * @param object $wordpress_org_data
	 * @param object $custom_data
	 *
	 * @return object
	 */
	protected function merge_update_data( $wordpress_org_data, $custom_data ) {
		return (object) array_merge(
			(array) $wordpress_org_data,
			(array) $custom_data
		);
	}
}