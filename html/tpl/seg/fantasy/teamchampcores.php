<?
namespace Destiny;
use Destiny\Utils\Tpl;
use Destiny\Utils\Lol;
?>
<?if(!empty($model->userChampScores) || !empty($model->topChampions)):?>
<div class="content content-dark clearfix">
	<div class="stream">
		<div class="control-group clearfix">
			<h4>Top league champions</h4>
			<?php if(!empty($model->topChampions)): ?>
			<?foreach($model->topChampions as $topChamp):?>
			<?$title = Tpl::out($topChamp['championName'])?>
			<div data-id="<?=(int) $topChamp['championId']?>" data-name="<?=$title?>" class="champion" style="float: left; width: 20%;">
				<div class="clearfix">
					<div title="<?=$title?>">
						<img style="max-width: 100%;" src="<?=Lol::getIcon($topChamp['championName'])?>">
						<div title="Score" style="text-align: center;">
							<i class="icon-rating"></i> <?=Tpl::n($topChamp['scoreValueSum'])?>
						</div>
					</div>
				</div>
			</div>
			<?endforeach;?>
			<?endif;?>
			<?for($s=0;$s<5-count($model->topChampions);$s++):?>
			<div class="champion" style="float: left; width: 20%;">
				<div class="clearfix">
					<div title="<?=$title?>">
						<img style="max-width: 100%;" title="None" src="<?=Config::cdn()?>/img/320x320.gif" />
						<div title="Score" style="text-align: center;">None</div>
					</div>
				</div>
			</div>
			<?endfor;?>
		</div>

	</div>
	<div class="stream">
		<div class="control-group clearfix">
			<h4>Your top champions</h4>
			<?php if(!empty($model->userChampScores)): ?>
			<?foreach($model->userChampScores as $champScore):?>
			<?$title = Tpl::out($champScore['championName'])?>
			<div data-id="<?=(int) $champScore['championId']?>" data-name="<?=$title?>" class="champion" style="float: left; width: 20%;">
				<div class="clearfix">
					<div title="<?=$title?>">
						<img style="max-width: 100%;" title="<?=Tpl::out($champScore['championName'])?>" src="<?=Config::cdn()?>/img/320x320.gif" data-src="<?=Lol::getIcon($champScore['championName'])?>" />
						<div title="Score" style="text-align: center;">
							<i class="icon-rating"></i> <?=Tpl::n($champScore['scoreValueSum'])?>
						</div>
					</div>
				</div>
			</div>
			<?endforeach;?>
			<?endif;?>
			<?for($s=0;$s<5-count($model->userChampScores);$s++):?>
			<div class="champion" style="float: left; width: 20%;">
				<div class="clearfix">
					<div title="<?=$title?>">
						<img style="max-width: 100%;" title="None" src="<?=Config::cdn()?>/img/320x320.gif" />
						<div title="Score" style="text-align: center;">None</div>
					</div>
				</div>
			</div>
			<?endfor;?>
		</div>
	</div>
</div>
<?endif;?>