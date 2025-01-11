<?php

defined('ABSPATH') or exit;

/**
 * Class MC4WP_Contact_Form_7_Integration
 *
 * @ignore
 */
class MC4WP_Contact_Form_7_Integration extends MC4WP_Integration
{
    /**
     * @var string
     */
    public $name = 'Contact Form 7';

    /**
     * @var string
     */
    public $description = 'Subscribes people from Contact Form 7 forms.';


    /**
     * Add hooks
     */
    public function add_hooks()
    {
        add_action('wpcf7_init', [ $this, 'init' ]);
        add_action('wpcf7_mail_sent', [ $this, 'process' ], 1);
        add_action('wpcf7_posted_data', [ $this, 'alter_cf7_data' ]);
    }

    /**
    * Registers the CF7 shortcode
     *
    * @return boolean
    */
    public function init()
    {
        if (function_exists('wpcf7_add_form_tag')) {
            wpcf7_add_form_tag('mc4wp_checkbox', [ $this, 'shortcode' ]);
        } else {
            wpcf7_add_shortcode('mc4wp_checkbox', [ $this, 'shortcode' ]);
        }

        return true;
    }

    /**
     * @{inheritdoc}
     *
     * Contact Form 7 listens to the following triggers.
     *
     * - _mc4wp_subscribe_contact-form-7
     * - mc4wp-subscribe
     *
     * @return bool
     */
    public function checkbox_was_checked()
    {
        $data = $this->get_data();

        return ( isset($data[ $this->checkbox_name ]) && (int) $data[ $this->checkbox_name ] === 1 )
            || ( isset($data['mc4wp-subscribe']) && (int) $data['mc4wp-subscribe'] === 1 );
    }

    /**
    * Alter Contact Form 7 data.
    *
    * Adds mc4wp_checkbox to post data so users can use `mc4wp_checkbox` in their email templates
    *
    * @param array $data
    * @return array
    */
    public function alter_cf7_data($data = [])
    {
        $data['mc4wp_checkbox'] = $this->checkbox_was_checked() ? __('Yes', 'mailchimp-for-wp') : __('No', 'mailchimp-for-wp');
        return $data;
    }

    /**
     * Subscribe from Contact Form 7 Forms
     *
     * @todo improve smart guessing based on selected Mailchimp lists
     *
     * @param WPCF7_ContactForm $cf7_form
     * @return bool
     */
    public function process($cf7_form)
    {

        // was sign-up checkbox checked?
        if (! $this->checkbox_was_checked()) {
            return false;
        }

        $parser = new MC4WP_Field_Guesser($this->get_data());
        $data   = $parser->combine([ 'guessed', 'namespaced' ]);

        // do nothing if no email was found
        if (empty($data['EMAIL'])) {
            $this->get_log()->warning(sprintf('%s > Unable to find EMAIL field.', $this->name));
            return false;
        }

        return $this->subscribe($data, $cf7_form->id());
    }

    /**
     * Return the shortcode output
     *
     * @return string
     */
    public function shortcode($args = [])
    {
        if (! empty($args['labels'][0])) {
            $this->options['label'] = $args['labels'][0];
        }

        if (isset($args['options'])) {
            // check for default:0 or default:1 to set the checked attribute
            if (in_array('default:1', $args['options'], true)) {
                $this->options['precheck'] = true;
            } elseif (in_array('default:0', $args['options'], true)) {
                $this->options['precheck'] = false;
            }
        }

        // disable paragraph wrap because CF7 defaults to `wpautop`
        $this->options['wrap_p'] = 0;

        return $this->get_checkbox_html();
    }

    /**
     * @return bool
     */
    public function is_installed()
    {
        return function_exists('wpcf7_contact_form');
    }

    /**
     * @since 3.0
     * @return array
     */
    public function get_ui_elements()
    {
        return array_diff(parent::get_ui_elements(), [ 'enabled', 'implicit' ]);
    }

    /**
     * @param int $object_id
     * @since 3.0
     * @return string
     */
    public function get_object_link($object_id)
    {

        // for backwards compatibility, not all CF7 sign-ups have an object id
        if (empty($object_id)) {
            return '';
        }

        // Return empty string if CF7 is no longer activated.
        if (! function_exists('wpcf7_contact_form')) {
            return '';
        }

        $form = wpcf7_contact_form($object_id);
        if (! is_object($form)) {
            return '';
        }
        return sprintf('<a href="%s">%s</a>', admin_url('admin.php?page=wpcf7&post=' . $object_id), $form->title());
    }
}
