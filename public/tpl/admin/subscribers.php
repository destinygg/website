<?php
use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\Lol;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Utils\Country;
use Destiny\Common\Config;
use Destiny\Common\UserRole;
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
	
	<?php if(!empty($model->subscribersT2)): ?>
	<section class="container">
		<h3>T2 Subscribers</h3>
		<div class="content content-dark stream-grids clearfix">
			<div class="stream stream-grid" style="width:100%;">
				<table class="grid">
					<thead>
						<tr>
							<td></td>
							<td style="width: 100%;">User</td>
							<td>Subscription</td>
							<td></td>
						</tr>
					</thead>
					<tbody>
					<?php $i=1; ?>
					<?php foreach($model->subscribersT2 as $sub): ?>
					<tr>
						<td><?=$i?></td>
						<td><a href="/admin/user/<?=$sub['userId']?>/edit"><?=Tpl::out($sub['username'])?></a></td>
						<td><?=Tpl::out($sub['description'])?></td>
						<td><?=Tpl::moment(Date::getDateTime($sub['createdDate']), Date::STRING_FORMAT)?> - <?=Tpl::moment(Date::getDateTime($sub['endDate']), Date::STRING_FORMAT)?></td>
					</tr>
					<?php $i++; endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</section>
	<?php endif; ?>
	
	<?php if(!empty($model->subscribersT1)): ?>
	<section class="container">
		<h3>T1 Subscribers</h3>
		<div class="content content-dark stream-grids clearfix">
			<div class="stream stream-grid" style="width:100%;">
				<table class="grid">
					<thead>
						<tr>
							<td></td>
							<td style="width: 100%;">User</td>
							<td>Subscription</td>
							<td></td>
						</tr>
					</thead>
					<tbody>
					<?php $i=1; ?>
					<?php foreach($model->subscribersT1 as $sub): ?>
					<tr>
						<td><?=$i?></td>
						<td><a href="/admin/user/<?=$sub['userId']?>/edit"><?=Tpl::out($sub['username'])?></a></td>
						<td><?=Tpl::out($sub['description'])?></td>
						<td><?=Tpl::moment(Date::getDateTime($sub['createdDate']), Date::STRING_FORMAT)?> - <?=Tpl::moment(Date::getDateTime($sub['endDate']), Date::STRING_FORMAT)?></td>
					</tr>
					<?php $i++; endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</section>
	<?php endif; ?>
	
	<br>
	<?include'./tpl/seg/foot.php'?>
	<?include'./tpl/seg/commonbottom.php'?>
	
	<script src="<?=Config::cdnv()?>/web/js/admin.js"></script>
	
</body>
</html>