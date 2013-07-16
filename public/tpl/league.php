<?
namespace Destiny;
use Destiny\Common\Service\Fantasy\TeamService;
use Destiny\Common\Service\Fantasy\LeaderboardService;
use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\Http;
use Destiny\Common\Utils\Lol;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Config;
use Destiny\Common\Session;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($model->title)?></title>
<meta charset="utf-8">
<?include'./tpl/seg/opengraph.php'?>
<?include'./tpl/seg/commontop.php'?>
<?include'./tpl/seg/google.tracker.php'?>
</head>
<body id="games" class="league">
	<?include'./tpl/seg/top.php'?>

	<section class="container">
		<h1 class="page-title">Fantasy season 1 has reset!</h1>
		<hr size="1">
		<p>Well done to the top 4 players for the fantasy league season one. 1<sup>st</sup> <a title="kilpo7">kilpo7</a>, 2<sup>nd</sup> <a title="Derpski">Derpski</a>, 3<sup>rd</sup> <a title="&amp; Bronzer ..."> DICEDLEMMING</a> and the ever soothing 4<sup>th</sup> <a title="dmcredgrave"> dmcredgrave</a>.
			<br><span class="label label-important">RESET</span> Scores, champion purchases and leaderboards have been reset. All data has been kept for the mean time until we find a home for it.
		</p>
	</section>
	
	<?//if(!Session::hasRole(\Destiny\UserRole::USER)):?>
	<?//include'./tpl/seg/fantasy/calltoaction.php'?>
	<?//endif;?>
	
	<?if(Session::hasRole(\Destiny\Common\UserRole::USER)):?>
	
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
					<?php foreach ($model->ingame['gameData']['gameTeams'][Lol::TEAMSIDE_BLUE] as $bSummoner): ?>
						<?php $bChamp = Lol::getChampPick($model->ingame['gameData'], $bSummoner)?>
						<a class="champion" href="http://www.lolking.net/summoner/na/<?=$bSummoner['summonerId']?>" title="<?=Tpl::out($bSummoner['name'])?>" data-placement="bottom" rel="tooltip">
							<img style="width: 45px; height: 45px;" src="<?=Config::cdn()?>/web/img/64x64.gif" data-src="<?=Lol::getIcon($bChamp['championName'])?>" />
						</a>
					<?php endforeach; ?>
					</div>
				</div>
			</div>
			<div style="width:50%; float:right;">
				<div class="clearfix game-team-purple">
					<div class="pull-right">
					<?php foreach ($model->ingame['gameData']['gameTeams'][Lol::TEAMSIDE_PURPLE] as $pSummoner): ?>
						<?php $pChamp = Lol::getChampPick($model->ingame['gameData'], $pSummoner)?>
						<a class="champion" href="http://www.lolking.net/summoner/na/<?=$pSummoner['summonerId']?>" title="<?=Tpl::out($pSummoner['name'])?>" data-placement="bottom" rel="tooltip">
							<img style="width: 45px; height: 45px;" src="<?=Config::cdn()?>/web/img/64x64.gif" data-src="<?=Lol::getIcon($pChamp['championName'])?>" />
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
	
	<?php if(!empty($model->topTeamChampions) || !empty($model->teamGameScores)): ?>
	<?include'./tpl/seg/fantasy/recentgames.php'?>
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
	<?include'./tpl/seg/commonbottom.php'?>
	
</body>
</html>