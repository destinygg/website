<?php
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Config;
$words = include 'words.php';
$word = $words [array_rand ( $words, 1 )];
?>
<!DOCTYPE html>
<html>
<head>
<title>Error</title>
<meta charset="utf-8">
<link href="<?=Config::cdn()?>/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
<link href="<?=Config::cdn()?>/errors/css/style.min.css" rel="stylesheet" media="screen">
<link rel="shortcut icon" href="<?=Config::cdn()?>/favicon.png">
<?php include Tpl::file('seg/google.tracker.php') ?>
</head>
<body class="error logicerror">

	<?php include'top.php'?>

	<section id="header-band">
		<div class="container">
			<div id="overview">
				<div class="clearfix">
					<h1><strong><?=$word?>!</strong> An error occurred</h1>
					<p>
						<?=$model->error->getMessage()?>
						<br>Would you like to <a href="/">return to the start</a>?
					</p>
				</div>
				<div id="destiny-illustration"></div>
			</div>
		</div>
	</section>
	
	<?php include'foot.php'?>
	
	<script src="<?=Config::cdn()?>/vendor/js/jquery-1.10.2.min.js"></script>
	<script src="<?=Config::cdn()?>/vendor/js/bootstrap.js"></script>

</body>
</html>