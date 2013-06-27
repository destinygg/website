<?php 
namespace Destiny;
use Destiny\Config;
use Destiny\Utils\Tpl;
use Destiny\Utils\Lol;
use Destiny\Utils\Date;
?>
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
								<img title="<?=Tpl::out($teamGameScore1['championName'])?>" style="width: 20px; height: 20px;" src="<?=Config::cdn()?>/web/img/64x64.gif" data-src="<?=Lol::getIcon($teamGameScore1['championName'])?>" />
								<?php endif; ?>
								<?php endforeach; ?>
							</div>
							<span class="pull-left subtle" style="padding:0 5px;">vs</span>
							<div class="pull-left game-team game-team-purple">
								<?php foreach($model->teamGameChampScores as $teamGameScore2): ?>
								<?php if($teamGameScore2['gameId'] == $gameScore['gameId'] && $teamGameScore2['teamSideId'] == Lol::TEAMSIDE_PURPLE): ?>
								<img title="<?=Tpl::out($teamGameScore2['championName'])?>" style="width: 20px; height: 20px;" src="<?=Config::cdn()?>/web/img/64x64.gif" data-src="<?=Lol::getIcon($teamGameScore2['championName'])?>" />
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
					<img title="<?=Tpl::out($topChamp['championName'])?>" style="width: 20px; height: 20px;" src="<?=Config::cdn()?>/web/img/64x64.gif" data-src="<?=Lol::getIcon($topChamp['championName'])?>" />
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