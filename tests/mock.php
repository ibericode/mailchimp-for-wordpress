<?php

if (! defined('ABSPATH')) {
    define('ABSPATH', '/');
}

define('MC4WP_PLUGIN_DIR', __DIR__ . '/../');

class WP_Post {}


/** @ignore */
function _deprecated_function($a, $b, $c = null)
{
}

/** @ignore */
function add_filter($hook, $callback, $prio = 10, $arguments = 1)
{
}

/** @ignore */
function add_action($hook, $callback, $prio = 10, $arguments = 1)
{
}

/** @ignore */
function get_option($option, $default = null)
{
    return $default;
}

/** @ignore */
function absint($v) { return $v; }

/** @ignore */
function update_option($a, $b, $c)
{
}

/** @ignore */
function apply_filters($hook, $value, $parameter_1 = null)
{
    return $value;
}

/** @ignore */
function is_user_logged_in()
{
    return false;
}

/** @ignore */
function stripslashes_deep($data)
{
    return $data;
}

/** @ignore */
function sanitize_text_field($value)
{
    return $value;
}

/** @ignore */
function esc_html($value)
{
    return $value;
}


/** @ignore */
function get_post_meta($id, $meta_key = '', $single = true)
{
    return false;
}

/** @ignore */
function get_bloginfo($key)
{
    return '';
}

/** @ignore */
function __($string, $text_domain = '')
{
    return $string;
}

/** @ignore */
function get_post($id)
{
    global $expected_post;

    if (isset($expected_post)) {
        $expected_post->ID = $id;
        return $expected_post;
    }

    return false;
}

/** @ignore */
function mock_post(array $props) : WP_Post
{
    $post = new WP_Post;
    $props = array_merge(
        array(
            'ID' => 1,
            'post_type' => 'mc4wp-form',
            'post_title' => 'Form Title',
            'post_content' => '',
            'post_status' => 'publish',
        ),
        $props
    );
    foreach($props as $key => $value) {
        $post->$key = $value;
    }

    return $post;
}

/** @ignore */
function unmock_post()
{
    global $expected_post;
    unset($expected_post);
}

/** @ignore */
function mock_get_post(array $props)
{
    global $expected_post;
    $expected_post = mock_post($props);
}

/** @ignore */
function wp_verify_nonce($nonce, $action)
{
    return true;
}

/** @ignore */
function is_email($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Straight copy from WP source
 *
 * @ignore
 */
function shortcode_parse_atts($text)
{
    $atts = array();
    $pattern = '/([\w-]+)\s*=\s*"([^"]*)"(?:\s|$)|([\w-]+)\s*=\s*\'([^\']*)\'(?:\s|$)|([\w-]+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
    $text = preg_replace("/[\x{00a0}\x{200b}]+/u", " ", $text);
    if (preg_match_all($pattern, $text, $match, PREG_SET_ORDER)) {
        foreach ($match as $m) {
            if (!empty($m[1])) {
                $atts[strtolower($m[1])] = stripcslashes($m[2]);
            } elseif (!empty($m[3])) {
                $atts[strtolower($m[3])] = stripcslashes($m[4]);
            } elseif (!empty($m[5])) {
                $atts[strtolower($m[5])] = stripcslashes($m[6]);
            } elseif (isset($m[7]) && strlen($m[7])) {
                $atts[] = stripcslashes($m[7]);
            } elseif (isset($m[8])) {
                $atts[] = stripcslashes($m[8]);
            }
        }

        // Reject any unclosed HTML elements
        foreach ($atts as &$value) {
            if (false !== strpos($value, '<')) {
                if (1 !== preg_match('/^[^<]*+(?:<[^>]*+>[^<]*+)*+$/', $value)) {
                    $value = '';
                }
            }
        }
    } else {
        $atts = ltrim($text);
    }
    return $atts;
}
