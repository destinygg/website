<?
namespace Destiny;
use Destiny\Utils\Tpl;
use Destiny\Utils\Date;
use Destiny\Session;
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
<body id="subscription" class="profile">

	<?include'./tpl/seg/top.php'?>
	
	<section class="container">
		<h1 class="page-title">
			Profile 
			<small><a><?=Tpl::out($model->user['username'])?></a></small>
		</h1>
		<div style="margin:20px 0 0 0;" class="navbar navbar-inverse navbar-subnav">
			<div class="navbar-inner">
				<ul class="nav">
					<li><a href="/profile" title="Your personal details">Details</a></li>
					<?php if(Session::hasRole(\Destiny\UserRole::ADMIN)): ?>
					<li class="active"><a href="/profile/subscription" title="Your subscriptions">Subscription</a></li>
					<?php endif; ?>
					<li><a href="/profile/authentication" title="Your login methods">Authentication</a></li>
				</ul>
			</div>
		</div>
	</section>
		
	<section class="container">

		<?php if(empty($model->subscription)): ?>
		<h3>Subscription</h3>
		<div class="content content-dark clearfix">
			<div style="width: 100%;" class="clearfix stream">
				<form action="/subscribe" method="post">
					<div class="control-group">
						<p>You have no active subscriptions. Click the 'Subscribe' button below to get one!</p>
					</div>
					<div class="form-actions block-foot">
						<a href="/subscribe" class="btn btn-primary"><i class="icon-heart icon-white"></i> Subscribe</a>
					</div>
				</form>
			</div>
		</div>
		<br>
		<?php endif; ?>
		
		<?if(!empty($model->subscription)):?>
		<h3>Subscription</h3>
		<div class="content content-dark clearfix">
			<div style="width: 100%;" class="clearfix stream">
				<form action="/subscribe" method="post">
					<div class="control-group">
						<dl class="dl-horizontal">
							<dt>Status:</dt>
							<dd>
							<span class="label label-<?=($model->subscription['status'] == 'Active') ? 'success':'warning'?>"><?=Tpl::out($model->subscription['status'])?></span>
							<?php if($model->subscription['recurring']):?>
							<span class="label label-warning" title="This subscription is automatically renewed">Recurring</span>
							<?php endif; ?>
							</dd>
							
							<dt>Source:</dt>
							<dd><?=Tpl::out($model->subscription['subscriptionSource'])?></dd>
							<dt>Created date:</dt>
							<dd><?=Tpl::moment(Date::getDateTime($model->subscription['createdDate']), Date::STRING_FORMAT_YEAR)?></dd>
							<dt>End date:</dt>
							<dd><?=Tpl::moment(Date::getDateTime($model->subscription['endDate']), Date::STRING_FORMAT_YEAR)?></dd>
							<dt>Time left:</dt>
							<dd><?=Date::getRemainingTime(Date::getDateTime($model->subscription['endDate']))?></dd>
							
							<?php if(Session::hasRole(\Destiny\UserRole::ADMIN)): ?>
							<dt>&nbsp;</dt>
							<dd><a title="Cancel this subscription" onclick="if(confirm('Are you sure? this cannot be undone.')) window.location.href = '/subscription/cancel';" href="#">Cancel subscription</a></dd>
							<?php endif; ?>
							
							<?php if(!empty($model->paymentProfile)): ?>
								<br />
								<dt>Billing:</dt>
								<dd><?=Tpl::out($model->paymentProfile['state'])?></dd>
								<dt>Amount:</dt>
								<dd><?=Tpl::currency($model->paymentProfile['currency'], $model->paymentProfile['amount'])?></dd>
								
								<?if(strcasecmp($model->paymentProfile['state'], 'ActiveProfile')===0):?>
								<dt>Profile:</dt>
								<dd><?=Tpl::mask($model->paymentProfile['paymentProfileId'])?></dd>
								<?php endif; ?>
								
								<dt>Billing Cycle:</dt>
								<dd><?=Tpl::out($model->paymentProfile ['billingCycle'])?></dd>
								
								<?if(strcasecmp($model->paymentProfile['state'], 'ActiveProfile')===0):?>
								
								<?php 
								$billingNextDate = Date::getDateTime($model->paymentProfile['billingNextDate']);
								$billingStartDate = Date::getDateTime($model->paymentProfile['billingStartDate']);
								?>
							
								<dt>Billing start date:</dt>
								<dd><?=Tpl::moment($billingStartDate, Date::STRING_FORMAT_YEAR)?></dd>
								<?php if($billingNextDate != $billingNextDate): ?>
								<dt>Billing next date:</dt>
								<dd><?=Tpl::moment($billingNextDate, Date::STRING_FORMAT_YEAR)?></dd>
								<?php endif; ?>
								<?php endif; ?>
								
								<?if(strcasecmp($model->paymentProfile['state'], 'Cancelled')===0):?>
								<dt>&nbsp;</dt>
								<dd><a title="Re-activate this recurring payment" href="/payment/activate">Re-activate payment</a></dd>
								<?php endif; ?>
								
								<?if(strcasecmp($model->paymentProfile['state'], 'ActiveProfile')===0):?>
								<dt>&nbsp;</dt>
								<dd><a title="Cancel this recurring payment" href="/payment/cancel">Cancel payment</a></dd>
								<?php endif; ?>
							
							<?php endif; ?>
						</dl>
					</div>
				</form>
			</div>
		</div>
		<br>
		<?php endif; ?>
		
		<?php if(!empty($model->payments)): ?>
		<h3>Payments</h3>
		<div class="content content-dark clearfix">
			<div style="width: 100%;" class="clearfix stream">
				<table class="grid">
					<tbody>
						<?php foreach($model->payments as $payment): ?>
						<tr>
							<td style="width: 100%;">
								<a title="Payment details" href="/payment/details/<?=$payment['paymentId']?>"><?=substr($payment['transactionId'], 0, 8)?></a>
								<span> - </span>
								<span><?=Tpl::currency($payment['currency'], $payment['amount'])?></span>
								<small class="subtle">on <?=Tpl::moment(Date::getDateTime($payment['paymentDate']),Date::STRING_FORMAT)?></small>
							</td>
							<td style="text-align: right;"><small class="subtle">Payment</small></td>
							<td style="text-align: right;"><span style="width: 60px; text-align: center;" class="badge badge-<?=($payment['paymentStatus'] == 'Completed') ? 'inverse':'warning'?>"><?=Tpl::out($payment['paymentStatus'])?></span></td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
		<?endif;?>
			
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