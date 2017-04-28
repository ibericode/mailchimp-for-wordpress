<?php if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class MC4WP_Ninja_Forms_Field
 */
class MC4WP_Ninja_Forms_Field extends NF_Fields_Checkbox
{
    protected $_name = 'mc4wp_optin';

    protected $_nicename = 'MailChimp';

    protected $_section = 'misc';

    public function __construct() {
        parent::__construct();

        $this->_nicename = __( 'MailChimp opt-in', 'mailchimp-for-wp' );
    }
}
