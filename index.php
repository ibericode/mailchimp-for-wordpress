<?php
// prevent directory listing

header( 'Status: 403 Forbidden' );
header( 'HTTP/1.1 403 Forbidden' );
exit;
