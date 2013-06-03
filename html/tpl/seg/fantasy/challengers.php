<?
namespace Destiny;
use Destiny\Utils\Tpl;
use Destiny\Utils\Lol;
?>

<div class="content content-dark clearfix">

	<table id="challengerGrid" class="grid" style="width:100%;">
		<thead>
			<tr>
				<td style="width:100%">Leaders</td>
				<td style="text-align:right;">Score</td>
				<td style="text-align:right;">Credits</td>
				<td style="text-align:right;">Transfers</td>
				<td style="text-align:right;"></td>
			</tr>
		</thead>
		<tbody>
			<?$leaderboard = Service\Fantasy\Db\Challenge::getInstance()->getTeamChallengers(Session::$team['teamId'], 10)?>
			<?foreach($leaderboard as $rank=>$topTeam):?>
			<?$title = Tpl::out($topTeam->displayName)?>
			<tr>
				<td style="text-align:left;">
					<?if($topTeam->teamId != intval(Session::$team['teamId'])):?>
					<a href="#removechallenger" class="remove-challenger" title="Remove" data-teamid="<?=intval($topTeam->teamId)?>"><i class="icon-remove icon-white subtle"></i></a>
					<?endif;?>
					<?=Tpl::flag($topTeam->country)?>
					<?=Tpl::subIcon($topTeam->subscriber)?>
					<?=$title?>
				</td>
				<td style="text-align:right;"><?=Tpl::n($topTeam->scoreValue)?></td>
				<td style="text-align:right;"><?=Tpl::n($topTeam->credits)?></td>
				<td style="text-align:right;"><?=Tpl::n($topTeam->transfersRemaining)?></td>
				<td style="text-align:right;">
					<div class="team-champions" style="width:<?=(25*Config::$a['fantasy']['team']['maxChampions'])?>px;">
					<?$champions = Service\Fantasy\Db\Champion::getInstance ()->getChampionsById (explode(',', $topTeam->champions));?>
					<?foreach($champions as $champion):?>
						<div class="thumbnail"><img title="<?=Tpl::out($champion['championName'])?>" style="width:25px; height:25px;" src="<?=Config::cdn()?>/img/64x64.gif" data-src="<?=Lol::getIcon($champion['championName'])?>" /></div>
					<?endforeach;?>
					<?for($i=0;$i<Config::$a['fantasy']['team']['maxChampions']-count($champions);$i++):?>
						<div class="thumbnail"><img style="width:25px; height:25px;" src="<?=Config::cdn()?>/img/64x64.gif"  /></div>
					<?endfor;?>
					</div>
				</td>
			</tr>
			<?endforeach;?>
		</tbody>
	</table>
	
	<form class="challengeForm clearfix pull-left">
		<div class="input-append">
			<input class="span3" autocomplete="off" name="name" type="text" placeholder="Who do you want to challenge?" />
			<button class="btn btn-inverse" type="submit">Challenge!</button>
		</div>
	</form>
	
</div>