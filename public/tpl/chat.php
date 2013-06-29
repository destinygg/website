<?
namespace Destiny;
use Destiny\Utils\Http;
use Destiny\Utils\Tpl;
use Destiny\Utils\Date;
use Destiny\Session;
use Destiny\Config;
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
		<h2 class="title">Dark theme</h2>
		<iframe style="width: 100%; height: 500px; float:left;" frameborder="0" scrolling="no" src="/embed/chat?theme=dark"></iframe>
	</section>
	<section class="container">
		<h2 class="title">Light theme</h2>
		<iframe style="width: 100%; height: 500px; float:left;" frameborder="0" scrolling="no" src="/embed/chat?theme=light"></iframe>
	</section>
	
	<?include'./tpl/seg/foot.php'?>
	<?include'./tpl/seg/commonbottom.php'?>
</body>
</html>