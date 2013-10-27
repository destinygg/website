<?php
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Config;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($model->title)?></title>
<meta charset="utf-8">
<?php include Tpl::file('seg/commontop.php') ?>
</head>
<body id="admin">

	<?php include Tpl::file('seg/top.php') ?>
	
	<section class="container">
	
		<h1 class="page-title">Administration</h1>
		<div class="navbar navbar-inverse navbar-subnav">
			<div class="navbar-inner">
				<form class="navbar-form" id="user-search">
					&nbsp;<input type="text" class="span6" placeholder="Enter a username..." autocomplete="off">
					<button type="submit" class="btn btn-inverse">Edit</button>
				</form>
			</div>
		</div>
		
	</section>
	
	<section id="broadcastui" class="container">
		<h2>Broadcast</h2>
		<div class="navbar navbar-inverse navbar-subnav">
			<div class="navbar-inner">
				<form class="navbar-form" id="broadcast">
					&nbsp;<input type="text" class="span6" placeholder="Enter a message..." autocomplete="off">
					<button type="submit" class="btn btn-inverse">Send</button>
				</form>
			</div>
		</div>
	</section>
	
	<?php include Tpl::file('seg/commonbottom.php') ?>
	
	<script src="<?=Config::cdnv()?>/web/js/admin.js"></script>
	
</body>
</html>