<?php
defined('ABSPATH') or exit;

$tags = mc4wp('forms')->get_tags();
?>
<h2><?php echo esc_html__('Add dynamic form variable', 'mailchimp-for-wp'); ?></h2>
<p>
    <?php echo sprintf(wp_kses(__('The following list of variables can be used to <a href="%s">add some dynamic content to your form or success and error messages</a>.', 'mailchimp-for-wp'), array( 'a' => array( 'href' => array() ) )), 'https://www.mc4wp.com/kb/using-variables-in-your-form-or-messages/') . ' ' . __('This allows you to personalise your form or response messages.', 'mailchimp-for-wp'); ?>
</p>
<table class="widefat striped">
    <?php
    foreach ($tags as $tag => $config) {
        $tag = ! empty($config['example']) ? $config['example'] : $tag;
        ?>
        <tr>
            <td>
                <input type="text" class="widefat" value="<?php echo esc_attr(sprintf('{%s}', $tag)); ?>" readonly="readonly" onfocus="this.select();" />
                <p class="description" style="margin-bottom:0;"><?php echo strip_tags($config['description'], '<strong><b><em><i><a><code>'); ?></p>
            </td>
        </tr>
        <?php
    }
    ?>
</table>
