<h3><?php _e( 'Your MailChimp Account' ,'mailchimp-for-wp' ); ?></h3>
<p><?php _e( 'The table below shows your MailChimp lists and their details. If you just applied changes to your MailChimp lists, please use the following button to renew the cached lists configuration.', 'mailchimp-for-wp' ); ?></p>


<div id="mc4wp-list-fetcher">
	<form method="post" action="">
		<input type="hidden" name="_mc4wp_action" value="empty_lists_cache" />

		<p>
			<input type="submit" value="<?php _e( 'Renew MailChimp lists', 'mailchimp-for-wp' ); ?>" class="button" />
		</p>
	</form>
</div>

<div class="mc4wp-lists-overview">
	<?php if( empty( $lists ) ) { ?>
		<p><?php _e( 'No lists were found in your MailChimp account', 'mailchimp-for-wp' ); ?>.</p>
	<?php } else {
		printf( '<p>' . __( 'A total of %d lists were found in your MailChimp account.', 'mailchimp-for-wp' ) . '</p>', count( $lists ) );

		echo '<table class="widefat striped">';

		$headings = array(
			__( 'List Name', 'mailchimp-for-wp' ),
			__( 'ID', 'mailchimp-for-wp' ),
			__( 'Subscribers', 'mailchimp-for-wp' )
		);

		echo '<thead>';
		echo '<tr>';
		foreach( $headings as $heading ) {
			echo sprintf( '<th>%s</th>', $heading );
		}
		echo '</tr>';
		echo '</thead>';

		foreach ( $lists as $list ) {
			/** @var MC4WP_MailChimp_List $list */
			echo '<tr>';
			echo sprintf( '<td><a href="javascript:mc4wp.helpers.toggleElement(\'.list-%s-details\')">%s</a><span class="row-actions alignright"></span></td>', $list->id, esc_html( $list->name ) );
			echo sprintf( '<td><code>%s</code></td>', esc_html( $list->id ) );
			echo sprintf( '<td>%s</td>', esc_html( $list->subscriber_count ) );
			echo '</tr>';

			echo sprintf( '<tr class="list-details list-%s-details" style="display: none;">', $list->id );
			echo '<td colspan="3" style="padding: 0 20px 40px;">';

			echo sprintf( '<p class="alignright" style="margin: 20px 0;"><a href="%s" target="_blank"><span class="dashicons dashicons-edit"></span> ' . __( 'Edit this list in MailChimp', 'mailchimp-for-wp' ) . '</a></p>', $list->get_web_url() );

			// Fields
			if ( ! empty( $list->merge_fields ) ) { ?>
				<h3><?php _e('Merge Fields', 'mailchimp-for-wp');?></h3>
				<table class="widefat striped">
					<thead>
						<tr>
							<th><?php _e('Name', 'mailchimp-for-wp');?></th>
							<th><?php _e('Tag', 'mailchimp-for-wp');?></th>
							<th><?php _e('Type', 'mailchimp-for-wp');?></th>
						</tr>
					</thead>
					<?php foreach ( $list->merge_fields as $merge_field ) { ?>
						<tr title="<?php printf( __( '%s (%s) with field type %s.', 'mailchimp-for-wp' ), esc_html( $merge_field->name ), esc_html( $merge_field->tag ), esc_html( $merge_field->field_type ) ); ?>">
							<td><?php echo esc_html( $merge_field->name );
								if ( $merge_field->required ) {
									echo '<span style="color:red;">*</span>';
								} ?></td>
							<td><code><?php echo esc_html( $merge_field->tag ); ?></code></td>
							<td>
								<?php
									echo esc_html( $merge_field->field_type );

									if( ! empty( $merge_field->choices ) ) {
										echo ' (' . join( ', ', $merge_field->choices ) . ')';
									}
								?>

							</td>
						</tr>
					<?php } ?>
				</table>
			<?php }

			// interest_categories
			if ( ! empty( $list->interest_categories ) ) { ?>

				<h3><?php _e('Interest Categories', 'mailchimp-for-wp');?></h3>
				<table class="widefat striped">
					<thead>
						<tr>
							<th><?php _e('Name', 'mailchimp-for-wp');?></th>
							<th><?php _e('Type', 'mailchimp-for-wp');?></th>
							<th><?php _e('Interests', 'mailchimp-for-wp');?></th>
						</tr>
					</thead>
					<?php foreach ( $list->interest_categories as $interest_category ) { ?>
						<tr>
							<td>
								<strong><?php echo esc_html( $interest_category->name ); ?></strong><br /><br />
								ID: <code><?php echo esc_html( $interest_category->id ); ?></code>
							</td>
							<td><?php echo esc_html( $interest_category->field_type ); ?></td>
							<td>
								<div class="row" style="margin-bottom: 4px;">
									<div class="col col-3"><strong style="display: block; border-bottom: 1px solid #eee;">Name</strong></div>
									<div class="col col-3"><strong style="display: block; border-bottom: 1px solid #eee;">ID</strong></div>
								</div>
								<?php
								foreach( $interest_category->interests as $id => $interest ) {
									echo '<div class="row tiny-margin">';
									echo sprintf( '<div class="col col-3">%s</div><div class="col col-3"><code title="Interest ID">%s</code></div>', $interest, $id );
									echo '<br style="clear: both;" />';
									echo '</div>';
								}
								?>



							</td>
						</tr>
					<?php } ?>
				</table>

			<?php }

			echo '</td>';
			echo '</tr>';

			?>
		<?php } // end foreach $lists
		echo '</table>';
	} // end if empty ?>
</div>
