<?
namespace Destiny;
use Destiny\Utils\Tpl;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($model->title)?></title>
<meta charset="utf-8">
<?include'./tpl/seg/commontop.php'?>
<?include'./tpl/seg/google.tracker.php'?>
</head>
<body id="ordercomplete">

	<?include'./tpl/seg/top.php'?>
	
	<section class="container">
		<h1 class="title">
			<span>Complete</span> <small>successful</small>
		</h1>
		<div class="content content-dark clearfix">
			<div class="ui-step-legend-wrap clearfix">
				<div class="ui-step-legend clearfix">
					<ul>
						<li style="width: 25%;"><a>Select a subscription</a></li>
						<li style="width: 25%;"><a>Confirmation</a></li>
						<li style="width: 25%;"><a>Pay subscription</a></li>
						<li style="width: 25%;" class="active"><a>Complete</a><i class="arrow"></i></li>
					</ul>
				</div>
			</div>
			<div style="width: 100%;" class="clearfix stream">
				<form action="/" method="GET">
					<div class="control-group">
						<p>Your order was successful, The order reference is <span class="label label-inverse">#<?=$model->order['orderId']?></span><br />Please email <a href="mailto:<?=Config::$a['paypal']['support_email']?>"><?=Config::$a['paypal']['support_email']?></a> for any queries or issues.</p>
						<div id="subscriptions">
							<?php $subscription = $model->subscription?>
							<div class="subscription active">
								<strong class="sub-amount">$<?=$subscription['amount']?></strong>
								<span class="sub-label"><?=$subscription['label']?></span>
								<div>
									<?php if(!empty($model->paymentProfile)): ?>
									<?=$subscription['agreement']?>
									<?php else: ?>
									<?=$subscription['billingFrequency']?> <?=strtolower($subscription['billingPeriod'])?> - once-off payment
									<?php endif; ?>
								</div>
							</div>
							<p>Thank you for your support!</p>
						</div>
					</div>
					<div class="form-actions block-foot">
						<img class="pull-right" src="<?=Config::cdn()?>/img/Paypal.logosml.png" />
						<a href="/profile/subscription" class="btn">Back to profile</a>
					</div>
				</form>
			</div>
		</div>
	</section>
	
	<?include'./tpl/seg/foot.php'?>
	<?include'./tpl/seg/commonbottom.php'?>
	
</body>
</html>