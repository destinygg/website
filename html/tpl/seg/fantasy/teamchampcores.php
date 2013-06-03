<?
namespace Destiny;
use Destiny\Utils\Tpl;
use Destiny\Utils\Lol;
?>
<?$userChampScores = \Destiny\Service\Fantasy\Db\Leaderboard::getInstance()->getTeamChampionScores (Session::$team['teamId'], 5);?>
<?$topChampions = \Destiny\Service\Fantasy\Cache::getInstance()->getTopTeamChampionScores (array(), 5);?>
<?if(!empty($userChampScores) || !empty($topChampions)):?>
<div class="content content-dark clearfix">
	<div class="stream">
		<h3 class="title" style="border: none">Top league champions</h3>
		<div style="margin:0 15px 5px 15px;" class="clearfix">
			<?foreach($topChampions as $topChamp):?>
			<?$title = Tpl::out($topChamp['championName'])?>
			<div data-id="<?=(int) $topChamp['championId']?>" data-name="<?=$title?>" class="champion" style="float:left; width: 20%;">
				<div class="clearfix">
					<div title="<?=$title?>">
						<img style="max-width:100%;" src="<?=Lol::getIcon($topChamp['championName'])?>">
						<div title="Score" style="text-align: center;"><i class="icon-rating"></i> <?=Tpl::n($topChamp['scoreValueSum'])?></div>
					</div>
				</div>
			</div>
			<?endforeach;?>
			<?for($s=0;$s<5-count($topChampions);$s++):?>
			<div class="champion" style="float:left; width: 20%;">
				<div class="clearfix">
					<div title="<?=$title?>">
						<img style="max-width:100%;" title="None" src="<?=Config::cdn()?>/img/320x320.gif" />
						<div title="Score" style="text-align: center;">None</div>
					</div>
				</div>
			</div>
			<?endfor;?>
		</div>
	</div>
	<div class="stream">
		<h3 class="title" style="border: none">Your top champions</h3>
		<div style="margin:0 15px 5px 15px;" class="clearfix">
			<?foreach($userChampScores as $champScore):?>
			<?$title = Tpl::out($champScore['championName'])?>
			<div data-id="<?=(int) $champScore['championId']?>" data-name="<?=$title?>" class="champion" style="float:left; width: 20%;">
				<div class="clearfix">
					<div title="<?=$title?>">
						<img style="max-width:100%;" title="<?=Tpl::out($champScore['championName'])?>" src="<?=Config::cdn()?>/img/320x320.gif" data-src="<?=Lol::getIcon($champScore['championName'])?>" />
						<div title="Score" style="text-align: center;"><i class="icon-rating"></i> <?=Tpl::n($champScore['scoreValueSum'])?></div>
					</div>
				</div>
			</div>
			<?endforeach;?>
			<?for($s=0;$s<5-count($userChampScores);$s++):?>
			<div class="champion" style="float:left; width: 20%;">
				<div class="clearfix">
					<div title="<?=$title?>">
						<img style="max-width:100%;" title="None" src="<?=Config::cdn()?>/img/320x320.gif" />
						<div title="Score" style="text-align: center;">None</div>
					</div>
				</div>
			</div>
			<?endfor;?>
		</div>
	</div>
</div>
<?endif;?>