<?php
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Config;
use Destiny\Common\Utils\Date;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($model->title)?></title>
<meta charset="utf-8">
<?php include Tpl::file('seg/commontop.php') ?>
</head>
<body id="admin" class="thin">

	<?php include Tpl::file('seg/top.php') ?>
	<?php include Tpl::file('admin/seg/top.php') ?>
				
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
		<div class="alert alert-info" style="margin-bottom:0;">
			<strong>Success!</strong>
			<?=Tpl::out($model->success)?>
		</div>
	</section>
	<?php endif; ?>
	
	<section class="container">
		<h3>Broadcast</h3>
		<div class="content content-dark clearfix">
			<form class="form-search" action="/admin/chat/broadcast">
				<div class="control-group">
					<input name="message" type="text" class="input-xlarge" value="" placeholder="Enter a message to broadcast in chat...." />
					<button type="submit" class="btn btn-primary">Broadcast</button>
				</div>
			</form>
		</div>
	</section>
	
	<section class="container">
		<h3>Search users by IP</h3>
		<div class="content content-dark clearfix">
			<form class="form-search" action="/admin/chat/ip">
				<div class="control-group">
					<input name="ip" type="text" class="input-xlarge" value="<?=Tpl::out($model->searchIp)?>" placeholder="Enter an IP address...." />
					<button type="submit" class="btn btn-primary">Search</button>
				</div>
			</form>
		</div>
	</section>
		
	<?php if(!empty($model->searchIp)): ?>
	<section class="container">
		<div class="content content-dark clearfix">
		<?php if(!empty($model->usersByIp)): ?>
			<table class="grid">
				<thead>
					<tr>
						<td>Username</td>
						<td>Email</td>
						<td>Created</td>
					</tr>
				</thead>
				<tbody>
				<?php foreach($model->usersByIp as $user): ?>
					<tr>
						<td><a href="/admin/user/<?=$user['userId']?>/edit"><?=Tpl::out($user['username'])?></a></td>
						<td><?=Tpl::out($user['email'])?></td>
						<td><?=Tpl::moment(Date::getDateTime($user['createdDate']), Date::STRING_FORMAT_YEAR)?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php else: ?>
		<div class="control-group">
			<p>No users with the IP "<?=Tpl::out($model->searchIp)?>"</p>
		</div>
		<?php endif; ?>
		</div>
	</section>
	<?php endif; ?>
	
	<?php include Tpl::file('seg/commonbottom.php') ?>
	<script src="<?=Config::cdnv()?>/web/js/admin.js"></script>
	
</body>
</html>