<?php

/**
 * Class MC4WP_Form_Tags
 *
 * @access private
 * @ignore
 */
class MC4WP_Form_Tags extends MC4WP_Dynamic_Content_Tags
{
    /**
     * @var MC4WP_Form
     */
    protected $form;

    /**
     * @var MC4WP_Form_Element
     */
    protected $form_element;

    public function add_hooks()
    {
        add_filter('mc4wp_form_response_html', array( $this, 'replace_in_form_response' ), 10, 2);
        add_filter('mc4wp_form_content', array( $this, 'replace_in_form_content' ), 10, 3);
        add_filter('mc4wp_form_redirect_url', array( $this, 'replace_in_form_redirect_url' ), 10, 2);
    }

    public function register()
    {
        parent::register();

        $this->tags['response'] = array(
            'description'   => __('Replaced with the form response (error or success messages).', 'mailchimp-for-wp'),
            'callback'      => array( $this, 'get_form_response' )
        );

        $this->tags['data'] = array(
            'description' => sprintf(__("Data from the URL or a submitted form.", 'mailchimp-for-wp')),
            'callback'    => array( $this, 'get_data' ),
            'example'     => "data key='UTM_SOURCE' default='Default Source'"
        );

        $this->tags['cookie'] = array(
            'description' => sprintf(__("Data from a cookie.", 'mailchimp-for-wp')),
            'callback'    => array( $this, 'get_cookie' ),
            'example'     => "cookie name='my_cookie' default='Default Value'"
        );

        $this->tags['subscriber_count'] = array(
            'description' => __('Replaced with the number of subscribers on the selected list(s)', 'mailchimp-for-wp'),
            'callback'    => array( $this, 'get_subscriber_count' )
        );

        $this->tags['email']  = array(
            'description' => __('The email address of the current visitor (if known).', 'mailchimp-for-wp'),
            'callback'    => array( $this, 'get_email' ),
        );

        $this->tags['current_url']  = array(
            'description' => __('The URL of the page.', 'mailchimp-for-wp'),
            'callback'    => 'mc4wp_get_request_url',
        );

        $this->tags['current_path'] = array(
            'description' => __('The path of the page.', 'mailchimp-for-wp'),
            'callback'    => 'mc4wp_get_request_path',
        );

        $this->tags['date']         = array(
            'description' => sprintf(__('The current date. Example: %s.', 'mailchimp-for-wp'), '<strong>' . date('Y/m/d')  . '</strong>'),
            'replacement' => date('Y/m/d')
        );

        $this->tags['time']         = array(
            'description' => sprintf(__('The current time. Example: %s.', 'mailchimp-for-wp'), '<strong>' . date('H:i:s') . '</strong>'),
            'replacement' => date('H:i:s')
        );

        $this->tags['language']     = array(
            'description' => sprintf(__('The site\'s language. Example: %s.', 'mailchimp-for-wp'), '<strong>' . get_locale() . '</strong>'),
            'callback'    => 'get_locale',
        );

        $this->tags['ip']           = array(
            'description' => sprintf(__('The visitor\'s IP address. Example: %s.', 'mailchimp-for-wp'), '<strong>' . mc4wp_get_request_ip_address() . '</strong>'),
            'callback'    => 'mc4wp_get_request_ip_address',
        );

        $this->tags['user']      = array(
            'description' => sprintf(__("The property of the currently logged-in user.", 'mailchimp-for-wp')),
            'callback'    => array( $this, 'get_user_property' ),
            'example'     => "user property='user_email'"
        );

        $this->tags['post'] = array(
            'description' => sprintf(__("Property of the current page or post.", 'mailchimp-for-wp')),
            'callback'    => array( $this, 'get_post_property' ),
            'example'     => "post property='ID'"
        );
    }


    public function replace_in_form_content($string, MC4WP_Form $form, MC4WP_Form_Element $element = null)
    {
        $this->form = $form;
        $this->form_element = $element;

        $string = $this->replace($string);
        return $string;
    }

    public function replace_in_form_response($string, MC4WP_Form $form)
    {
        $this->form = $form;

        $string = $this->replace($string);
        return $string;
    }

    public function replace_in_form_redirect_url($string, MC4WP_Form $form)
    {
        $this->form = $form;
        $string = $this->replace_in_url($string);
        return $string;
    }

    /**
     * Returns the number of subscribers on the selected lists (for the form context)
     *
     * @return int
     */
    public function get_subscriber_count()
    {
        $mailchimp = new MC4WP_MailChimp();
        $count = $mailchimp->get_subscriber_count($this->form->get_lists());
        return number_format($count);
    }

    /**
     * Returns the form response
     *
     * @return string
     */
    public function get_form_response()
    {
        if ($this->form_element instanceof MC4WP_Form_Element) {
            return $this->form_element->get_response_html();
        }

        return '';
    }

    /**
     * Gets data value from GET or POST variables.
     *
     * @param $args
     *
     * @return string
     */
    public function get_data($args = array())
    {
        if (empty($args['key'])) {
            return '';
        }

        $default = isset($args['default']) ? $args['default'] : '';
        $key = $args['key'];

        $data = array_merge($_GET, $_POST);
        $value = isset($data[$key]) ? $data[$key] : $default;

        // turn array into readable value
        if (is_array($value)) {
            $value = array_filter($value);
            $value = join(', ', $value);
        }

        return esc_html($value);
    }

    /**
     * Gets data variable from cookie.
     *
     * @param array $args
     *
     * @return string
     */
    public function get_cookie($args = array())
    {
        if (empty($args['name'])) {
            return '';
        }

        $name = $args['name'];
        $default = isset($args['default']) ? $args['default'] : '';

        if (isset($_COOKIE[ $name ])) {
            return esc_html(stripslashes($_COOKIE[ $name ]));
        }

        return $default;
    }

    /*
     * Get property of currently logged-in user
     *
     * @param array $args
     *
     * @return string
     */
    public function get_user_property($args = array())
    {
        $property = empty($args['property']) ? 'user_email' : $args['property'];
        $default = isset($args['default']) ? $args['default'] : '';
        $user = wp_get_current_user();

        if ($user instanceof WP_User && isset($user->{$property})) {
            return esc_html($user->{$property});
        }

        return $default;
    }

    /*
     * Get property of viewed post
     *
     * @param array $args
     *
     * @return string
     */
    public function get_post_property($args = array())
    {
        global $post;
        $property = empty($args['property']) ? 'ID' : $args['property'];
        $default = isset($args['default']) ? $args['default'] : '';


        if ($post instanceof WP_Post && isset($post->{$property})) {
            return $post->{$property};
        }

        return $default;
    }

    /**
     * @return string
     */
    public function get_email()
    {
        if (!empty($_REQUEST['EMAIL'])) {
            return $_REQUEST['EMAIL'];
        }

        // then , try logged-in user
        if (is_user_logged_in()) {
            $user = wp_get_current_user();
            return $user->user_email;
        }

        // TODO: Read from cookie? Or add $_COOKIE support to {data} tag?
        return '';
    }
}
