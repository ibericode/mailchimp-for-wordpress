<?php

/**
 * Adds MC4WP_Widget widget.
 */
class MC4WP_Widget extends WP_Widget {

	/**
	 * @var array
	 */
	protected $default_options = array(
		'title' => '',
		'form_id' => 0
	);

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
			'MC4WP_Widget', // Base ID
			__( 'MailChimp Sign-Up Form', 'mailchimp-for-wp' ), // Name
			array(
				'description' => __( 'Displays your MailChimp for WordPress sign-up form', 'mailchimp-for-wp' ),
			)
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array   $args     Widget arguments.
	 * @param array   $options Saved values from database.
	 */
	public function widget( $args, $options ) {

		$options = array_merge( $this->default_options, $options );
		$options['title'] = apply_filters( 'widget_title', $options['title'] );

		if( empty( $options['form_id'] ) ) {
			$options['form_id'] = get_option( 'mc4wp_default_form_id', 0 );
		}

		echo $args['before_widget'];

		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $options['title'] . $args['after_title'];
		}

		echo mc4wp_get_form( $options['form_id'] );

		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 *
	 * @return string|void
	 */
	public function form( $options ) {
		$title = isset( $options['title'] ) ? $options['title'] : __( 'Newsletter', 'mailchimp-for-wp' );

		do_action( 'mc4wp_widget_before_form', $options, $this );
		?>

        <p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'mailchimp-for-wp' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
        </p>

		<?php do_action( 'mc4wp_widget_after_form', $options, $this ); ?>

        <p class="help">
			<?php printf( __( 'You can edit your sign-up form(s) in the <a href="%s">MailChimp for WordPress form settings</a>.', 'mailchimp-for-wp' ), admin_url( 'admin.php?page=mailchimp-for-wp-form-settings' ) ); ?>
        </p>
		<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array   $values Values just sent to be saved.
	 * @param array   $old_options Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $values, $old_options ) {
		$options = array();
		$options['title'] = ( ! empty( $values['title'] ) ) ? sanitize_text_field( $values['title'] ) : '';
		$options = apply_filters( 'mc4wp_save_widget_options', $options, $values, $this );
		return $options;
	}

} // class MC4WP_Widget
