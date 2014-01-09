<?
namespace Destiny;
use Destiny\Common\Utils\Tpl;
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
	<?php include Tpl::file('seg/headerband.php') ?>
	
	<section class="container">
		<div class="row">
			<div class="span4">
				<div class="subfeature">
					<h1>Standard Tier I</h1>
					<p>Get access to chat features and be eligible for future subscriber events!</p>
					<div class="subfeature-options clearfix">
						<div class="subfeature-t1">
							<?php $sub = $model->subscriptions['1-MONTH-SUB']?>
							<form action="<?=$model->formAction?>" method="post">
								<input type="hidden" name="subscription" value="<?=$sub['id']?>" />
								<div class="subfeature-info">
									<div class="subfeature-price">$<?=floatval($sub['amount'])?></div>
									<span>for  <?=$sub['billingFrequency']?> <?=strtolower($sub['billingPeriod'])?></span>
								</div>
								<button type="submit" class="btn btn-primary">Choose</button>
							</form>
						</div>
						<div class="subfeature-t2">
							<?php $sub = $model->subscriptions['3-MONTH-SUB']?>
							<form action="<?=$model->formAction?>" method="post">
								<input type="hidden" name="subscription" value="<?=$sub['id']?>" />
								<div class="subfeature-info">
									<div class="subfeature-price">$<?=floatval($sub['amount'])?></div>
									<span>for  <?=$sub['billingFrequency']?> <?=strtolower($sub['billingPeriod'])?></span>
								</div>
								<button type="submit" class="btn btn-primary">Choose</button>
							</form>
						</div>
					</div>
				</div>
			</div>
			<div class="span4">
				<div class="subfeature">
					<h1>Premium Tier II</h1>
					<p>Got a bit more to contribute? Probably the best investment of all time.</p>
					<div class="subfeature-options clearfix">
						<div class="subfeature-t1">
							<?php $sub = $model->subscriptions['1-MONTH-SUB2']?>
							<form action="<?=$model->formAction?>" method="post">
								<input type="hidden" name="subscription" value="<?=$sub['id']?>" />
								<div class="subfeature-info">
									<div class="subfeature-price">$<?=floatval($sub['amount'])?></div>
									<span>for  <?=$sub['billingFrequency']?> <?=strtolower($sub['billingPeriod'])?></span>
								</div>
								<button type="submit" class="btn btn-primary">Choose</button>
							</form>
						</div>
						<div class="subfeature-t2">
							<?php $sub = $model->subscriptions['3-MONTH-SUB2']?>
							<form action="<?=$model->formAction?>" method="post">
								<input type="hidden" name="subscription" value="<?=$sub['id']?>" />
								<div class="subfeature-info">
									<div class="subfeature-price">$<?=floatval($sub['amount'])?></div>
									<span>for  <?=$sub['billingFrequency']?> <?=strtolower($sub['billingPeriod'])?></span>
								</div>
								<button type="submit" class="btn btn-primary">Choose</button>
							</form>
						</div>
					</div>
				</div>
			</div>
			<div class="span4">
				<div class="subfeature">
					<h1>Pro Tier III</h1>
					<p>Wow such value so prestige you should purchase it immediately.</p>
					<div class="subfeature-options clearfix">
						<div class="subfeature-t1">
							<?php $sub = $model->subscriptions['1-MONTH-SUB3']?>
							<form action="<?=$model->formAction?>" method="post">
								<input type="hidden" name="subscription" value="<?=$sub['id']?>" />
								<div class="subfeature-info">
									<div class="subfeature-price">$<?=floatval($sub['amount'])?></div>
									<span>for  <?=$sub['billingFrequency']?> <?=strtolower($sub['billingPeriod'])?></span>
								</div>
								<button type="submit" class="btn btn-primary">Choose</button>
							</form>
						</div>
						<div class="subfeature-t2">
							<?php $sub = $model->subscriptions['3-MONTH-SUB3']?>
							<form action="<?=$model->formAction?>" method="post">
								<input type="hidden" name="subscription" value="<?=$sub['id']?>" />
								<div class="subfeature-info">
									<div class="subfeature-price">$<?=floatval($sub['amount'])?></div>
									<span>for  <?=$sub['billingFrequency']?> <?=strtolower($sub['billingPeriod'])?></span>
								</div>
								<button type="submit" class="btn btn-primary">Choose</button>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
			
	</section>
	
	<?php include Tpl::file('seg/foot.php') ?>
	<?php include Tpl::file('seg/commonbottom.php') ?>
	
</body>
</html>