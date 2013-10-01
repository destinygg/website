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
<body id="ordercomplete">

	<?php include Tpl::file('seg/top.php') ?>
	
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
				
					<div class="control-group" style="margin-bottom:0;">
						<p>Your order was successful, The order reference is <span class="label label-inverse">#<?=$model->order['orderId']?></span><br />Please email <a href="mailto:<?=Config::$a['paypal']['support_email']?>"><?=Config::$a['paypal']['support_email']?></a> for any queries or issues.
						<br /><br />Thank you for your support!</p>
					</div>
								
					<div class="subscriptions clearfix">
						<div class="subscription-tier clearfix">
							<div class="subscription" style="width: auto;">
								<h3><?=$model->subscriptionType['tierItemLabel']?></h3>
								<div><span class="sub-amount">$<?=$model->subscriptionType['amount']?></span> (<?=$model->subscriptionType['billingFrequency']?> <?=strtolower($model->subscriptionType['billingPeriod'])?>)</div>
								<?php if($model->subscription['recurring'] == 1): ?>
								<div><i class="icon-ok icon-white"></i> Automatically renew this subscription</div>
								<?php endif; ?>
								
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
	
	<?php include Tpl::file('seg/foot.php') ?>
	<?php include Tpl::file('seg/commonbottom.php') ?>
	
</body>
</html>