<?php

/**
 * Class MC4WP_Dynamic_Content_Tags
 *
 * @access private
 * @ignore
 */
abstract class MC4WP_Dynamic_Content_Tags
{
    private const TAG_REGEX = '/\{(\w+)(\ +(?:(?!\{)[^}\n])+)*\}/';

    private const WHITESPACE_REFERENCES = [' ' => '&#32;', "\t" => '&#9;', "\n" => '&#10;', "\r" => '&#13;', "\f" => '&#12;'];

    // Square brackets are encoded in every replacement value, so that a value can not form a shortcode when the output is
    // passed through do_shortcode, like for the form block.
    private const SHORTCODE_REFERENCES = ['[' => '&#91;', ']' => '&#93;'];

    /**
     * @var callable The escape function for replacement values.
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
            'callback'    => [$this, 'get_cookie'],
            'example'     => "cookie name='my_cookie' default='Default Value'",
        ];

        $this->tags['email'] = [
            'description' => __('The email address of the current visitor (if known).', 'mailchimp-for-wp'),
            'callback'    => [$this, 'get_email'],
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
            // translators: %s is an example of the current date, e.g. 2024/01/15.
            'description' => sprintf(__('The current date. Example: %s.', 'mailchimp-for-wp'), '<strong>' . gmdate('Y/m/d', time() + (get_option('gmt_offset') * HOUR_IN_SECONDS)) . '</strong>'),
            'replacement' => gmdate('Y/m/d', time() + (get_option('gmt_offset') * HOUR_IN_SECONDS)),
        ];

        $this->tags['time'] = [
            // translators: %s is an example of the current time, e.g. 14:30:00.
            'description' => sprintf(__('The current time. Example: %s.', 'mailchimp-for-wp'), '<strong>' . gmdate('H:i:s', time() + (get_option('gmt_offset') * HOUR_IN_SECONDS)) . '</strong>'),
            'replacement' => gmdate('H:i:s', time() + (get_option('gmt_offset') * HOUR_IN_SECONDS)),
        ];

        $this->tags['language'] = [
            // translators: %s is an example of the site's locale, e.g. en_US.
            'description' => sprintf(__('The site\'s language. Example: %s.', 'mailchimp-for-wp'), '<strong>' . get_locale() . '</strong>'),
            'callback'    => 'get_locale',
        ];

        $this->tags['ip'] = [
            // translators: %s is an example of the visitor's IP address.
            'description' => sprintf(__('The visitor\'s IP address. Example: %s.', 'mailchimp-for-wp'), '<strong>' . mc4wp_get_request_ip_address() . '</strong>'),
            'callback'    => 'mc4wp_get_request_ip_address',
        ];

        $this->tags['user'] = [
            'description' => __('The property of the currently logged-in user.', 'mailchimp-for-wp'),
            'callback'    => [$this, 'get_user_property'],
            'example'     => "user property='user_email'",
        ];

        $this->tags['post'] = [
            'description' => __('Property of the current page or post.', 'mailchimp-for-wp'),
            'callback'    => [$this, 'get_post_property'],
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

        if (isset($tags[$tag])) {
            $replacement = $this->get_tag_value($matches);

            // escape replacement value, unless it's configured as providing raw HTML (like {response})
            if (!isset($tags[$tag]['raw_html']) || !$tags[$tag]['raw_html']) {
                $replacement = strtr(call_user_func($this->escape_function, $replacement), self::SHORTCODE_REFERENCES);
            }

            return $replacement;
        }

        // default to not replacing it
        return $matches[0];
    }

    /**
     * Returns the unescaped value of a registered dynamic content tag.
     *
     * @param array $matches
     *
     * @return mixed
     */
    private function get_tag_value(array $matches)
    {
        $config = $this->all()[$matches[1]];

        if (isset($config['replacement'])) {
            return $config['replacement'];
        }

        if (isset($config['callback'])) {
            // parse attributes
            $attributes = [];
            if (isset($matches[2])) {
                $attribute_string = $matches[2];
                $attributes       = shortcode_parse_atts($attribute_string);
            }

            // call function
            return call_user_func($config['callback'], $attributes);
        }

        return '';
    }

    /**
     * @param string $string The string containing dynamic content tags.
     * @param callable $escape_function Escape mode for the replacement value.
     * @return string
     */
    private function replace($string, $escape_function = 'esc_html')
    {
        // cheap check for tags, before going into recursive regex
        if (strpos($string, '{') === false) {
            return $string;
        }

        $this->escape_function = $escape_function;
        return preg_replace_callback(self::TAG_REGEX, [$this, 'replace_tag'], $string);
    }

    /**
     * Replaces dynamic content tags in HTML, escaping each value for where the tag is in the HTML.
     *
     * @param string $string
     *
     * @return string
     */
    protected function replace_in_html($string)
    {
        // cheap check for tags, before going into recursive regex
        if (strpos($string, '{') === false) {
            return $string;
        }

        // Without the HTML API (WordPress < 6.2), escape values so that they are safe in text as well as in attributes.
        if (! class_exists('WP_HTML_Tag_Processor')) {
            return $this->replace($string, [$this, 'escape_context_free']);
        }

        // Replace each dynamic content tag with a unique marker, so that the HTML API can tell where each tag is.
        // Markers only consist of letters and digits, so they do not change how the HTML is parsed.
        $tags    = $this->all();
        $marker  = 'mc4wp' . bin2hex(random_bytes(8)) . 'x';
        $matches = [];
        $html    = preg_replace_callback(
            self::TAG_REGEX,
            function (array $match) use ($tags, $marker, &$matches) {
                if (! isset($tags[$match[1]])) {
                    return $match[0];
                }

                $matches[] = $match;
                return $marker . (count($matches) - 1) . 'x';
            },
            $string
        );

        if (count($matches) === 0) {
            return $string;
        }

        $marker_regex = '/' . $marker . '(\d+)x/';
        $values       = array_map([$this, 'get_tag_value'], $matches);

        try {
            $escape_functions = $this->get_attribute_escape_functions($html, $marker_regex, $values);
        } catch (Throwable $e) {
            // The HTML API of WordPress 6.2 can throw an error on malformed HTML.
            return $this->replace($string, [$this, 'escape_context_free']);
        }

        return preg_replace_callback(
            $marker_regex,
            function (array $marker) use ($tags, $matches, $values, $escape_functions) {
                $i = (int) $marker[1];
                if (isset($escape_functions[$i])) {
                    return call_user_func($escape_functions[$i], $values[$i]);
                }

                // In text, or in a comment, script, style or textarea.
                // Escape the value, unless it's configured as providing raw HTML (like {response}).
                return empty($tags[$matches[$i][1]]['raw_html']) ? strtr(esc_html($values[$i]), self::SHORTCODE_REFERENCES) : $values[$i];
            },
            $html
        );
    }

    /**
     * Uses the HTML API to find the markers of dynamic content tags inside HTML tags, and how to escape their values.
     *
     * @param string $html HTML with markers
     * @param string $marker_regex
     * @param array $values Unescaped values, by marker index
     *
     * @return callable[] Escape functions, by marker index
     */
    private function get_attribute_escape_functions(string $html, string $marker_regex, array $values): array
    {
        $escape_functions = [];

        // Close any tag that is still open at the end of the HTML, as the HTML API does not report it.
        $processor = new WP_HTML_Tag_Processor($html . '\'">');
        while ($processor->next_tag()) {
            foreach ($processor->get_attribute_names_with_prefix('') ?? [] as $name) {
                // A value in an attribute name could add attributes.
                if (preg_match_all($marker_regex, $name, $markers)) {
                    $escape_functions += array_fill_keys($markers[1], '__return_empty_string');
                }

                $value = $processor->get_attribute($name);
                if (is_string($value) && preg_match_all($marker_regex, $value, $markers)) {
                    $escape_functions += array_fill_keys($markers[1], $this->get_attribute_escape_function($name, $value, $marker_regex, $values));
                }
            }
        }

        return $escape_functions;
    }

    /**
     * @param string $name Attribute name
     * @param string $value Attribute value, with markers
     * @param string $marker_regex
     * @param array $values Unescaped values, by marker index
     *
     * @return callable
     */
    private function get_attribute_escape_function(string $name, string $value, string $marker_regex, array $values)
    {
        // Event handler attributes and srcdoc hold code, so no escaping makes a value safe there.
        if (strpos($name, 'on') === 0 || $name === 'srcdoc') {
            return '__return_empty_string';
        }

        // A URL attribute must not get a protocol like "javascript:". As static text around a value can complete a protocol,
        // check the URL with all values in it. If its protocol is not allowed, all values in the attribute are removed.
        if (in_array($name, wp_kses_uri_attributes(), true)) {
            $url = preg_replace_callback(
                $marker_regex,
                function (array $marker) use ($values) {
                    return (string) $values[(int) $marker[1]];
                },
                $value
            );

            // Before WordPress 6.6, the HTML API does not decode all character references (like "&colon;") in the static
            // text, which could hide a protocol. So an ampersand before the path of the URL is not allowed either.
            $protocol = $this->get_protocol($url);
            if (($protocol !== null && ! in_array($protocol, wp_allowed_protocols(), true)) || strpos(substr($url, 0, strcspn($url, '/?#')), '&') !== false) {
                return '__return_empty_string';
            }
        }

        return [$this, 'escape_attribute'];
    }

    /**
     * Returns the protocol of a URL the way browsers parse it: ignoring leading whitespace and control characters, and
     * tabs and newlines anywhere.
     *
     * @param string $url
     *
     * @return string|null Lowercase protocol, or null for a URL without protocol
     */
    private function get_protocol(string $url): ?string
    {
        $url = str_replace(["\t", "\n", "\r"], '', ltrim($url, "\x00..\x20"));
        return preg_match('/^([a-z][a-z0-9+.-]*):/i', $url, $protocol) ? strtolower($protocol[1]) : null;
    }

    /**
     * Escapes a value for an attribute value. Encodes every ampersand, so that the browser sees the same value that was
     * checked, whitespace, which would otherwise end an unquoted attribute value, and square brackets.
     *
     * @param mixed $value
     *
     * @return string
     */
    private function escape_attribute($value)
    {
        return strtr(htmlspecialchars((string) $value, ENT_QUOTES), self::WHITESPACE_REFERENCES + self::SHORTCODE_REFERENCES);
    }

    /**
     * Escapes a value for when its HTML context is unknown: it is safe in text and in quoted or unquoted attribute values.
     * A value with the "javascript:" protocol is removed, as it could be at the start of a URL attribute.
     *
     * @param mixed $value
     *
     * @return string
     */
    private function escape_context_free($value)
    {
        if ($this->get_protocol((string) $value) === 'javascript') {
            return '';
        }

        return $this->escape_attribute($value);
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

        if (isset($_COOKIE[$name])) {
            return wp_unslash($_COOKIE[$name]);
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
        // first, try to get from request data
        $keys = [ 'EMAIL', 'email', 'email_address', 'email-address' ];
        foreach ($keys as $k) {
            if (! empty($_REQUEST[$k])) {
                return sanitize_email(wp_unslash($_REQUEST[$k]));
            }
        }

        // then, try logged-in user
        if (is_user_logged_in()) {
            $user = wp_get_current_user();
            return $user->user_email;
        }


        // TODO: Read from cookie? Or add $_COOKIE support to {data} tag?
        return '';
    }
}
