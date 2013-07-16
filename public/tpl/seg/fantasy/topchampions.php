<?
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Utils\Lol;
use Destiny\Common\Config;
?>
<?if(!empty($model->topChampions)):?>
<div class="content content-dark clearfix">
	<div class="stream" style="width: 100%">
		<div class="control-group clearfix">
			<h4>Top league champions</h4>
			<?php if(!empty($model->topChampions)): ?>
			<?foreach($model->topChampions as $topChamp):?>
			<?$title = Tpl::out($topChamp['championName'])?>
			<div data-id="<?=(int) $topChamp['championId']?>" data-name="<?=$title?>" class="champion" style="float: left; width: 10%;">
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
			<?for($s=0;$s<5-(($model->topChampions) ? count($model->topChampions):0);$s++):?>
			<div class="champion" style="float: left; width: 10%;">
				<div class="clearfix">
					<div title="<?=$title?>">
						<img style="max-width: 100%;" title="None" src="<?=Config::cdn()?>/web/img/320x320.gif" />
						<div title="Score" style="text-align: center;">None</div>
					</div>
				</div>
			</div>
			<?endfor;?>
		</div>

	</div>
</div>
<?endif;?>