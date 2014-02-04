<?
namespace Destiny;
use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Config;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($model->title)?></title>
<meta charset="utf-8">
<?php include Tpl::file('seg/opengraph.php') ?>
<?php include Tpl::file('seg/commontop.php') ?>
<?php include Tpl::file('seg/google.tracker.php') ?>
</head>
<body id="bigscreen" class="thin" style="overflow: hidden;">
	<div class="page-wrap clearfix">
		
		<?php include Tpl::file('seg/top.php') ?>
	
		<div id="page-content" class="container clearfix">
			<div id="stream-panel" class="pull-left">
				<div>
					<div class="panelheader clearfix">
						<div class="toolgroup clearfix">
							<div class="pull-left channel-stat game">
								<?php if(!isset($model->streamInfo['stream']) || empty($model->streamInfo['stream'])): ?>
								<i class="icon-time icon-white subtle"></i> <span>Last broadcast ended <?=Date::getElapsedTime(Date::getDateTime($model->streamInfo['lastbroadcast']))?></span>
								<?php else: ?>
								<i class="icon-time icon-white subtle"></i> <span>Started <?=Date::getElapsedTime(Date::getDateTime($model->streamInfo['stream']['channel']['updated_at']))?></span>
								<?php if(intval($model->streamInfo['stream']['channel']['delay']) > 1): ?>
								- <?=(intval($model->streamInfo['stream']['channel']['delay'])/60)?>m delay
								<?php endif; ?>
							<?php endif; ?>
							</div>
							<div class="pull-right channel-stat" style="text-align:right;"><?=Tpl::out($model->streamInfo['status'])?></div>
						</div>
					</div>
					<div class="stream-element-wrap">
						<div class="stream-overlay to-botright"></div>
						<div class="stream-overlay to-botleft"></div>
						<div class="stream-overlay to-main"></div>
						<div class="stream-overlay fsbtn" title="Fullscreen"></div>
						<object class="stream-element" type="application/x-shockwave-flash" id="live_embed_player_flash" data="http://www.twitch.tv/widgets/live_embed_player.swf?channel=destiny" height="100%" width="100%">
							<param name="allowFullScreen" value="true">
							<param name="allowScriptAccess" value="always">
							<param name="allowNetworking" value="all">
							<param name="movie" value="http://www.twitch.tv/widgets/live_embed_player.swf">
							<param name="flashvars" value="hostname=www.twitch.tv&amp;channel=<?=Config::$a['twitch']['user']?>&amp;auto_play=true&amp">
						</object>
					</div>
				</div>
			</div>
			<div id="chat-panel" class="pull-right">
				<div>
					<div class="panelheader clearfix">
						<div class="toolgroup clearfix">
							<div class="pull-left">
								<span id="chat-panel-users" title="Total users" style="display: none;">
									<i class="icon-user icon-white subtle"></i>
									<span id="chat-panel-usercount">Loading...</span>
								</span>
							</div>
							<div id="chat-panel-tools" class="pull-right">
								<a title="Refresh"><i class="icon-refresh icon-white subtle"></i></a>
								<a title="Popout"><i class="icon-share icon-white subtle"></i></a>
								<a title="Close"><i class="icon-remove icon-white subtle"></i></a>
							</div>
						</div>
					</div>
					<iframe id="chat-frame" class="stream-element" style="border:none;" seamless="seamless" src="/embed/chat"></iframe>
				</div>
			</div>
		</div>
	
	</div>
	
	<?php include Tpl::file('seg/commonbottom.php') ?>
	
</body>
</html>