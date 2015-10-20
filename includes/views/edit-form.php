<?php defined( 'ABSPATH' ) or exit;

$tabs = array(
	'fields' => __( 'Fields', 'mailchimp-for-wp' ),
	'messages' => __( 'Messages', 'mailchimp-for-wp' ),
	'settings' => __( 'Settings', 'mailchimp-for-wp' ),
	'appearance' => __( 'Appearance', 'mailchimp-for-wp' )
);
?>
<div id="mc4wp-admin" class="wrap mc4wp-settings">

	<h1 class="page-title">
		<?php _e( "Edit Form", 'mailchimp-for-wp' ); ?>

		<!-- Form actions -->
		<a href="#" class="page-title-action">
			<span class="dashicons dashicons-plus-alt" style=""></span>
			<?php _e( 'Add new form', 'mailchimp-for-wp' ); ?>
		</a>
	</h1>

	<?php $this->admin_messages(); ?>

	<!-- Wrap entire page in <form> -->
	<form method="post">
		<input type="hidden" name="_mc4wp_action" value="edit_form" />
		<input type="hidden" name="mc4wp_form_id" value="<?php echo esc_attr( $form->ID ); ?>" />
		<?php wp_nonce_field( 'edit_form', '_mc4wp_nonce' ); ?>

		<div id="titlediv" class="small-margin">
			<div id="titlewrap">
				<label class="screen-reader-text" id="title-prompt-text" for="title"><?php _e( 'Enter form title here', 'mailchimp-for-wp' ); ?></label>
				<input type="text" name="mc4wp_form[name]" size="30" value="<?php echo esc_attr( $form->name ); ?>" id="title" spellcheck="true" autocomplete="off">
			</div>
			<div class="inside" style="margin-top: 3px;">

					<input id="shortcode" type="hidden" value="[mc4wp_form id='<?php echo $form->ID; ?>']">

					<a href="#" class="button-secondary" onclick="prompt('Shortcode:', document.getElementById('shortcode').value); return false;">
						<span class="dashicons dashicons-editor-code"></span>
						<?php _e( 'Get shortcode', 'mailchimp-for-wp' ); ?>
					</a>

					<a href="<?php echo esc_url( $previewer->get_preview_url() ); ?>" target="_blank" class="button-secondary">
						<span class="dashicons dashicons-welcome-view-site" style=""></span>
						<?php _e( 'Preview this form', 'mailchimp-for-wp' ); ?>
					</a>

			</div>
		</div>

		<h2 class="nav-tab-wrapper" id="mc4wp-tabs-nav">
			<?php foreach( $tabs as $tab => $name ) {
				echo sprintf( '<a class="nav-tab %s" href="%s">%s</a>', ( $active_tab === $tab ) ? 'nav-tab-active' : '', $this->tab_url( $tab ), $name );
			} ?>
		</h2>

		<div id="mc4wp-tabs">

			<?php foreach( $tabs as $tab => $name ) : ?>

				<!-- .tab -->
				<div class="tab <?php if( $active_tab === $tab ) { echo 'tab-active'; } ?>" id="tab-<?php echo $tab; ?>">
					<?php include dirname( __FILE__ ) . '/tabs/form-' . $tab .'.php'; ?>
				</div>
				<!-- / .tab -->

			<?php endforeach; // foreach tabs ?>


		</div>

	</form><!-- Entire page form wrap -->


	<?php if( isset( $_GET['old'] ) ) { ?>

	<div id="mc4wp-content">

		<?php settings_errors(); ?>


			<form action="options.php" method="post">
				<?php settings_fields( 'mc4wp_lite_form_settings' ); ?>
				
				<h3 class="mc4wp-title"><?php _e( 'Required form settings', 'mailchimp-for-wp' ); ?></h3>
				<table class="form-table">



					<tr valign="top">
						<td colspan="3">
							<h4><?php _e( 'Form mark-up', 'mailchimp-for-wp' ); ?></h4>

							<div class="mc4wp-wrapper">
								<div class="mc4wp-col mc4wp-first">
										</div>

								<div class="mc4wp-col mc4wp-last">
									<?php include('parts/admin-field-wizard.php'); ?>
								</div>
							</div>
						</td>
					</tr>
			</table>



		<?php submit_button(); ?>


	</form>

	<?php include 'parts/admin-footer.php'; ?>
</div>
<div id="mc4wp-sidebar">
	<?php do_action( 'mc4wp_admin_before_sidebar' ); ?>

	<div class="mc4wp-box" id="mc4wp-info-tabs">
		<h3 class="mc4wp-title"><?php _e( 'Form Styling', 'mailchimp-for-wp' ); ?></h3>
		<p><?php printf( __( 'Alter the visual appearance of the form by applying CSS rules to %s.', 'mailchimp-for-wp' ), '<b>.mc4wp-form</b>' ); ?></p>
		<p><?php printf( __( 'You can add the CSS rules to your theme stylesheet using the <a href="%s">Theme Editor</a> or by using a plugin like %s', 'mailchimp-for-wp' ), admin_url( 'theme-editor.php?file=style.css' ), '<a href="https://wordpress.org/plugins/simple-custom-css/">Simple Custom CSS</a>' ); ?>.</p>
		<p><?php printf( __( 'The <a href="%s" target="_blank">plugin FAQ</a> lists the various CSS selectors you can use to target the different form elements.', 'mailchimp-for-wp' ), 'https://wordpress.org/plugins/mailchimp-for-wp/faq/' ); ?></p>
		<p><?php printf( __( 'If you need an easier way to style your forms, consider <a href="%s">upgrading to MailChimp for WordPress Pro</a> which comes with an easy Styles Builder.', 'mailchimp-for-wp' ), 'https://mc4wp.com/#utm_source=wp-plugin&utm_medium=mailchimp-for-wp&utm_campaign=form-settings' ); ?></p>

		<h3 class="mc4wp-title"><?php _e( 'Variables', 'mailchimp-for-wp' ); ?></h3>
		<?php include dirname( __FILE__ ) . '/parts/admin-text-variables.php'; ?>

	</div>

		<?php include 'parts/admin-need-support.php'; ?>

	<?php do_action( 'mc4wp_admin_after_sidebar' ); ?>

</div>

	<?php } ?>

</div>