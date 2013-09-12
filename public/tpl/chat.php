<?
namespace Destiny;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Config;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($model->title)?></title>
<meta charset="utf-8">
<meta name="description" content="<?=Config::$a['meta']['description']?>">
<meta name="keywords" content="<?=Config::$a['meta']['keywords']?>">
<meta name="author" content="<?=Config::$a['meta']['author']?>">
<?include'./tpl/seg/opengraph.php'?>
<?include'./tpl/seg/commontop.php'?>
<?include'./tpl/seg/google.tracker.php'?>
</head>
<body id="home">
	<?include'./tpl/seg/top.php'?>
	
	<section class="container">
		<iframe style="width: 100%; height: 540px; float:left; background: #111;border:none;" seamless="seamless" src="/embed/chat?theme=dark"></iframe>
	</section>
	
	<?include'./tpl/seg/panel.videos.php'?>
	<?include'./tpl/seg/panel.music.php'?>
	<?include'./tpl/seg/panel.calendar.php'?>
	<?include'./tpl/seg/panel.ads.php'?>
	<?include'./tpl/seg/foot.php'?>
	<?include'./tpl/seg/commonbottom.php'?>
</body>
</html>