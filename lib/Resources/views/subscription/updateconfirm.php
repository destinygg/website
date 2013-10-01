<?
namespace Destiny;
use Destiny\Common\Utils\Tpl;
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
<body id="orderconfirm">

	<?php include Tpl::file('seg/top.php') ?>
	
	<section class="container">
		<h1 class="title">
			<span>Update</span> <small>confirm your selection</small>
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
				<form action="/subscription/update" method="post">
					<input type="hidden" name="subscription" value="<?=$model->subscription['id']?>">

					<div class="clearfix">
					
						<div class="subscriptions pull-left" style="border-right:1px dashed #222;">
							<div class="subscription-tier clearfix">
								<div class="subscription" style="width: auto;">
									<div><span class="label label-warning">FROM</span></div>
									<h2><?=$model->currentSubscriptionType['tierItemLabel']?></h2>
									<div><span class="sub-amount">$<?=$model->currentSubscriptionType['amount']?></span> (<?=$model->currentSubscriptionType['billingFrequency']?> <?=strtolower($model->currentSubscriptionType['billingPeriod'])?>)</div>
								</div>
							</div>
						</div>
						
						<div class="subscriptions pull-left">
							<div class="subscription-tier clearfix">
								<div class="subscription" style="width: auto;">
									<div><span class="label label-success">TO</span></div>
									<h2><?=$model->subscription['tierItemLabel']?></h2>
									<div><span class="sub-amount">$<?=$model->subscription['amount']?></span> (<?=$model->subscription['billingFrequency']?> <?=strtolower($model->subscription['billingPeriod'])?>)</div>
								</div>
							</div>
						</div>
						
					</div>
						
					<div class="control-group">
					
						<?php if($model->currentSubscriptionType['id'] == $model->subscription['id']): ?>
						<?php if($model->currentSubscription['recurring'] == 0): ?>
						<div><i class="icon-ok icon-white"></i> Automatically renew this subscription</div>
						<?php else: ?>
						<div><i class="icon-remove icon-white"></i> Automatically renew this subscription</div>
						<?php endif; ?>
						<?php else: ?>
						<label class="checkbox">
							<input type="checkbox" name="renew" value="1" checked="checked" /> Automatically renew this subscription
						</label>
						<?php endif; ?>
						<br />
						
						<?php if($model->currentSubscriptionType['id'] != $model->subscription['id']): ?>
						<p><strong>NOTE</strong> The remaining time on your current subscription will not be carried over, and your new subscription will begin immediately.</p>
						<?php else: ?>
						<p><strong>NOTE</strong> You are about to change your automatic renewal on your current subscription.</p>
						<?php endif; ?>
						
						<p>
							<span>By clicking the 'Pay subscription' button below, you are confirming that this purchase is what you wanted and that you have read the <a href="/help/agreement">user agreement</a>.</span>
							<br /><a href="#" onclick="$(this).hide().parent().next().show(); return false;">Planning to use eChecks?</a>
						</p>
						<p style="display: none;">
							<span class="label label-warning">NOTE:</span> Those who choose to use the &quot;eCheck&quot; as a payment type, will not receive their subscription
							<br>until the payment has been cleared which can take up to 7 business days.
						</p>
					</div>
					
					<div class="form-actions block-foot">
						<img class="pull-right" title="Powered by Paypal" src="<?=Config::cdn()?>/web/img/Paypal.logosml.png" />
						<button type="submit" class="btn btn-primary btn-large"><i class="icon-shopping-cart icon-white"></i> Pay subscription</button>
						<a href="/subscribe" class="btn btn-link">Cancel</a>
					</div>
				</form>
			</div>
		</div>
	</section>
	
	<?php include Tpl::file('seg/foot.php') ?>
	<?php include Tpl::file('seg/commonbottom.php') ?>
	
</body>
</html>