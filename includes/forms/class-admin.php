<?php

/**
 * Class MC4WP_Forms_Admin
 *
 * @ignore
 * @access private
 */
class MC4WP_Forms_Admin
{
    /**
     * @var MC4WP_Admin_Messages
     */
    protected $messages;

    /**
     * @param MC4WP_Admin_Messages $messages
     */
    public function __construct(MC4WP_Admin_Messages $messages)
    {
        $this->messages = $messages;
    }

    /**
     * Add hooks
     */
    public function add_hooks()
    {
        add_action('register_shortcode_ui', array( $this, 'register_shortcake_ui' ));
        add_action('mc4wp_save_form', array( $this, 'update_form_stylesheets' ));
        add_action('mc4wp_admin_edit_form', array( $this, 'process_save_form' ));
        add_action('mc4wp_admin_add_form', array( $this, 'process_add_form' ));
        add_filter('mc4wp_admin_menu_items', array( $this, 'add_menu_item' ), 5);
        add_action('mc4wp_admin_show_forms_page-edit-form', array( $this, 'show_edit_page' ));
        add_action('mc4wp_admin_show_forms_page-add-form', array( $this, 'show_add_page' ));
        add_action('mc4wp_admin_enqueue_assets', array( $this, 'enqueue_assets' ), 10, 2);

        add_action('enqueue_block_editor_assets', array( $this, 'enqueue_gutenberg_assets' ));
    }


    public function enqueue_gutenberg_assets()
    {
        wp_enqueue_script('mc4wp-form-block', mc4wp_plugin_url('assets/js/forms-block.js'), array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-components' ));

        $forms = mc4wp_get_forms();
        $data  = array();
        foreach ($forms as $form) {
            $data[] = array(
                'name' => $form->name,
                'id'   => $form->ID,
            );
        }
        wp_localize_script('mc4wp-form-block', 'mc4wp_forms', $data);
    }

    /**
     * @param string $suffix
     * @param string $page
     */
    public function enqueue_assets($suffix, $page = '')
    {
        if ($page !== 'forms' || empty($_GET['view']) || $_GET['view'] !== 'edit-form') {
            return;
        }

        wp_register_script('mc4wp-forms-admin', mc4wp_plugin_url('assets/js/forms-admin.js'), array( 'mc4wp-admin' ), MC4WP_VERSION, true);
        wp_enqueue_script('mc4wp-forms-admin');
        wp_localize_script(
            'mc4wp-forms-admin',
            'mc4wp_forms_i18n',
            array(
                'addToForm'             => __('Add to form', 'mailchimp-for-wp'),
                'agreeToTerms'          => __('I have read and agree to the terms & conditions', 'mailchimp-for-wp'),
                'agreeToTermsShort'     => __('Agree to terms', 'mailchimp-for-wp'),
                'agreeToTermsLink'      => __('Link to your terms & conditions page', 'mailchimp-for-wp'),
                'city'                  => __('City', 'mailchimp-for-wp'),
                'checkboxes'            => __('Checkboxes', 'mailchimp-for-wp'),
                'choices'               => __('Choices', 'mailchimp-for-wp'),
                'choiceType'            => __('Choice type', 'mailchimp-for-wp'),
                'chooseField'           => __('Choose a field to add to the form', 'mailchimp-for-wp'),
                'close'                 => __('Close', 'mailchimp-for-wp'),
                'country'               => __('Country', 'mailchimp-for-wp'),
                'dropdown'              => __('Dropdown', 'mailchimp-for-wp'),
                'emailAddress'          => __('Email address', 'mailchimp-for-wp'),
                'fieldType'             => __('Field type', 'mailchimp-for-wp'),
                'fieldLabel'            => __('Field label', 'mailchimp-for-wp'),
                'formAction'            => __('Form action', 'mailchimp-for-wp'),
                'formActionDescription' => __('This field will allow your visitors to choose whether they would like to subscribe or unsubscribe', 'mailchimp-for-wp'),
                'formFields'            => __('Form fields', 'mailchimp-for-wp'),
                'forceRequired'         => __('This field is marked as required in Mailchimp.', 'mailchimp-for-wp'),
                'initialValue'          => __('Initial value', 'mailchimp-for-wp'),
                'interestCategories'    => __('Interest categories', 'mailchimp-for-wp'),
                'isFieldRequired'       => __('Is this field required?', 'mailchimp-for-wp'),
                'listChoice'            => __('List choice', 'mailchimp-for-wp'),
                'listChoiceDescription' => __('This field will allow your visitors to choose a list to subscribe to.', 'mailchimp-for-wp'),
                'listFields'            => __('List fields', 'mailchimp-for-wp'),
                'min'                   => __('Min', 'mailchimp-for-wp'),
                'max'                   => __('Max', 'mailchimp-for-wp'),
                'noAvailableFields'     => __('No available fields. Did you select a Mailchimp list in the form settings?', 'mailchimp-for-wp'),
                'optional'              => __('Optional', 'mailchimp-for-wp'),
                'placeholder'           => __('Placeholder', 'mailchimp-for-wp'),
                'placeholderHelp'       => __('Text to show when field has no value.', 'mailchimp-for-wp'),
                'preselect'             => __('Preselect', 'mailchimp-for-wp'),
                'remove'                => __('Remove', 'mailchimp-for-wp'),
                'radioButtons'          => __('Radio buttons', 'mailchimp-for-wp'),
                'streetAddress'         => __('Street Address', 'mailchimp-for-wp'),
                'state'                 => __('State', 'mailchimp-for-wp'),
                'subscribe'             => __('Subscribe', 'mailchimp-for-wp'),
                'submitButton'          => __('Submit button', 'mailchimp-for-wp'),
                'wrapInParagraphTags'   => __('Wrap in paragraph tags?', 'mailchimp-for-wp'),
                'value'                 => __('Value', 'mailchimp-for-wp'),
                'valueHelp'             => __('Text to prefill this field with.', 'mailchimp-for-wp'),
                'zip'                   => __('ZIP', 'mailchimp-for-wp'),
            )
        );
    }

    /**
     * @param $items
     *
     * @return mixed
     */
    public function add_menu_item($items)
    {
        $items['forms'] = array(
            'title'         => esc_html__('Forms', 'mailchimp-for-wp'),
            'text'          => esc_html__('Form', 'mailchimp-for-wp'),
            'slug'          => 'forms',
            'callback'      => array( $this, 'show_forms_page' ),
            'load_callback' => array( $this, 'redirect_to_form_action' ),
            'position'      => 10,
        );

        return $items;
    }

    /**
     * Act on the "add form" form
     */
    public function process_add_form()
    {
        $form_data    = $_POST['mc4wp_form'];
        $form_content = include MC4WP_PLUGIN_DIR . '/config/default-form-content.php';

        // Fix for MultiSite stripping KSES for roles other than administrator
        remove_all_filters('content_save_pre');

        $form_id = wp_insert_post(
            array(
                'post_type'    => 'mc4wp-form',
                'post_status'  => 'publish',
                'post_title'   => $form_data['name'],
                'post_content' => $form_content,
            )
        );

        // if settings were passed, save those too.
        if (isset($form_data['settings'])) {
            update_post_meta($form_id, '_mc4wp_settings', $form_data['settings']);
        }

        // set default form ID
        $this->set_default_form_id($form_id);

        $this->messages->flash(esc_html__('Form saved.', 'mailchimp-for-wp'));
        $edit_form_url = mc4wp_get_edit_form_url($form_id);
        wp_redirect($edit_form_url);
        exit;
    }

    /**
     * Saves a form to the database
     * @param int $form_id
     * @param array $data
     * @return int
     */
    private function save_form($form_id, array $data)
    {
        $keys = array(
            'settings' => array(),
            'messages' => array(),
            'name'     => '',
            'content'  => '',
        );
        $data = array_merge($keys, $data);
        $data = $this->sanitize_form_data($data);

        $post_data = array(
            'ID' => $form_id,
            'post_type'    => 'mc4wp-form',
            'post_status'  => ! empty($data['status']) ? $data['status'] : 'publish',
            'post_title'   => $data['name'],
            'post_content' => $data['content'],
        );

        // Fix for MultiSite stripping KSES for roles other than administrator
        remove_all_filters('content_save_pre');
        wp_insert_post($post_data);

        // merge new settings  with current settings to allow passing partial data
        $current_settings = get_post_meta($form_id, '_mc4wp_settings', true);
        if (is_array($current_settings)) {
            $data['settings'] = array_merge($current_settings, $data['settings']);
        }
        update_post_meta($form_id, '_mc4wp_settings', $data['settings']);

        // save form messages in individual meta keys
        foreach ($data['messages'] as $key => $message) {
            update_post_meta($form_id, 'text_' . $key, $message);
        }

        /**
         * Runs right after a form is updated.
         *
         * @since 3.0
         *
         * @param int $form_id
         */
        do_action('mc4wp_save_form', $form_id);

        return $form_id;
    }

    /**
     * @param array $data
     * @return array
     */
    public function sanitize_form_data(array $data)
    {
        $raw_data = $data;

        // strip <form> tags from content
        $data['content'] = preg_replace('/<\/?form(.|\s)*?>/i', '', $data['content']);

        // replace lowercased name="name" to prevent 404
        $data['content'] = str_ireplace(' name=\"name\"', ' name=\"NAME\"', $data['content']);

        // sanitize text fields
        $data['settings']['redirect'] = sanitize_text_field($data['settings']['redirect']);

        // strip tags from messages
        foreach ($data['messages'] as $key => $message) {
            $data['messages'][ $key ] = strip_tags($message, '<strong><b><br><a><script><u><em><i><span><img>');
        }

        // make sure lists is an array
        if (! isset($data['settings']['lists'])) {
            $data['settings']['lists'] = array();
        }

        $data['settings']['lists'] = array_filter((array) $data['settings']['lists']);

        // if current user can not post unfiltered HTML, run HTML through whitelist using wp_kses
        if (! current_user_can('unfiltered_html')) {
            $data['content'] = mc4wp_kses($data['content']);
            foreach ($data['messages'] as $key => $message) {
                $data['messages'][ $key ] = mc4wp_kses($data['messages'][ $key ]);
            }
        }

        /**
         * Filters the form data just before it is saved.
         *
         * @param array $data Sanitized array of form data.
         * @param array $raw_data Raw array of form data.
         *
         * @since 3.0.8
         * @ignore
         */
        $data = (array) apply_filters('mc4wp_form_sanitized_data', $data, $raw_data);

        return $data;
    }

    /**
     * Saves a form
     */
    public function process_save_form()
    {
        // save global settings (if submitted)
        if (isset($_POST['mc4wp']) && is_array($_POST['mc4wp'])) {
            $options = get_option('mc4wp', array());
            $posted  = $_POST['mc4wp'];
            foreach ($posted as $key => $value) {
                $options[ $key ] = trim($value);
            }
            update_option('mc4wp', $options);
        }

        // update form, settings and messages
        $form_id   = (int) $_POST['mc4wp_form_id'];
        $form_data = $_POST['mc4wp_form'];
        $this->save_form($form_id, $form_data);
        $this->set_default_form_id($form_id);
        $this->messages->flash(esc_html__('Form saved.', 'mailchimp-for-wp'));
    }

    /**
     * @param int $form_id
     */
    private function set_default_form_id($form_id)
    {
        $default_form_id = get_option('mc4wp_default_form_id', 0);

        if (empty($default_form_id)) {
            update_option('mc4wp_default_form_id', $form_id);
        }
    }

    /**
     * Goes through each form and aggregates array of stylesheet slugs to load.
     *
     * @hooked `mc4wp_save_form`
     */
    public function update_form_stylesheets()
    {
        $stylesheets = array();

        $forms = mc4wp_get_forms();
        foreach ($forms as $form) {
            $stylesheet = $form->get_stylesheet();

            if (! empty($stylesheet) && ! in_array($stylesheet, $stylesheets, true)) {
                $stylesheets[] = $stylesheet;
            }
        }

        update_option('mc4wp_form_stylesheets', $stylesheets);
    }

    /**
     * Redirect to correct form action
     *
     * @ignore
     */
    public function redirect_to_form_action()
    {
        if (! empty($_GET['view'])) {
            return;
        }

        try {
            // try default form first
            $default_form = mc4wp_get_form();
            $redirect_url = mc4wp_get_edit_form_url($default_form->ID);
        } catch (Exception $e) {
            // no default form, query first available form and go there
            $forms = mc4wp_get_forms(
                array(
                'posts_per_page' => 1,
                'orderby' => 'ID',
                'order' => 'ASC',
                )
            );

            if (count($forms) > 0) {
                // take first form and use it to go to the "edit form" screen
                $form         = array_shift($forms);
                $redirect_url = mc4wp_get_edit_form_url($form->ID);
            } else {
                // we don't have a form yet, go to "add new" screen
                $redirect_url = mc4wp_get_add_form_url();
            }
        }

        if (headers_sent()) {
            echo sprintf('<meta http-equiv="refresh" content="0;url=%s" />', $redirect_url);
        } else {
            wp_redirect($redirect_url);
        }

        exit;
    }

    /**
     * Show the Forms Settings page
     *
     * @internal
     */
    public function show_forms_page()
    {
        $view = ! empty($_GET['view']) ? $_GET['view'] : '';

        /**
         * @ignore
         */
        do_action('mc4wp_admin_show_forms_page', $view);

        /**
         * @ignore
         */
        do_action('mc4wp_admin_show_forms_page-' . $view);
    }

    /**
     * Show the "Edit Form" page
     *
     * @internal
     */
    public function show_edit_page()
    {
        $form_id   = ! empty($_GET['form_id']) ? (int) $_GET['form_id'] : 0;
        $mailchimp = new MC4WP_MailChimp();
        $lists     = $mailchimp->get_lists();

        try {
            $form = mc4wp_get_form($form_id);
        } catch (Exception $e) {
            echo '<h2>', esc_html__('Form not found.', 'mailchimp-for-wp'), '</h2>';
            echo '<p>', $e->getMessage(), '</p>';
            echo '<p><a href="javascript:history.go(-1);"> &lsaquo; ', esc_html__('Go back', 'mailchimp-for-wp'), '</a></p>';
            return;
        }

        $opts       = $form->settings;
        $active_tab = isset($_GET['tab']) ? trim($_GET['tab']) : 'fields';

        $form_preview_url = add_query_arg(
            array(
                'mc4wp_preview_form' => $form_id,
            ),
            site_url('/', 'admin')
        );

        require __DIR__ . '/views/edit-form.php';
    }

    /**
     * Shows the "Add Form" page
     *
     * @internal
     */
    public function show_add_page()
    {
        $mailchimp       = new MC4WP_MailChimp();
        $lists           = $mailchimp->get_lists();
        $number_of_lists = count($lists);
        require __DIR__ . '/views/add-form.php';
    }

    /**
     * Get URL for a tab on the current page.
     *
     * @since 3.0
     * @internal
     * @param $tab
     * @return string
     */
    public function tab_url($tab)
    {
        return add_query_arg(array( 'tab' => $tab ), remove_query_arg('tab'));
    }

    /**
     * Registers UI for when shortcake is activated
     */
    public function register_shortcake_ui()
    {
        $assets = new MC4WP_Form_Asset_Manager();
        $assets->load_stylesheets();

        $forms   = mc4wp_get_forms();
        $options = array();
        foreach ($forms as $form) {
            $options[ $form->ID ] = $form->name;
        }

        /**
         * Register UI for your shortcode
         *
         * @param string $shortcode_tag
         * @param array $ui_args
         */
        shortcode_ui_register_for_shortcode(
            'mc4wp_form',
            array(
                'label'         => esc_html__('Mailchimp Sign-Up Form', 'mailchimp-for-wp'),
                'listItemImage' => 'dashicons-feedback',
                'attrs'         => array(
                    array(
                        'label'   => esc_html__('Select the form to show', 'mailchimp-for-wp'),
                        'attr'    => 'id',
                        'type'    => 'select',
                        'options' => $options,
                    ),
                ),
            )
        );
    }
}
