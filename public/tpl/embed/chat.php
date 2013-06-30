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
<?php endif; ?>
<?include'./tpl/seg/google.tracker.php'?>
</head>
<body id="chat-embedded">

<div id="destinychat" class="chat chat-frame chat-theme-<?=$model->chatOptions['theme']?>" data-options="<?=Tpl::out(json_encode($model->chatOptions))?>">
	<div class="chat-output clearfix">
		<div class="chat-lines">
			<?php foreach($model->chatlog as $k => $log):?>
				<div class="line<?php if ($log['nick']) echo ' nick-', Tpl::out($log['nick']);?>">
					<?php if ($log['timestamp']):?>
						<time class="p-time" datetime="TODO">TODO</time>&nbsp;
					<?php endif; ?>
					<?php if ($log['nick']):?>
						<span class="p-user"><?=Tpl::out($log['nick'])?></span><span class="p-userpostfix">:&nbsp;</span>
					<?php endif; ?>
					<?php if ($log['data']):?>
						<span class="p-message"><?=Tpl::out($log['data'])?></span>
					<?php endif; ?>
				</div>
			<?php endforeach;?>
		</div>
	</div>
	<div class="chat-input">
		<form class="chat-input-wrap">
			<div class="chat-input-control">
				<input type="text" placeholder="Enter a message to chat..." class="input" autocomplete="off" />
			</div>
		</form>
		<div class="chat-tools-wrap clearfix">
			<button class="btn btn-mini btn-primary pull-left">Send</button>
		</div>
	</div>
</div>

<?include'./tpl/seg/commonbottom.php'?>
<script src="<?=Config::cdn()?>/vendor/js/jquery.mousewheel.min.js"></script>
<script src="<?=Config::cdn()?>/vendor/js/jquery.mCustomScrollbar.min.js"></script>
<?php if(is_file(_STATICDIR .'/chat/js/engine.'.Config::version().'.js')):?>
<script src="<?=Config::cdn()?>/chat/js/engine.<?=Config::version()?>.js"></script>
<?php else: ?>
<script src="<?=Config::cdn()?>/chat/js/chat.js"></script>
<?php endif; ?>

</body>
</html>