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
            <td><?php echo $f->name; ?> <?php
            if ($f->required) {
                ?>
                <span class="mc4wp-red">*</span>
            <?php } ?></td>
            <td><code><?php echo $f->tag; ?></code></td>
            <td>
                <?php echo $f->type; ?>
                <?php
                if ($f->options && $f->options->date_format) {
                    echo '(' . $f->options->date_format . ')';
                }
                ?>
                <?php
                if ($f->options && $f->options->choices) {
                    echo '(' . join(', ', $f->options->choices) . ')';
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
                <strong><?php echo $f->title; ?></strong>
                <br />
                <br />
                ID: <code><?php echo $f->id; ?></code>
            </td>
            <td><?php echo $f->type; ?></td>
            <td>
                <table>
                    <thead>
                        <tr><th>Name</th><th>ID</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($f->interests as $id => $name) { ?>
                        <tr>
                            <td><?php echo $name; ?></td>
                            <td><code><?php echo $id; ?></code></td>
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
            <td><code><?php echo $mp->marketing_permission_id; ?></code></td>
            <td><?php echo $mp->text; ?></td>
        </tr>
    <?php } ?>
    </tbody>
</table>
<?php } // end if marketing permissions ?>
