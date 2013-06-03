<?
namespace Destiny;
use Destiny\Utils\Tpl;
use Destiny\Utils\Lol;
?>
<div class="content content-dark stream-grids clearfix">
	
	<div class="stream stream-grid">
		<table class="grid" style="width:100%;">
			<thead>
				<tr>
					<td style="width:100%">Top subscribers</td>
					<td style="text-align:right;">Score</td>
					<td style="text-align:right;"></td>
				</tr>
			</thead>
			<tbody>
				<?$gameLeaders = Service\Fantasy\Cache::getInstance()->getSubscriberTeamLeaderboard(null, 10);?>
				<?foreach($gameLeaders as $gameRank=>$gameTeam):?>
				<?$title = Tpl::out($gameTeam->displayName)?>
				<tr data-teamid="<?=$gameTeam->teamId?>">
					<td style="text-align:left;">
						<?=Tpl::flag($gameTeam->country)?>
						<?=Tpl::subIcon($gameTeam->subscriber)?>
						<?=$title?>
					</td>
					<td style="text-align:right;"><?=$gameTeam->scoreValue?></td>
					<td style="text-align:right;">
						<?$weekChampions = $gameTeam->champions;?>
						<div class="team-champions" style="width:<?=(25*Config::$a['fantasy']['team']['maxChampions'])?>px;">
						<?foreach($weekChampions as $weekChampion):?>
							<div class="thumbnail"><img title="<?=Tpl::out($weekChampion->championName)?>" style="width:25px; height:25px;" src="<?=Config::cdn()?>/img/64x64.gif"  data-src="<?=Lol::getIcon($weekChampion->championName)?>" /></div>
						<?endforeach;?>
						<?for($x=0;$x<Config::$a['fantasy']['team']['maxChampions']-count($weekChampions);$x++):?>
							<div class="thumbnail"><img style="width:25px; height:25px;" src="<?=Config::cdn()?>/img/64x64.gif"  /></div>
						<?endfor;?>
						</div>
					</td>
				</tr>
				<?endforeach;?>
				<?for($s=0;$s<10-count($gameLeaders);$s++):?>
				<tr>
					<td style="text-align:left;">-</td>
					<td style="text-align:right;">&nbsp;</td>
					<td style="text-align:right;">&nbsp;</td>
				</tr>
				<?endfor;?>
			</tbody>
		</table>
	</div>
	
	<div class="stream stream-grid">
		<table class="grid" style="width:100%;">
			<thead>
				<tr>
					<td style="width:100%">Popular summoners</td>
					<td style="text-align:right;">Games</td>
				</tr>
			</thead>
			<tbody>
				<?$summoners = Service\Fantasy\Cache::getInstance()->getTopSummoners(null,10);?>
				<?foreach($summoners as $rank=>$summoner):?>
				<?$title = Tpl::out($summoner->summonerName)?>
				<?$champ = $summoner->mostPlayedChampion?>
				<tr>
					<td style="text-align:left;">
					<a href="http://www.lolking.net/summoner/na/<?=$summoner->summonerId?>" class="subtle-link">
						<img title="<?=Tpl::out($champ->championName)?>" style="width:20px; height:20px;" src="<?=Config::cdn()?>/img/64x64.gif" data-src="<?=Lol::getIcon($champ->championName)?>" />
						<?=$title?>
					</a>
					</td>
					<td style="text-align:right;">
						<div><span title="Games played" style="color: #b19e00;"><?=Tpl::out($summoner->gamesPlayed)?></span> / <span title="Games won" style="color: #1a6f00;"><?=Tpl::out($summoner->gamesWon)?></span> / <span title="Games lost" style="color: #8a1919;"><?=Tpl::out($summoner->gamesLost)?></span></div>
					</td>
				</tr>
				<?endforeach;?>
				<?for($s=0;$s<10-count($summoners);$s++):?>
				<tr>
					<td style="text-align:left;">-</td>
					<td style="text-align:right;">&nbsp;</td>
				</tr>
				<?endfor;?>
			</tbody>
		</table>
	</div>
	
</div>
