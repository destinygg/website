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
<body id="admin" class="thin">

	<?php include Tpl::file('seg/top.php') ?>
	<?php include Tpl::file('admin/seg/top.php') ?>

	<section class="container">
		<?php if ( ! empty( $model->activeBans ) ): ?>
		<h3><?=Tpl::out( sprintf('Active bans (%d)', count( $model->activeBans ) ) )?></h3>
		<div class="content content-dark stream-grids clearfix">
			<div class="stream stream-grid" style="width:100%;">
				<table class="grid">
					<thead>
						<tr>
							<td style="width: 200px;">User</td>
							<td style="width: 400px;">Reason</td>
							<td style="width: 80px;">Created on</td>
							<td style="width: 80px;">Ends on</td>
							<td><a onclick="return confirm('Are you sure?');" class="btn btn-danger" href="/admin/bans/purgeall">Remove all bans!</a></td>
						</tr>
					</thead>
					<tbody>
					<?php foreach($model->activeBans as $ban): ?>
					<tr>
						<td><a href="/admin/user/<?=$ban['targetuserid']?>/edit"><?=Tpl::out($ban['targetusername'])?></a></td>
						<td class="wrap">Banned by <?=Tpl::out($ban['banningusername'])?> with reason: <?=Tpl::out($ban['reason'])?></td>
						<td><?=Tpl::moment(Date::getDateTime($ban['starttimestamp']), Date::STRING_FORMAT)?></td>
						<td>
							<?php if ( !$ban['endtimestamp'] )
									echo "Permanent";
								else
									echo Tpl::moment(Date::getDateTime($ban['endtimestamp']), Date::STRING_FORMAT);
							?>
						</td>
						<td><a class="btn btn-danger" href="/admin/user/<?=$ban['targetuserid']?>/ban/remove?follow=<?=rawurlencode( $_SERVER['REQUEST_URI'] )?>">Remove</a>
					</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
		<?php else: ?>
		<h3>No active bans found, good job for not being an asshole!</h3>
		<?php endif; ?>
	</section>

	<br />
	<?php include Tpl::file('seg/commonbottom.php') ?>
	
	<script src="<?=Config::cdnv()?>/web/js/admin.js"></script>
	
</body>
</html>