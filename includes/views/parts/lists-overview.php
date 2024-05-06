<h3><?php echo esc_html__('Your Mailchimp Account', 'mailchimp-for-wp'); ?></h3>
<p><?php echo esc_html__('The table below shows your Mailchimp lists and their details. If you just applied changes to your Mailchimp lists, please use the following button to renew the cached lists configuration.', 'mailchimp-for-wp'); ?></p>


<div id="mc4wp-list-fetcher">
    <form method="post" action="">
        <input type="hidden" name="_mc4wp_action" value="empty_lists_cache" />

        <p>
            <input type="submit" value="<?php echo esc_html__('Renew Mailchimp lists', 'mailchimp-for-wp'); ?>" class="button" />
        </p>
    </form>
</div>

<div class="mc4wp-lists-overview">
    <?php
    if (empty($lists)) {
        ?>
        <p><?php echo esc_html__('No lists were found in your Mailchimp account', 'mailchimp-for-wp'); ?>.</p>
        <?php
    } else {
        echo sprintf('<p>' . esc_html__('A total of %d lists were found in your Mailchimp account.', 'mailchimp-for-wp') . '</p>', count($lists));

        echo '<table class="widefat striped" id="mc4wp-mailchimp-lists-overview">';

        $headings = array(
            esc_html__('List Name', 'mailchimp-for-wp'),
            esc_html__('ID', 'mailchimp-for-wp'),
            esc_html__('Subscribers', 'mailchimp-for-wp'),
        );

        echo '<thead>';
        echo '<tr>';
        foreach ($headings as $heading) {
            echo sprintf('<th>%s</th>', $heading);
        }
        echo '</tr>';
        echo '</thead>';

        foreach ($lists as $list) {
            echo '<tr>';
            echo sprintf('<td><a href="#" class="mc4wp-mailchimp-list" data-list-id="%s">%s</a><span class="row-actions alignright"></span></td>', esc_attr($list->id), esc_html($list->name));
            echo sprintf('<td><code>%s</code></td>', esc_html($list->id));
            echo sprintf('<td>%s</td>', esc_html($list->stats->member_count));
            echo '</tr>';

            echo sprintf('<tr class="list-details list-%s-details" style="display: none;">', $list->id);
            echo '<td colspan="3" style="padding: 0 20px 40px;">';
            echo sprintf('<p class="alignright" style="margin: 20px 0;"><a href="https://admin.mailchimp.com/lists/members/?id=%s" target="_blank"><span class="dashicons dashicons-edit"></span> ' . esc_html__('Edit this list in Mailchimp', 'mailchimp-for-wp') . '</a></p>', $list->web_id);
            echo '<div><div>', esc_html__('Loading... Please wait.', 'mailchimp-for-wp'), '</div></div>';
            echo '</td>';
            echo '</tr>';
            ?>
            <?php
        } // end foreach $lists
        echo '</table>';
    } // end if empty
    ?>
</div>
