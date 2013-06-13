<?
namespace Destiny;
use Destiny\Utils\Tpl;
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
<?include'seg/google.tracker.php'?>
</head>
<body id="order-confirm">

	<?include'seg/top.php'?>
	
	<section class="container">
		<h1 class="title">
			<span>Confirmation</span> <small>almost there...</small>
		</h1>
		<div class="content content-dark clearfix">
			<div class="ui-step-legend-wrap clearfix">
				<div class="ui-step-legend clearfix">
					<ul>
						<li style="width: 25%;"><a>Select a subscription</a></li>
						<li style="width: 25%;" class="active"><a>Confirmation</a><i class="arrow"></i></li>
						<li style="width: 25%;"><a>Pay subscription</a></li>
						<li style="width: 25%;"><a>Complete</a></li>
					</ul>
				</div>
			</div>
			<div style="width: 100%;" class="clearfix stream">
				<form action="/order/create" method="post" style="margin: 0;">
					<input type="hidden" name="renew" value="<?=$model->renew?>"> <input type="hidden" name="subscription" value="<?=$model->subscription['id']?>">
					<input type="hidden" name="checkoutId" value="<?=$model->checkoutId?>">
					<fieldset>
						<div class="control-group" style="margin: 10px 20px;">
							<p>
								Please confirm your order by pressing the 'Pay subscription'
								button below. <br />Payments are processed and secured by
								PayPal.
							</p>
							<div id="subscriptions">
								<div class="subscription active">
									<strong class="sub-amount">$<?=$model->subscription['amount']?></strong>
									<span class="sub-label"><?=$model->subscription['label']?></span>
									<div>
									<?php if($model->renew): ?>
									<?=$model->subscription['agreement']?>
									<?php else: ?>
									<?=$model->subscription['billingFrequency']?> <?=strtolower($model->subscription['billingPeriod'])?> - once-off payment
									<?php endif; ?>
									</div>
								</div>
								<p>By clicking the 'Pay subscription' button below, you are confirming that this purchase is what you wanted.</p>
							</div>
						</div>
						<div class="form-actions"
							style="margin: 15px 0 0 0; border-top-left-radius: 0; border-top-right-radius: 0; border-bottom-right-radius: 0;">
							<img class="pull-right" src="<?=Config::cdn()?>/img/Paypal.logosml.png" />
							<button type="submit" class="btn btn-primary"><i class="icon-shopping-cart icon-white"></i> Pay subscription</button>
							<a href="/subscribe" class="btn">Cancel</a>
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