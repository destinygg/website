<?php
use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\Lol;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Config;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($model->title)?></title>
<meta charset="utf-8">
<?include'./tpl/seg/commontop.php'?>
</head>
<body id="admin">

	<?include'./tpl/seg/top.php'?>
	
	<section class="container" id="admintabs">
		<h1 class="page-title">Administration</h1>
		<div class="navbar navbar-inverse navbar-subnav">
			<div class="navbar-inner">
				<form class="navbar-form" id="user-search">
					&nbsp;<input type="text" class="span2" placeholder="Enter a username..." autocomplete="off">
					<button type="submit" class="btn btn-inverse">Edit</button>
				</form>
			</div>
		</div>
	</section>
	
	<?include'./tpl/seg/commonbottom.php'?>
	
	<script src="<?=Config::cdnv()?>/web/js/admin.js"></script>
	
</body>
</html>