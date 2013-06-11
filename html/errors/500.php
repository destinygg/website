<?
namespace Destiny;
use Destiny\Application;
use Destiny\Utils\Tpl;
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
<title>Error : 500</title>
<link href="<?=$cdn?>/css/bootstrap.min.css" rel="stylesheet" media="screen">
<link href="<?=$cdn?>/errors/errors.css" rel="stylesheet" media="screen">
<link rel="shortcut icon" href="<?=$cdn?>/favicon.png">
<?include'ga.php'?>
</head>
<body class="error logicerror">

	<section id="header-band">
		<div class="container">
			<header class="hero-unit" id="overview">
				<div class="clearfix">
					<h1>
						<strong><?=$word?>!</strong> An error occurred
					</h1>
					<?if(preg_match('/^local[.*]+/i', $_SERVER['HTTP_HOST']) > 0):?>
					<p class="alert alert-error"><strong>Error:</strong> <?=Tpl::out($model->error->getMessage())?></p>
					<pre><?=Tpl::out($model->error->getTraceAsString())?></pre>
					<?else:?>
					<p>The hamster jimmies have been rustled. <br />Would you like to <a href="/">return to the start</a>?</p>
					<?endif;?>
				</div>
				<div id="destiny-illustration"></div>
			</header>
		</div>
	</section>
	
	<?include'foot.php'?>
	
	<script src="<?=$cdn?>/js/vendor/jquery-1.9.1.min.js"></script>
	<script src="<?=$cdn?>/js/vendor/bootstrap.js"></script>

</body>
</html>