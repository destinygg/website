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
<link href="<?=Config::cdn()?>/css/bootstrap.min.css" rel="stylesheet" media="screen">
<link href="<?=Config::cdn()?>/css/destiny.<?=Config::version()?>.css" rel="stylesheet" media="screen">
<link href="<?=Config::cdn()?>/css/destiny.admin.<?=Config::version()?>.css" rel="stylesheet" media="screen">
<link rel="shortcut icon" href="<?=Config::cdn()?>/favicon.png">
</head>
<body id="admin">

	<?include'./tpl/seg/top.php'?>
	
	<section class="container" id="admintabs">
		<h1 class="page-title">
			<span>Administration</span> <small>(<a title="<?=Tpl::out($model->user['email'])?>" href="mailto:<?=Tpl::out($model->user['email'])?>"><?=Tpl::out($model->user['username'])?></a>)
			</small>
		</h1>
		<hr size="1">
		
		<h3>Fantasy League</h3>
		<div class="content content-dark clearfix">
			<div style="width: 100%;" class="clearfix stream">
				<div class="tab-content control-group">
					
					<ul class="nav nav-pills">
						<li class="active"><a href="#Games" data-toggle="tab">Games</a></li>
						<li><a href="#Tracking" data-toggle="tab">Tracking</a></li>
					</ul>
					
					<div class="tab-pane active clearfix" id="Games">
						<table class="grid">
							<thead>
								<tr>
									<td>Id</td>
									<td style="width: 100%">Type</td>
									<td>Start</td>
									<td>End</td>
									<td>Length</td>
									<td>Aggregated</td>
									<td>&nbsp;</td>
								</tr>
							</thead>
							<tbody>
								<?php foreach($model->games as $game):?>
								<?php
								$createdDate = Date::getDateTime ( $game ['gameCreatedDate'] );
								$endDate = Date::getDateTime ( $game ['gameEndDate'] );
								$length = round ( ($endDate->getTimestamp () - $createdDate->getTimestamp ()) / 60 );
								?>
								<tr>
									<td><?=$game['gameId']?></td>
									<td rel="<?=$game['gameType']?>"><?=Lol::$gameTypes[$game['gameType']]?></td>
									<td><?=Date::getDateTime($game['gameCreatedDate'])->format('H:i:s d-m-Y')?></td>
									<td><?=Date::getDateTime($game['gameEndDate'])->format('H:i:s d-m-Y')?></td>
									<td><?=($length>0) ? $length .' minutes':'<span class="subtle">Unknown</span>'?></td>
									<td><?=($game['aggregated'] == '1')? Date::getDateTime($game['aggregatedDate'])->format(Date::STRING_FORMAT):'False'?></td>
									<td>
										<a rel="<?=$game['gameId']?>" title="Delete game" class="btn btn-mini btn-danger btn-delete"><i class="icon-fire icon-white"></i></a>
										<?if($game['aggregated'] == '1'):?>
										<a rel="<?=$game['gameId']?>" title="Reset game" class="btn btn-mini btn-warning btn-reset"><i class="icon-remove icon-white"></i></a>
										<?endif;?>
									</td>
								</tr>
								<?endforeach;?>
								<?for($s=0;$s<10-count($model->games);$s++):?>
								<tr>
									<td>-</td>
									<td>-</td>
									<td>-</td>
									<td>-</td>
									<td>-</td>
									<td>-</td>
								</tr>
								<?endfor;?>
							</tbody>
						</table>
					</div>
					
					<div class="tab-pane clearfix" id="Tracking">
						<table class="grid">
							<thead>
								<tr>
									<td>Id</td>
									<td style="width: 100%">Created</td>
									<td>Data</td>
								</tr>
							</thead>
							<tbody>
								<?foreach($model->tracks as $track):?>
								<tr>
									<td><?=$track['gameId']?></td>
									<td><?=Date::getDateTime($track['gameStartTime'])->format(Date::STRING_FORMAT)?></td>
									<td><?=strlen($track['gameData'])?> <span class="subtle">bytes</span></td>
								</tr>
								<?endforeach;?>
								<?for($s=0;$s<10-count($model->tracks);$s++):?>
								<tr>
									<td>-</td>
									<td>-</td>
									<td>-</td>
								</tr>
								<?endfor;?>
							</tbody>
						</table>
					</div>
						
				</div>
				
			</div>
		</div>

	</section>
	
	</section>
	
	<?include'./tpl/seg/foot.php'?>
	
	<script src="<?=Config::cdn()?>/js/vendor/jquery-1.9.1.min.js"></script>
	<script src="<?=Config::cdn()?>/js/vendor/jquery.cookie.js"></script>
	<script src="<?=Config::cdn()?>/js/vendor/bootstrap.js"></script>
	<script src="<?=Config::cdn()?>/js/vendor/moment.js"></script>
	<script src="<?=Config::cdn()?>/js/destiny.<?=Config::version()?>.js"></script>
	<script src="<?=Config::cdn()?>/js/destiny.admin.<?=Config::version()?>.js"></script>
	<script>destiny.init({cdn:'<?=Config::cdn()?>'});</script>
</body>
</html>