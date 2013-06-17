<?
namespace Destiny;
use Destiny\Utils\Tpl;
use Destiny\Utils\Lol;
use Destiny\Utils\Date;
?>
<?if(!empty($model->games) && count($model->games) >= 3):?>
<div class="content content-dark clearfix">
	<div class="games control-group clearfix">
	<?foreach($model->games as $game):?>
		<?
		$team1 = ( int ) $game ['gameWinSideId'];
		$team2 = ( int ) $game ['gameLoseSideId'];
		$teamChamps1 = $teamChamps2 = array ();
		foreach ( $game ['champions'] as $champ ) {
			if (( int ) $champ ['teamSideId'] == $team1) {
				$teamChamps1 [] = $champ;
			}
			if (( int ) $champ ['teamSideId'] == $team2) {
				$teamChamps2 [] = $champ;
			}
		}
		?>
		<div class="game clearfix pull-left" data-gameId="<?=$game['gameId']?>" style="width: 33.3333333%;">
			<div class="game-champions clearfix">
				<div class="game-team game-team1 game-team-win pull-left" style="width: 50%;">
					<?foreach($teamChamps1 as $champ1):?>
					<div class="game-champion clearfix">
						<div class="thumbnail">
							<a href="http://www.lolking.net/summoner/<?=$game['gameRegion']?>/<?=(int) $champ1['summonerId']?>"><img title="<?=Tpl::out($champ1['championName'])?>" src="<?=Config::cdn()?>/img/320x320.gif" data-src="<?=Lol::getIcon($champ1['championName'])?>" /></a>
						</div>
						<div class="summoner-detail">
							<div class="name">
								<a class="subtle-link" title="<?=Tpl::out($champ1['summonerName'])?>" href="http://www.lolking.net/summoner/<?=$game['gameRegion']?>/<?=(int) $champ1['summonerId']?>"><?=Tpl::out($champ1['summonerName'])?></a>
							</div>
							<div class="points"><?=Lol::getGameChampionPoints($game, $champ1, $model->userScores)?></div>
						</div>
					</div>
					<?endforeach;?>
					<?for($s=0;$s<5-count($teamChamps1);$s++):?>
					<div class="game-champion clearfix">
						<div class="thumbnail">
							<a href="#"><img src="<?=Config::cdn()?>/img/320x320.gif" /></a>
						</div>
						<div class="summoner-detail">
							<div class="name">
								<a>Unknown</a>
							</div>
							<div class="points">0</div>
						</div>
					</div>
					<?endfor;?>
				</div>

				<div class="game-team game-team2 game-team-lose pull-left" style="width: 50%;">
					<?foreach($teamChamps2 as $champ2):?>
					<div class="game-champion clearfix">
						<div class="thumbnail">
							<a href="http://www.lolking.net/summoner/<?=$game['gameRegion']?>/<?=(int) $champ2['summonerId']?>"><img title="<?=Tpl::out($champ2['championName'])?>" src="<?=Config::cdn()?>/img/320x320.gif" data-src="<?=Lol::getIcon($champ2['championName'])?>" /></a>
						</div>
						<div class="summoner-detail">
							<div class="name">
								<a class="subtle-link" title="<?=Tpl::out($champ2['summonerName'])?>" href="http://www.lolking.net/summoner/<?=$game['gameRegion']?>/<?=(int) $champ2['summonerId']?>"><?=Tpl::out($champ2['summonerName'])?></a>
							</div>
							<div class="points"><?=Lol::getGameChampionPoints($game, $champ2, $model->userScores)?></div>
						</div>
					</div>
					<?endforeach;?>
					<?for($s=0;$s<5-count($teamChamps2);$s++):?>
					<div class="game-champion clearfix">
						<div class="thumbnail">
							<a href="#"><img src="<?=Config::cdn()?>/img/320x320.gif" /></a>
						</div>
						<div class="summoner-detail">
							<div class="name">
								<a>Unknown</a>
							</div>
							<div class="points">0</div>
						</div>
					</div>
					<?endfor;?>
				</div>

			</div>
			<?
			$createdDate = Date::getDateTime ( $game ['gameCreatedDate'] );
			$endDate = Date::getDateTime ( $game ['gameEndDate'] );
			$length = round ( ($endDate->getTimestamp () - $createdDate->getTimestamp ()) / 60 );
			?>
			<small> <time class="pull-left" data-moment="true"><?=Date::getDateTime($game['gameCreatedDate'])->format(Date::FORMAT)?></time>
				<time class="pull-right"><?=($length>0) ? $length .' minutes':''?></time>
			</small>
		</div>
		
	<?endforeach;?>
	</div>

	<div id="checkRecentGamesSection" class="form-actions block-foot" style="text-align: center">
		<button class="btn" href="#checkRecentGames" data-lastcheck="<?=date(Date::FORMAT)?>">
			<i class="icon-refresh"></i> Check for new games
		</button>
	</div>
</div>
<?endif;?>