<? 
use Destiny\Utils\Date;
use Destiny\Session;
use Destiny\Config;
?>
<?if((bool) Config::$a['blocks']['twitch']):?>
<section id="twitchpanel" class="container split-view" data-youtube-user="<?=Config::$a['youtube']['user']?>" data-youtube-playlist="<?=Config::$a['youtube']['playlistId']?>" data-video-embed="http://www.twitch.tv/<?=Config::$a['twitch']['user']?>/popout" data-chat-embed="http://www.twitch.tv/chat/embed?channel=<?=Config::$a['twitch']['user']?>&popout_chat=true">
	<div class="content content-dark">
		<div class="panelheader clearfix">
			<div class="toolgroup clearfix">
				<div class="pull-left channel-stat game">
					<?php if($model->streamInfo['stream'] == null): ?>
					<i class="icon-time icon-white subtle"></i> <span>Last broadcast ended <?=Date::getElapsedTime(Date::getDateTime($model->streamInfo['lastbroadcast']))?></span>
					<?php else: ?>
					<i class="icon-time icon-white subtle"></i> <span>Started <?=Date::getElapsedTime(Date::getDateTime($model->streamInfo['stream']['channel']['updated_at']))?></span>
					<?php if(intval($model->streamInfo['stream']['channel']['delay']) > 1): ?>
					- <?=(intval($model->streamInfo['stream']['channel']['delay'])/60)?>m delay
					<?php endif; ?>
					<?php endif; ?>
				</div>
				<div class="btn-group pull-right">
					<a id="bigscreenmode" title="Big screen mode" class="btn btn-mini btn-link">[Bigscreen mode]</a>
					<a id="popoutvideo" title="Pop-out video" class="btn btn-mini btn-link">Pop-out player</a>
					<a id="popoutchat" title="Pop-out chat" class="btn btn-mini btn-link">Pop-out chat</a>
				</div>
			</div>
		</div>
		<div id="twitch-elements" class="clearfix">
			<div id="twitch-player" class="twitch-element-wrap pull-left">
				<?if((bool) Config::$a['blocks']['stream']):?>
				<object class="twitch-element" type="application/x-shockwave-flash" id="live_embed_player_flash" data="http://www.twitch.tv/widgets/live_embed_player.swf?channel=<?=Config::$a['twitch']['user']?>" bgcolor="#000000">
					<param name="allowFullScreen" value="true" />
					<param name="allowScriptAccess" value="always" />
					<param name="allowNetworking" value="all" />
					<param name="movie" value="http://www.twitch.tv/widgets/live_embed_player.swf" />
					<param name="flashvars" value="hostname=www.twitch.tv&channel=<?=Config::$a['twitch']['user']?>&auto_play=true" />
				</object>
				<?endif;?>
			</div>
			<div id="twitch-chat" class="twitch-element-wrap pull-left">
				<?if((bool) Config::$a['blocks']['chat']):?>
				<iframe class="twitch-element" frameborder="0" scrolling="no" id="chat_embed" src="http://www.twitch.tv/chat/embed?channel=<?=Config::$a['twitch']['user']?>&amp;popout_chat=true"></iframe>
				<?endif;?>
			</div>
		</div>
	</div>
</section>
<?endif;?>