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
<?include'./tpl/seg/commontop.php'?>
<?php if(Config::$a['compressed']):?>
<link href="<?=Config::cdn()?>/chat/css/style.<?=Config::version()?>.css" rel="stylesheet" media="screen">
<?php else: ?>
<link href="<?=Config::cdn()?>/chat/css/style.css" rel="stylesheet" media="screen">
<?php endif; ?>
<?include'./tpl/seg/google.tracker.php'?>
</head>
<body id="chat" class="embed">

	<div id="destinyChat" class="chat chat-frame">
		<div class="chat-output clearfix">
			<div class="chat-lines"></div>
		</div>
		<div class="chat-input">
			<form class="chat-input-wrap clearfix">
				<div class="chat-input-control">
					<input type="text" placeholder="Enter a message..." class="input" autocomplete="off" />
				</div>
			</form>
			<div class="chat-tools-wrap clearfix">
				<button class="btn btn-mini btn-primary pull-left" onclick="$('#destinyChat').data('chat').send();">Send</button>
				<button class="btn btn-mini btn-danger pull-right" onclick="$('#destinyChat').data('chat').purge();">Purge</button>
			</div>
		</div>
	</div>
	
<?include'./tpl/seg/commonbottom.php'?>

<?php if(Config::$a['compressed']):?>
<script src="<?=Config::cdn()?>/chat/js/engine.<?=Config::version()?>.js"></script>
<?php else: ?>
<script src="<?=Config::cdn()?>/chat/js/gui.js"></script>
<script src="<?=Config::cdn()?>/chat/js/impl.js"></script>
<?php endif; ?>

</body>
</html>