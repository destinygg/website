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
<?include'./tpl/seg/commontop.php'?>
<?include'./tpl/seg/google.tracker.php'?>
</head>
<body id="profile">

	<?include'./tpl/seg/top.php'?>
	
	<section class="container">
		<h1 class="page-title">
			Profile 
			<small><a><?=Tpl::out($model->user['username'])?></a></small>
		</h1>
		<div style="margin:20px 0 0 0;" class="navbar navbar-inverse navbar-subnav">
			<div class="navbar-inner">
				<ul class="nav">
					<li class="active"><a href="/profile" title="Your personal details">Details</a></li>
					<li><a href="/profile/subscription" title="Your subscriptions">Subscription</a></li>
					<li><a href="/profile/authentication" title="Your login methods">Authentication</a></li>
				</ul>
			</div>
		</div>
	</section>
	
	<?php if(!Session::hasRole(UserRole::SUBSCRIBER)): ?>
	<section class="container">
		<div class="content content-dark clearfix">
			<div class="control-group">
				<p><span class="label label-important">Subscription</span> You have no active subscriptions. Click <a href="/subscribe">here</a> to get one!</p>
			</div>
		</div>
	</section>
	<?php endif; ?>
	
	<section class="container">
		<h3>Account</h3>
		
		<?php if(!empty($model->profileUpdated)): ?>
		<div class="alert alert-info">
			<strong>Success!</strong>
			Your profile has been updated
		</div>
		<?php endif; ?>
		
		<?php if(!empty($model->error)): ?>
		<div class="alert alert-error">
			<strong>Error!</strong>
			<?=Tpl::out($model->error->getMessage())?>
		</div>
		<?php endif; ?>
		
		<?if((!isset($_COOKIE['alert-dismissed-namechangealert']) || $_COOKIE['alert-dismissed-namechangealert'] != true)):?>
		<div id="namechangealert" class="alert alert-error">
			<button type="button" class="close persist" data-dismiss="alert">&times;</button>
			<strong>Heads up!</strong> A name change limit has been implement. You are now limited to <?=Config::$a['profile']['nameChangeLimit']?> name changes per account
			<br>Please contact <a href="mailto:<?=Config::$a['paypal']['support_email']?>"><?=Config::$a['paypal']['support_email']?></a> if you require assistance, or would like to request a manual name change.
		</div>
		<?php endif; ?>
	
		<div class="content content-dark clearfix">
			<div style="width: 100%;" class="clearfix stream">
				<form id="profileSaveForm" action="/profile" method="post">
					<input type="hidden" name="url" value="/league" />
					
					<?php if($model->user['nameChangedCount'] < Config::$a['profile']['nameChangeLimit']): ?>
					<div class="control-group">
						<label>Username:
						<br><small style="opacity:0.5;">(You have <?=Tpl::n(Config::$a['profile']['nameChangeLimit'] - $model->user['nameChangedCount'])?> name changes left)</small>
						</label> 
						<input type="text" name="username" value="<?=Tpl::out($model->user['username'])?>" placeholder="Username" />
						<span class="help-block">A-z 0-9 and underscores. Must contain at least 4 and at most 20 characters</span>
					</div>
					<?php endif; ?>
					
					<?php if($model->user['nameChangedCount'] >= Config::$a['profile']['nameChangeLimit']): ?>
					<div class="control-group">
						<label>Username:
						<br><small style="opacity:0.5;">(You have no more name changes available)</small>
						</label> 
						<input type="text" disabled="disabled" name="username" value="<?=Tpl::out($model->user['username'])?>" placeholder="Username" />
					</div>
					<?php endif; ?>
					
					<div class="control-group">
						<label>Email:</label> 
						<input type="text" name="email" value="<?=Tpl::out($model->user['email'])?>" placeholder="Email" />
						<span class="help-block">Be it valid or not, it will be safe with us.</span>
					</div>
					<div class="control-group">
						<label>Country:</label> 
						<select name="country">
							<option value="">Select your country</option>
							<?$countries = Utils\Country::getCountries();?>
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
		
					<div class="control-group">
						<a href="#" rel="resetteam">Reset fantasy team</a>
					</div>
		
					<div class="form-actions block-foot">
						<button class="btn" type="submit">Save changes</button>
					</div>
				</form>
			</div>
		</div>
	</section>
	
	<?include'./tpl/seg/foot.php'?>
	<?include'./tpl/seg/commonbottom.php'?>
	
</body>
</html>