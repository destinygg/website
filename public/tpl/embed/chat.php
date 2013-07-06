<?php
namespace Destiny;
use Destiny\Utils\Http;
use Destiny\Utils\Tpl;
use Destiny\UserRole;
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
<link href="<?=Config::cdn()?>/chat/css/emoticons.css" rel="stylesheet" media="screen">
<?php endif; ?>
<?include'./tpl/seg/google.tracker.php'?>
</head>
<body id="chat-embedded">

<div id="destinychat" class="chat chat-frame chat-theme-dark">

	<div class="chat-output clearfix">
		<div class="chat-lines"></div>
	</div>
	
	<form class="chat-input clearfix hidden">
		<div class="chat-input-wrap clearfix">
			<?php if(Session::hasRole(UserRole::USER)): ?>
			<div class="chat-input-control">
				<input type="text" placeholder="Enter a message to chat..." class="input" autocomplete="off" />
			</div>
			<?php else: ?>
			<a class="chat-login-msg" href="/login" target="_parent">You must be logged in to chat</a>
			<?php endif; ?>
		</div>
		<div class="chat-tools-wrap">
			<a class="iconbtn chat-send-btn" title="Send"><i class="icon-bullhorn icon-white subtle"></i></a>
			<a class="iconbtn chat-settings-btn" title="Settings"><i class="icon-cog icon-white subtle"></i></a>
			<a class="iconbtn chat-users-btn" title="Users"><i class="icon-user icon-white subtle"></i></a>
		</div>
	</form>
	
	<div id="chat-user-list" class="chat-menu" style="display: none;">
		<div class="list-wrap clearfix">
			<div class="toolbar">
				<h5>Users <button type="button" class="close">&times;</button></h5>
			</div>
			<div class="scrollable">
				<h6>Admins</h6>
				<ul class="unstyled admins"></ul>
				<hr/>
				<h6>VIP</h6>
				<ul class="unstyled vips"></ul>
				<hr/>
				<h6>Moderators</h6>
				<ul class="unstyled moderators"></ul>
				<hr/>
				<h6>Bots</h6>
				<ul class="unstyled bots"></ul>
				<hr/>
				<h6>Plebs</h6>
				<ul class="unstyled plebs"></ul>
			</div>
		</div>
	</div>
	
	<div id="chat-settings" class="chat-menu" style="display: none;">
		<div class="list-wrap clearfix">
			<div class="toolbar">
				<h5>Settings <button type="button" class="close">&times;</button></h5>
			</div>
			<div class="scrollable">
				<ul class="unstyled">
					<li><label class="checkbox"><input name="showicon" type="checkbox" /> Show all user icons</label></li>
					<li><label class="checkbox"><input name="showtime" type="checkbox" /> Show time for each message</label></li>
					<li><label class="checkbox"><input name="notifications" type="checkbox" /> Desktop notifications on highlight</label></li>
				</ul>
			</div>
		</div>
	</div>
	
</div>

<?include'./tpl/seg/commonbottom.php'?>
<script src="<?=Config::cdn()?>/vendor/js/jquery.mousewheel.min.js"></script>
<script src="<?=Config::cdn()?>/vendor/js/jquery.mCustomScrollbar.min.js"></script>
<script src="/chat/history"></script>
<?php if(is_file(_STATICDIR .'/chat/js/engine.'.Config::version().'.js')):?>
<script src="<?=Config::cdn()?>/chat/js/engine.<?=Config::version()?>.js"></script>
<?php else: ?>
<script src="<?=Config::cdn()?>/chat/js/scroll.native.js"></script>
<script src="<?=Config::cdn()?>/chat/js/scroll.mCustom.js"></script>
<script src="<?=Config::cdn()?>/chat/js/gui.js"></script>
<script src="<?=Config::cdn()?>/chat/js/chat.js"></script>
<?php endif; ?>
<script>
var c = new chat(<?=Tpl::jsout($model->user)?>, <?=Tpl::jsout(array_merge(array('ui'=>'#destinychat'), $model->options))?>);
</script>
</body>
</html>