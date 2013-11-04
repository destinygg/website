<? 
use Destiny\Common\Utils\Date;
use Destiny\Common\Config;
?>
<section id="twitchpanel" class="container split-view" data-youtube-user="<?=Config::$a['youtube']['user']?>" data-youtube-playlist="<?=Config::$a['youtube']['playlistId']?>" data-video-embed="http://www.twitch.tv/<?=Config::$a['twitch']['user']?>/popout" data-chat-embed="/embed/chat">
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
			<div id="player-embed" class="twitch-element-wrap pull-left">
				<iframe class="twitch-element" src="http://www.twitch.tv/embed?channel=<?=Config::$a['twitch']['user']?>" height="100%" width="100%" style="border:none; overflow: hidden;" scrolling="no" seamless="seamless"></iframe>
			</div>
			<div id="chat-embed" class="twitch-element-wrap pull-left">
				<iframe class="twitch-element" style="border:none;" seamless="seamless" src="/embed/chat"></iframe>
			</div>
		</div>
	</div>
</section>