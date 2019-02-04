<?php

/**
 * Class MC4WP_Dynamic_Content_Tags
 *
 * @access private
 * @ignore
 */
class MC4WP_Dynamic_Content_Tags
{

    /**
     * @var string
     */
    public $context;

    /**
     * @var string The escape mode for replacement values.
     */
    protected $escape_mode = 'html';

    /**
     * @var array Array of registered dynamic content tags
     */
    protected $tags = array();


    /**
     * @param string $context
     * @param array $tags;
     */
    public function __construct($context, $tags = array())
    {
        $this->context = $context;
        $this->tags = $tags;
    }

    /**
     * Return all registered tags
     *
     * @return array
     */
    public function all()
    {
        $context = $this->context;
        $tags = $this->tags;

        /**
         * Filters the registered dynamic content tags for all contexts.
         *
         * @since 3.0
         * @param array $tags
         * @ignore
         */
        $this->tags = (array) apply_filters('mc4wp_dynamic_content_tags', $tags);

        /**
         * Filters the registered dynamic content tags for a specific context.
         *
         * The dynamic part of the hook, `$context`, refers to the context (forms / integrations)
         *
         * @since 3.0
         * @param array $tags
         * @ignore
         */
        $this->tags = (array) apply_filters('mc4wp_dynamic_content_tags_' . $context, $tags);
        return $this->tags;
    }

    /**
     * @param $matches
     *
     * @return string
     */
    protected function replace_tag($matches)
    {
        $tags = $this->all();
        $tag = $matches[1];

        if (isset($tags[ $tag ])) {
            $config = $tags[ $tag ];
            $replacement = '';

            if (isset($config['replacement'])) {
                $replacement = $config['replacement'];
            } elseif (isset($config['callback'])) {

                // parse attributes
                $attributes = array();
                if (isset($matches[2])) {
                    $attribute_string = $matches[2];
                    $attributes       = shortcode_parse_atts($attribute_string);
                }

                // call function
                $replacement = call_user_func($config['callback'], $attributes);
            }

            return $this->escape_value($replacement);
        }


        // default to not replacing it
        return $matches[0];
    }

    /**
     * @param string $string The string containing dynamic content tags.
     * @param string $escape_mode Escape mode for the replacement value. Leave empty for no escaping.
     * @return string
     */
    public function replace($string, $escape_mode = '')
    {
        $this->escape_mode = $escape_mode;

        // replace strings like this: {tagname attr="value"}
        $string = preg_replace_callback('/\{(\w+)(\ +(?:(?!\{)[^}\n])+)*\}/', array( $this, 'replace_tag' ), $string);

        // call again to take care of nested variables
        $string = preg_replace_callback('/\{(\w+)(\ +(?:(?!\{)[^}\n])+)*\}/', array( $this, 'replace_tag' ), $string);
        return $string;
    }

    /**
     * @param string $string
     *
     * @return string
     */
    public function replace_in_html($string)
    {
        return $this->replace($string, 'html');
    }

    /**
     * @param string $string
     *
     * @return string
     */
    public function replace_in_attributes($string)
    {
        return $this->replace($string, 'attributes');
    }

    /**
     * @param string $string
     *
     * @return string
     */
    public function replace_in_url($string)
    {
        return $this->replace($string, 'url');
    }



    /**
     * @param $value
     *
     * @return string
     */
    protected function escape_value_url($value)
    {
        return urlencode($value);
    }

    /**
     * @param $value
     *
     * @return string
     */
    protected function escape_value_attributes($value)
    {
        return esc_attr($value);
    }

    /**
     * @param $value
     *
     * @return string
     */
    protected function escape_value_html($value)
    {
        return esc_html($value);
    }

    /**
     * @param $value
     *
     * @return mixed
     */
    protected function escape_value($value)
    {
        if (empty($this->escape_mode)) {
            return $value;
        }

        return call_user_func(array( $this, 'escape_value_' . $this->escape_mode ), $value);
    }
}
