<? 
namespace Destiny; 
use Destiny\Utils\Tpl;
use Destiny\Utils\Date;
$ordersService = \Destiny\Service\Orders::getInstance ();
$subService = \Destiny\Service\Subscriptions::getInstance ();
$order = $ordersService->getOrder ();
$activeSub = $subService->getUserActiveSubscription(Session::$userId);
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Config::$a['meta']['title']?></title>
<meta charset="utf-8">
<meta name="description" content="<?=Config::$a['meta']['description']?>">
<meta name="keywords" content="<?=Config::$a['meta']['keywords']?>">
<meta name="author" content="<?=Config::$a['meta']['author']?>">
<link href="<?=Config::cdn()?>/css/vendor/bootstrap.min.css" rel="stylesheet" media="screen">
<link href="<?=Config::cdn()?>/css/destiny.<?=Config::version()?>.css" rel="stylesheet" media="screen">
<link rel="shortcut icon" href="<?=Config::cdn()?>/favicon.png">
<?include'seg/google.tracker.php'?>
</head>
<body id="subscribe">

	<?include'seg/top.php'?>
	
	<section class="container">
		<div class="content content-dark clearfix">
			<div style="width:100%;" class="clearfix stream">
				<h3 class="title">Payment successful</h3>
				<form style="margin:0; border-top:1px solid #222;">
					<fieldset>
						<div class="control-group" style="margin:20px;">
							<p>Your payment was successful. The order reference is: <span class="label label-inverse"><?=$ordersService->buildOrderRef ( $order )?></span>
							<br /> Please email <a href="mailto:<?=Config::$a['paypal']['support_email']?>"><?=Config::$a['paypal']['support_email']?></a> for any queries or issues.
							</p>
							<?if(!empty($activeSub)):?>
							<p>Additional subscriptions can be purchased at any time.<br />Your active subscription will expire in <span class="label label-inverse"><?=Date::getRemainingTime(new \DateTime($activeSub['endDate']))?></span></p>
							<br />
							<?endif;?>
							<p><a class="btn btn-success" href="/"><i class="icon-check icon-white"></i> Go back to destiny.gg</a></p>
						</div>
					</fieldset>
				</form>
			</div>
		</div>
	</section>
	
	<?include'seg/foot.php'?>
	
	<script src="<?=Config::cdn()?>/js/vendor/jquery-1.9.1.min.js"></script>
	<script src="<?=Config::cdn()?>/js/vendor/jquery.cookie.js"></script>
	<script src="<?=Config::cdn()?>/js/vendor/bootstrap.js"></script>
	<script src="<?=Config::cdn()?>/js/vendor/moment.js"></script>
	<script src="<?=Config::cdn()?>/js/destiny.<?=Config::version()?>.js"></script>
	<script>destiny.init({cdn:'<?=Config::cdn()?>'});</script>
</body>
</html>