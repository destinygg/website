<?php
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
<link href="<?=Config::cdn()?>/vendor/css/jquery.mCustomScrollbar.css" rel="stylesheet" media="screen">
<?php if(is_file(_STATICDIR .'/chat/css/style.'.Config::version().'.css')):?>
<link href="<?=Config::cdn()?>/chat/css/style.<?=Config::version()?>.css" rel="stylesheet" media="screen">
<?php else: ?>
<link href="<?=Config::cdn()?>/chat/css/style.css" rel="stylesheet" media="screen">
<link href="<?=Config::cdn()?>/chat/css/twitch_sprite.css" rel="stylesheet" media="screen">
<?php endif; ?>
<?include'./tpl/seg/google.tracker.php'?>
</head>
<body id="chat-embedded">

<div id="destinychat" class="chat chat-frame chat-theme-<?=$model->options['theme']?>">
	<div class="chat-output clearfix">
		<div class="chat-lines"></div>
	</div>
	<div class="chat-input">
		<form class="chat-input-wrap">
			<div class="chat-input-control">
				<input type="text" placeholder="Enter a message to chat..." class="input" autocomplete="off" />
			</div>
		</form>
		<div class="chat-tools-wrap clearfix">
			<div class="pull-left">
				<button type="submit" class="chat-send-btn btn btn-mini btn-inverse">Send</button>
			</div>
			<div class="pull-right">
				<button type="button" class="chat-settings-btn btn btn-mini btn-inverse"><i class="icon-cog icon-white"></i> Config</button>
				<button type="button" class="chat-users-btn btn btn-mini btn-inverse"><i class="icon-user icon-white"></i> Users</button>
			</div>
		</div>
	</div>
</div>

<?include'./tpl/seg/commonbottom.php'?>
<script src="<?=Config::cdn()?>/vendor/js/jquery.mousewheel.min.js"></script>
<script src="<?=Config::cdn()?>/vendor/js/jquery.mCustomScrollbar.min.js"></script>
<?php if(is_file(_STATICDIR .'/chat/js/engine.'.Config::version().'.js')):?>
<script src="<?=Config::cdn()?>/chat/js/engine.<?=Config::version()?>.js"></script>
<?php else: ?>
<script src="<?=Config::cdn()?>/chat/js/scroll.native.js"></script>
<script src="<?=Config::cdn()?>/chat/js/scroll.mCustom.js"></script>
<script src="/chat/history.js"></script>
<script src="<?=Config::cdn()?>/chat/js/gui.js"></script>
<script src="<?=Config::cdn()?>/chat/js/chat.js"></script>
<?php endif; ?>
<script>
new chat(<?=Tpl::jsout($model->user)?>, <?=Tpl::jsout(array_merge(array('ui'=>'#destinychat', 'backlog'=>$model->backlog), $model->options), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP)?>);
</script>
</body>
</html>