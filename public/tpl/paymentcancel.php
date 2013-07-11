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
<?include'./tpl/seg/commontop.php'?>
<?include'./tpl/seg/google.tracker.php'?>
</head>
<body id="paymentcancel">

	<?include'./tpl/seg/top.php'?>
	
	<section class="container">
		<h1 class="title">
			<span>Cancel</span> <small>scheduled payment</small>
		</h1>
		<div class="content content-dark clearfix">
			<div style="width: 100%;" class="clearfix stream">
			
				<?php if(!$model->unsubscribed): ?>
				<form action="/payment/cancel" method="post">
					<input type="hidden" name="confirmationId" value="<?=$model->confirmationId?>" />
					<div class="control-group">
						<p>
							You are about to cancel the scheduled payments for your active subscription.
							<br>This can be re-activated from your profile at any time.
						</p>
						
						<dl class="dl-horizontal">
							<dt>Status:</dt>
							<dd><span class="label label-<?=($model->paymentProfile['state'] == 'ActiveProfile') ? 'success':'warning'?>"><?=Tpl::out($model->paymentProfile['state'])?></span></dd>
							<dt>Amount:</dt>
							<dd><?=Tpl::currency($model->paymentProfile['currency'], $model->paymentProfile['amount'])?></dd>
							<dt>Profile:</dt>
							<dd><?=Tpl::out($model->paymentProfile['paymentProfileId'], 'none')?></dd>
							<dt>Billing Cycle:</dt>
							<dd><?=Tpl::out($model->paymentProfile ['billingCycle'])?></dd>
							<dt>Billing start date:</dt>
							<dd><?=Tpl::out(Date::getDateTime($model->paymentProfile['billingStartDate'])->format(Date::STRING_FORMAT_YEAR), 'none')?></dd>
							<?php if($model->paymentProfile['billingNextDate'] != $model->paymentProfile['billingStartDate']): ?>
							<dt>Billing next date:</dt>
							<dd><?=Tpl::out(Date::getDateTime($model->paymentProfile['billingNextDate'])->format(Date::STRING_FORMAT_YEAR), 'none')?></dd>
							<?php endif; ?>
						</dl>
						
						<p>
							<span class="label label-inverse">NOTE</span> This does not affect your current subscription status. 
							<br>Your subscription will expire at the end of its duration. 
						</p>
					</div>
					<div class="form-actions block-foot">
						<img class="pull-right" title="Powered by Paypal" src="<?=Config::cdn()?>/web/img/Paypal.logosml.png" />
						<button type="submit" class="btn btn-danger">Cancel scheduled payment</button>
						<a href="/profile/subscription" class="btn">Back to profile</a>
					</div>
				</form>
				<?php endif; ?>
				
				<?php if($model->unsubscribed): ?>
				<div class="control-group">
					<p>
						Your scheduled payment has been cancelled.
						<br>Thank you for your support!
					</p>
				</div>
				<div class="form-actions block-foot">
					<img class="pull-right" title="Powered by Paypal" src="<?=Config::cdn()?>/web/img/Paypal.logosml.png" />
					<a href="/profile/subscription" class="btn">Go back to profile</a>
				</div>
				<?php endif; ?>
				
			</div>
		</div>
	</section>
	
	<?include'./tpl/seg/foot.php'?>
	<?include'./tpl/seg/commonbottom.php'?>
	
</body>
</html>