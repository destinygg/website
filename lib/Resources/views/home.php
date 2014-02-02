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
<?php include Tpl::file('seg/opengraph.php') ?>
<?php include Tpl::file('seg/commontop.php') ?>
<?php include Tpl::file('seg/google.tracker.php') ?>
</head>
<body id="home">
	<?php include Tpl::file('seg/top.php') ?>
	<?php include Tpl::file('seg/headerband.php') ?>
	<?php include Tpl::file('seg/livebanner.php') ?>
	<?php include Tpl::file('seg/panel.videos.php') ?>
	<?php include Tpl::file('seg/panel.music.php') ?>
	<?php include Tpl::file('seg/panel.calendar.php') ?>
	<?php include Tpl::file('seg/panel.ads.php') ?>
	<?php include Tpl::file('seg/foot.php') ?>
	<?php include Tpl::file('seg/commonbottom.php') ?>
</body>
</html>