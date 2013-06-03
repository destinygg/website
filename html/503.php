<?php
header ( 'HTTP/1.1 503 Service Temporarily Unavailable' );
header ( 'Status: 503 Service Temporarily Unavailable' );
header ( 'Retry-After: 3600' );
include 'errors/503.php';
?>