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
				<h3 class="title">Subscription 
				<?if(!empty($model->subscription)):?>
				<span class="label label-success" title="Active time remaining: <?=Date::getRemainingTime(new \DateTime($model->subscription['endDate']))?>">Active</span>
				<?else:?>
				<span class="label label-inverse" title="No subscriptions">Inactive</span>
				<?endif;?>
				</h3>
				<form action="/subscribe" method="post" style="margin: 0; border-top: 1px solid #222;">
					<fieldset>
						<div class="control-group" style="margin: 10px 20px 20px 20px;">
						
							<?if(empty($model->subscription)):?>
							<p>You have no active subscriptions</p>
							<?endif;?>
							
							<?if(!empty($model->subscription)):?>
							<p>Your subscription will expire on <?=Date::getDateTime($model->subscription['endDate'], Date::STRING_FORMAT)?></p>
							<?endif;?>
							
							<?php if(count($model->orders)>0): ?>
							<table class="grid" style="width: 100%; margin-bottom: 20px;">
								<thead>
									<tr>
										<td style="width: 100%;">Transactions</td>
										<td></td>
										<td>Status</td>
									</tr>
								</thead>
								<tbody>
									<?php foreach($model->orders as $order): ?>
									<tr class="on">
										<td><a title="See this order invoice" href="/order/invoice?orderId=<?=$order['orderId']?>" style="display: block"><?=Tpl::out($order['orderReference'])?></a></td>
										<td style="text-align: right;"><small class="subtle"><?=Tpl::out($order['description'])?></small></td>
										<td style="text-align: right;"><span style="width: 60px; text-align: center;" class="badge badge-<?=($order['state'] == 'Completed') ? 'inverse':'warning'?>"><?=Tpl::out($order['state'])?></span></td>
									</tr>
									<?php foreach($order['payments'] as $payment): ?>
									<tr class="off">
										<td><?=Tpl::currency($payment['currency'], $payment['amount'])?> - <?=Date::getDateTime($payment['paymentDate'], Date::STRING_FORMAT)?></td>
										<td style="text-align: right;"><small class="subtle">Payment</small></td>
										<td style="text-align: right;"><span style="width: 60px; text-align: center;" class="badge badge-<?=($payment['paymentStatus'] == 'Completed') ? 'inverse':'warning'?>"><?=Tpl::out($payment['paymentStatus'])?></span></td>
									</tr>
									<?php endforeach; ?>
									<?php endforeach; ?>
								</tbody>
							</table>
							<?endif;?>
							
						</div>

						<div class="form-actions" style="margin: 15px 0 0 0; border-top-left-radius: 0; border-top-right-radius: 0; border-bottom-right-radius: 0;">
							<?if(!empty($model->subscription)):?>
							<a href="/unsubscribe" class="btn btn-danger"><i class="icon-remove icon-white"></i> Cancel subscription</a>\
							<?else:?>
							<a href="/subscribe" class="btn btn-primary"><i class="icon-shopping-cart icon-white"></i> Subscribe</a>
							<?endif;?>
						</div>

					</fieldset>
				</form>
			</div>

		</div>
	</section>

	<section class="container">
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