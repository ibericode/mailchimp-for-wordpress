<?php

defined('ABSPATH') || exit;


mc4wp_register_integration('infopeak-captcha', 'MC4WP_InfoPeak_Captcha_Integration');

MC4WP_InfoPeak_Captcha::get_instance()->set_hooks();
