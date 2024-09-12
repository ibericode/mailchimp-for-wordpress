<?php
/**
 * @var object[] $merge_fields
 * @var object[] $interest_categories
 * @var object[] $marketing_permissions
 */
?>
<h3>Merge fields</h3>
<table class="widefat striped">
    <thead>
    <tr>
        <th>Name</th>
        <th>Tag</th>
        <th>Type</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($merge_fields as $f) { ?>
        <tr>
            <td><?php echo esc_html($f->name); ?> <?php
            if ($f->required) {
                ?>
                <span class="mc4wp-red">*</span>
            <?php } ?></td>
            <td><code><?php echo esc_html($f->tag); ?></code></td>
            <td>
                <?php echo esc_html($f->type); ?>
                <?php
                if ($f->options && $f->options->date_format) {
                    echo esc_html('(' . $f->options->date_format . ')');
                }
                ?>
                <?php
                if ($f->options && $f->options->choices) {
                    echo esc_html('(' . join(', ', $f->options->choices) . ')');
                }
                ?>
            </td>
        </tr>
    <?php } ?>
    </tbody>
</table>

<?php if ($interest_categories) { ?>
<h3>Interest Categories</h3>
<table class="striped widefat">
    <thead>
    <tr>
        <th>Name</th>
        <th>Type</th>
        <th>Interests</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($interest_categories as $f) { ?>
        <tr>
            <td>
                <strong><?php echo esc_html($f->title); ?></strong>
                <br />
                <br />
                ID: <code><?php echo esc_html($f->id); ?></code>
            </td>
            <td><?php echo esc_html($f->type); ?></td>
            <td>
                <table>
                    <thead>
                        <tr><th>Name</th><th>ID</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($f->interests as $id => $name) { ?>
                        <tr>
                            <td><?php echo esc_html($name); ?></td>
                            <td><code><?php echo esc_html($id); ?></code></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>

            </td>
        </tr>
    <?php } ?>
    </tbody>
</table>
<?php } // end if interest categories ?>

<?php if ($marketing_permissions) { ?>
<h3>Marketing Permissions</h3>
<table class="striped widefat">
    <thead>
    <tr>
        <th>ID</th>
        <th>Name</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($marketing_permissions as $mp) { ?>
        <tr>
            <td><code><?php echo esc_html($mp->marketing_permission_id); ?></code></td>
            <td><?php echo esc_html($mp->text); ?></td>
        </tr>
    <?php } ?>
    </tbody>
</table>
<?php } // end if marketing permissions ?>
