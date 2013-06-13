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
<?include'seg/google.tracker.php'?>
</head>
<body id="profile">

	<?include'seg/top.php'?>
	
	<section class="container">
		<h1 class="page-title">
			Profile <small>(<a title="Mailto: <?=Tpl::out(Session::get('email'))?>" href="mailto:<?=Tpl::out(Session::get('email'))?>"><?=Tpl::out(Session::get('displayName'))?></a>)
			</small>
		</h1>
		<div class="content content-dark clearfix">

			<div style="width: 100%;" class="clearfix stream">
				<h3 class="title">Subscription</h3>
				
				<form action="/subscribe" method="post" style="margin: 0; border-top: 1px solid #222;">
					<div class="control-group" style="margin: 10px 20px 20px 20px;">

						<?if(empty($model->subscription)):?>
						<p>You have no active subscriptions. Click the 'Subscribe' button below to get one!</p>
						<?endif;?>
						
						<?if(!empty($model->subscription)):?>
						<dl class="dl-horizontal">
							<dt>Status:</dt>
							<dd>
							<span class="label label-<?=($model->subscription['status'] == 'Active') ? 'success':'warning'?>"><?=Tpl::out($model->subscription['status'])?></span>
							<?php if($model->subscription['recurring']):?>
							<span class="label label-warning" title="This subscription is automatically renewed">Recurring</span>
							<?php endif; ?>
							</dd>
							
							<dt>Created date:</dt>
							<dd><?=Tpl::out(Date::getDateTime($model->subscription['createdDate'],Date::STRING_FORMAT_YEAR))?></dd>
							<dt>End date:</dt>
							<dd><?=Tpl::out(Date::getDateTime($model->subscription['endDate'],Date::STRING_FORMAT_YEAR))?></dd>
							<dt>Time left:</dt>
							<dd><?=Date::getRemainingTime(new \DateTime($model->subscription['endDate']))?></dd>
							<br />
							
							<?php if(!empty($model->paymentProfile)): ?>
							<dt>Billing:</dt>
							<dd><?=Tpl::out($model->paymentProfile['state'])?></dd>
							<dt>Amount:</dt>
							<dd><?=Tpl::currency($model->paymentProfile['currency'], $model->paymentProfile['amount'])?></dd>
							<?if(strcasecmp($model->paymentProfile['state'], 'ActiveProfile')===0):?>
							<dt>Profile:</dt>
							<dd><?=Tpl::out($model->paymentProfile['paymentProfileId'])?></dd>
							<?php endif; ?>
							<dt>Billing Cycle:</dt>
							<dd><?=Tpl::out($model->paymentProfile ['billingCycle'])?></dd>
							<?if(strcasecmp($model->paymentProfile['state'], 'ActiveProfile')===0):?>
							<dt>Billing start date:</dt>
							<dd><?=Tpl::out(Date::getDateTime($model->paymentProfile['billingStartDate'],Date::STRING_FORMAT_YEAR))?></dd>
							<?php if($model->paymentProfile['billingNextDate'] != $model->paymentProfile['billingStartDate']): ?>
							<dt>Billing next date:</dt>
							<dd><?=Tpl::out(Date::getDateTime($model->paymentProfile['billingNextDate'],Date::STRING_FORMAT_YEAR))?></dd>
							<?php endif; ?>
							<?php endif; ?>
							
							<?if(strcasecmp($model->paymentProfile['state'], 'Cancelled')===0):?>
							<dt>&nbsp;</dt>
							<dd><a title="Re-activate this recurring payment" href="/payment/activate">Re-activate</a></dd>
							<?php endif; ?>
							<?if(strcasecmp($model->paymentProfile['state'], 'ActiveProfile')===0):?>
							<dt>&nbsp;</dt>
							<dd><a title="Cancel this recurring payment" href="/payment/cancel">Cancel</a></dd>
							<?php endif; ?>
							<?php endif; ?>
			
						</dl>
						<?php endif; ?>
						
					</div>

					<?if(empty($model->subscription) && empty($model->paymentProfile)):?>
					<div class="form-actions" style="margin: 15px 0 0 0; border-top-left-radius: 0; border-top-right-radius: 0; border-bottom-right-radius: 0;">
						<a href="/subscribe" class="btn btn-primary"><i class="icon-heart icon-white"></i> Subscribe</a>
					</div>
					<?endif;?>

				</form>
			</div>

		</div>
		
		<?php if(!empty($model->orders)): ?>
		<hr size="1" />
		
		<div class="content content-dark clearfix">
			<div style="width: 100%;" class="clearfix stream">
				<h3 class="title">Transactions</h3>
				<div class="control-group" style="margin:0; padding: 20px; border-top: 1px solid #222;">
					<table class="grid" style="width: 100%;">
						<tbody>
							<?php foreach($model->orders as $order): ?>
							<tr class="on">
								<td style="width: 100%;"><a title="See this order invoice" href="/order/invoice?orderId=<?=$order['orderId']?>" style="display: block"><?=Tpl::out($order['orderReference'])?></a></td>
								<td style="text-align: right;"><small class="subtle"><?=Tpl::out($order['description'])?></small></td>
								<td style="text-align: right;"><span style="width: 60px; text-align: center;" class="badge badge-<?=($order['state'] == 'Completed') ? 'inverse':'warning'?>"><?=Tpl::out($order['state'])?></span></td>
							</tr>
							<?php foreach($order['payments'] as $payment): ?>
							<tr class="off">
								<td style="width: 100%;"><?=Tpl::currency($payment['currency'], $payment['amount'])?> - <?=Date::getDateTime($payment['paymentDate'], Date::STRING_FORMAT)?></td>
								<td style="text-align: right;"><small class="subtle">Payment</small></td>
								<td style="text-align: right;"><span style="width: 60px; text-align: center;" class="badge badge-<?=($payment['paymentStatus'] == 'Completed') ? 'inverse':'warning'?>"><?=Tpl::out($payment['paymentStatus'])?></span></td>
							</tr>
							<?php endforeach; ?>
							<?php endforeach; ?>
						</tbody>
					</table>
					
				</div>
			</div>
		</div>
		<?endif;?>
		
		<hr size="1" />
		<div class="content content-dark clearfix">

			<div style="width: 100%;" class="clearfix stream">
				<h3 class="title">Preferences</h3>
				<form id="profileSaveForm" action="/profile/save" method="post" style="margin: 0; border-top: 1px solid #222;">
					<input type="hidden" name="url" value="/league" />
					<fieldset>
						<div class="control-group" style="margin: 10px 20px;">
							<label>Country:</label> 
							<select name="country">
								<option>Select your country</option>
								<?$countries = Utils\Country::getCountries();?>
								
								<option value="">&nbsp;</option>
								<option value="US" <?if(Session::get('country') == 'US'):?>
									selected="selected" <?endif;?>>United States</option>
								<option value="GB" <?if(Session::get('country') == 'GB'):?>
									selected="selected" <?endif;?>>United Kingdom</option>
								<option value="">&nbsp;</option>
								
								<?foreach($countries as $country):?>
								<option value="<?=$country['alpha-2']?>"
									<?if(Session::get('country') != 'US' && Session::get('country') != 'GB' && Session::get('country') == $country['alpha-2']):?>
									selected="selected" <?endif;?>><?=Tpl::out($country['name'])?></option>
								<?endforeach;?>
							</select>
						</div>

						<div class="control-group" style="margin: 10px 20px;">
							<label>Fantasy team:</label>
							<label class="radio">
								<input type="radio" name="teambar_homepage" value="0" <?=(!Service\Settings::get('teambar_homepage')) ? 'checked':''?>>
								Show <u>only</u> on league page
							</label>
							<label class="radio">
								<input type="radio" name="teambar_homepage" value="1" <?=(Service\Settings::get('teambar_homepage')) ? 'checked':''?>>
								Show on home page &amp; league page
							</label>
						</div>

						<div class="control-group" style="margin: 10px 20px;">
							<a href="#" rel="resetteam">Reset team</a>
						</div>

						<div class="form-actions"
							style="margin: 15px 0 0 0; border-top-left-radius: 0; border-top-right-radius: 0; border-bottom-right-radius: 0;">
							<button class="btn" type="submit">Save changes</button>
						</div>
					</fieldset>
				</form>
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