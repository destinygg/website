<?
namespace Destiny;
use Destiny\Utils\Tpl;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Config::$a['meta']['shortName']?> : Administration</title>
<meta charset="utf-8">
<link href="<?=Config::cdn()?>/css/vendor/bootstrap.min.css" rel="stylesheet" media="screen">
<link href="<?=Config::cdn()?>/css/destiny.<?=Config::version()?>.css" rel="stylesheet" media="screen">
<link href="<?=Config::cdn()?>/css/destiny.admin.<?=Config::version()?>.css" rel="stylesheet" media="screen">
<link rel="shortcut icon" href="<?=Config::cdn()?>/favicon.png">
</head>
<body>

	<?include'seg/top.php'?>
	
	<section class="container" id="admintabs">
	
		<h1 class="page-title">
			<span>Administration</span> 
			<small>(<a title="<?=Tpl::out(Session::$user['email'])?>" href="mailto:<?=Tpl::out(Session::$user['email'])?>"><?=Tpl::out(Session::$user['displayName'])?></a>)</small>
		</h1>
		
		<?include'admin/games.php'?>
		<hr size="1" />
		<?include'admin/logs.php'?>
		
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