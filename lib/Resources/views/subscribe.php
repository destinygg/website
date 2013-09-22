<?
namespace Destiny;
use Destiny\Common\Utils\Tpl;
use Destiny\User\UserRole;
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
	
	<?php if (Session::hasRole ( UserRole::USER )): ?>
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
				<form action="/order/confirm" method="post">
					<input type="hidden" name="checkoutId" value="<?=$model->checkoutId?>" />
					<div class="control-group">
						<?php if(!empty($model->subscription)): ?>
						<p>
							<span class="label label-inverse">HMMM</span> 
							You already have an active subscription. 
							<br>Click the button below to go to	your profile.
						</p>
						<?php endif; ?>
						
						<?php if(empty($model->subscription)): ?>
						
						<br>
						<div id="subscriptions" class="clearfix">
							<h4>Tier I Subscriptions</h4>
							<hr size="1" style="margin:10px 0;">
							<div id="tier-one" class="clearfix">
								<?php $sub = $model->subscriptions['1-MONTH-SUB']?>
								<div class="subscription active pull-left" style="width:300px;">
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
											Once-off payment for 1 month
										</label>
									</div>
								</div>
								<?php $sub = $model->subscriptions['3-MONTH-SUB']?>
								<div class="subscription pull-left">
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
											Once-off payment for 3 months
										</label>
									</div>
								</div>
							</div>
							
							<br>
							<h4>Tier II Subscriptions</h4>
							<hr size="1" style="margin:10px 0;">
							<div id="tier-one" class="clearfix">
								
								<?php $sub = $model->subscriptions['1-MONTH-SUB2']?>
								<div class="subscription pull-left" style="width:300px;">
									<label class="radio">
										<input type="radio" name="subscription" value="<?=$sub['id']?>">
										<strong class="sub-amount">$<?=$sub['amount']?></strong>
										<span class="sub-label"><?=$sub['label']?></span>
									</label>
									<div class="payment-options">
										<label class="radio">
											<input type="radio" name="renew" value="1">
											Renew each months
										</label>
										<label class="radio">
											<input type="radio" name="renew" value="0">
											Once-off payment for 1 month
										</label>
									</div>
								</div>
								<?php $sub = $model->subscriptions['3-MONTH-SUB2']?>
								<div class="subscription pull-left">
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
											Once-off payment for 3 months
										</label>
									</div>
								</div>
							</div>
						</div>
						
						
						<?php endif; ?>
						
					</div>
					<div class="form-actions block-foot">
						<img class="pull-right" title="Powered by Paypal" src="<?=Config::cdn()?>/web/img/Paypal.logosml.png" />
						<?php if(empty($model->subscription)): ?>
						<button type="submit" class="btn btn-primary"><i class="icon-check icon-white"></i> Confirm selection</button>
						<a href="/profile/subscription" class="btn">Back to profile</a>
						<?php else: ?>
						<a href="/profile/subscription" class="btn">Back to profile</a>
						<?php endif; ?>
					</div>
				</form>
			</div>
		</div>
	</section>
	<?php else: ?>
	
	<section class="container">
		<h1 class="title">
			<span>Subscribe</span> <small>become one of the brave</small>
		</h1>
		<div class="content content-dark clearfix">
			<div class="control-group">
				<p>Want a destiny.gg subscription?<br />You need to <a href="/login?follow=subscribe">create an account</a> or <a href="/login?follow=subscribe">login first</a> first!</p>
			</div>
			<div class="form-actions block-foot">
				<a href="/login?follow=subscribe" class="btn btn-large btn-primary">Login</a>
				<a href="/login?follow=subscribe" class="btn btn-large">Create an Account</a>
			</div>
		</div>
	</section>
	
	<?php endif; ?>
	
	<?php include Tpl::file('seg/foot.php') ?>
	<?php include Tpl::file('seg/commonbottom.php') ?>
	
</body>
</html>