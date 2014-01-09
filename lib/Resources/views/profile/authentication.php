<?
use Destiny\Common\Utils\Tpl;
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
<body id="authentication" class="profile">

	<?php include Tpl::file('seg/top.php') ?>
	<?php include Tpl::file('seg/headerband.php') ?>
	
	<section class="container">
		<div class="navbar navbar-inverse navbar-subnav">
			<div class="navbar-inner">
				<ul class="nav pull-left">
					<li><a href="/profile" title="Your personal details">Details</a></li>
					<li><a href="/profile/games" title="Your games">Games</a></li>
				</ul>
				<ul class="nav pull-right">
					<li class="active"><a href="/profile/authentication" title="Your login methods">Authentication</a></li>
				</ul>
			</div>
		</div>
	</section>	
	
	<section class="container">
		<h3>Providers <small>authentication</small></h3>
		<p style="color:#666;">Authentication providers are what we use to know who you are! you can login with any of the services below</p>
		<div class="content content-dark clearfix">
			<div style="width: 100%;" class="clearfix stream">
				
				<table class="grid" style="width:100%">
					<thead>
						<tr>
							<td>Profile</td>
							<td style="width:100%;">Status</td>
						</tr>
					</thead>
					<tbody>
						<?php foreach(Config::$a ['authProfiles'] as $profileType): ?>
						<tr>
							<td>
								<i class="icon-<?=$profileType?>"></i> <?=ucwords($profileType)?>
							</td>
							<td>
								<?php if(in_array($profileType, $model->authProfileTypes)): ?>
								<?php $model->requireConnections = true; ?>
								<span class="subtle"><i class="icon-ok icon-white"></i> Connected</span>
								<?php else: ?>
								<a href="/profile/connect/<?=$profileType?>"><i class="icon-heart icon-white subtle"></i> Connect</a>
								<?php endif; ?>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				
				<?php if($model->requireConnections): ?>
				<div class="control-group">
					<p><span class="label label-inverse">Important!</span> Connecting profiles will merge destiny.gg accounts if duplicates are found.</p>
				</div>
				<?php endif; ?>
				
			</div>
		</div>
	</section>
	
	<section class="container">
		<h3>Login keys</h3>
		<p style="color:#666;">Login keys allow you to authenticate with the destiny.gg chat without the need for a username or password. Keys MUST be kept a <strong>confidential</strong>.</p>
		<div class="content content-dark clearfix">
			<div style="width: 100%;" class="clearfix stream">
				<form action="/profile/authtoken/create" method="post">
					<table class="grid" style="width:100%">
						<thead>
							<tr>
								<td>Key</td>
								<td style="width:100%;">Created</td>
							</tr>
						</thead>
						<tbody>
							<?php if(!empty($model->authTokens)): ?>
							<?php foreach($model->authTokens as $authToken): ?>
							<tr>
								<td><a href="/profile/authtoken/<?=$authToken['authToken']?>/delete" class="btn btn-danger btn-mini">Delete</a> <span><?=$authToken['authToken']?></span></td>
								<td><?=Date::getDateTime($authToken['createdDate'])->format(Date::STRING_FORMAT)?></td>
							</tr>
							<?php endforeach; ?>
							<?php else: ?>
							<tr>
								<td colspan="2"><span class="subtle">You have no authentication keys</span></td>
							</tr>
							<?php endif; ?>
						</tbody>
					</table>
					
					<div class="control-group">
						<button class="btn btn-primary btn-large">Create new key</button>
					</div>
				</form>
			</div>
		</div>
	</section>
	
	<?php include Tpl::file('seg/foot.php') ?>
	<?php include Tpl::file('seg/commonbottom.php') ?>
</body>
</html>