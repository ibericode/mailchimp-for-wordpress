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
        return [
            'procaptcha_site_key',
            'procaptcha_secret_key',
        ];
    }

    /**
     * @return array
     */
    protected function get_default_options()
    {
        return [
            'enabled' => '0',
            'css' => '0',
            'site_key' => '',
            'secret_key' => '',
            'theme' => 'light',
            'type' => 'frictionless',
            'display_for_authorized' => '0',
        ];
    }
}
