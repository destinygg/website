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
<?php if(is_file(_STATICDIR .'/chat/css/style.min.css')):?>
<link href="<?=Config::cdnv()?>/chat/css/style.min.css" rel="stylesheet" media="screen">
<?php else: ?>
<link href="<?=Config::cdnv()?>/chat/css/style.css" rel="stylesheet" media="screen">
<link href="<?=Config::cdnv()?>/chat/css/emoticons.css" rel="stylesheet" media="screen">
<?php endif; ?>
<?include'./tpl/seg/google.tracker.php'?>
</head>
<body id="chat-embedded">

<div id="destinychat" class="chat chat-frame chat-theme-dark chat-icons">

	<div class="chat-output clearfix">
		<div class="chat-lines"></div>
	</div>
	
	<form class="chat-input clearfix hidden">
		<div class="chat-input-wrap clearfix">
			<div class="chat-input-control">
			<?php if(Session::hasRole(UserRole::USER)): ?>
			<input type="text" placeholder="Enter a message and hit send..." class="input" autocomplete="off" />
			<?php else: ?>
			<a class="chat-login-msg" href="/login" target="_parent">You must be logged in to chat</a>
			<input type="hidden" class="input" />
			<?php endif; ?>
			</div>
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
				<h5>Users (~<span></span>)<button type="button" class="close">&times;</button></h5>
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
				<h6>Subscribers</h6>
				<ul class="unstyled subs"></ul>
				<hr/>
				<h6>Plebs</h6>
				<ul class="unstyled plebs"></ul>
				<hr/>
				<h6>Bots</h6>
				<ul class="unstyled bots"></ul>
			</div>
		</div>
	</div>
	
	<div id="chat-settings" class="chat-menu" style="display: none;">
		<div class="list-wrap clearfix">
			<div class="toolbar">
				<h5>Settings <button type="button" class="close">&times;</button></h5>
			</div>
			<div class="scrollable">
				<ul class="unstyled" style="font-size:0.9em;">
					<li>
						<label class="checkbox" title="Show all user flair icons">
							<input name="hideflairicons" type="checkbox" /> Hide flair icons
						</label>
					</li>
					<li>
						<label class="checkbox" title="Show the timestamps next to the messages">
							<input name="showtime" type="checkbox" /> Show time for messages
						</label>
					</li>
					<li>
						<label class="checkbox" title="Highlight text that you are mentioned in">
							<input name="highlight" type="checkbox" checked="checked"/> Highlight on mention
						</label>
					</li>
					<li>
						<label class="text" title="Your custom list of words that will make messages highlight">
							Custom highlight words.
							<input name="customhighlight" type="text" placeholder="Separated using a comma (,)" style="font-size:0.9em;"/>
						</label>
					</li>
					<li>
						<label class="checkbox" title="Show desktop notifications on hightlight">
							<input name="notifications" type="checkbox" /> Desktop notification on highlight
						</label>
					</li>
					<li>
						<hr style="margin:5px 0;">
						See the <a href="/chat/faq" target="_blank">chat FAQ</a> for more information
					</li>
				</ul>
			</div>
		</div>
	</div>
	
	<div class="user-tools" style="display: none;">
		<div>
			<a href="#"><i class="icon-ban-circle icon-white"></i> Mute</a> 
			<a href="#"><i class="icon-eye-close icon-white"></i> Ignore</a>
		</div>
	</div>
	
</div>

<?include'./tpl/seg/commonbottom.php'?>
<script src="<?=Config::cdn()?>/vendor/js/jquery.mousewheel.min.js"></script>
<script src="<?=Config::cdn()?>/vendor/js/jquery.mCustomScrollbar.min.js"></script>
<script src="/chat/history"></script>
<?php if(is_file(_STATICDIR .'/chat/js/engine.min.js')):?>
<script src="<?=Config::cdnv()?>/chat/js/engine.min.js"></script>
<?php else: ?>
<script src="<?=Config::cdnv()?>/chat/js/autocomplete.js"></script>
<script src="<?=Config::cdnv()?>/chat/js/scroll.mCustom.js"></script>
<script src="<?=Config::cdnv()?>/chat/js/chat.menu.js"></script>
<script src="<?=Config::cdnv()?>/chat/js/gui.js"></script>
<script src="<?=Config::cdnv()?>/chat/js/chat.js"></script>
<?php endif; ?>
<script>
var c = new chat(<?=Tpl::jsout($model->user)?>, <?=Tpl::jsout(array_merge(array('ui'=>'#destinychat'), $model->options))?>);
</script>
</body>
</html>