<?
namespace Destiny;
use Destiny\Utils\Tpl;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($model->title)?></title>
<meta charset="utf-8">
<link href="<?=Config::cdn()?>/css/bootstrap.min.css" rel="stylesheet" media="screen">
<link href="<?=Config::cdn()?>/css/destiny.<?=Config::version()?>.css" rel="stylesheet" media="screen">
<link href="<?=Config::cdn()?>/css/destiny.admin.<?=Config::version()?>.css" rel="stylesheet" media="screen">
<link rel="shortcut icon" href="<?=Config::cdn()?>/favicon.png">
</head>
<body id="admin">

	<?include'seg/top.php'?>
	
	<section class="container" id="admintabs">

		<h1 class="page-title">
			<span>Administration</span> <small>(<a title="<?=Tpl::out(Session::get('email'))?>" href="mailto:<?=Tpl::out(Session::get('email'))?>"><?=Tpl::out(Session::get('displayName'))?></a>)
			</small>
		</h1>
		<hr size="1" />

		<h3 class="title">Fantasy League</h3>
		<?php include'admin/games.php'?>
		<div style="margin: 20px 0;">
			<p>
				<a href="#Events" rel="Aggregate" class="btn btn-inverse btn-cron-action">Aggregate recent game(s)</a>
				<a href="#Events" rel="Recentgames" class="btn btn-inverse btn-cron-action">Check recent games</a>
				<a href="#Events" rel="Ingame" class="btn btn-inverse btn-cron-action">Check ingame progress</a>
			</p>
		</div>
		<hr size="1" />

		<h3 class="title">Logs</h3>
		<?include'admin/logs.php'?>
		<hr size="1" />

	</section>
	
	<?include'seg/foot.php'?>
	
	<script src="<?=Config::cdn()?>/js/vendor/jquery-1.9.1.min.js"></script>
	<script src="<?=Config::cdn()?>/js/vendor/jquery.cookie.js"></script>
	<script src="<?=Config::cdn()?>/js/vendor/bootstrap.js"></script>
	<script src="<?=Config::cdn()?>/js/vendor/moment.js"></script>
	<script src="<?=Config::cdn()?>/js/destiny.<?=Config::version()?>.js"></script>
	<script src="<?=Config::cdn()?>/js/destiny.admin.<?=Config::version()?>.js"></script>
	<script>destiny.init({cdn:'<?=Config::cdn()?>'});</script>
</body>
</html>