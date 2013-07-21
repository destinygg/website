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
<?include'./tpl/seg/commontop.php'?>
<?include'./tpl/seg/google.tracker.php'?>
</head>
<body id="orderconfirm">

	<?include'./tpl/seg/top.php'?>
	
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
				<form action="/order/create" method="post">
					<input type="hidden" name="renew" value="<?=$model->renew?>"> <input type="hidden" name="subscription" value="<?=$model->subscription['id']?>">
					<input type="hidden" name="checkoutId" value="<?=$model->checkoutId?>">
					<div class="control-group">
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
							
							<p>
								<span class="label label-warning">NOTE:</span> Those who choose to use the &quot;eCheck&quot; as a payment type, will not receive their subscription
								<br>until the payment has been cleared which can take up to 7 business days. 
							</p>
							
							<p>By clicking the 'Pay subscription' button below, you are confirming that this purchase is what you wanted and that you have read the <a href="/help/agreement">user agreement</a>.</p>
						</div>
					</div>
					<div class="form-actions block-foot">
						<img class="pull-right" title="Powered by Paypal" src="<?=Config::cdn()?>/web/img/Paypal.logosml.png" />
						<button type="submit" class="btn btn-primary"><i class="icon-shopping-cart icon-white"></i> Pay subscription</button>
						<a href="/subscribe" class="btn">Cancel</a>
					</div>
				</form>
			</div>
		</div>
	</section>
	
	<?include'./tpl/seg/foot.php'?>
	<?include'./tpl/seg/commonbottom.php'?>
	
</body>
</html>