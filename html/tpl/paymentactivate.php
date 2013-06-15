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
<?include'seg/google.tracker.php'?>
</head>
<body id="unsubscribe">

	<?include'seg/top.php'?>
	
	<section class="container">
		<h1 class="title">
			<span>Re-activate</span> <small>scheduled payment</small>
		</h1>
		<div class="content content-dark clearfix">
			<div style="width: 100%;" class="clearfix stream">
			
				<?php if(!$model->paymentActivated): ?>
				<form action="/payment/activate" method="post">
					<input type="hidden" name="confirmationId" value="<?=$model->confirmationId?>" />
					<div class="control-group">
						<p>
							You are about to activate a scheduled payment for your subscription.
							<br>This can be cancelled from your profile at any time.
						</p>
						
						<dl class="dl-horizontal">
							<dt>Amount:</dt>
							<dd><?=Tpl::currency($model->paymentProfile['currency'], $model->paymentProfile['amount'])?></dd>
							<dt>Billing Cycle:</dt>
							<dd><?=Tpl::out($model->paymentProfile ['billingCycle'])?></dd>
							<dt>Billing start date:</dt>
							<dd><?=Tpl::out(Date::getDateTime($model->paymentProfile['billingStartDate'],Date::STRING_FORMAT_YEAR), 'none')?></dd>
							<?php if($model->paymentProfile['billingNextDate'] != $model->paymentProfile['billingStartDate']): ?>
							<dt>Billing next date:</dt>
							<dd><?=Tpl::out(Date::getDateTime($model->paymentProfile['billingNextDate'],Date::STRING_FORMAT_YEAR), 'none')?></dd>
							<?php endif; ?>
						</dl>
						
						<p>
							<span class="label label-inverse">NOTE</span> The first payment will be billed at the end of your current subscription.
						</p>
					</div>
					<div class="form-actions block-foot">
						<img class="pull-right" src="<?=Config::cdn()?>/img/Paypal.logosml.png" />
						<button type="submit" class="btn btn-primary"><i class="icon-shopping-cart icon-white"></i> Activate payment</button>
						<a href="/profile" class="btn">Back to profile</a>
					</div>
				</form>
				<?php endif; ?>
				
				<?php if($model->paymentActivated): ?>
				<div class="control-group">
					<p>
						Your scheduled payment has been created
						<br>Thank you for your support!
					</p>
					
					<dl class="dl-horizontal">
						<dt>Amount:</dt>
						<dd><?=Tpl::currency($model->paymentProfile['currency'], $model->paymentProfile['amount'])?></dd>
						<dt>Billing Cycle:</dt>
						<dd><?=Tpl::out($model->paymentProfile ['billingCycle'])?></dd>
						<dt>Billing start date:</dt>
						<dd><?=Tpl::out(Date::getDateTime($model->paymentProfile['billingStartDate'],Date::STRING_FORMAT_YEAR), 'none')?></dd>
						<?php if($model->paymentProfile['billingNextDate'] != $model->paymentProfile['billingStartDate']): ?>
						<dt>Billing next date:</dt>
						<dd><?=Tpl::out(Date::getDateTime($model->paymentProfile['billingNextDate'],Date::STRING_FORMAT_YEAR), 'none')?></dd>
						<?php endif; ?>
					</dl>
					
					<dl class="dl-horizontal">
						<dt>Status:</dt>
						<dd><span class="label label-<?=($model->paymentProfile['state'] == 'ActiveProfile') ? 'success':'warning'?>"><?=Tpl::out($model->paymentProfile['state'])?></span></dd>
						<dt>Amount:</dt>
						<dd><?=Tpl::currency($model->paymentProfile['currency'], $model->paymentProfile['amount'])?></dd>
						<dt>Profile:</dt>
						<dd><?=Tpl::out($model->paymentProfile['paymentProfileId'], 'none')?></dd>
					</dl>
				</div>
				<div class="form-actions block-foot">
					<img class="pull-right" src="<?=Config::cdn()?>/img/Paypal.logosml.png" />
					<a href="/profile" class="btn">Go back to profile</a>
				</div>
				<?php endif; ?>
				
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