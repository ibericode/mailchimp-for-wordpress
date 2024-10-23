<?php

defined('ABSPATH') or exit;

/**
 * Class MC4WP_Ninja_Forms_Integration
 *
 * @ignore
 */
class MC4WP_Procaptcha_Integration extends MC4WP_Integration
{
	/**
	 * @var string
	 */
	public $name = 'Procaptcha (by Prosopo)';

	/**
	 * @var string
	 */
	public $description = 'Privacy-friendly and GDPR-compliant anti-bot protection.';

	/**
	 * @return void
	 */
	protected function add_hooks()
	{
		add_action('mc4wp_form_content', array($this, 'inject_captcha_element'));
	}

	/**
	 * @return bool
	 */
	protected function is_enabled()
	{
		$enabled_setting = $this->options['enabled'] ?? '';

		return '1' === $enabled_setting;
	}

	/**
	 * @param string $html
	 * @return string
	 */
	public function inject_captcha_element($html)
	{
		$stub = '<input type="hidden" name="procaptcha">';

		if (false === strpos($html, $stub)) {
			return $html;
		}

		$captcha_element = '';

		// fixme
		if (true === $this->is_enabled()) {
			$captcha_element = 'test';
		}

		return str_replace($stub, $captcha_element, $html);
	}

	/**
	 * @return bool
	 */
	public function is_installed()
	{
		return true;
	}

	/**
	 * @return array
	 */
	public function get_ui_elements()
	{
		return array(
			'procaptcha_site_key',
			'procaptcha_secret_key',
		);
	}

	/**
	 * @return array
	 */
	protected function get_default_options()
	{
		return array(
			'enabled' => 0,
			'css' => 0,
			'site_key' => '',
			'secret_key' => '',
			'theme' => 'light',
			'type' => 'frictionless',
		);
	}
}
