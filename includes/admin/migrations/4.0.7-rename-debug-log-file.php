<?php

defined( 'ABSPATH' ) or exit;

// get old log filename
$upload_dir   = wp_upload_dir( null, false );
$old_filename = trailingslashit( $upload_dir['basedir'] ) . 'mc4wp-debug.log';
$new_filename = trailingslashit( $upload_dir['basedir'] ) . 'mc4wp-debug-log.php';

// check if old default log file exists
if ( ! file_exists( $old_filename ) ) {
	return;
}

// rename to new file.
@rename( $old_filename, $new_filename );

// if success, insert php exit tag as first line
if ( file_exists( $new_filename ) ) {
	$handle = fopen( $new_filename, 'r+' );

	if ( is_resource( $handle ) ) {
		// make sure first line of log file is a PHP tag + exit statement (to prevent direct file access)
		$line            = fgets( $handle );
		$php_exit_string = '<?php exit; ?>';
		if ( strpos( $line, $php_exit_string ) !== 0 ) {
			rewind( $handle );
			fwrite( $handle, $php_exit_string . PHP_EOL . $line );
		}

		fclose( $handle );
	}
}
