<?php

if( ! defined("MC4WP_LITE_VERSION") ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

/**
 * Adds MC4WP_Widget widget.
 */
class MC4WP_Lite_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'MC4WP_Widget', // Base ID
			__( 'MailChimp Sign-Up Form', 'mailchimp-for-wp' ), // Name
			array( 'description' => __( 'Displays your MailChimp for WordPress sign-up form', 'mailchimp-for-wp' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array   $args     Widget arguments.
	 * @param array   $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {

		$title = isset( $instance['title'] ) ? $instance['title'] : '';
		$title = apply_filters( 'widget_title', $title );

		echo $args['before_widget'];

		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		// make sure template functions exist (for usage in avia layout builder)
		if ( ! function_exists( 'mc4wp_get_form' ) ) {
			include_once MC4WP_LITE_PLUGIN_DIR . 'includes/functions/template.php';
		}

		echo mc4wp_get_form();

		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array   $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$title = isset( $instance['title'] ) ? $instance['title'] : __( 'Newsletter', 'mailchimp-for-wp' );
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'mailchimp-for-wp' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p class="help">
			<?php printf( __( 'You can edit your sign-up form in the %sMailChimp for WordPress form settings%s.', 'mailchimp-for-wp' ), '<a href="' . admin_url('admin.php?page=mc4wp-lite-form-settings') . '">', '</a>' ); ?>
		</p>
		<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array   $new_instance Values just sent to be saved.
	 * @param array   $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';
		return $instance;
	}

} // class MC4WP_Widget
