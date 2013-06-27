<?php
use Destiny\Config;
$words = include 'words.php';
$word = $words [array_rand ( $words, 1 )];
?>
<!DOCTYPE html>
<html>
<head>
<title>Error : Page not found</title>
<meta charset="utf-8">
<link href="<?=Config::cdn()?>/vendor/css/bootstrap.min.css" rel="stylesheet" media="screen">
<link href="<?=Config::cdn()?>/errors/css/style.css" rel="stylesheet" media="screen">
<link rel="shortcut icon" href="<?=Config::cdn()?>/favicon.png">
<?include'./tpl/seg/google.tracker.php'?>
</head>
<body class="error notfound">

	<?include'top.php'?>

	<section id="header-band">
		<div class="container">
			<header class="hero-unit" id="overview">
				<div class="clearfix">
					<h1><strong><?=$word?>!</strong> Page not found</h1>
					<p>Could not find the page you where 'looking for'. <br />Would you like to <a href="/">return to the start</a>?</p>
				</div>
				<div id="destiny-illustration"></div>
			</header>
		</div>
	</section>
	
	<?include'foot.php'?>
	
	<script src="<?=Config::cdn()?>/js/vendor/jquery-1.10.1.min.js"></script>
	<script src="<?=Config::cdn()?>/js/vendor/bootstrap.js"></script>

</body>
</html>