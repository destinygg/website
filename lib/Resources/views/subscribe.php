<?
namespace Destiny;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\User\UserRole;
use Destiny\Common\Session;
use Destiny\Common\Config;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($model->title)?></title>
<meta charset="utf-8">
<?php include Tpl::file('seg/commontop.php') ?>
<?php include Tpl::file('seg/google.tracker.php') ?>
</head>
<body id="subscribe">

	<?php include Tpl::file('seg/top.php') ?>
	
	
	<?php if (!Session::hasRole ( UserRole::USER )): ?>
	
	<section class="container">
		<h1 class="title">
			<span>Subscribe</span> <small>become one of the brave</small>
		</h1>
		<div class="content content-dark clearfix">
			<div class="control-group">
				<p>Want a destiny.gg subscription?<br />You need to <a href="/login?follow=subscribe">login first</a> first!</p>
			</div>

			<div class="subscriptions clearfix">
				
				<div class="subscription-tier clearfix">
					<div class="subscription" style="width:auto;">
						<h2>Standard Tier I</h2>
						<p>Get access to chat features and be eligable for future subscriber events!</p>
						<?php $sub = $model->subscriptions['1-MONTH-SUB']?>
						<span data-subscription="<?=$sub['id']?>" class="btn btn-large btn-primary disabled">$<?=$sub['amount']?> (<?=$sub['billingFrequency']?> <?=strtolower($sub['billingPeriod'])?>)</span>
						<?php $sub = $model->subscriptions['3-MONTH-SUB']?>
						<span data-subscription="<?=$sub['id']?>" class="btn btn-large btn-primary disabled">$<?=$sub['amount']?> (<?=$sub['billingFrequency']?> <?=strtolower($sub['billingPeriod'])?>)</span>
					</div>
				</div>
				
				<div class="subscription-tier clearfix">
					<div class="subscription" style="width:auto;">
						<h2>Premium Tier II</h2>
						<p>Got a bit more to contribute? Same as tier I but more awesome</p>
						<?php $sub = $model->subscriptions['1-MONTH-SUB2']?>
						<span data-subscription="<?=$sub['id']?>" class="btn btn-large btn-primary disabled">$<?=$sub['amount']?> (<?=$sub['billingFrequency']?> <?=strtolower($sub['billingPeriod'])?>)</span>
						<?php $sub = $model->subscriptions['3-MONTH-SUB2']?>
						<span data-subscription="<?=$sub['id']?>" class="btn btn-large btn-primary disabled">$<?=$sub['amount']?> (<?=$sub['billingFrequency']?> <?=strtolower($sub['billingPeriod'])?>)</span>
					</div>
				</div>
				
			</div>
				
			<div class="form-actions block-foot">
				<a href="/login?follow=subscribe" class="btn btn-link">Login &amp; subscribe</a>
			</div>
		</div>
	</section>
	
	<?php else: ?>
	
	<section class="container">
		<h1 class="title">
			<span>Subscribe</span> <small>make your selection</small>
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
				<form action="/order/confirm" method="post">
					<input type="hidden" name="subscription" value="" />
					<div class="subscriptions clearfix">
						
						<div class="subscription-tier clearfix">
							<div class="subscription" style="width:auto;">
								<h2>Standard Tier I</h2>
								<p>Get access to chat features and be eligable for future subscriber events!</p>
								<?php $sub = $model->subscriptions['1-MONTH-SUB']?>
								<button data-subscription="<?=$sub['id']?>" class="btn btn-large btn-primary">$<?=$sub['amount']?> (<?=$sub['billingFrequency']?> <?=strtolower($sub['billingPeriod'])?>)</button>
								<?php $sub = $model->subscriptions['3-MONTH-SUB']?>
								<button data-subscription="<?=$sub['id']?>" class="btn btn-large btn-primary">$<?=$sub['amount']?> (<?=$sub['billingFrequency']?> <?=strtolower($sub['billingPeriod'])?>)</button>
							</div>
						</div>
						
						<div class="subscription-tier clearfix">
							<div class="subscription" style="width:auto;">
								<h2>Premium Tier II</h2>
								<p>Got a bit more to contribute? Same as tier I but more awesome</p>
								<?php $sub = $model->subscriptions['1-MONTH-SUB2']?>
								<button data-subscription="<?=$sub['id']?>" class="btn btn-large btn-primary">$<?=$sub['amount']?> (<?=$sub['billingFrequency']?> <?=strtolower($sub['billingPeriod'])?>)</button>
								<?php $sub = $model->subscriptions['3-MONTH-SUB2']?>
								<button data-subscription="<?=$sub['id']?>" class="btn btn-large btn-primary">$<?=$sub['amount']?> (<?=$sub['billingFrequency']?> <?=strtolower($sub['billingPeriod'])?>)</button>
							</div>
						</div>
						
					</div>
					<div class="form-actions block-foot">
						<img class="pull-right" title="Powered by Paypal" src="<?=Config::cdn()?>/web/img/Paypal.logosml.png" />
						<a href="/profile" class="btn btn-link">Back to profile</a>
					</div>
				</form>
			</div>
		</div>
	</section>
	
	<?php endif; ?>
	
	<?php include Tpl::file('seg/foot.php') ?>
	<?php include Tpl::file('seg/commonbottom.php') ?>
	
	<?php if (Session::hasRole ( UserRole::USER )): ?>
	<script>
	$('.subscription').on('click', 'button', function(){
		$('input[name="subscription"]').val($(this).data('subscription'));
		$(this).closest('form').submit();
		return false;
	});
	</script>
	<?php endif; ?>
	
</body>
</html>