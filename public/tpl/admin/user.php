<?
namespace Destiny;
use Destiny\Utils\Date;
use Destiny\Utils\Lol;
use Destiny\Utils\Tpl;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($model->title)?></title>
<meta charset="utf-8">
<?include'./tpl/seg/commontop.php'?>
</head>
<body id="admin">

	<?include'./tpl/seg/top.php'?>
	
	<section class="container" id="admintabs">
		<h1 class="page-title">
			<span>User</span> <small>(<a title="<?=Tpl::out($model->user['email'])?>" href="mailto:<?=Tpl::out($model->user['email'])?>"><?=Tpl::out($model->user['username'])?></a>)
			</small>
		</h1>
		<hr size="1">
		
		<div class="content content-dark clearfix">
			<div class="clearfix">
				<form action="/admin/user/update" method="post">
					<input type="hidden" name="id" value="<?=Tpl::out($model->user['userId'])?>" />
					<div class="control-group">
						<label class="control-label" for="inputUsername">Username / Nickname</label>
						<div class="controls">
							<input type="text" name="username" id="inputUsername" value="<?=Tpl::out($model->user['username'])?>" placeholder="Username">
							<span class="help-block">A-z 0-9 and underscores. Must contain at least 4 and at most 20 characters</span>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="inputEmail">Email</label>
						<div class="controls">
							<input type="text" name="email" id="inputEmail" value="<?=Tpl::out($model->user['email'])?>" placeholder="Email">
							<span class="help-block">Be it valid or not, it will be safe with us.</span>
						</div>
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
						<label>Flair:</label>
						<?php foreach($model->features as $featureName=>$featureId): ?>
						<?php if(strcasecmp($featureName, 'subscriber') !== 0 && strcasecmp($featureName, 'admin') !== 'admin'): ?>
						<label class="checkbox">
							<input type="checkbox" name="features[]" value="<?=$featureName?>" <?=(in_array($featureName, $model->user['features']))?'checked="checked"':''?>>
							<?=ucwords($featureName)?>
						</label>
						<?php endif; ?>
						<?php endforeach; ?>
					</div>
					
					<div class="control-group">
						<label>Website Roles:</label>
						<label class="checkbox">
							<input type="checkbox" name="roles[]" value="<?=UserRole::ADMIN?>" <?=(in_array(UserRole::ADMIN, $model->user['roles']))?'checked="checked"':''?>>
							Administrator
						</label>
					</div>
					
					
					<div class="form-actions" style="margin-bottom:0; border-radius:0 0 4px 4px;">
						<button type="submit" class="btn btn-primary">Confirm</button>
						<a href="/admin" class="btn">Back to Admin</a>
					</div>
				</form>
			</div>
		</div>
		
		
	</section>
	
	<?include'./tpl/seg/foot.php'?>
	<?include'./tpl/seg/commonbottom.php'?>
	
</body>
</html>