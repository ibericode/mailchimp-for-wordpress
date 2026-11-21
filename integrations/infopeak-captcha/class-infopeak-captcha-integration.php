<?php

defined('ABSPATH') || exit;

/**
 * Class MC4WP_InfoPeak_Captcha_Integration
 *
 * @ignore
 */
class MC4WP_InfoPeak_Captcha_Integration extends MC4WP_Integration
{
    /**
     * @var string
     */
    public $name = 'InfoPeak Captcha';

    /**
     * @var string
     */
    public $description = 'EU-hosted, cookieless proof-of-work captcha.';

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
            'infopeak_captcha_site_key',
            'infopeak_captcha_secret_key',
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
            'display_for_authorized' => '0',
        ];
    }
}
