<?
namespace Destiny;
use Destiny\Utils\Date;
use Destiny\Utils\Http;
use Destiny\Utils\Lol;
use Destiny\Utils\Tpl;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($model->title)?></title>
<meta charset="utf-8">
<meta name="description" content="League of Legends Fantasy League. Free to play">
<meta name="keywords" content="League of Legends,Fantasy League,Free to play">
<meta name="author" content="<?=Config::$a['meta']['author']?>">
<meta property="og:site_name" content="<?=Config::$a['meta']['shortName']?>" />
<meta property="og:title" content="<?=Config::$a['meta']['shortName']?> : Fantasy League" />
<meta property="og:description" content="League of Legends Fantasy League. Free to play" />
<meta property="og:image" content="<?=Config::cdn()?>/img/destinyspash600x600.png" />
<meta property="og:url" content="<?=Http::getBaseUrl()?>" />
<meta property="og:type" content="video.other" />
<meta property="og:video" content="<?=Config::$a['meta']['video']?>" />
<meta property="og:video:secure_url" content="<?=Config::$a['meta']['videoSecureUrl']?>" />
<meta property="og:video:type" content="application/x-shockwave-flash" />
<meta property="og:video:height" content="259" />
<meta property="og:video:width" content="398" />
<link href="<?=Config::cdn()?>/css/bootstrap.min.css" rel="stylesheet" media="screen">
<link href="<?=Config::cdn()?>/css/destiny.<?=Config::version()?>.css" rel="stylesheet" media="screen">
<link rel="shortcut icon" href="<?=Config::cdn()?>/favicon.png">
<?include'./tpl/seg/google.tracker.php'?>
</head>
<body id="help" class="league">
	<?include'./tpl/seg/top.php'?>
	
	<?if(!Session::hasRole(\Destiny\UserRole::USER)):?>
	<?include'./tpl/seg/fantasy/calltoaction.php'?>
	<?endif;?>
	
	<?if(Session::hasRole(\Destiny\UserRole::USER)):?>
	<?include'./tpl/seg/fantasy/teambar.php'?>
	<?include'./tpl/seg/fantasy/teammaker.php'?>
	<?endif;?>
	
	<section class="container">
	
		<?php include'./tpl/seg/fantasy/fantasysubnav.php' ?>
		
		<div class="tab-content">
			<div id="Help" class="tab-pane active clearfix">

				<div class="content content-dark clearfix">
					<div class="clearfix">
						<div class="clearfix pull-left" style="width: 33.33333%;">
							<div class="control-group">
								<h4>Points <small class="subtle">(per game)</small></h4>
								<ul class="unstyled">
									<li><strong style="color: #1a6f00;"><?=Config::$a['fantasy']['scores']['PARTICIPATE']?></strong> point(s) are given for participation</li>
									<li><strong style="color: #1a6f00;"><?=Config::$a['fantasy']['scores']['WIN']?></strong> point(s) per champion on the winning team</li>
									<li>Receive <strong style="color: #b19e00;"><?=Config::$a['fantasy']['milestones'][0]['reward']['value']?></strong> transfer(s) every <strong><?=Config::$a['fantasy']['milestones'][0]['goalValue']?></strong> games</li>
									<li>Receive <strong style="color: #b19e00;"><?=Config::$a['fantasy']['credit']['scoreToCreditEarnRate']?></strong> credit(s) per point earned</li>
								</ul>
							</div>
						</div>
						<div class="clearfix pull-left" style="width: 33.33333%;">
							<div class="control-group">
								<h4>Limits <small class="subtle">(per team)</small></h4>
								<ul class="unstyled">
									<?if(Config::$a['fantasy']['team']['maxChampions'] != Config::$a['fantasy']['team']['minChampions']):?>
									<li><strong><?=Config::$a['fantasy']['team']['maxChampions']?></strong> maximum, <strong><?=Config::$a['fantasy']['team']['minChampions']?></strong> minimum champions</li>
									<?endif;?>
									<?if(Config::$a['fantasy']['team']['maxChampions'] == Config::$a['fantasy']['team']['minChampions']):?>
									<li><strong style="color: #b19e00;"><?=Config::$a['fantasy']['team']['minChampions']?></strong> champions required to make a team</li>
									<?endif;?>
									<li><strong style="color: #b19e00;"><?=Config::$a['fantasy']['team']['maxAvailableTransfers']?></strong>maximum available transfers</li>
									<li>Teams start with <strong style="color: #b19e00;"><?=Config::$a['fantasy']['team']['startCredit']?></strong> credit and <strong style="color: #b19e00;"><?=Config::$a['fantasy']['team']['startTransfers']?></strong> transfers</li>
								</ul>
							</div>
						</div>
						<div class="clearfix pull-left" style="width: 33.33333%;">
							<div class="control-group">
								<h4>Free champions</h4>
								<ul class="unstyled">
									<li><strong style="color: #8a1919;">-<?=(Config::$a['fantasy']['team']['freeMultiplierPenalty']*100)?>%</strong> score penalty for free champion(s) points earned</li>
									<li>Free champions that are <strong>unlocked</strong> receive full points</li>
									<li>Free champions are rotated every 3 day(s)</li>
									<li>Champions that are <span style="text-decoration: underline;">not free</span> and <span style="text-decoration: underline;">not owned</span> do not earn points</li>
								</ul>
							</div>
						</div>
					</div>
					<div class="clearfix">
						<div class="clearfix pull-left" style="width: 33.33333%;">
							<div class="control-group">
								<h4>Champion multipliers</h4>
								<p>
									Each champion has their own score multiplier. 
									<br />Score * (1 - ((X/Y) * (Z/X))).
									<br /> X = Total games played by champion
									<br /> Y = Most played games by a single champion
									<br /> Z = Total games won by champion
								</p>
							</div>
						</div>
						<div class="clearfix pull-left" style="width: 33.33333%;">
							<div class="control-group">
								<h4>Games</h4>
								<ul class="unstyled">
									<li>Games are automatically recorded, A delay up to 15 minutes can occur between each game</li>
									<li>Champions must be in the team at the time of the update to earn points</li>
									<li>Only champions which are picked <strong>before</strong> live games begin earn points
									</li>
								</ul>
							</div>
						</div>
						<div class="clearfix pull-left" style="width: 33.33333%;">
							<div class="control-group">
								<h4>Teammate bonus</h4>
								<p> 
									Bonus points are given for each teammate.
									<br /> Score+round(Score *((a-1)/(<?=Config::$a['fantasy']['team']['maxPotentialChamps']?>-1))*<?=Config::$a['fantasy']['team']['teammateBonusModifier']?>).
								</p>
							</div>
						</div>
					</div>
				</div>
			
			
			</div>
		</div>
	</section>
	
	<?include'./tpl/seg/panel.ads.php'?>
	<?include'./tpl/seg/foot.php'?>
	
	<script src="<?=Config::cdn()?>/js/vendor/jquery-1.9.1.min.js"></script>
	<script src="<?=Config::cdn()?>/js/vendor/jquery.cookie.js"></script>
	<script src="<?=Config::cdn()?>/js/vendor/bootstrap.js"></script>
	<script src="<?=Config::cdn()?>/js/vendor/moment.js"></script>
	<script src="<?=Config::cdn()?>/js/destiny.<?=Config::version()?>.js"></script>
	<script>destiny.init({cdn:'<?=Config::cdn()?>'});</script>
</body>
</html>