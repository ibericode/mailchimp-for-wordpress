<?php

mc4wp_register_integration('prosopo-procaptcha', 'MC4WP_Procaptcha_Integration');

MC4WP_Procaptcha::get_instance()->set_hooks();
