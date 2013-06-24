<?
namespace Destiny;
use Destiny\Utils\Tpl;
use Destiny\Utils\Date;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($model->title)?></title>
<meta charset="utf-8">
<meta name="description" content="<?=Config::$a['meta']['description']?>">
<meta name="keywords" content="<?=Config::$a['meta']['keywords']?>">
<meta name="author" content="<?=Config::$a['meta']['author']?>">
<link href="<?=Config::cdn()?>/css/bootstrap.min.css" rel="stylesheet" media="screen">
<link href="<?=Config::cdn()?>/css/destiny.<?=Config::version()?>.css" rel="stylesheet" media="screen">
<link rel="shortcut icon" href="<?=Config::cdn()?>/favicon.png">
<?include'./tpl/seg/google.tracker.php'?>
</head>
<body id="paymenterror">

	<?include'./tpl/seg/top.php'?>
	
	<section class="container">
		<h1 class="title">
			<span>Re-activate</span> <small>scheduled payment</small>
		</h1>
		<div class="content content-dark clearfix">
			<div style="width: 100%;" class="clearfix stream">
				<div class="control-group">
					<p>An error has occurred during the activation process.
					<br>Please start again or email <a href="mailto:<?=Config::$a['paypal']['support_email']?>"><?=Config::$a['paypal']['support_email']?></a> for queries. </p>
					<div class="alert alert-error">
						<strong>Error!</strong>
						<?=Tpl::out($model->error->getMessage())?>
					</div>
				</div>
				<div class="form-actions block-foot">
					<img class="pull-right" src="<?=Config::cdn()?>/img/Paypal.logosml.png" />
					<a href="/profile/subscription" class="btn">Back to profile</a>
				</div>
			</div>
		</div>
	</section>
	
	<?include'./tpl/seg/foot.php'?>
	
	<script src="<?=Config::cdn()?>/js/vendor/jquery-1.9.1.min.js"></script>
	<script src="<?=Config::cdn()?>/js/vendor/jquery.cookie.js"></script>
	<script src="<?=Config::cdn()?>/js/vendor/bootstrap.js"></script>
	<script src="<?=Config::cdn()?>/js/vendor/moment.js"></script>
	<script src="<?=Config::cdn()?>/js/destiny.<?=Config::version()?>.js"></script>
	<script>destiny.init({cdn:'<?=Config::cdn()?>'});</script>
</body>
</html>