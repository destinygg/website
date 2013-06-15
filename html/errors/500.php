<?
$words = include 'words.php';
$word = $words [array_rand ( $words, 1 )];
if (preg_match ( '/^local/i', $_SERVER ['HTTP_HOST'] ) > 0) {
	$cdn = '//local.destiny.cdn';
} else {
	$cdn = '//cdn.destiny.gg';
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Error</title>
<link href="<?=$cdn?>/css/bootstrap.min.css" rel="stylesheet" media="screen">
<link href="<?=$cdn?>/errors/errors.css" rel="stylesheet" media="screen">
<link rel="shortcut icon" href="<?=$cdn?>/favicon.png">
<?include'ga.php'?>
</head>
<body class="error logicerror">

	<?include'top.php'?>

	<section id="header-band">
		<div class="container">
			<div id="overview">
				<div class="clearfix">
					<h1><strong><?=$word?>!</strong> An error occurred</h1>
					<p>The hamster jimmies have been rustled. <br />Would you like to <a href="/">return to the start</a>?</p>
				</div>
				<div id="destiny-illustration"></div>
			</div>
		</div>
	</section>
	
	<?include'foot.php'?>
	
	<script src="<?=$cdn?>/js/vendor/jquery-1.9.1.min.js"></script>
	<script src="<?=$cdn?>/js/vendor/bootstrap.js"></script>

</body>
</html>