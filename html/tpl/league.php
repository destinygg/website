<?
namespace Destiny;
use Destiny\Utils\Http;
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
<?include'seg/google.tracker.php'?>
</head>
<body id="league">
	<?include'seg/top.php'?>
	
	<?if(!Session::authorized()):?>
	<?include'seg/fantasy/calltoaction.php'?>
	<?endif;?>
	
	<?if(Session::authorized()):?>
	<?include'seg/fantasy/teambar.php'?>
	<?include'seg/fantasy/teammaker.php'?>
	
	<section class="container">
		<div style="margin:0;" class="navbar navbar-inverse">
			<div class="navbar-inner">
				<ul class="nav">
					<li class="active"><a href="#RecentGames" data-toggle="tab" title="Recent games">Games</a></li>
					<li><a href="#Challengers" data-toggle="tab" title="Your challenge group">Group</a></li>
					<li><a href="#Invites" data-toggle="tab" title="Your challenge invites">Invites<?=(count($model->invites) > 0)? ' <span class="badge badge-success">'. count($model->invites) .'</span>':''?></a></li>
					<li><a href="#Help" data-toggle="tab" title="Help &amp; About">Scoring</a></li>
				</ul>
				<ul class="nav pull-right">
					<li id="serverStatus">
						<?include'seg/fantasy/serverstatus.php'?>
					</li>
				</ul>
			</div>
		</div>
		<div class="tab-content">
			<div id="RecentGames" class="tab-pane active clearfix">
				<?include'seg/fantasy/ingame.php'?>
				<?include'seg/fantasy/teamgamescores.php'?>
				<?include'seg/fantasy/topsummoners.php'?>
				<?include'seg/fantasy/teamchampcores.php'?>
			</div>
			<div id="Challengers" class="tab-pane clearfix">
				<?include'seg/fantasy/challengers.php'?>
			</div>
			<div id="Invites" class="tab-pane clearfix">
				<?include'seg/fantasy/invites.php'?>
			</div>
			<div id="Help" class="tab-pane clearfix">
				<?include'seg/fantasy/score.legend.php'?>
			</div>
		</div>
	</section>
	<?endif;?>
	
	<?include'seg/fantasy/teamleaders.php'?>
	<?include'seg/panel.ads.php'?>
	<?include'seg/foot.php'?>
	
	<script src="<?=Config::cdn()?>/js/vendor/jquery-1.9.1.min.js"></script>
	<script src="<?=Config::cdn()?>/js/vendor/jquery.cookie.js"></script>
	<script src="<?=Config::cdn()?>/js/vendor/bootstrap.js"></script>
	<script src="<?=Config::cdn()?>/js/vendor/moment.js"></script>
	<script src="<?=Config::cdn()?>/js/destiny.<?=Config::version()?>.js"></script>
	<script>destiny.init({cdn:'<?=Config::cdn()?>'});</script>
</body>
</html>