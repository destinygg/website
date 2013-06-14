<?
namespace Destiny;
use Destiny\Utils\Tpl;
?>
<?if((bool) Config::$a['blocks']['lol']): ?>
<section id="lolpanel" class="container">
	<div class="content content-dark content-split clearfix">
	<?if(!empty($model->summoners)):?>
	<?foreach($model->summoners as $summoner):?>
		<div id="summoner-<?=$summoner['summonerId']?>"
			class="summoner-stub clearfix">

			<div class="summoner-info pull-left">
				<div class="summoner-info-stub pull-left">
					<h3 class="summoner-title">
						<?=Tpl::out($summoner['name'])?> 
						<small><a title="LOLKING profile" href="http://www.lolking.net/summoner/<?=$summoner['region']['id']?>/<?=$summoner['summonerId']?>">lolking.com</a></small>
					</h3>
					<span class="summoner-region">
						<?=$summoner['region']['label']?>
						<?if(isset($summoner['summonerLevel'])):?>
						 - Level <?=$summoner['summonerLevel']?>
						<?endif;?>
						<?if(isset($summoner['league'])):?>
						 - Elo <?=$summoner['league']['approximateElo']?>
						<?endif;?>
					</span>
				</div>
			</div>
			
			<?if($summoner['league'] == null):?>
			<div class="summoner-rank-info unranked pull-right">
				<div class="pull-left summon-rank-display">
					<div class="summoner-rank unranked pull-left">Unknown</div>
					<div class="summoner-rank-thumbnail pull-left">
						<i title="Unknown" style="width:45px; height:45px; background: url(<?=Config::cdn()?>/img/lol/rank/unknown.png) no-repeat center center; background-size: 60px 60px;"></i>
					</div>
				</div>
			</div>
			<?else:?>
			
			<?
			$position = $summoner ['league'] ['position'];
			$positionOffset = $position - $summoner ['league'] ['previousDayLeaguePosition'];
			?>
			
			<div class="summoner-rank-info ranked pull-right">
			
				<?if(isset($summoner['league']['miniSeries'])):?>
				<div class="pull-left summon-rank-stats summon-mini-series">
					<div>
						<span style="color: #b19e00;"><?=$summoner['league']['miniSeries']['target']?></span>
						target game(s)
					</div>
					<div>
						<span style="color: #1a6f00;"><?=$summoner['league']['miniSeries']['wins']?></span>
						/ <span style="color: #8a1919;"><?=$summoner['league']['miniSeries']['losses']?></span>
						Win Loss
					</div>
				</div>
				<?endif;?>
				
				<div class="pull-left summon-rank-stats">
					<div><?=Tpl::out($summoner['league']['leagueName'])?></div>
					<div>
						<span data-placement="left" rel="tooltip"
							title="Previous day position <?=$summoner['league']['previousDayLeaguePosition']?>">
							Position <i class="icon-arrow-<?=(($positionOffset > 0) ? 'down':'up')?> icon-white"></i>
						</span>
						<span data-placement="left" rel="tooltip" title="Out of <?=$summoner['league']['totalEntries']?>" style="color:<?=($positionOffset > 0) ? '#8a1919':'#1a6f00'?>;"><?=$position?></span>
						<?if(isset($summoner['league']['hotStreak']) && $summoner['league']['hotStreak'] == true):?>
						<i data-placement="left" rel="tooltip" class="icon-fire icon-white" title="HOOOOOTTT STTTRRREEEAAAAAKKKKKK!"></i>
						<?endif;?>
						<?if(isset($summoner['league']['freshBlood']) && $summoner['league']['freshBlood'] == true):?>
						<i data-placement="left" rel="tooltip" class="icon-tint icon-white" title="Fresh Meat!"></i>
						<?endif;?>
					</div>
				</div>

				<div class="pull-left summon-rank-stats">
					<div>
						<span style="color: #b19e00;"><?=$summoner['league']['leaguePoints']?></span>
						League Points
					</div>
					<div>
						<span style="color: #1a6f00;"><?=$summoner['league']['wins']?></span>
						/ <span style="color: #8a1919;"><?=$summoner['league']['losses']?></span>
						Win Loss
					</div>
				</div>
				<div class="pull-left summon-rank-display">
					<div class="summoner-rank ranked pull-left">
						<span style="text-transform: capitalize;"><?=strtolower($summoner['league']['tier'])?></span> <?=$summoner['league']['rank']?>
					</div>
					<div class="summoner-rank-thumbnail pull-left">
						<i data-placement="left" rel="tooltip" title="<?=$summoner['league']['tier']?> <?=$summoner['league']['rank']?>" style="width:45px; height:45px; background: url(<?=Config::cdn()?>/img/lol/rank/<?=strtolower($summoner['league']['tier'])?>_<?=$summoner['league']['rankInt']?>.png) no-repeat center center; background-size: 60px 60px;"></i>
					</div>
				</div>
			</div>
			
		<?endif;?>
		</div>
	<?endforeach;?>
	<?else:?>
		<p class="loading" style="text-align: center;">Loading statistics...</p>
	<?endif;?>
	</div>
</section>
<?endif;?>