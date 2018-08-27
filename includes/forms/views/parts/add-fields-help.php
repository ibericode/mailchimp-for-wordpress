<?php defined( 'ABSPATH' ) or exit; ?>

<div class="pl4wp-admin">
	<h2><?php _e( 'Add more fields', 'phplist-for-wp' ); ?></h2>

	<div class="help-text">

		<p>
			<?php echo __( 'To add more fields to your form, you will need to create those fields in PhpList first.', 'phplist-for-wp' ); ?>
		</p>

		<p><strong><?php echo __( "Here's how:", 'phplist-for-wp' ); ?></strong></p>

		<ol>
			<li>
				<p>
					<?php echo __( 'Log in to your PhpList account.', 'phplist-for-wp' ); ?>
				</p>
			</li>
			<li>
				<p>
					<?php echo __( 'Add list fields to any of your selected lists.', 'phplist-for-wp' ); ?>
					<?php echo __( 'Clicking the following links will take you to the right screen.', 'phplist-for-wp' ); ?>
				</p>
				<ul class="children lists--only-selected">
					<?php foreach( $lists as $list ) { ?>
					<li data-list-id="<?php echo $list->id; ?>" class="<?php echo in_array( $list->id, $opts['lists'] ) ? '' : 'hidden'; ?>">
						<a href="https://admin.phplist.com/lists/settings/merge-tags?id=<?php echo $list->web_id; ?>">
							<span class="screen-reader-text"><?php _e( 'Edit list fields for', 'phplist-for-wp' ); ?> </span>
							<?php echo $list->name; ?>
						</a>
					</li>
					<?php } ?>
				</ul>
			</li>
			<li>
				<p>
					<?php echo __( 'Click the following button to have PhpList for WordPress pick up on your changes.', 'phplist-for-wp' ); ?>
				</p>

				<p>
					<a class="button button-primary" href="<?php echo esc_attr( add_query_arg( array( '_pl4wp_action' => 'empty_lists_cache' ) ) ); ?>">
						<?php _e( 'Renew PhpList lists', 'phplist-for-wp' ); ?>
					</a>
				</p>
			</li>
		</ol>


	</div>
</div>
