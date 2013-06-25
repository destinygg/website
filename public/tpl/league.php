<?
namespace Destiny;
use Destiny\Utils\Date;
use Destiny\Utils\Http;
use Destiny\Utils\Lol;
use Destiny\Utils\Tpl;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($model->title)?></title>
<meta charset="utf-8">
<meta name="description" content="League of Legends Fantasy League. Free to play">
<meta name="keywords" content="League of Legends,Fantasy League,Free to play">
<meta name="author" content="<?=Config::$a['meta']['author']?>">
<meta property="og:site_name" content="<?=Config::$a['meta']['shortName']?>" />
<meta property="og:title" content="<?=Config::$a['meta']['shortName']?> : Fantasy League" />
<meta property="og:description" content="League of Legends Fantasy League. Free to play" />
<meta property="og:image" content="<?=Config::cdn()?>/img/destinyspash600x600.png" />
<meta property="og:url" content="<?=Http::getBaseUrl()?>" />
<meta property="og:type" content="video.other" />
<meta property="og:video" content="<?=Config::$a['meta']['video']?>" />
<meta property="og:video:secure_url" content="<?=Config::$a['meta']['videoSecureUrl']?>" />
<meta property="og:video:type" content="application/x-shockwave-flash" />
<meta property="og:video:height" content="259" />
<meta property="og:video:width" content="398" />
<link href="<?=Config::cdn()?>/css/bootstrap.min.css" rel="stylesheet" media="screen">
<link href="<?=Config::cdn()?>/css/destiny.<?=Config::version()?>.css" rel="stylesheet" media="screen">
<link rel="shortcut icon" href="<?=Config::cdn()?>/favicon.png">
<?include'./tpl/seg/google.tracker.php'?>
</head>
<body id="games" class="league">
	<?include'./tpl/seg/top.php'?>
	
	<?if(!Session::hasRole(\Destiny\UserRole::USER)):?>
	<?include'./tpl/seg/fantasy/calltoaction.php'?>
	<?endif;?>
	
	<?if(Session::hasRole(\Destiny\UserRole::USER)):?>
	<?include'./tpl/seg/fantasy/teambar.php'?>
	<?include'./tpl/seg/fantasy/teammaker.php'?>
	
	<section class="container">
	
		<?php include'./tpl/seg/fantasy/fantasysubnav.php' ?>
		 
		<div id="newGameAlert" class="alert alert-info" style="margin:15px 0 0 0; display:none;">
			<button type="button" class="close" data-dismiss="alert">&times;</button>
			<strong>Hey!</strong> a new game has been added, <a href="/league"><i class="icon-refresh"></i> refresh</a> the page to check if you scored any points
		</div>
				
		<div id="newInGameAlert" class="alert alert-info" style="margin:15px 0 0 0; display:none;">
			<button type="button" class="close" data-dismiss="alert">&times;</button>
			<strong>Hey!</strong> a new game has started, <a href="/league"><i class="icon-refresh"></i> refresh</a> the page to show more details
		</div>
		
		<?php if(!empty($model->ingame)): ?>
		<div id="activeGame" data-gameid="<?=$model->ingame['gameId']?>" class="game-horizontal clearfix" style="margin-top:15px; position: relative; border:none;">
			<div style="width:50%; float:left;">
				<div class="clearfix game-team-blue">
					<div class="pull-left">
					<?php foreach ($model->ingame['gameData']['gameTeams'][\Destiny\Utils\Lol::TEAMSIDE_BLUE] as $bSummoner): ?>
						<?php $bChamp = Lol::getChampPick($model->ingame['gameData'], $bSummoner)?>
						<a class="champion" href="http://www.lolking.net/summoner/na/<?=$bSummoner['summonerId']?>" title="<?=Tpl::out($bSummoner['name'])?>" data-placement="bottom" rel="tooltip">
							<img style="width: 45px; height: 45px;" src="<?=Config::cdn()?>/img/64x64.gif" data-src="<?=Lol::getIcon($bChamp['championName'])?>" />
						</a>
					<?php endforeach; ?>
					</div>
				</div>
			</div>
			<div style="width:50%; float:right;">
				<div class="clearfix game-team-purple">
					<div class="pull-right">
					<?php foreach ($model->ingame['gameData']['gameTeams'][\Destiny\Utils\Lol::TEAMSIDE_PURPLE] as $pSummoner): ?>
						<?php $pChamp = Lol::getChampPick($model->ingame['gameData'], $pSummoner)?>
						<a class="champion" href="http://www.lolking.net/summoner/na/<?=$pSummoner['summonerId']?>" title="<?=Tpl::out($pSummoner['name'])?>" data-placement="bottom" rel="tooltip">
							<img style="width: 45px; height: 45px;" src="<?=Config::cdn()?>/img/64x64.gif" data-src="<?=Lol::getIcon($pChamp['championName'])?>" />
						</a>
					<?php endforeach; ?>
					</div>
				</div>
			</div>
			<div class="ingame-progress-text" title="Game in progress">
				<div>
				Game in progress!
				<br><small>started <?=(isset($model->ingame['gameStartTime'])) ? Tpl::fromNow(Date::getDateTime($model->ingame['gameStartTime']), Date::STRING_FORMAT): 'just now'?></small>
				</div>
			</div>
		</div>
		<?php endif; ?>
	</section>
	<?endif;?>
	
	<?php if(!empty($model->topTeamChampions) && !empty($model->teamGameScores)): ?>
	<section class="container">
		<div class="content content-dark stream-grids clearfix">
		
			<div class="stream stream-grid" style="width:50%;">
				<table class="grid">
					<thead>
						<tr>
							<td>Recent games</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
						</tr>
					</thead>
					<tbody>
					<?php foreach($model->teamGameScores as $gameScore): ?>
					<?php
						$createdDate = Date::getDateTime ( $gameScore ['gameCreatedDate'] );
						$endDate = Date::getDateTime ( $gameScore ['gameEndDate'] );
					?>
					<tr data-gameid="<?=$gameScore['gameId']?>">
						<td>
							<div class="game-team-bar game-team-bar-horizontal pull-left">
								<div class="pull-left game-team game-team-blue">
									<?php foreach($model->teamGameChampScores as $teamGameScore1): ?>
									<?php if($teamGameScore1['gameId'] == $gameScore['gameId'] && $teamGameScore1['teamSideId'] == Lol::TEAMSIDE_BLUE): ?>
									<img title="<?=Tpl::out($teamGameScore1['championName'])?>" style="width: 20px; height: 20px;" src="<?=Config::cdn()?>/img/64x64.gif" data-src="<?=Lol::getIcon($teamGameScore1['championName'])?>" />
									<?php endif; ?>
									<?php endforeach; ?>
								</div>
								<span class="pull-left subtle" style="padding:0 5px;">vs</span>
								<div class="pull-left game-team game-team-purple">
									<?php foreach($model->teamGameChampScores as $teamGameScore2): ?>
									<?php if($teamGameScore2['gameId'] == $gameScore['gameId'] && $teamGameScore2['teamSideId'] == Lol::TEAMSIDE_PURPLE): ?>
									<img title="<?=Tpl::out($teamGameScore2['championName'])?>" style="width: 20px; height: 20px;" src="<?=Config::cdn()?>/img/64x64.gif" data-src="<?=Lol::getIcon($teamGameScore2['championName'])?>" />
									<?php endif; ?>
									<?php endforeach; ?>
								</div>
							</div>
						</td>
						<td style="text-align: right; width: 100%;">
							<small><?=Tpl::fromNow($createdDate, Date::FORMAT)?></small>
						</td>
						<td style="text-align: right;">
							<span style="color: green;">+<?=$gameScore['scoreValue']?></span>
							<a href="/league/game/<?=$gameScore['gameId']?>"><i class="icon-info-sign icon-white subtle"></i>&nbsp;</a>
						</td>
					</tr>
					<?php endforeach; ?>
					<?for($s=0;$s<10-(($model->teamGameScores) ? count($model->teamGameScores):0);$s++):?>
					<tr>
						<td><i class="icon-minus td-fill"></i></td>
						<td style="width: 100%;">&nbsp;</td>
						<td style="text-align:right;"><i class="icon-minus td-fill"></i></td>
					</tr>
					<?endfor;?>
					</tbody>
				</table>
			</div>
		
			<div class="stream stream-grid" style="width:50%;">
				<table class="grid">
					<thead>
						<tr>
							<td>Your top champions</td>
							<td style="width: 100%;">&nbsp;</td>
							<td>&nbsp;</td>
						</tr>
					</thead>
					<tbody>
					<?php foreach($model->topTeamChampions as $topChamp): ?>
					<tr>
						<td>
						<img title="<?=Tpl::out($topChamp['championName'])?>" style="width: 20px; height: 20px;" src="<?=Config::cdn()?>/img/64x64.gif" data-src="<?=Lol::getIcon($topChamp['championName'])?>" />
						<?=Tpl::out($topChamp['championName'])?>
						</td>
						<td style="text-align: right;"><small><?=$topChamp['gamesPlayed']?></small> <small class="subtle">games played</small></td>
						<td style="text-align: right;"><?=$topChamp['scoreValue']?> <small class="subtle">points</small></td>
					</tr>
					<?php endforeach; ?>
					<?for($s=0;$s<10-(($model->topTeamChampions) ? count($model->topTeamChampions):0);$s++):?>
					<tr>
						<td><i class="icon-minus td-fill"></i></td>
						<td>&nbsp;</td>
						<td style="text-align:right;"><i class="icon-minus td-fill"></i></td>
					</tr>
					<?endfor;?>
					</tbody>
				</table>
			</div>
			
		</div>
	</section>
	<?php endif; ?>
	
	<section class="container">
		<?include'./tpl/seg/fantasy/leaderboards.php'?>
	</section>
	<section class="container">
		<?include'./tpl/seg/fantasy/topchampions.php'?>
	</section>
	<section class="container">
		<?include'./tpl/seg/fantasy/leaderboardsmisc.php'?>
	</section>
	
	<?include'./tpl/seg/panel.ads.php'?>
	<?include'./tpl/seg/foot.php'?>
	
	<script src="<?=Config::cdn()?>/js/vendor/jquery-1.9.1.min.js"></script>
	<script src="<?=Config::cdn()?>/js/vendor/jquery.cookie.js"></script>
	<script src="<?=Config::cdn()?>/js/vendor/bootstrap.js"></script>
	<script src="<?=Config::cdn()?>/js/vendor/moment.js"></script>
	<script src="<?=Config::cdn()?>/js/destiny.<?=Config::version()?>.js"></script>
	<script>destiny.init({cdn:'<?=Config::cdn()?>'});</script>
</body>
</html>