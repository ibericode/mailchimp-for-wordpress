<?php

/**
 * Class MC4WP_Form_Output_Manager
 *
 * @ignore
 * @access private
 */
class MC4WP_Form_Output_Manager
{
    /**
     * @var int The # of forms outputted
     */
    public $count = 0;

    /**
     * @const string
     */
    private const SHORTCODE = 'mc4wp_form';

    /**
     * Add hooks
     */
    public function add_hooks()
    {
        // enable shortcodes in form content
        add_filter('mc4wp_form_content', 'do_shortcode');
        add_action('init', [ $this, 'register_shortcode' ]);
    }

    /**
     * Registers the [mc4wp_form] shortcode
     */
    public function register_shortcode()
    {
        add_shortcode(self::SHORTCODE, [ $this, 'shortcode' ]);
    }

    /**
     * @param array $attributes
     * @param string $content
     * @return string
     */
    public function shortcode($attributes = [], $content = '')
    {
        $default_attributes = [
            'id'            => '',
            'lists'         => '',
            'email_type'    => '',
            'element_id'    => '',
            'element_class' => '',
        ];

        $attributes = shortcode_atts(
            $default_attributes,
            $attributes,
            self::SHORTCODE
        );

        $config = [
            'element_id'    => $attributes['element_id'],
            'lists'         => $attributes['lists'],
            'email_type'    => $attributes['email_type'],
            'element_class' => $attributes['element_class'],
        ];

        return $this->output_form($attributes['id'], $config, false);
    }

    /**
     * @param int   $id
     * @param array $config
     * @param bool $echo
     *
     * @return string
     */
    public function output_form($id = 0, $config = [], $echo = true)
    {
        $html = $this->generate_html($id, $config);

        // echo content if necessary
        if ($echo) {
            echo $html;
        }

        return $html;
    }

    protected function generate_html($id = 0, $config = [])
    {
        try {
            $form = mc4wp_get_form($id);
        } catch (Exception $e) {
            if (current_user_can('manage_options')) {
                return sprintf('<strong style="color: indianred;">Mailchimp for WordPress error:</strong> %s', $e->getMessage());
            }

            return '';
        }

        $html = '';

        if (!mc4wp_get_api_key()) {
            if (current_user_can('manage_options')) {
                $html .= '<p style="color: indianred;">' . __('You need to configure your Mailchimp API key for this form to work properly.', 'mailchimp-for-wp') . '</p>';
            } else {
                // if no API key set and request is for an unauthorized user
                // show nothing
                return '';
            }
        }

        ++$this->count;

        // set a default element_id if none is given
        if (empty($config['element_id'])) {
            $config['element_id'] = 'mc4wp-form-' . $this->count;
        }

        $form_html = $form->get_html($config['element_id'], $config);

        try {
            // start new output buffer
            ob_start();

            /**
             * Runs just before a form element is outputted.
             *
             * @since 3.0
             *
             * @param MC4WP_Form $form
             */
            do_action('mc4wp_output_form', $form);

            // output the form (in output buffer)
            echo $form_html;

            // grab all contents in current output buffer & then clean + end it.
            $html .= ob_get_clean();
        } catch (Error $e) {
            $html .= $form_html;
        }

        return $html;
    }
}
