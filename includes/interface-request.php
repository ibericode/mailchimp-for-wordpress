<?php

interface iMC4WP_Request {

	/**
	 * Prepare the given request data so it matches the format expected by the process() method.
	 *
	 * @return bool
	 */
	public function prepare();

	/**
	 * Validate the request
	 *
	 * @return bool
	 */
	public function validate();

	/**
	 * Process the request any way you like
	 *
	 * @return bool
	 */
	public function process();

	/**
	 * Respond to the request any way you see fit
	 *
	 * @return bool
	 */
	public function respond();

	/**
	 * Return the response string for this request (is added to the corresponding form element)
	 *
	 * @return string
	 */
	public function get_response_html();

	/**
	 * @return array
	 */
	public function get_lists();

	/**
	 * @return array
	 */
	public function get_data();
}