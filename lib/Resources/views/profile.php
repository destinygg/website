<?
namespace Destiny;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Utils\Country;
use Destiny\Common\Utils\Date;
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
<body id="profile">

	<?php include Tpl::file('seg/top.php') ?>
	
	<section class="container">
		<div class="navbar navbar-inverse navbar-subnav">
			<div class="navbar-inner">
				<ul class="nav pull-left">
					<li class="active"><a href="/profile" title="Your personal details">Details</a></li>
					<li><a href="/profile/games" title="Your games">Games</a></li>
				</ul>
				<ul class="nav pull-right">
					<li><a href="/profile/authentication" title="Your login methods">Authentication</a></li>
				</ul>
			</div>
		</div>
	</section>
		
	<?php if(!empty($model->error)): ?>
	<section class="container">
		<div class="alert alert-error" style="margin:0;">
			<strong>Error!</strong>
			<?=Tpl::out($model->error)?>
		</div>
	</section>
	<?php endif; ?>
		
	<?php if(!empty($model->success)): ?>
	<section class="container">
		<div class="alert alert-info" style="margin:0;">
			<strong>Success!</strong>
			<?=Tpl::out($model->success)?>
		</div>
	</section>
	<?php endif; ?>
	
	<section class="container collapsible">
		<h3><i class="icon-plus-sign icon-white"></i> Subscription</h3>
		
		<?if(!empty($model->subscription) && !empty($model->subscriptionType)):?>
		<div class="content content-dark clearfix">

			<div class="subscriptions clearfix">
				<div class="subscription" style="width: auto;">
				
					<div><?=$model->subscriptionType['tierItemLabel']?></div>
					<div><span class="sub-amount">$<?=$model->subscriptionType['amount']?></span> (<?=$model->subscriptionType['billingFrequency']?> <?=strtolower($model->subscriptionType['billingPeriod'])?>)</div>

					<?php if($model->subscription['recurring'] == 0): ?>
					<br />
					<dl>
						<dt>Remaining time</dt>
						<dd><?=Date::getRemainingTime(Date::getDateTime($model->subscription['endDate']))?></dd>
					</dl> 
					<?php endif; ?>
					
					<?if(strcasecmp($model->paymentProfile['state'], 'ActiveProfile')===0):?>
					<dl>
						<dt>Time remaining until renewal</dt>
						<dd><?=Date::getRemainingTime(Date::getDateTime($model->subscription['endDate']))?></dd>
					</dl> 
					<dl>
						<?php 
						$billingNextDate = Date::getDateTime($model->paymentProfile['billingNextDate']);
						$billingStartDate = Date::getDateTime($model->paymentProfile['billingStartDate']);
						?>
						<dt>Next billing date</dt>
						<?php if($billingNextDate > $billingStartDate): ?>
						<dd><?=Tpl::moment($billingNextDate, Date::STRING_FORMAT_YEAR)?></dd>
						<?php else: ?>
						<dd><?=Tpl::moment($billingStartDate, Date::STRING_FORMAT_YEAR)?></dd>
						<?php endif; ?>
					</dl>
					<?php endif; ?>
					
				</div>
			</div>
		
			<div class="form-actions block-foot" style="margin-top:0;">
				<a class="btn btn-large btn-primary" href="/subscribe">Update</a>
				<a class="btn btn-link" href="/subscription/cancel">Cancel subscription</a>
			</div>
		
		</div>
		<?php else: ?>
		<div class="content content-dark clearfix">
			<div class="control-group">Not subscribed? <a title="Subscribe" href="/subscribe">Try it out</a></div>
		</div>
		<?php endif; ?>
		
	</section>
	
	<section class="container collapsible">
		<h3><i class="icon-plus-sign icon-white"></i> Account</h3>
		
		<div class="content content-dark clearfix">
			<div style="width: 100%;" class="clearfix stream">
				<form id="profileSaveForm" action="/profile/update" method="post">
					
					<?php if($model->user['nameChangedCount'] < Config::$a['profile']['nameChangeLimit']): ?>
					<div class="control-group">
						<label>Username:
						<br><small style="opacity:0.5;">(You have <?=Tpl::n(Config::$a['profile']['nameChangeLimit'] - $model->user['nameChangedCount'])?> name changes left)</small>
						</label> 
						<input class="input-xlarge" type="text" name="username" value="<?=Tpl::out($model->user['username'])?>" placeholder="Username" />
						<span class="help-block">A-z 0-9 and underscores. Must contain at least 3 and at most 20 characters</span>
					</div>
					<?php endif; ?>
					
					<?php if($model->user['nameChangedCount'] >= Config::$a['profile']['nameChangeLimit']): ?>
					<div class="control-group">
						<label>Username:
						<br><small style="opacity:0.5;">(You have no more name changes available)</small>
						</label> 
						<input class="input-xlarge" type="text" disabled="disabled" name="username" value="<?=Tpl::out($model->user['username'])?>" placeholder="Username" />
					</div>
					<?php endif; ?>
					
					<div class="control-group">
						<label>Email:
						<br><small style="opacity:0.5;">Be it valid or not, it will be safe with us.</small>
						</label> 
						<input class="input-xlarge" type="text" name="email" value="<?=Tpl::out($model->user['email'])?>" placeholder="Email" />
					</div>
					
					<div class="control-group">
						<label>Nationality:
						<br><small style="opacity:0.5;">The country you indentify with</small>
						</label> 
						<select class="input-xlarge" name="country">
							<option value="">Select your country</option>
							<?$countries = Country::getCountries();?>
							<option value="">&nbsp;</option>
							<option value="US" <?if($model->user['country'] == 'US'):?>
								selected="selected" <?endif;?>>United States</option>
							<option value="GB" <?if($model->user['country'] == 'GB'):?>
								selected="selected" <?endif;?>>United Kingdom</option>
							<option value="">&nbsp;</option>
							<?foreach($countries as $country):?>
							<option value="<?=$country['alpha-2']?>"<?if($model->user['country'] != 'US' && $model->user['country'] != 'GB' && $model->user['country'] == $country['alpha-2']):?>selected="selected" <?endif;?>><?=Tpl::out($country['name'])?></option>
							<?endforeach;?>
						</select>
					</div>
		
					<div class="form-actions block-foot">
						<button class="btn btn-large btn-primary" type="submit">Save details</button>
					</div>
					
				</form>
			</div>
		</div>
	</section>
	
	<section class="container collapsible">
		<h3><i class="icon-plus-sign icon-white"></i> Address <small>(optional)</small></h3>
		
		<div class="content content-dark clearfix">
			<div style="width: 100%;" class="clearfix stream">
				<form id="addressSaveForm" action="/profile/address/update" method="post">
				
					<div class="control-group">
						<i class="icon-info-sign icon-white"></i> All fields are required
					</div>
					
					<div class="control-group">
						<label>Full Name:
						<br><small style="opacity:0.5;">The name of the person for this address</small>
						</label>
						<input class="input-xlarge" type="text" name="fullName" value="<?=Tpl::out($model->address['fullName'])?>" placeholder="Full Name" />
					</div>
					<div class="control-group">
						<label>Address Line 1:
						<br><small style="opacity:0.5;">Street address, P.O box, company name, c/o</small>
						</label>
						<input class="input-xlarge" type="text" name="line1" value="<?=Tpl::out($model->address['line1'])?>" placeholder="Address Line 1" />
					</div>
					<div class="control-group">
						<label>Address Line 2:
						<br><small style="opacity:0.5;">Apartment, Suite, Building, Unit, Floor etc.</small>
						</label>
						<input class="input-xlarge" type="text" name="line2" value="<?=Tpl::out($model->address['line2'])?>" placeholder="Address Line 2" />
					</div>
				
					<div class="control-group">
						<label>City:</label>
						<input class="input-xlarge" type="text" name="city" value="<?=Tpl::out($model->address['city'])?>" placeholder="City" />
					</div>
					<div class="control-group">
						<label>State/Province/Region:</label>
						<input class="input-xlarge" type="text" name="region" value="<?=Tpl::out($model->address['region'])?>" placeholder="Region" />
					</div>
					<div class="control-group">
						<label>ZIP/Postal Code:</label>
						<input class="input-xlarge" type="text" name="zip" value="<?=Tpl::out($model->address['zip'])?>" placeholder="Zip/Postal Code" />
					</div>
					<div class="control-group">
						<label>Country:</label> 
						<select class="input-xlarge" name="country">
							<option value="">Select your country</option>
							<?$countries = Country::getCountries();?>
							<option value="">&nbsp;</option>
							<option value="US" <?if($model->address['country'] == 'US'):?>
								selected="selected" <?endif;?>>United States</option>
							<option value="GB" <?if($model->address['country'] == 'GB'):?>
								selected="selected" <?endif;?>>United Kingdom</option>
							<option value="">&nbsp;</option>
							<?foreach($countries as $country):?>
							<option value="<?=$country['alpha-2']?>"<?if($model->address['country'] != 'US' && $model->address['country'] != 'GB' && $model->address['country'] == $country['alpha-2']):?>selected="selected" <?endif;?>><?=Tpl::out($country['name'])?></option>
							<?endforeach;?>
						</select>
					</div>
						
					<div class="form-actions block-foot">
						<button class="btn btn-large btn-primary" type="submit">Save address</button>
					</div>
					
				</form>
			</div>
		</div>
	</section>
	
	<br />
	
	<?php include Tpl::file('seg/foot.php') ?>
	<?php include Tpl::file('seg/commonbottom.php') ?>
	<script src="<?=Config::cdnv()?>/vendor/jquery.validate/jquery.validate.min.js"></script>
	<script>
	$(function(){
		$('form#addressSaveForm').validate({
			rules: {
				fullName : { required: true },
				line1    : { required: true },
				line2    : { required: true },
				city     : { required: true },
				region   : { required: true },
				zip      : { required: true },
				country  : { required: true }
			},
			highlight: function(element) {
				$(element).closest('.control-group').addClass('error');
			},
			unhighlight: function(element) {
				$(element).closest('.control-group').removeClass('error');
			}
		});
	});
	</script>
	
</body>
</html>