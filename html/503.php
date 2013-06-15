<?php
header ( 'HTTP/1.1 503 Service Temporarily Unavailable' );
header ( 'Status: 503 Service Temporarily Unavailable' );
header ( 'Retry-After: 3600' );
//$e->message = 'Hamster #' . rand ( 1000, 9999 ) . ' is being replaced. The site will be back up in about <strong>5</strong> minutes';
$e->message = 'Highspeed hamster integration. Expected up time in about 30 minutes to an hour.<br>Your patience has been noted.';
include 'errors/503.php';
?>