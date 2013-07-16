<?php
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Utils\Lol;
use Destiny\Common\Config;
?>
<div class="content content-dark stream-grids clearfix">

	<div class="stream stream-grid">
		<table class="grid">
			<thead>
				<tr>
					<td style="width: 100%">Popular summoners</td>
					<td></td>
				</tr>
			</thead>
			<tbody>
				<?php if(!empty($model->topSummoners)): ?>
				<?foreach($model->topSummoners as $rank=>$summoner):?>
				<?$title = Tpl::out($summoner['summonerName'])?>
				<?$champ = $summoner['mostPlayedChampion']?>
				<tr>
					<td style="text-align: left;">
					<a href="http://www.lolking.net/summoner/na/<?=$summoner['summonerId']?>" class="subtle-link">
						<img title="<?=Tpl::out($champ['championName'])?>" style="width: 20px; height: 20px;" src="<?=Config::cdn()?>/web/img/64x64.gif" data-src="<?=Lol::getIcon($champ['championName'])?>" />
						<?=$title?>
					</a>
					</td>
					<td style="text-align: right;">
						<span title="Games played" style="color: #b19e00;"><?=Tpl::out($summoner['gamesPlayed'])?></span>
						/ <span title="Games won" style="color: #1a6f00;"><?=Tpl::out($summoner['gamesWon'])?></span>
						/ <span title="Games lost" style="color: #8a1919;"><?=Tpl::out($summoner['gamesLost'])?></span>
						<small class="subtle">games</small>
					</td>
				</tr>
				<?endforeach;?>
				<?endif;?>
				<?for($s=0;$s<10-count($model->topSummoners);$s++):?>
				<tr>
					<td style="text-align: left;"><i class="icon-minus td-fill"></i></td>
					<td style="text-align: right;">&nbsp;</td>
				</tr>
				<?endfor;?>
			</tbody>
		</table>
	</div>

	<div class="stream stream-grid">
		<table class="grid">
			<thead>
				<tr>
					<td style="width: 100%">Recent match leaders</td>
					<td>&nbsp;</td>
				</tr>
			</thead>
			<tbody>
				<?php if(!empty($model->gameLeaders)): ?>
				<?foreach($model->gameLeaders as $gameRank=>$gameTeam):?>
				<?$title = Tpl::out($gameTeam['username'])?>
				<tr>
					<td style="text-align: left;">
						<?=Tpl::flag($gameTeam['country'])?>
						<?=Tpl::subIcon($gameTeam['subscriber'])?>
						<?=$title?>
					</td>
					<td style="text-align: right;"><?=$gameTeam['sumScore']?> <small class="subtle">points earned</small></td>
				</tr>
				<?endforeach;?>
				<?endif;?>
				<?for($s=0;$s<10-count($model->gameLeaders);$s++):?>
				<tr>
					<td style="text-align: left;"><i class="icon-minus td-fill"></i></td>
					<td style="text-align: right;">&nbsp;</td>
				</tr>
				<?endfor;?>
			</tbody>
		</table>
	</div>

</div>
