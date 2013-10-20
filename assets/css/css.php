<?php 
// Set headers to serve CSS and encourage browser caching
$expires = 60 * 60 * 34 * 3; // cache time: 3 days
header('Content-Type: text/css; charset: UTF-8'); 
header("Cache-Control: public, max-age=" . $expires);
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');

if(isset($_GET['checkbox'])) {
	readfile('checkbox.css');
}

if(isset($_GET['form'])) {
	readfile('form.css');
}

exit;