<?
namespace Destiny;
use Destiny\Service\Fantasy\LeaderboardService;

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
<?include'./tpl/seg/opengraph.php'?>
<?include'./tpl/seg/commontop.php'?>
<?include'./tpl/seg/google.tracker.php'?>
</head>
<body id="games" class="league">
	<?include'./tpl/seg/top.php'?>
	
	<?php if(!$model->leagueEnded): ?>
	<section id="fantasyendseason" class="container">
		<h1 class="page-title">
			<span style="color:#eee;">Fantasy season 1 ending</span>
			<time style="color: white; border-bottom:1px dashed #cacaca;"><?=$model->endTime->format(Date::FORMAT)?></time>
		</h1>
		<hr size="1">
		Prizes will be awarded to the top 4 more details on that will come soon. <a href="http://blog.destiny.gg/season-0-launch-on-april-22nd-2013/">Read more here</a>
		<br>Thanks again, and we hope you join in for the next season
		<br>
		</p>
	</section>
	<?php else: ?>
	<section class="container">
		<h1 class="page-title">
			<span style="color:#eee;">Fantasy season 1 has ended!</span>
		</h1>
		<hr size="1">
		<p>Well done to <a>kilpo7</a>, <a>Derpski</a>, <a>dmcredgrave</a> and <a>Zmsmms</a> as well as all that participated and watched the stream!
		<br>Prizes will be awarded to the top 4 more details on that will come soon. <a href="http://blog.destiny.gg/season-0-launch-on-april-22nd-2013/">Read more here</a>
		<br>The fantasy league will continue to run, but season 2 will be a test run with no prizes, where we can experiment more.
		<br><br>Thanks again, and we hope you join in for the next season
		<br>
		</p>
	</section>
	<?php endif; ?>
	
	<?//if(!Session::hasRole(\Destiny\UserRole::USER)):?>
	<?//include'./tpl/seg/fantasy/calltoaction.php'?>
	<?//endif;?>
	
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
							<img style="width: 45px; height: 45px;" src="<?=Config::cdn()?>/web/img/64x64.gif" data-src="<?=Lol::getIcon($bChamp['championName'])?>" />
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
	
	<?php if(!empty($model->topTeamChampions) && !empty($model->teamGameScores)): ?>
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
	
	<?php if(!$model->leagueEnded): ?>
	<script>
	(function(){
		$('#fantasyendseason').each(function(){
			var timeId = null, fantasySeasonEnd = $('#fantasyendseason time:first'), endTime = moment(fantasySeasonEnd.text());
			var seasonEndMomentTime = function(){
				if(endTime.valueOf() > moment().valueOf()){
					fantasySeasonEnd.html(endTime.fromNow());
				}else{
					fantasySeasonEnd.html('Now!');
					window.clearInterval(timeId);
					$('#fantasyendseason').fadeOut(5000, function(){
						window.location.reload();
					});
				}
			};
			timeId = window.setInterval(seasonEndMomentTime, 1000);
			seasonEndMomentTime();
		});
	})();
	</script>
	<?php endif; ?>
	
</body>
</html>