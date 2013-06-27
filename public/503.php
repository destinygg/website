<?php
header ( 'HTTP/1.1 503 Service Temporarily Unavailable' );
header ( 'Status: 503 Service Temporarily Unavailable' );
header ( 'Retry-After: 3600' );

$words = include './errors/words.php';
$word = $words [array_rand ( $words, 1 )];

if (preg_match ( '/^local/i', $_SERVER ['HTTP_HOST'] ) > 0) {
	$cdn = '//local.destiny.cdn';
} else {
	$cdn = '//cdn.destiny.gg';
}

$e->message = 'Hamster #' . rand ( 1000, 9999 ) . ' is being replaced. The site will be back up in about <strong>5</strong> minutes';
?>
<!DOCTYPE html>
<html>
<head>
<title>Maintenance</title>
<meta charset="utf-8">
<link href="<?=$cdn?>/vendor/css/bootstrap.min.css" rel="stylesheet" media="screen">
<link href="<?=$cdn?>/errors/css/style.css" rel="stylesheet" media="screen">
<link rel="shortcut icon" href="<?=$cdn?>/favicon.png">
</head>
<body class="error maintenance">

	<?include'./errors/top.php'?>

	<section id="header-band">
		<div class="container">
			<header class="hero-unit" id="overview">
				<div class="clearfix">
					<h1><strong><?=$word?>!</strong> Down for maintenance</h1>
					<p><?=$e->message?></p>
				</div>
				<div id="destiny-illustration"></div>
			</header>
		</div>
	</section>
	
	<?include'./errors/foot.php'?>
	
	<script src="<?=$cdn?>/js/vendor/jquery-1.10.1.min.js"></script>
	<script src="<?=$cdn?>/js/vendor/bootstrap.js"></script>

</body>
</html>
