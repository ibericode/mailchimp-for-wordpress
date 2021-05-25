<?php defined( 'ABSPATH' ) or exit; ?>

<div class="mc4wp-admin">
	<h2><?php echo esc_html__( 'Add more fields', 'mailchimp-for-wp' ); ?></h2>

	<div>

		<p>
			<?php echo esc_html__( 'To add more fields to your form, you will need to create those fields in Mailchimp first.', 'mailchimp-for-wp' ); ?>
		</p>

		<p><strong><?php echo esc_html__( "Here's how:", 'mailchimp-for-wp' ); ?></strong></p>

		<ol>
			<li>
				<p>
					<?php echo esc_html__( 'Log in to your Mailchimp account.', 'mailchimp-for-wp' ); ?>
				</p>
			</li>
			<li>
				<p>
					<?php echo esc_html__( 'Add list fields to any of your selected lists.', 'mailchimp-for-wp' ); ?>
					<?php echo esc_html__( 'Clicking the following links will take you to the right screen.', 'mailchimp-for-wp' ); ?>
				</p>
				<ul class="children lists--only-selected">
					<?php
					foreach ( $lists as $list ) {
						?>
					<li data-list-id="<?php echo $list->id; ?>" style="display: <?php echo in_array( $list->id, $opts['lists'] ) ? '' : 'none'; ?>">
						<a href="https://admin.mailchimp.com/lists/settings/merge-tags?id=<?php echo $list->web_id; ?>">
							<span class="screen-reader-text"><?php echo esc_html__( 'Edit list fields for', 'mailchimp-for-wp' ); ?> </span>
							<?php echo $list->name; ?>
						</a>
					</li>
						<?php
					}
					?>
				</ul>
			</li>
			<li>
				<p>
					<?php echo esc_html__( 'Click the following button to have Mailchimp for WordPress pick up on your changes.', 'mailchimp-for-wp' ); ?>
				</p>

				<p>
					<a class="button button-primary" href="
					<?php
					echo esc_attr(
						add_query_arg(
							array(
							'_mc4wp_action' => 'empty_lists_cache',
							'_wpnonce' => wp_create_nonce( '_mc4wp_action' ),
							)
						)
					);
					?>
					">
						<?php echo esc_html__( 'Renew Mailchimp lists', 'mailchimp-for-wp' ); ?>
					</a>
				</p>
			</li>
		</ol>


	</div>
</div>
