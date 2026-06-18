<?php

defined('ABSPATH') || exit;


/**
 * Get a service by its name
 *
 * _Example:_
 *
 * $forms = mc4wp_get_service('forms');
 * $api = mc4wp_get_service('api');
 *
 * When no service parameter is given, the entire container will be returned.
 *
 * @ignore
 * @access private
 *
 * @param null|string $service (optional)
 * @return mixed
 * @throws Exception when service is not found
 * @deprecated Use mc4wp_get_container() or mc4wp_get_service() instead.
 */
function mc4wp($service = null)
{
    $container = mc4wp_get_container();
    if (null !== $service) {
        return $container->get($service);
    }
    return $container;
}

/**
 * @since 4.13
 * @return MC4WP_Container
 */
function mc4wp_get_container(): MC4WP_Container
{
    static $container;
    if (null === $container) {
        $container = new MC4WP_Container();
    }
    return $container;
}

/**
 * @since 4.13
 * @param string $service
 * @return mixed
 * @throws Exception when service is not found
 */
function mc4wp_get_service(string $service)
{
    return mc4wp_get_container()->get($service);
}

/**
 * Gets the Mailchimp for WP options from the database
 * Uses default values to prevent undefined index notices.
 *
 * @since 1.0
 * @access public
 * @static array $options
 * @return array
 */
function mc4wp_get_options()
{
    $defaults = require MC4WP_PLUGIN_DIR . '/config/default-settings.php';
    $options  = (array) get_option('mc4wp', []);
    $options  = array_merge($defaults, $options);

    /**
     * Filters the Mailchimp for WordPress settings (general).
     *
     * @param array $options
     */
    return apply_filters('mc4wp_settings', $options);
}

/**
 * @return array
 */
function mc4wp_get_settings()
{
    return mc4wp_get_options();
}

/**
 * @since 4.2.6
 * @return string
 */
function mc4wp_get_api_key()
{
    // try to get from constant
    if (defined('MC4WP_API_KEY') && constant('MC4WP_API_KEY') !== '') {
        return MC4WP_API_KEY;
    }

    // get from options
    $opts = mc4wp_get_options();
    return $opts['api_key'];
}

/**
 * Gets the Mailchimp for WP API class (v3) and injects it with the API key
 *
 * @since 4.0
 * @access public
 *
 * @return MC4WP_API_V3
 */
function mc4wp_get_api_v3()
{
    $api_key = mc4wp_get_api_key();
    return new MC4WP_API_V3($api_key);
}

/**
 * Creates a new instance of the Debug Log
 *
 * @return MC4WP_Debug_Log
 */
function mc4wp_get_debug_log()
{
    $opts = mc4wp_get_options();

    // get default log file location
    $upload_dir   = wp_upload_dir(null, false);
    $file         = $upload_dir['basedir'] . '/mailchimp-for-wp/debug-log.php';
    $default_file = $file;

    /**
     * Filters the log file to write to.
     *
     * @param string $file The log file location. Default: /wp-content/uploads/mailchimp-for-wp/mc4wp-debug.log
     */
    $file = apply_filters('mc4wp_debug_log_file', $file);

    if ($file === $default_file) {
        $dir = dirname($file);
        if (! is_dir($dir)) {
            wp_mkdir_p($dir);
        }

        if (! is_file($dir . '/.htaccess')) {
            $lines = [
                '<IfModule !authz_core_module>',
                'Order deny,allow',
                'Deny from all',
                '</IfModule>',
                '<IfModule authz_core_module>',
                'Require all denied',
                '</IfModule>',
            ];
            file_put_contents($dir . '/.htaccess', join(PHP_EOL, $lines));
        }

        if (! is_file($dir . '/index.html')) {
            file_put_contents($dir . '/index.html', '');
        }
    }

    /**
     * Filters the minimum level to log messages.
     *
     * @see MC4WP_Debug_Log
     *
     * @param string|int $level The minimum level of messages which should be logged.
     */
    $level = apply_filters('mc4wp_debug_log_level', $opts['debug_log_level']);

    return new MC4WP_Debug_Log($file, $level);
}

/**
 * Get URL to a file inside the plugin directory
 *
 * @since 4.8.3
 */
function mc4wp_plugin_url(string $path): string
{
    return plugins_url($path, MC4WP_PLUGIN_FILE);
}


/**
 * Get absolute URL for current request
 */
function mc4wp_get_request_url(): string
{
    global $wp;

    // get requested url from global $wp object
    $site_request_uri = $wp->request;

    // fix for IIS servers using index.php in the URL
    $request_path = wp_unslash($_SERVER['REQUEST_URI'] ?? '');
    if (false !== strpos($request_path, '/index.php/' . $site_request_uri)) {
        $site_request_uri = 'index.php/' . $site_request_uri;
    }

    // concatenate request url to home url
    $url = home_url($site_request_uri);
    return trailingslashit($url);
}

/**
 * Get relative URL path for current request
 */
function mc4wp_get_request_path(): string
{
    return (string) wp_unslash($_SERVER['REQUEST_URI'] ?? '');
}

/**
* Get IP address for client making current request
*
* @return string|null
*/
function mc4wp_get_request_ip_address()
{
    if (isset($_SERVER['X-Forwarded-For'])) {
        $ip_address = wp_unslash($_SERVER['X-Forwarded-For']);
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip_address = wp_unslash($_SERVER['HTTP_X_FORWARDED_FOR']);
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip_address = wp_unslash($_SERVER['REMOTE_ADDR']);
    }

    if (isset($ip_address)) {
        if (! is_array($ip_address)) {
            $ip_address = explode(',', $ip_address);
        }

        // use first IP in list
        $ip_address = trim($ip_address[0]);

        // if IP address is not valid, simply return null
        if (! filter_var($ip_address, FILTER_VALIDATE_IP)) {
            return null;
        }

        return $ip_address;
    }

    return null;
}

/**
 * Performs opinionated sanitization of all string values inside the passed value.
 * - Strips all tags
 * - Strips slashes
 * - Trims whitespace
 * - Decodes HTML entities
 * - Limits string values to 1024 bytes
 *
 * @access public
 * @param mixed $value
 *
 * @return mixed
 */
function mc4wp_sanitize_deep($value)
{
    return map_deep($value, static function ($value) {
        if (is_string($value)) {
            // strip tags
            $value = wp_strip_all_tags($value);

            // strip slashes
            $value = stripslashes($value);

            // trim whitespace
            $value = trim($value);

            // convert &amp; back to &
            $value = html_entity_decode($value, ENT_NOQUOTES);

            // limit value to 1024 characters
            // see https://mailchimp.com/help/manage-audience-signup-form-fields/#Limits_for_audience_fields
            $value = substr($value, 0, 1024);
        }

        return $value;
    });
}

/**
 * Returns true if (and only if) the value is a valid RFC 822 email address
 *
 * @param mixed $value
 */
function mc4wp_is_email($value): bool
{
    if (! is_string($value) || $value === '') {
        return false;
    }

    if (strlen($value) > 320) {
        return false;
    }

    return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 *
 * @since 4.0
 * @ignore
 *
 * @param array $data
 * @return array
 */
function _mc4wp_update_groupings_data($data = [])
{

    // data still has old "GROUPINGS" key?
    if (empty($data['GROUPINGS'])) {
        return $data;
    }

    // prepare new key
    if (! isset($data['INTERESTS'])) {
        $data['INTERESTS'] = [];
    }

    $map = get_option('mc4wp_groupings_map', []);

    foreach ($data['GROUPINGS'] as $grouping_id => $groups) {
        // for compatibility with expanded grouping arrays
        $grouping_key = $grouping_id;
        if (is_array($groups) && isset($groups['id']) && isset($groups['groups'])) {
            $grouping_id = $groups['id'];
            $groups      = $groups['groups'];
        }

        // do we have transfer data for this grouping id?
        if (! isset($map[ $grouping_id ])) {
            continue;
        }

        // if we get a string, explode on delimiter(s)
        if (is_string($groups)) {
            // for BC with 3.x: explode on comma's
            $groups = join('|', explode(',', $groups));

            // explode on current delimiter
            $groups = explode('|', $groups);
        }

        // loop through groups and find interest ID
        $migrated = 0;
        foreach ($groups as $key => $group_name_or_id) {
            // do we know the new interest ID?
            if (empty($map[ $grouping_id ]['groups'][ $group_name_or_id ])) {
                continue;
            }

            $interest_id = $map[ $grouping_id ]['groups'][ $group_name_or_id ];

            // add to interests data
            if (! in_array($interest_id, $data['INTERESTS'], false)) {
                ++$migrated;
                $data['INTERESTS'][] = $interest_id;
            }
        }

        // remove old grouping ID if we migrated all groups.
        if ($migrated === count($groups)) {
            unset($data['GROUPINGS'][ $grouping_key ]);
        }
    }

    // if everything went well, this is now empty & moved to new INTERESTS key.
    if (empty($data['GROUPINGS'])) {
        unset($data['GROUPINGS']);
    }

    // is this empty? just unset it then.
    if (empty($data['INTERESTS'])) {
        unset($data['INTERESTS']);
    }

    return $data;
}

/**
 * Guesses merge vars based on given data & current request.
 *
 * @since 3.0
 * @access public
 *
 * @param array $data
 *
 * @return array
 */
function mc4wp_add_name_data($data)
{

    // Guess first and last name
    if (! empty($data['NAME']) && empty($data['FNAME']) && empty($data['LNAME'])) {
        $data['NAME'] = trim($data['NAME']);
        $strpos       = strpos($data['NAME'], ' ');

        if ($strpos !== false) {
            $data['FNAME'] = trim(substr($data['NAME'], 0, $strpos));
            $data['LNAME'] = trim(substr($data['NAME'], $strpos));
        } else {
            $data['FNAME'] = $data['NAME'];
        }
    }

    // Set name value
    if (empty($data['NAME']) && ! empty($data['FNAME']) && ! empty($data['LNAME'])) {
        $data['NAME'] = sprintf('%s %s', $data['FNAME'], $data['LNAME']);
    }

    return $data;
}

/**
 * Gets the "email type" for new subscribers.
 *
 * Possible return values are either "html" or "text"
 *
 * @access public
 * @since 3.0
 *
 * @return string
 */
function mc4wp_get_email_type()
{
    $email_type = 'html';

    /**
     * Filters the email type preference for this new subscriber.
     *
     * @param string $email_type
     */
    $email_type = (string) apply_filters('mc4wp_email_type', $email_type);

    return $email_type;
}

/**
 *
 * @ignore
 * @return bool
 */
function _mc4wp_use_sslverify()
{

    // Disable for all transports other than CURL
    if (! function_exists('curl_version')) {
        return false;
    }

    $curl = curl_version();

    // Disable if OpenSSL is not installed
    if (empty($curl['ssl_version'])) {
        return false;
    }

    // Disable if on WP 4.4, see https://core.trac.wordpress.org/ticket/34935
    if ($GLOBALS['wp_version'] === '4.4') {
        return false;
    }

    return true;
}

/**
 * This will replace the first half of a string with "*" characters.
 *
 * @param string $string
 * @return string
 */
function mc4wp_obfuscate_string($string)
{
    if (false === is_string($string) || $string === '') {
        return $string;
    }

    $length = strlen($string);
    if ($length <= 2) {
        return $string;
    }
    $keep = (int) floor($length / 3);
    $keep = (int) min($keep, 4);
    return substr($string, 0, $keep) . str_repeat('*', $length - ($keep * 2)) . substr($string, -$keep);
}

/**
 * @param array $m
 * @return string
 * @internal
 * @ignore
 */
function _mc4wp_obfuscate_email_addresses_callback($m)
{
    return mc4wp_obfuscate_string($m[1]) . '@' . mc4wp_obfuscate_string($m[2]);
}

/**
 * Obfuscates email addresses in a string.
 *
 * @param string $string String possibly containing email address
 * @return string
 */
function mc4wp_obfuscate_email_addresses($string)
{
    return preg_replace_callback('/([A-Z0-9._%+-]{1,64})@([A-Z0-9.-]{1,253}\.[A-Z]{2,63})/i', '_mc4wp_obfuscate_email_addresses_callback', $string);
}

/**
 * Truncates a debug log message to a reasonable maximum size.
 *
 * @param string $message
 * @return string
 */
function mc4wp_truncate_log_message($message)
{
    /**
     * Filters the maximum length of a debug log message in bytes.
     *
     * Return 0 or a negative value to disable truncation.
     *
     * @param int $max_length Maximum message length in bytes.
     */
    $max_length = (int) apply_filters('mc4wp_debug_log_message_max_length', 8192);

    if ($max_length <= 0) {
        return $message;
    }

    $length = strlen($message);
    if ($length <= $max_length) {
        return $message;
    }

    $suffix = sprintf('... [truncated, original length: %d bytes]', $length);

    if (strlen($suffix) >= $max_length) {
        return substr($message, 0, $max_length);
    }

    return substr($message, 0, $max_length - strlen($suffix)) . $suffix;
}

/**
 * Refreshes Mailchimp lists. This can take a while if the connected Mailchimp account has many lists.
 *
 * @return void
 */
function mc4wp_refresh_mailchimp_lists()
{
    $mailchimp = new MC4WP_MailChimp();
    $mailchimp->refresh_lists();
}

/**
* Get element from array, allows for dot notation eg: "foo.bar"
*
* @param array $array
* @param string|null $key
* @param mixed $default
* @return mixed
*/
function mc4wp_array_get($array, $key, $default = null)
{
    if ($key === null) {
        return $array;
    }

    if (isset($array[ $key ])) {
        return $array[ $key ];
    }

    foreach (explode('.', $key) as $segment) {
        if (! is_array($array) || ! array_key_exists($segment, $array)) {
            return $default;
        }

        $array = $array[ $segment ];
    }

    return $array;
}

/**
 * Filters string and strips out all HTML tags and attributes, except what's in our whitelist.
 *
 * @param string $string The string to apply KSES whitelist on
 * @return string
 * @since 4.8.8
 */
function mc4wp_kses($string)
{
    $always_allowed_attr = array_fill_keys(
        [
            'aria-describedby',
            'aria-details',
            'aria-label',
            'aria-labelledby',
            'aria-hidden',
            'class',
            'id',
            'style',
            'title',
            'role',
            'data-*',
            'tabindex',
        ],
        true
    );
    $input_allowed_attr  = array_merge(
        $always_allowed_attr,
        array_fill_keys(
            [
                'type',
                'required',
                'placeholder',
                'value',
                'name',
                'step',
                'min',
                'max',
                'checked',
                'width',
                'autocomplete',
                'autofocus',
                'minlength',
                'maxlength',
                'size',
                'pattern',
                'disabled',
                'readonly',
            ],
            true
        )
    );

    $allowed = [
        'p'        => $always_allowed_attr,
        'label'    => array_merge($always_allowed_attr, [ 'for' => true ]),
        'input'    => $input_allowed_attr,
        'button'   => $input_allowed_attr,
        'fieldset' => $always_allowed_attr,
        'legend'   => $always_allowed_attr,
        'ul'       => $always_allowed_attr,
        'ol'       => $always_allowed_attr,
        'li'       => $always_allowed_attr,
        'select'   => array_merge($input_allowed_attr, [ 'multiple' => true ]),
        'option'   => array_merge($input_allowed_attr, [ 'selected' => true ]),
        'optgroup' => [
            'disabled' => true,
            'label' => true,
        ],
        'textarea' => array_merge(
            $input_allowed_attr,
            [
                'rows' => true,
                'cols' => true,
            ]
        ),
        'div'      => $always_allowed_attr,
        'strong'   => $always_allowed_attr,
        'b'         => $always_allowed_attr,
        'i'         => $always_allowed_attr,
        'br'        => [],
        'em'       => $always_allowed_attr,
        'span'     => $always_allowed_attr,
        'a'        => array_merge($always_allowed_attr, [ 'href' => true ]),
        'img'      => array_merge(
            $always_allowed_attr,
            [
                'src' => true,
                'alt' => true,
                'width' => true,
                'height' => true,
                'srcset' => true,
                'sizes' => true,
                'referrerpolicy' => true,
            ]
        ),
        'u' => $always_allowed_attr,
    ];

    return wp_kses($string, $allowed);
}

/**
 * Helper function for safely deprecating a changed filter hook.
 *
 * @param string $old_hook
 * @param string $new_hook
 *
 * @return void
 */
function mc4wp_apply_deprecated_filters($old_hook, $new_hook)
{
    add_filter($new_hook, function ($value, $a = null, $b = null, $c = null) use ($new_hook, $old_hook) {
        return apply_filters_deprecated($old_hook, [ $value, $a, $b, $c ], '4.9.0', $new_hook);
    }, 10, 3);
}
