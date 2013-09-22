<?
namespace Destiny;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Utils\Country;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($model->title)?></title>
<meta charset="utf-8">
<?php include Tpl::file('seg/commontop.php') ?>
<?php include Tpl::file('seg/google.tracker.php') ?>
</head>
<body id="register">

	<?php include Tpl::file('seg/top.php') ?>
	
	<section class="container">
	
		<h1 class="title">
			<span>Confirm</span>
			
			<small>your <i class="icon-<?=Tpl::out($model->authProvider)?>"></i> <?=Tpl::out(strtolower($model->authProvider))?> details</small>
		</h1>
		<hr size="1">
		
		<?php if(!empty($model->error)): ?>
		<div class="alert alert-error">
			<strong>Error!</strong>
			<?=Tpl::out($model->error->getMessage())?>
		</div>
		<?php endif; ?>
		
		<div class="content content-dark clearfix">
			<div class="clearfix">
				<div class="control-group">
					<p>Almost there... since not all authentication providers support nick names, you get to choose your own.
					<br>Your email address is never shown publically and no emails will be sent to you without your permission.
					</p>
				</div>
				<form action="/register" method="post">
					<input type="hidden" name="code" value="<?=Tpl::out($model->code)?>" />
					<input type="hidden" name="follow" value="<?=Tpl::out($model->follow)?>" />
					<div class="control-group">
						<label class="control-label" for="inputUsername">Username / Nickname</label>
						<div class="controls">
							<input type="text" name="username" id="inputUsername" value="<?=Tpl::out($model->username)?>" placeholder="Username">
							<span class="help-block">A-z 0-9 and underscores. Must contain at least 4 and at most 20 characters</span>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="inputEmail">Email</label>
						<div class="controls">
							<input type="text" name="email" id="inputEmail" value="<?=Tpl::out($model->email)?>" placeholder="Email">
							<span class="help-block">Be it valid or not, it will be safe with us.</span>
						</div>
					</div>
					<div class="control-group">
						<label>Country:</label> 
						<select name="country">
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
					<div class="control-group">
						<div class="controls">
							<label class="checkbox">
								<input type="checkbox" name="rememberme" <?=($model->rememberme) ? 'checked':''?>> Remember my login
							</label>
							<span class="help-block">(this should only be used if you are on a private computer)</span>
						</div>
					</div>
					<div class="form-actions" style="margin-bottom:0; border-radius:0 0 4px 4px;">
						<button type="submit" class="btn btn-primary">Continue</button>
						<a href="/login" class="btn">Cancel</a>
					</div>
				</form>
			</div>
		</div>
		
	</section>
	
	<?php include Tpl::file('seg/foot.php') ?>
	<?php include Tpl::file('seg/commonbottom.php') ?>
	
</body>
</html>