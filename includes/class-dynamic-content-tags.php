<?php

/**
 * Class MC4WP_Dynamic_Content_Tags
 *
 * @access private
 * @ignore
 */
abstract class MC4WP_Dynamic_Content_Tags
{
    /**
     * @var string The escape function for replacement values.
     */
    protected $escape_function = 'esc_html';

    /**
     * @var array Array of registered dynamic content tags
     */
    protected $tags = [];

    /**
     * Register template tags
     */
    protected function register()
    {
        // Global tags can go here
        $this->tags['cookie'] = [
        'description' => __('Data from a cookie.', 'mailchimp-for-wp'),
        'callback'    => [ $this, 'get_cookie' ],
        'example'     => "cookie name='my_cookie' default='Default Value'",
        ];

        $this->tags['email'] = [
        'description' => __('The email address of the current visitor (if known).', 'mailchimp-for-wp'),
        'callback'    => [ $this, 'get_email' ],
        ];

        $this->tags['current_url'] = [
        'description' => __('The URL of the page.', 'mailchimp-for-wp'),
        'callback'    => 'mc4wp_get_request_url',
        ];

        $this->tags['current_path'] = [
        'description' => __('The path of the page.', 'mailchimp-for-wp'),
        'callback'    => 'mc4wp_get_request_path',
        ];

        $this->tags['date'] = [
        'description' => sprintf(__('The current date. Example: %s.', 'mailchimp-for-wp'), '<strong>' . gmdate('Y/m/d', time() + ( get_option('gmt_offset') * HOUR_IN_SECONDS )) . '</strong>'),
        'replacement' => gmdate('Y/m/d', time() + ( get_option('gmt_offset') * HOUR_IN_SECONDS )),
        ];

        $this->tags['time'] = [
        'description' => sprintf(__('The current time. Example: %s.', 'mailchimp-for-wp'), '<strong>' . gmdate('H:i:s', time() + ( get_option('gmt_offset') * HOUR_IN_SECONDS )) . '</strong>'),
        'replacement' => gmdate('H:i:s', time() + ( get_option('gmt_offset') * HOUR_IN_SECONDS )),
        ];

        $this->tags['language'] = [
        'description' => sprintf(__('The site\'s language. Example: %s.', 'mailchimp-for-wp'), '<strong>' . get_locale() . '</strong>'),
        'callback'    => 'get_locale',
        ];

        $this->tags['ip'] = [
        'description' => sprintf(__('The visitor\'s IP address. Example: %s.', 'mailchimp-for-wp'), '<strong>' . mc4wp_get_request_ip_address() . '</strong>'),
        'callback'    => 'mc4wp_get_request_ip_address',
        ];

        $this->tags['user'] = [
        'description' => __('The property of the currently logged-in user.', 'mailchimp-for-wp'),
        'callback'    => [ $this, 'get_user_property' ],
        'example'     => "user property='user_email'",
        ];

        $this->tags['post'] = [
        'description' => __('Property of the current page or post.', 'mailchimp-for-wp'),
        'callback'    => [ $this, 'get_post_property' ],
        'example'     => "post property='ID'",
        ];
    }

    /**
     * @return array
     */
    public function all()
    {
        if (count($this->tags) === 0) {
            $this->register();
        }

        return $this->tags;
    }

    /**
     * @param array $matches
     *
     * @return string
     */
    protected function replace_tag(array $matches)
    {
        $tags = $this->all();
        $tag  = $matches[1];

        if (isset($tags[ $tag ])) {
            $config      = $tags[ $tag ];
            $replacement = '';

            if (isset($config['replacement'])) {
                $replacement = $config['replacement'];
            } elseif (isset($config['callback'])) {
                // parse attributes
                $attributes = [];
                if (isset($matches[2])) {
                    $attribute_string = $matches[2];
                    $attributes       = shortcode_parse_atts($attribute_string);
                }

                // call function
                $replacement = call_user_func($config['callback'], $attributes);
            }

            // escape replacement value, unless it's configured as providing raw HTML (like {response})
            if (!isset($config['raw_html']) || !$config['raw_html']) {
                $replacement = call_user_func($this->escape_function, $replacement);
            }

            return $replacement;
        }

        // default to not replacing it
        return $matches[0];
    }

    /**
     * @param string $string The string containing dynamic content tags.
     * @param string $escape_function Escape mode for the replacement value.
     * @return string
     */
    private function replace($string, $escape_function = 'esc_html')
    {
        // first, replace inside attributes
        $this->escape_function = 'esc_attr';
        $string = preg_replace_callback('/\=[\'"]?[^\'"]*\{(\w+)(\ +(?:(?!\{)[^}\n])+)*\
    }/', [ $this, 'replace_tag' ], $string);

        $this->escape_function = $escape_function;

        // replace strings like this: {tagname attr="value"}
        $string = preg_replace_callback('/\{(\w+)(\ +(?:(?!\{)[^}\n])+)*\
}/', [ $this, 'replace_tag' ], $string);

        // call again to take care of nested variables
        $string = preg_replace_callback('/\{(\w+)(\ +(?:(?!\{)[^}\n])+)*\}/', [ $this, 'replace_tag' ], $string);
        return $string;
    }

    /**
     * @param string $string
     *
     * @return string
     */
    protected function replace_in_html($string)
    {
        return $this->replace($string, 'esc_html');
    }

    /**
     * @param string $string
     *
     * @return string
     */
    protected function replace_in_attributes($string)
    {
        return $this->replace($string, 'esc_attr');
    }

    /**
     * @param string $string
     *
     * @return string
     */
    protected function replace_in_url($string)
    {
        return $this->replace($string, 'urlencode');
    }

    /**
     * Gets data variable from cookie.
     *
     * @param array $args
     *
     * @return string
     */
    protected function get_cookie($args = [])
    {
        if (empty($args['name'])) {
            return '';
        }

        $name    = $args['name'];
        $default = isset($args['default']) ? $args['default'] : '';

        if (isset($_COOKIE[ $name ])) {
            return $_COOKIE[ $name ];
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
    protected function get_user_property($args = [])
    {
        $property = empty($args['property']) ? 'user_email' : $args['property'];
        $default  = isset($args['default']) ? $args['default'] : '';
        $user     = wp_get_current_user();

        if ($user instanceof WP_User && isset($user->{$property})) {
            return $user->{$property};
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
    protected function get_post_property($args = [])
    {
        global $post;
        $property = empty($args['property']) ? 'ID' : $args['property'];
        $default  = isset($args['default']) ? $args['default'] : '';

        if ($post instanceof WP_Post && isset($post->{$property})) {
            return $post->{$property};
        }

        return $default;
    }

    /**
     * @return string
     */
    protected function get_email()
    {
        if (! empty($_REQUEST['EMAIL'])) {
            return sanitize_email($_REQUEST['EMAIL']);
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
