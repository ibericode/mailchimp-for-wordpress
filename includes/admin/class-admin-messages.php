<?php

/**
 * Class MC4WP_Admin_Messages
 *
 * @ignore
 * @since 3.0
 */
class MC4WP_Admin_Messages
{
    /**
     * @var array
     */
    protected $bag;

    /**
     * @var bool
     */
    protected $dirty = false;

    /**
     * Add hooks
     */
    public function add_hooks()
    {
        add_action('admin_notices', array( $this, 'show' ));
        register_shutdown_function(array( $this, 'save' ));
    }

    private function load()
    {
        if (is_null($this->bag)) {
            $this->bag = get_option('mc4wp_flash_messages', array());
        }
    }

    // empty flash bag
    private function reset()
    {
        $this->bag   = array();
        $this->dirty = true;
    }

    /**
     * Flash a message (shows on next pageload)
     *
     * @param        $message
     * @param string $type
     */
    public function flash($message, $type = 'success')
    {
        $this->load();
        $this->bag[] = array(
            'text' => $message,
            'type' => $type,
        );
        $this->dirty = true;
    }



    /**
     * Show queued flash messages
     */
    public function show()
    {
        $this->load();

        foreach ($this->bag as $message) {
            echo sprintf('<div class="notice notice-%s is-dismissible"><p>%s</p></div>', $message['type'], $message['text']);
        }

        $this->reset();
    }

    /**
     * Save queued messages
     *
     * @hooked `shutdown`
     */
    public function save()
    {
        if ($this->dirty) {
            update_option('mc4wp_flash_messages', $this->bag, false);
        }
    }
}
