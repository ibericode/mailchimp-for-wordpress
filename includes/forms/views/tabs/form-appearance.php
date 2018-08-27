<?php

$theme = wp_get_theme();
$css_options = array(
	'0' => sprintf( __( 'Inherit from %s theme', 'phplist-for-wp' ), $theme->Name ),
	'basic' => __( 'Basic', 'phplist-for-wp' ),
	__( 'Form Themes', 'phplist-for-wp' ) => array(
		'theme-light' => __( 'Light Theme', 'phplist-for-wp' ),
		'theme-dark' => __( 'Dark Theme', 'phplist-for-wp' ),
		'theme-red' => __( 'Red Theme', 'phplist-for-wp' ),
		'theme-green' => __( 'Green Theme', 'phplist-for-wp' ),
		'theme-blue' => __( 'Blue Theme', 'phplist-for-wp' ),
	)
);

/**
 * Filters the <option>'s in the "CSS Stylesheet" <select> box.
 *
 * @ignore
 */
$css_options = apply_filters( 'pl4wp_admin_form_css_options', $css_options );

?>

<h2><?php _e( 'Form Appearance', 'phplist-for-wp' ); ?></h2>

<table class="form-table">
	<tr valign="top">
		<th scope="row"><label for="pl4wp_load_stylesheet_select"><?php _e( 'Form Style' ,'phplist-for-wp' ); ?></label></th>
		<td class="nowrap valigntop">
			<select name="pl4wp_form[settings][css]" id="pl4wp_load_stylesheet_select">

				<?php foreach( $css_options as $key => $option ) {
					if( is_array( $option ) ) {
						$label = $key;
						$options = $option;
						printf( '<optgroup label="%s">', $label );
						foreach( $options as $key => $option ) {
							printf( '<option value="%s" %s>%s</option>', $key, selected( $opts['css'], $key, false ), $option );
						}
						print( '</optgroup>' );
					} else {
						printf( '<option value="%s" %s>%s</option>', $key, selected( $opts['css'], $key, false ), $option );
					}
				} ?>
			</select>
			<p class="help">
				<?php _e( 'If you want to load some default CSS styles, select "basic formatting styles" or choose one of the color themes' , 'phplist-for-wp' ); ?>
			</p>
		</td>
	</tr>

	<?php
	/** @ignore */
	do_action( 'pl4wp_admin_form_after_appearance_settings_rows', $opts, $form );
	?>

</table>

<?php submit_button(); ?>
