<h3><?php _e( 'Your MailChimp Account' ,'mailchimp-for-wp' ); ?></h3>
<p><?php _e( 'The table below shows your MailChimp lists and their details. If you just applied changes to your MailChimp lists, please use the following button to renew the cached lists configuration.', 'mailchimp-for-wp' ); ?></p>

<form method="post" action="">
	<input type="hidden" name="_mc4wp_action" value="empty_lists_cache" />

	<p>
		<input type="submit" value="<?php _e( 'Renew MailChimp lists', 'mailchimp-for-wp' ); ?>" class="button" />
	</p>
</form>

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
			echo sprintf( '<td>%s</td>', esc_html( $list->id ) );
			echo sprintf( '<td>%s</td>', esc_html( $list->subscriber_count ) );
			echo '</tr>';

			echo sprintf( '<tr class="list-details list-%s-details" style="display: none;">', $list->id );
			echo '<td colspan="3" style="padding: 0 20px 40px;">';

			echo sprintf( '<p class="alignright" style="margin: 20px 0;"><a href="%s" target="_blank"><span class="dashicons dashicons-edit"></span> ' . __( 'Edit this list in MailChimp', 'mailchimp-for-wp' ) . '</a></p>', $list->get_web_url() );

			// Fields
			if ( ! empty( $list->merge_vars ) ) { ?>
				<h3>Fields</h3>
				<table class="widefat striped">
					<thead>
						<tr>
							<th>Name</th>
							<th>Tag</th>
							<th>Type</th>
						</tr>
					</thead>
					<?php foreach ( $list->merge_vars as $merge_var ) { ?>
						<tr title="<?php printf( __( '%s (%s) with field type %s.', 'mailchimp-for-wp' ), esc_html( $merge_var->name ), esc_html( $merge_var->tag ), esc_html( $merge_var->field_type ) ); ?>">
							<td><?php echo esc_html( $merge_var->name );
								if ( $merge_var->required ) {
									echo '<span style="color:red;">*</span>';
								} ?></td>
							<td><code><?php echo esc_html( $merge_var->tag ); ?></code></td>
							<td>
								<?php
									echo esc_html( $merge_var->field_type );

									if( ! empty( $merge_var->choices ) ) {
										echo ' (' . join( ', ', $merge_var->choices ) . ')';
									}
								?>

							</td>
						</tr>
					<?php } ?>
				</table>
			<?php }

			// Groupings
			if ( ! empty( $list->groupings ) ) { ?>

				<h3>Groupings</h3>
				<table class="widefat striped">
					<thead>
						<tr>
							<th>Name</th>
							<th>ID</th>
							<th>Groups</th>
						</tr>
					</thead>
					<?php foreach ( $list->groupings as $grouping ) { ?>
						<tr title="<?php esc_attr( printf( __( '%s (ID: %s) with type %s.', 'mailchimp-for-wp' ), $grouping->name, $grouping->id, $grouping->field_type ) ); ?>">
							<td><?php echo esc_html( $grouping->name ); ?></td>
							<td><?php echo esc_html( $grouping->id ); ?></td>
							<td>
								<ul class="ul-square">
									<?php foreach ( $grouping->groups as $group ) { ?>
										<li><?php echo esc_html( $group ); ?></li>
									<?php } ?>
								</ul>
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