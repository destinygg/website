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
<body id="subscribe">

	<?include'seg/top.php'?>
	
	<section class="container">
		<h1 class="title">
			<span>Subscribe</span> <small>become one of the brave</small>
		</h1>
		<div class="content content-dark clearfix">
			<div class="ui-step-legend-wrap clearfix">
				<div class="ui-step-legend clearfix">
					<ul>
						<li style="width: 25%;" class="active"><a>Select a subscription</a><i class="arrow"></i></li>
						<li style="width: 25%;"><a>Confirmation</a></li>
						<li style="width: 25%;"><a>Pay subscription</a></li>
						<li style="width: 25%;"><a>Complete</a></li>
					</ul>
				</div>
			</div>
			<div style="width: 100%;" class="clearfix stream">
				<form action="/order/confirm" method="post" style="margin: 0;">
					<input type="hidden" name="checkoutId" value="<?=$model->checkoutId?>" />
					<fieldset>
						<div class="control-group" style="margin: 10px 20px;">
							<?php if(!empty($model->subscription)): ?>
							<p>
								<span class="label label-inverse">HMMM</span> You already have
								an active subscription. <br />Click the button below to go to
								your profile.
							</p>
							<?php endif; ?>
							
							<?php if(empty($model->subscription)): ?>
							<p>
								Choose a subscription from the selection below. <br />Payments
								are processed and secured by PayPal.
							</p>
							<div id="subscriptions">
								<?php $sub = $model->subscriptions['1-MONTH-SUB']?>
								<div class="subscription active">
									<label class="radio">
										<input type="radio" name="subscription" value="<?=$sub['id']?>" checked="checked">
										<strong class="sub-amount">$<?=$sub['amount']?></strong>
										<span class="sub-label"><?=$sub['label']?></span>
									</label>
									<div class="payment-options">
										<label class="radio">
											<input type="radio" name="renew" value="1" checked>
											Renew each month
										</label> 
										<label class="radio">
											<input type="radio" name="renew" value="0">
											Once-off payment
										</label>
									</div>
								</div>
								<?php $sub = $model->subscriptions['3-MONTH-SUB']?>
								<div class="subscription">
									<label class="radio">
										<input type="radio" name="subscription" value="<?=$sub['id']?>">
										<strong class="sub-amount">$<?=$sub['amount']?></strong>
										<span class="sub-label"><?=$sub['label']?></span>
									</label>
									<div class="payment-options">
										<label class="radio">
											<input type="radio" name="renew" value="1">
											Renew every 3 months
										</label>
										<label class="radio">
											<input type="radio" name="renew" value="0">
											Once-off payment
										</label>
									</div>
								</div>
							</div>
							<?php endif; ?>
							
						</div>
						<div class="form-actions" style="margin: 15px 0 0 0; border-top-left-radius: 0; border-top-right-radius: 0; border-bottom-right-radius: 0;">
							<img class="pull-right" src="<?=Config::cdn()?>/img/Paypal.logosml.png" />
							<?php if(empty($model->subscription)): ?>
							<button type="submit" class="btn btn-primary"><i class="icon-check icon-white"></i> Confirm selection</button>
							<a href="/profile" class="btn">Back to profile</a>
							<?php else: ?>
							<a href="/profile" class="btn">Back to profile</a>
							<?php endif; ?>
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