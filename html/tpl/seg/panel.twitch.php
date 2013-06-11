<? namespace Destiny; ?>
<?if((bool) Config::$a['blocks']['twitch']):?>
<section id="twitchpanel" class="container split-view" data-youtube-user="<?=Config::$a['youtube']['user']?>" data-video-embed="http://www.twitch.tv/<?=Config::$a['twitch']['user']?>/popout" data-chat-embed="http://www.twitch.tv/chat/embed?channel=<?=Config::$a['twitch']['user']?>&popout_chat=true">
	<div class="content content-dark">
		<div class="panelheader clearfix">
			<div class="toolgroup clearfix">
				<div class="pull-left channel-stat game" style="display: none;"></div>
				<div class="btn-group pull-right" style="margin-left: 10px;">
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