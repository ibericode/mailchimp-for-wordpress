<?php

defined('ABSPATH') or exit;

/**
 * Class MC4WP_Simple_Basic_Contact_Form_Integration
 *
 * Integrates Mailchimp for WordPress with the Simple Basic Contact Form plugin.
 *
 * @since 4.9.0
 * @ignore
 */
class MC4WP_Simple_Basic_Contact_Form_Integration extends MC4WP_Integration
{
    /**
     * @var string
     */
    public $name = 'Simple Basic Contact Form';

    /**
     * @var string
     */
    public $description = 'Subscribes people from Simple Basic Contact Form forms.';

    /**
     * Add hooks
     */
    public function add_hooks()
    {
        add_filter('scf_filter_contact_form', [ $this, 'add_checkbox' ], 20);
        add_action('scf_send_email', [ $this, 'process' ], 10, 5);
    }

    /**
     * Adds the MC4WP checkbox to the Simple Basic Contact Form HTML.
     *
     * Inserts the checkbox before the closing </form> tag so it appears
     * inside the form, just before the submit area.
     *
     * @param string $form_html The full contact form HTML.
     * @return string Modified form HTML with the checkbox injected.
     */
    public function add_checkbox($form_html)
    {
        // do not add a checkbox when integration is implicit
        if ($this->options['implicit']) {
            return $form_html;
        }

        $checkbox_html = $this->get_checkbox_html();

        // insert the checkbox just before the closing </form> tag
        $form_html = str_ireplace('</form>', $checkbox_html . '</form>', $form_html);

        return $form_html;
    }

    /**
     * Process the form submission and subscribe the user if the checkbox was checked.
     *
     * Fires on the `scf_send_email` action after the contact form email is sent.
     *
     * @param string $recipient The email recipient.
     * @param string $topic     The email subject.
     * @param string $message   The email message body.
     * @param string $headers   The email headers.
     * @param string $email     The sender's email address (form submitter).
     *
     * @return bool
     */
    public function process($recipient, $topic, $message, $headers, $email)
    {
        // was sign-up checkbox checked?
        if (! $this->triggered()) {
            return false;
        }

        $parser = new MC4WP_Field_Guesser($this->get_data());
        $data   = $parser->combine([ 'guessed', 'namespaced' ]);

        // use the email from the action parameter if not found via field guesser
        if (empty($data['EMAIL']) && ! empty($email)) {
            $data['EMAIL'] = $email;
        }

        // do nothing if no email was found
        if (empty($data['EMAIL'])) {
            $this->get_log()->warning(sprintf('%s > Unable to find EMAIL field.', $this->name));
            return false;
        }

        return $this->subscribe($data);
    }

    /**
     * Are the required dependencies for this integration installed?
     *
     * @return bool
     */
    public function is_installed()
    {
        return function_exists('simple_contact_form');
    }

    /**
     * Returns the UI elements to show on the settings page.
     *
     * Removes 'implicit' since this integration uses an explicit checkbox approach.
     *
     * @since 4.9.0
     * @return array
     */
    public function get_ui_elements()
    {
        return array_diff(parent::get_ui_elements(), [ 'implicit' ]);
    }
}
