<?php
use Destiny\Common\Service\Fantasy\ChampionService;
use Destiny\Common\Utils\Lol;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Config;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($model->title)?></title>
<meta charset="utf-8">
<?include'./tpl/seg/opengraph.php'?>
<?include'./tpl/seg/commontop.php'?>
<?include'./tpl/seg/google.tracker.php'?>
</head>
<body id="group" class="league">
	<?include'./tpl/seg/top.php'?>
	
	<?include'./tpl/seg/fantasy/teambar.php'?>
	<?include'./tpl/seg/fantasy/teammaker.php'?>
	
	<section class="container">
	
		<?php include'./tpl/seg/fantasy/fantasysubnav.php' ?>
		
		<div class="tab-content">
			<div id="Challengers" class="tab-pane active clearfix">

				<div class="content content-dark clearfix">
				
					<table id="challengerGrid" class="grid">
						<thead>
							<tr>
								<td style="width: 100%">Leaders</td>
								<td style="text-align: right;">Score</td>
								<td style="text-align: right;">Credits</td>
								<td style="text-align: right;">Transfers</td>
								<td style="text-align: right;"></td>
							</tr>
						</thead>
						<tbody>
							<?foreach($model->challengers as $rank=>$topTeam):?>
							<?$title = Tpl::out($topTeam['username'])?>
							<tr>
								<td style="text-align: left;">
									<?if($topTeam['teamId'] != $model->team['teamId']):?>
									<a href="#removechallenger" class="remove-challenger" title="Remove" data-teamid="<?=intval($topTeam['teamId'])?>"><i class="icon-remove icon-white subtle"></i></a>
									<?endif;?>
									<?=Tpl::flag($topTeam['country'])?>
									<?=Tpl::subIcon($topTeam['subscriber'])?>
									<?=$title?>
								</td>
								<td style="text-align: right;"><?=Tpl::n($topTeam['scoreValue'])?></td>
								<td style="text-align: right;"><?=Tpl::n($topTeam['credits'])?></td>
								<td style="text-align: right;"><?=Tpl::n($topTeam['transfersRemaining'])?></td>
								<td style="text-align: right;">
									<div class="team-champions" style="width:<?=(25*Config::$a['fantasy']['team']['maxChampions'])?>px;">
									<?$champions = ChampionService::instance ()->getChampionsById (explode(',', $topTeam['champions']));?>
									<?foreach($champions as $champion):?>
										<div class="thumbnail">
											<img title="<?=Tpl::out($champion['championName'])?>" style="width: 25px; height: 25px;" src="<?=Config::cdn()?>/web/img/64x64.gif" data-src="<?=Lol::getIcon($champion['championName'])?>" />
										</div>
									<?endforeach;?>
									<?for($i=0;$i<Config::$a['fantasy']['team']['maxChampions']-count($champions);$i++):?>
										<div class="thumbnail">
											<img style="width: 25px; height: 25px;" src="<?=Config::cdn()?>/web/img/64x64.gif" />
										</div>
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
							<button class="btn" type="submit">Challenge!</button>
						</div>
					</form>
				
				</div>
			
			
			</div>
		</div>
	</section>
	
	<?include'./tpl/seg/panel.ads.php'?>
	<?include'./tpl/seg/foot.php'?>
	<?include'./tpl/seg/commonbottom.php'?>
</body>
</html>