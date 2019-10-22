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
    protected $escape_function = null;

    /**
     * @var array Array of registered dynamic content tags
     */
    protected $tags = array();

    protected function register()
    {
        // Global tags can go here
    }

    /**
     * @return array
     */
    public function all()
    {
        if ($this->tags === array()) {
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

            if (is_callable($this->escape_function)) {
                $replacement = call_user_func($this->escape_function, $replacement);
            }

            return $replacement;
        }


        // default to not replacing it
        return $matches[0];
    }

    /**
     * @param string $string The string containing dynamic content tags.
     * @param string $escape_function Escape mode for the replacement value. Leave empty for no escaping.
     * @return string
     */
    protected function replace($string, $escape_function = '')
    {
        $this->escape_function = $escape_function;

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

}
