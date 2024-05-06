<?php

/**
 * Class MC4WP_Integration_Fixture
 *
 * @since 3.0
 * @ignore
 */
class MC4WP_Integration_Fixture
{
    /**
     * @var string
     */
    public $slug;

    /**
     * @var string
     */
    public $class;

    /**
     * @var bool
     */
    public $enabled;

    /**
     * @var bool
     */
    public $enabled_by_default;

    /**
     * @var MC4WP_Integration
     */
    public $instance;

    /**
     * @var array
     */
    public $options;

    /**
     * @param string $slug
     * @param string $class
     * @param bool $enabled_by_default
     * @param array $options
     */
    public function __construct($slug, $class, $enabled_by_default, array $options)
    {
        $this->slug               = $slug;
        $this->class              = $class;
        $this->enabled_by_default = $enabled_by_default;
        $this->enabled            = $enabled_by_default;
        $this->options            = $options;

        if (! empty($options['enabled'])) {
            $this->enabled = true;
        }
    }

    /**
     * Returns the actual instance
     *
     * @return MC4WP_Integration
     */
    public function load()
    {
        if (! $this->instance instanceof MC4WP_Integration) {
            $this->instance = new $this->class($this->slug, $this->options);
        }

        return $this->instance;
    }

    /**
     * Tunnel everything to MC4WP_Integration class
     *
     * @param string $name
     * @param array $arguments
     *
     * @return MC4WP_Integration
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array(array( $this->load(), $name ), $arguments);
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function __get($name)
    {
        return $this->load()->$name;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->slug;
    }
}
