<?php
namespace Destiny;
use Destiny\Utils\Tpl;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($model->title)?></title>
<meta charset="utf-8">
<?include'./tpl/seg/commontop.php'?>
<?include'./tpl/seg/google.tracker.php'?>
</head>
<body id="schedule">
	<?include'./tpl/seg/top.php'?>
	<?include'./tpl/seg/embed.calendar.php'?>
	<?include'./tpl/seg/panel.calendar.php'?>
	<?include'./tpl/seg/panel.videos.php'?>
	<?include'./tpl/seg/panel.ads.php'?>
	<?include'./tpl/seg/foot.php'?>
	<?include'./tpl/seg/commonbottom.php'?>
</body>
</html>