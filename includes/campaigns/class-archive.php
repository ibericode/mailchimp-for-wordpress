<?php

/**
 * Handles the Mailchimp campaign archive shortcode and Gutenberg block.
 *
 * @class   MC4WP_Campaign_Archive
 * @since   4.13.0
 * @access  public
 */
class MC4WP_Campaign_Archive
{
    const SHORTCODE = 'mc4wp_campaigns';

    /**
     * Add hooks.
     *
     * @return void
     */
    public function add_hooks()
    {
        add_shortcode(self::SHORTCODE, [ $this, 'shortcode' ]);
        add_action('init', [ $this, 'register_block_type' ]);
        add_action('enqueue_block_editor_assets', [ $this, 'enqueue_block_editor_assets' ]);
    }

    /**
     * Enqueue block editor assets for the campaign archive block.
     *
     * @since 4.13.0
     * @return void
     */
    public function enqueue_block_editor_assets()
    {
        wp_enqueue_script(
            'mc4wp-campaigns-block',
            mc4wp_plugin_url('assets/js/campaigns-block.js'),
            [
                'wp-blocks',
                'wp-i18n',
                'wp-element',
                'wp-components',
                'wp-block-editor',
            ],
            MC4WP_VERSION,
            true
        );
    }

    /**
     * Register the Gutenberg block type.
     *
     * @since 4.13.0
     * @return void
     */
    public function register_block_type()
    {
        if (! function_exists('register_block_type')) {
            return;
        }

        register_block_type(
            'mailchimp-for-wp/campaigns',
            [
                'render_callback' => [ $this, 'shortcode' ],
                'attributes'      => [
                    'count'      => [
                        'type'    => 'integer',
                        'default' => 10,
                    ],
                    'title_type' => [
                        'type'    => 'string',
                        'default' => 'title',
                    ],
                    'show_date'  => [
                        'type'    => 'boolean',
                        'default' => true,
                    ],
                ],
            ]
        );
    }

    /**
     * Shortcode handler. Also used as the Gutenberg block render callback.
     *
     * @since 4.13.0
     * @param array $attributes Shortcode or block attributes.
     * @return string
     */
    public function shortcode($attributes = [])
    {
        $attributes = shortcode_atts(
            [
                'count'      => 10,
                'title_type' => 'title', // 'title' or 'subject_line'
                'show_date'  => true,
            ],
            $attributes,
            self::SHORTCODE
        );

        $count      = absint($attributes['count']);
        $title_type = in_array($attributes['title_type'], [ 'title', 'subject_line' ], true)
            ? $attributes['title_type']
            : 'title';
        $show_date  = filter_var($attributes['show_date'], FILTER_VALIDATE_BOOLEAN);

        $campaigns = $this->get_campaigns($count);

        if (empty($campaigns) || ! is_array($campaigns)) {
            return is_string($campaigns) ? $campaigns : '';
        }

        $html = '<ul class="mc4wp-campaign-archive">';

        foreach ($campaigns as $campaign) {
            $archive_url = isset($campaign->long_archive_url) ? $campaign->long_archive_url : '';

            $label = isset($campaign->settings->{$title_type}) ? $campaign->settings->{$title_type} : '';

            if (empty($label)) {
                $label = isset($campaign->settings->title) ? $campaign->settings->title : '';
            }

            if (empty($archive_url) || empty($label)) {
                continue;
            }

            $html .= '<li class="mc4wp-campaign-archive__item">';
            $html .= '<a class="mc4wp-campaign-archive__link" href="' . esc_url($archive_url) . '">';
            $html .= esc_html($label);
            $html .= '</a>';

            if ($show_date && ! empty($campaign->send_time)) {
                $timestamp = strtotime($campaign->send_time);
                if ($timestamp) {
                    $html .= ' <span class="mc4wp-campaign-archive__date">';
                    /* translators: %s: Campaign send date. */
                    $html .= sprintf(
                        esc_html__('(%s)', 'mailchimp-for-wp'),
                        esc_html(date_i18n(get_option('date_format'), $timestamp))
                    );
                    $html .= '</span>';
                }
            }

            $html .= '</li>';
        }

        $html .= '</ul>';

        return $html;
    }

    /**
     * Fetch sent campaigns from the Mailchimp API, with transient caching.
     *
     * Results are cached for 1 hour per (count) combination to reduce API requests.
     *
     * @since 4.13.0
     * @param int $count Maximum number of campaigns to retrieve.
     * @return array|string
     */
    private function get_campaigns($count)
    {
        $transient_key = 'mc4wp_campaigns_v2_' . $count;
        $cached        = get_transient($transient_key);

        if (false !== $cached) {
            return $cached;
        }

        try {
            $api = mc4wp_get_api_v3();

            $result = $api->get_campaigns([
                'status'        => 'sent',
                'count'         => $count,
                'sort_field'    => 'send_time',
                'sort_dir'      => 'DESC',
                'fields'        => 'campaigns.id,campaigns.settings.title,campaigns.settings.subject_line,campaigns.send_time,campaigns.long_archive_url',
            ]);

            if (is_object($result) && isset($result->campaigns) && is_array($result->campaigns)) {
                $campaigns = $result->campaigns;
            } else {
                return current_user_can('manage_options') ? '<!-- MC4WP Campaign Archive: Invalid API response format -->' : '';
            }
        } catch (MC4WP_API_Exception $e) {
            // Log API errors gracefully, do not break the page.
            mc4wp('log')->error('Campaign Archive: ' . $e->getMessage());
            return current_user_can('manage_options') ? '<!-- MC4WP Campaign Archive Error: ' . esc_html($e->getMessage()) . ' -->' : '';
        }

        if (empty($campaigns)) {
            return current_user_can('manage_options') ? '<!-- MC4WP Campaign Archive: Zero campaigns returned from Mailchimp. -->' : '';
        }

        set_transient($transient_key, $campaigns, HOUR_IN_SECONDS);

        return $campaigns;
    }
}
