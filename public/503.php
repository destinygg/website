<?php
header ( 'HTTP/1.1 503 Service Temporarily Unavailable' );
header ( 'Status: 503 Service Temporarily Unavailable' );
header ( 'Retry-After: 3600' );
$e->message = 'Hamster #' . rand ( 1000, 9999 ) . ' is being replaced. The site will be back up in about <strong>5</strong> minutes';
include 'errors/503.php';
?>