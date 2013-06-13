<?
namespace Destiny;
use Destiny\Utils\Tpl;
use Destiny\Utils\Currency;
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
<body id="invoice">

	<?include'seg/top.php'?>
	
	<section class="container">
		<h1 class="title">
			<span>Invoice</span> <small><?=Tpl::out($model->orderReference)?></small>
		</h1>

		<div class="content content-dark clearfix">

			<div style="width: 100%; clear: both;" class="clearfix stream">
				<h3 class="title">Details</h3>
				<div style="padding: 10px 20px; margin: 0; border-top: 1px solid #222;">
					<dl class="dl-horizontal">
						<dt>Status:</dt>
						<dd><span class="label label-<?=($model->order['state'] == 'Completed') ? 'success':'warning'?>"><?=Tpl::out($model->order['state'])?></span></dd>
						<dt>Amount:</dt>
						<dd><?=Tpl::currency($model->order['currency'], $model->order['amount'])?></dd>
						<dt>Description:</dt>
						<dd><?=Tpl::out($model->order['description'])?></dd>
						<dt>Created on:</dt>
						<dd><?=Date::getDateTime($model->order['createdDate'], Date::STRING_FORMAT_YEAR)?></dd>
					</dl>
				</div>
			</div>
			
			<?php if(!empty($model->payment)): ?>
			<div style="width: 100%; clear: both;" class="clearfix stream">
				<h3 class="title">Payment</h3>
				<div style="padding: 10px 20px; margin: 0; border-top: 1px solid #222;">
					<dl class="dl-horizontal">
						<dt>Status:</dt>
						<dd><span class="label label-<?=($model->payment['paymentStatus'] == 'Completed') ? 'success':'warning'?>"><?=Tpl::out($model->payment['paymentStatus'])?></span></dd>
						<dt>Amount:</dt>
						<dd><?=Tpl::currency($model->payment['currency'], $model->payment['amount'])?></dd>
						<dt>Id:</dt>
						<dd><?=Tpl::out($model->payment['transactionId'])?></dd>
						<dt>Type:</dt>
						<dd><?=Tpl::out($model->payment['transactionType'])?></dd>
						<dt>Payer:</dt>
						<dd><?=Tpl::out($model->payment['payerId'])?></dd>
						<dt>Payment:</dt>
						<dd><?=Tpl::out($model->payment['paymentType'])?></dd>
						<dt>Payed on:</dt>
						<dd><?=Date::getDateTime($model->payment['paymentDate'], Date::STRING_FORMAT_YEAR)?></dd>
					</dl>
				</div>
			</div>
			<?php endif; ?>
		
			<?php if(!empty($model->paymentProfile)): ?>
			<div style="width: 100%; clear: both;" class="clearfix stream">
				<h3 class="title">Recurring</h3>
				<div style="padding: 10px 20px; margin: 0; border-top: 1px solid #222;">
					<dl class="dl-horizontal">
						<dt>Status:</dt>
						<dd><span class="label label-<?=($model->paymentProfile['state'] == 'ActiveProfile') ? 'success':'warning'?>"><?=Tpl::out($model->paymentProfile['state'])?></span></dd>
						<dt>Amount:</dt>
						<dd><?=Tpl::currency($model->paymentProfile['currency'], $model->paymentProfile['amount'])?></dd>
						<dt>Profile:</dt>
						<dd><?=Tpl::out($model->paymentProfile['paymentProfileId'], 'none')?></dd>
						<dt>Billing period:</dt>
						<dd><?=Tpl::out($model->paymentProfile['billingPeriod'], 'none')?></dd>
						<dt>Billing frequency:</dt>
						<dd><?=Tpl::out($model->paymentProfile['billingFrequency'], 'none')?></dd>
						<dt>Billing start date:</dt>
						<dd><?=Tpl::out(Date::getDateTime($model->paymentProfile['billingStartDate'],Date::STRING_FORMAT_YEAR), 'none')?></dd>
						<?php if($model->paymentProfile['billingNextDate'] != $model->paymentProfile['billingStartDate']): ?>
						<dt>Billing next date:</dt>
						<dd><?=Tpl::out(Date::getDateTime($model->paymentProfile['billingNextDate'],Date::STRING_FORMAT_YEAR), 'none')?></dd>
						<?php endif; ?>
					</dl>
				</div>
			</div>
			<?php endif; ?>
			
			<div class="form-actions clearfix" style="clear: both; margin: 15px 0 0 0; border-top-left-radius: 0; border-top-right-radius: 0; border-bottom-right-radius: 0;">
				<img class="pull-right" src="<?=Config::cdn()?>/img/Paypal.logosml.png" />
				<a class="btn" href="/profile">Back to profile</a>
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