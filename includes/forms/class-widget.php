<?php

defined('ABSPATH') or exit;

/**
 * Adds MC4WP_Widget widget.
 *
 * @ignore
 */
class MC4WP_Form_Widget extends WP_Widget
{
    /**
     * @var array
     */
    private $default_instance_settings = array(
        'title'   => '',
        'form_id' => '',
    );

    /**
     * Register widget with WordPress.
     */
    public function __construct()
    {

        // translate default widget title
        $this->default_instance_settings['title'] = __('Newsletter', 'mailchimp-for-wp');

        parent::__construct(
            'mc4wp_form_widget', // Base ID
            __('Mailchimp Sign-Up Form', 'mailchimp-for-wp'), // Name
            array(
                'description' => __('Displays your Mailchimp for WordPress sign-up form', 'mailchimp-for-wp'),
            )
        );
    }

    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array   $args     Widget arguments.
     * @param array   $instance_settings Saved values from database.
     */
    public function widget($args, $instance_settings)
    {

        // ensure $instance_settings is an array
        if (! is_array($instance_settings)) {
            $instance_settings = array();
        }

        $instance_settings = array_merge($this->default_instance_settings, $instance_settings);
        $title             = apply_filters('widget_title', $instance_settings['title'], $instance_settings, $this->id_base);

        echo $args['before_widget'];

        if (! empty($title)) {
            echo $args['before_title'] . $title . $args['after_title'];
        }

        mc4wp_show_form($instance_settings['form_id']);

        echo $args['after_widget'];
    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $settings Previously saved values from database.
     *
     * @return string|void
     */
    public function form($settings)
    {
        $settings = array_merge($this->default_instance_settings, (array) $settings); ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'mailchimp-for-wp'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($settings['title']); ?>" />
        </p>


        <?php
        /**
         * Runs right after the widget settings form is outputted
         *
         * @param array $settings
         * @param MC4WP_Form_Widget $this
         * @ignore
         */
        do_action('mc4wp_form_widget_form', $settings, $this);
        ?>

        <p class="description">
            <?php printf(__('You can edit your sign-up form in the <a href="%s">Mailchimp for WordPress form settings</a>.', 'mailchimp-for-wp'), admin_url('admin.php?page=mailchimp-for-wp-forms')); ?>
        </p>
        <?php
    }

    /**
     * Validates widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array   $new_settings Values just sent to be saved.
     * @param array   $old_settings Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update($new_settings, $old_settings)
    {
        if (! empty($new_settings['title'])) {
            $new_settings['title'] = sanitize_text_field($new_settings['title']);
        }

        /**
         * Filters the widget settings before they are saved.
         *
         * @param array $new_settings
         * @param array $old_settings
         * @param MC4WP_Form_Widget $widget
         * @ignore
         */
        $new_settings = apply_filters('mc4wp_form_widget_update_settings', $new_settings, $old_settings, $this);

        return $new_settings;
    }
}
