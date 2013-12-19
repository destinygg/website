<?php
use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Utils\Country;
use Destiny\Common\Config;
use Destiny\Common\User\UserRole;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($model->title)?></title>
<meta charset="utf-8">
<?php include Tpl::file('seg/commontop.php') ?>
</head>
<body id="admin">

	<?php include Tpl::file('seg/top.php') ?>
	
	<section class="container">
		<h1 class="page-title">
			<span>User</span>
			<small>update</small>
		</h1>
		<div class="navbar navbar-inverse navbar-subnav">
			<div class="navbar-inner">
				<form class="navbar-form" id="user-search">
					&nbsp;<input type="text" class="span2" placeholder="Enter a username..." autocomplete="off">
					<button type="submit" class="btn btn-inverse">Edit</button>
					<a href="/admin/" class="btn">Back</a>
				</form>
			</div>
		</div>
	</section>
	
	<section class="container">
			
		<?php if(!empty($model->success)): ?>
		<div class="alert alert-info">
			<strong>Success!</strong>
			<?=Tpl::out($model->success)?>
		</div>
		<?php endif; ?>
		
		<div style="float:left; width:70%;">
			<h3>Details</h3>
			<div class="content content-dark clearfix" style="margin-right:5px;">
				<div class="clearfix">
					<form action="/admin/user/<?=Tpl::out($model->user['userId'])?>/edit" method="post">
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
							<label>Features:</label>
							<?php foreach($model->features as $featureName=>$f): ?>
							<?php if(strcasecmp($featureName, 'subscriber') === 0 || strcasecmp($featureName, 'flair1') === 0 || strcasecmp($featureName, 'flair3') === 0 ) continue; // remove subscription flairs?>
							<label class="checkbox">
								<input type="checkbox" name="features[]" value="<?=$f['featureName']?>" <?=(in_array($featureName, $model->user['features']))?'checked="checked"':''?>>
								<?=$f['featureLabel']?>
							</label>
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
							<button type="submit" class="btn btn-primary btn-large">Update</button>
							<a href="/admin" class="btn btn-large">Cancel</a>
						</div>
					</form>
				</div>
			</div>

			<?php if(!empty($model->address)): ?>
			<h3>Address</h3>
			<div class="content content-dark clearfix" style="margin-right:5px;">
				<div class="vcard control-group">
					<div class="fn"><?=Tpl::out($model->address['fullName'])?></div>
					<br />
					<div class="adr">
						<div class="street-address">
							<?=Tpl::out($model->address['line1'])?>, <?=Tpl::out($model->address['line2'])?>
						</div>
						<div>
							<span class="city"><?=Tpl::out($model->address['city'])?></span>,
							<span class="region"><?=Tpl::out($model->address['region'])?></span>,
							<span class="postal-code"><?=Tpl::out($model->address['zip'])?></span>
							<?php 
							$country = Country::getCountryByCode ( $model->address['country'] );
							if(!empty($country)):
							?>
							<br />
							<abbr class="country"><?=Tpl::out($country['name'])?> <small>(<?=Tpl::out($country['alpha-2'])?>)</small></abbr>
							<?php endif; ?>
						</div>
					</div> 
				</div>
			</div>
			<?php endif; ?>

			<?php if(!empty($model->authSessions)): ?>
			<h3>Authentication sessions</h3>
			<div class="content content-dark clearfix" style="margin-right:5px;">
				<table class="grid">
					<tr>
						<td>Provider</td>
						<td style="width:100%;">Detail</td>
						<td>Created</td>
						<td>Modified</td>
					</tr>
					<?php foreach($model->authSessions as $auth): ?>
					<tr>
						<td><?= $auth['authProvider'] ?></td>
						<td><?= (!empty($auth['authDetail'])) ? Tpl::out($auth['authDetail']):Tpl::out($auth['authId']) ?></td>
						<td><?=Tpl::moment(Date::getDateTime($auth['createdDate']), Date::STRING_FORMAT_YEAR)?></td>
						<td><?=Tpl::moment(Date::getDateTime($auth['modifiedDate']), Date::STRING_FORMAT_YEAR)?></td>
					</tr>
					<?php endforeach; ?>
				</table>
			</div>
			<?php endif; ?>

		</div>
		<div style="float:right; width:30%;">
			<h3 style="padding-left:5px;">Ban / Mute</h3>
			<div class="content content-dark clearfix" style="margin-left:5px;">
				<div class="clearfix">
					
					<?php if(empty($model->ban)): ?>
					
					<div class="control-group">No active bans found</div>
					<div class="form-actions" style="margin-bottom:0; border-radius:0 0 4px 4px;">
						<a href="/admin/user/<?=$model->user['userId']?>/ban" class="btn btn-danger">Ban user</a>
					</div>
					
					<?php else: ?>
					<div class="control-group">
						<p>
							<?php if(!empty($model->ban['ipaddress'])): ?>
							Ip: <a target="_blank" href="http://freegeoip.net/json/<?=$model->ban['ipaddress']?>"><?=$model->ban['ipaddress']?></a>
							<?php else: ?>
							Ip: Not set
							<?php endif; ?>
						</p>
						<p>
							<?=Tpl::moment(Date::getDateTime($model->ban['starttimestamp']), Date::STRING_FORMAT)?>
							<?php if(!empty($model->ban['endtimestamp'])): ?>
							- <?=Tpl::moment(Date::getDateTime($model->ban['endtimestamp']), Date::STRING_FORMAT)?>
							<?php endif; ?>
						</p>
						<blockquote>
							<p style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?=Tpl::out($model->ban['reason'])?></p>
							<small class="subtle"><?=Tpl::out($model->ban['username'])?></small>
						</blockquote>
					</div>
					
					<?php if(!empty($model->banContext)): ?>
					<div id="ban-context" class="control-group">
						<ul class="unstyled" style="height:383px;">
							<?php foreach($model->banContext as $line): ?>
							<li style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
								<small class="subtle"><?=Tpl::moment(Date::getDateTime($line['timestamp']), Date::STRING_FORMAT, 'h:mm:ss')?></small>
								<span>&lt;<?=Tpl::out($line['username'])?>&gt; <?=Tpl::out($line['data'])?></span> 
							</li>
							<?php endforeach; ?>
						</ul>
					</div>
					<?php endif; ?>
					
					<div class="form-actions" style="margin-bottom:0; border-radius:0 0 4px 4px;">
						<a href="/admin/user/<?=$model->user['userId']?>/ban/<?=$model->ban['id']?>/edit" class="btn btn-primary btn-large">Edit ban</a>
						<a onclick="return confirm('Are you sure?');" href="/admin/user/<?=$model->user['userId']?>/ban/<?=$model->ban['id']?>/remove" class="btn btn-danger btn-large">Remove ban</a>
					</div>
					
					<?php endif; ?>
				</div>
			</div>
		</div>
		
	</section>
	<br>
	
	<?php include Tpl::file('seg/foot.php') ?>
	<?php include Tpl::file('seg/commonbottom.php') ?>
	
	<script src="<?=Config::cdnv()?>/web/js/admin.js"></script>
	
</body>
</html>