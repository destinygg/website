<?
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Utils\Lol;
use Destiny\Common\Config;
?>
<?php if(!empty($model->team)):?>
<?php $team = $model->team?>
<?$team['credits'] = floor((int) $team['credits'])?>
<section class="container fantasy-team-bar" id="userBar" data-team="<?=Tpl::out(json_encode($team))?>">
	<div class="content content-dark clearfix">

		<h3 class="title">
			<span class="username">
				<?=Tpl::flag($model->user['country'])?><?=Tpl::out($model->user['username'])?>
			</span><?if(!empty($team['teamRank'])):?><small class="subtle">Rank <?=Tpl::n($team['teamRank'])?></small><?endif;?>
		</h3>

		<div class="team-stats">
			<span class="team-stat scoreValue" title="Score" rel="tooltip" data-placement="bottom">
				<i class="icon-rating"></i><span class="stat-value"><?=Tpl::n($team['scoreValue'])?></span>
			</span>
			<span class="team-stat credits" title="Credits" rel="tooltip" data-placement="bottom">
				<i class="icon-money"></i><span class="stat-value"><?=$team['credits']?></span>
			</span>
			<span class="team-stat transfersRemaining" title="Transfers Remaining" rel="tooltip" data-placement="bottom">
				<i class="icon-transfers"></i><span class="stat-value"><?=Tpl::n($team['transfersRemaining'])?></span>
			</span>
		</div>
		<div class="team-champions">
			<div class="clearfix pull-left team-champions-slots">
				<?$w = (100/Config::$a['fantasy']['team']['maxChampions'])?>
				<?foreach ($model->teamChamps as $i => $champ):?>
					<?$champCss = ($champ['unlocked'] == '1') ? ' champion-unlocked': (($champ['championFree'] == '1') ? ' champion-free':' champion-illegal')?>
					<?$name = Tpl::out($champ['championName'])?>
					<div title="<?=$name?>" data-champion="<?=Tpl::out(json_encode($champ))?>" class="champion-slot champion-slot-full<?=$champCss?>" style="width:<?=$w?>%; float:left;">
					<div class="thumbnail">
						<img alt="<?=$name?>" src="<?=Config::cdn()?>/web/img/320x320.gif" data-src="<?=Lol::getIcon($champ['championName'])?>" />
						<div class="th-overlay" title="Champion is no longer free!"></div>
					</div>
				</div>
				<?endforeach;?>
				<?for ($i=0;$i<Config::$a['fantasy']['team']['maxChampions']-count($model->teamChamps); $i++):?>
					<div class="champion-slot champion-slot-empty" style="width:<?=$w?>%; float:left;">
					<div class="thumbnail">
						<img src="<?=Config::cdn()?>/web/img/320x320.gif" />
					</div>
				</div>
				<?endfor;?>
			</div>
		</div>

	</div>
</section>
<?endif;?>