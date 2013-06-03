<?
namespace Destiny;
use Destiny\Service\Settings;
use Destiny\Utils\Http;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Config::$a['meta']['title']?></title>
<meta charset="utf-8">
<meta name="description" content="<?=Config::$a['meta']['description']?>">
<meta name="keywords" content="<?=Config::$a['meta']['keywords']?>">
<meta name="author" content="<?=Config::$a['meta']['author']?>">
<meta property="og:site_name" content="<?=Config::$a['meta']['shortName']?>" />
<meta property="og:title" content="<?=Config::$a['meta']['title']?>"/>
<meta property="og:description" content="<?=Config::$a['meta']['description']?>" />
<meta property="og:image" content="<?=Config::cdn()?>/img/destinyspash600x600.png" />
<meta property="og:url" content="<?=Http::getBaseUrl()?>" />
<meta property="og:type" content="video.other" />
<meta property="og:video" content="<?=Config::$a['meta']['video']?>" />
<meta property="og:video:secure_url" content="<?=Config::$a['meta']['videoSecureUrl']?>" />
<meta property="og:video:type" content="application/x-shockwave-flash" />
<meta property="og:video:height" content="259" />
<meta property="og:video:width" content="398" />
<link href="<?=Config::cdn()?>/css/vendor/bootstrap.min.css" rel="stylesheet" media="screen">
<link href="<?=Config::cdn()?>/css/destiny.<?=Config::version()?>.css" rel="stylesheet" media="screen">
<link rel="shortcut icon" href="<?=Config::cdn()?>/favicon.png">
<?include'seg/google.tracker.php'?>
</head>
<body id="home">
	<?include'seg/top.php'?>
	
	<?if(Session::getAuthorized() && Settings::getInstance()->get('teambar_homepage')):?>
	<?include'seg/fantasy/teambar.php'?>
	<?include'seg/fantasy/teammaker.php'?>
	<?endif;?>
	
	<?include'seg/panel.twitch.php'?>
	<?include'seg/panel.lol.php'?>
	
	<?include'seg/panel.videos.php'?>
	<?include'seg/panel.music.php'?>
	<?include'seg/panel.calendar.php'?>
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