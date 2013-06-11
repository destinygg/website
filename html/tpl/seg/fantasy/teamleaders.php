<?

namespace Destiny;

use Destiny\Utils\Tpl;
use Destiny\Utils\Lol;

?>
<section class="container">
	<div class="content content-dark stream-grids clearfix">
		<div class="stream stream-grid">
			<table class="grid" style="width: 100%;">
				<thead>
					<tr>
						<td style="width: 100%">Leaders</td>
						<td style="text-align: right;">Score</td>
						<td style="text-align: right;"></td>
					</tr>
				</thead>
				<tbody>
					<?php if(!empty($model->leaderboard)): ?>
					<?foreach($model->leaderboard as $rank=>$topTeam):?>
					<?$title = Tpl::out($topTeam['displayName'])?>
					<tr>
						<td style="text-align: left;">
							<?=Tpl::flag($topTeam['country'])?>
							<?=Tpl::subIcon($topTeam['subscriber'])?>
							<?=$title?>
						</td>
						<td style="text-align: right;"><?=Tpl::n($topTeam['scoreValue'])?></td>
						<td style="text-align: right;">
							<div class="team-champions" style="width:<?=(25*Config::$a['fantasy']['team']['maxChampions'])?>px;">
							<?$champions = $topTeam['champions'];?>
							<?foreach($champions as $champion):?>
							<div class="thumbnail">
								<img title="<?=Tpl::out($champion['championName'])?>" style="width: 25px; height: 25px;" src="<?=Config::cdn()?>/img/64x64.gif" data-src="<?=Lol::getIcon($champion['championName'])?>" />
							</div>
							<?endforeach;?>
							<?for($i=0;$i<Config::$a['fantasy']['team']['maxChampions']-intval(count($champions));$i++):?>
							<div class="thumbnail">
								<img style="width: 25px; height: 25px;" src="<?=Config::cdn()?>/img/64x64.gif" />
							</div>
							<?endfor;?>
						</div>
						</td>
					</tr>
					<?endforeach;?>
					<?endif;?>
					<?for($s=0;$s<10-count($model->leaderboard);$s++):?>
					<tr>
						<td style="text-align: left;">-</td>
						<td style="text-align: right;">&nbsp;</td>
						<td style="text-align: right;">&nbsp;</td>
					</tr>
					<?endfor;?>
				</tbody>
			</table>
		</div>

		<div class="stream stream-grid">
			<table class="grid" style="width: 100%;">
				<thead>
					<tr>
						<td style="width: 100%">Recent match leaders</td>
						<td style="text-align: right;">Score</td>
						<td style="text-align: right;"></td>
					</tr>
				</thead>
				<tbody>
					<?php if(!empty($model->gameLeaders)): ?>
					<?foreach($model->gameLeaders as $gameRank=>$gameTeam):?>
					<?$title = Tpl::out($gameTeam['displayName'])?>
					<tr>
						<td style="text-align: left;">
							<?=Tpl::flag($gameTeam['country'])?>
							<?=Tpl::subIcon($gameTeam['subscriber'])?>
							<?=$title?>
						</td>
						<td style="text-align: right;"><?=$gameTeam['sumScore']?></td>
						<td style="text-align: right;">
						<?$weekChampions = $gameTeam['champions'];?>
							<div class="team-champions" style="width:<?=(25*Config::$a['fantasy']['team']['maxChampions'])?>px;">
							<?foreach($weekChampions as $weekChampion):?>
								<div class="thumbnail">
									<img title="<?=Tpl::out($weekChampion['championName'])?>" style="width: 25px; height: 25px;" src="<?=Config::cdn()?>/img/64x64.gif" data-src="<?=Lol::getIcon($weekChampion['championName'])?>" />
								</div>
							<?endforeach;?>
							<?for($x=0;$x<Config::$a['fantasy']['team']['maxChampions']-count($weekChampions);$x++):?>
								<div class="thumbnail">
									<img style="width: 25px; height: 25px;" src="<?=Config::cdn()?>/img/64x64.gif" />
								</div>
							<?endfor;?>
							</div>
						</td>
					</tr>
					<?endforeach;?>
					<?endif;?>
					<?for($s=0;$s<10-count($model->gameLeaders);$s++):?>
					<tr>
						<td style="text-align: left;">-</td>
						<td style="text-align: right;">&nbsp;</td>
						<td style="text-align: right;">&nbsp;</td>
					</tr>
					<?endfor;?>
				</tbody>
			</table>
		</div>

	</div>
</section>