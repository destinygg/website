<?php
use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Config;
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
		<div class="navbar navbar-inverse navbar-subnav">
			<div class="navbar-inner">
				<ul class="nav pull-left">
					<li><a href="/admin">Users</a></li>
					<li class="active"><a href="/admin/subscribers">Subscribers</a></li>
				</ul>
			</div>
		</div>
	</section>
	
	<?php function buildSubscribersTier(array $tier = null, $num){?>
	<?php if(!empty($tier)): ?>
	<section class="container">
		<h3>T<?=$num?> Subscribers</h3>
		<div class="content content-dark stream-grids clearfix">
			<div class="stream stream-grid" style="width:100%;">
				<table class="grid">
					<thead>
						<tr>
							<td style="width: 20px;"></td>
							<td style="width: 200px;">User</td>
							<td style="width: 100px;">Recurring</td>
							<td style="width: 80px;">Created on</td>
							<td>Ends on</td>
						</tr>
					</thead>
					<tbody>
					<?php $i=1; ?>
					<?php foreach($tier as $sub): ?>
					<?php $subType = Config::$a['commerce']['subscriptions'][$sub['subscriptionType']];?>
					<tr>
						<td><?=$i?></td>
						<td><a href="/admin/user/<?=$sub['userId']?>/edit"><?=Tpl::out($sub['username'])?></a></td>
						<td><?=($sub['recurring'] == 1) ? 'Yes':'No'?></td>
						<td><?=Tpl::moment(Date::getDateTime($sub['createdDate']), Date::STRING_FORMAT)?></td>
						<td><?=Tpl::moment(Date::getDateTime($sub['endDate']), Date::STRING_FORMAT)?></td>
					</tr>
					<?php $i++; endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</section>
	<?php endif; ?>
	<?php } ?>
	
	<?php buildSubscribersTier($model->subscribersT3, 3) ?>
	<?php buildSubscribersTier($model->subscribersT2, 2) ?>
	<?php buildSubscribersTier($model->subscribersT1, 1) ?>
	
	<br>
	<?php include Tpl::file('seg/foot.php') ?>
	<?php include Tpl::file('seg/commonbottom.php') ?>
	
	<script src="<?=Config::cdnv()?>/web/js/admin.js"></script>
	
</body>
</html>