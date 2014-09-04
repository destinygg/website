<? 
use Destiny\Common\Utils\Date;
use Destiny\Common\Config;
?>
<section id="twitchpanel" class="container split-view" data-youtube-user="<?=Config::$a['youtube']['user']?>" data-youtube-playlist="<?=Config::$a['youtube']['playlistId']?>" data-video-embed="http://www.twitch.tv/<?=Config::$a['twitch']['user']?>/popout" data-chat-embed="/embed/chat">
    <div class="content content-dark">
        <div class="panelheader clearfix">
            <div class="toolgroup clearfix">
                <div class="pull-left channel-stat game">
                <?php if(!empty($model->streamInfo)): ?>
                    <?php if(!isset($model->streamInfo['stream']) || empty($model->streamInfo['stream'])): ?>
                    <span class="glyphicon glyphicon-time"></span> 
                    <span>Last broadcast ended <?=Date::getElapsedTime(Date::getDateTime($model->streamInfo['lastbroadcast']))?></span>
                    <?php else: ?>
                    <span class="glyphicon glyphicon-time"></span> 
                    <span>Started <?=Date::getElapsedTime(Date::getDateTime($model->streamInfo['lastbroadcast']))?></span>
                    <?php if(intval($model->streamInfo['stream']['channel']['delay']) > 1): ?>
                    - <?=(intval($model->streamInfo['stream']['channel']['delay'])/60)?>m delay
                    <?php endif; ?>
                    <?php endif; ?>
                <?php endif; ?>
                </div>
                <div class="btn-group pull-right">
                    <a id="popoutchat" title="Pop-out chat" href="/embed/chat" class="btn btn-xs btn-link">Pop-out chat</a>
                    <a id="popoutvideo" title="Pop-out video" href="http://www.twitch.tv/destiny/popout" class="btn btn-xs btn-link">Pop-out stream</a>
                </div>
            </div>
        </div>
        <div id="player-embed">
            <div class="stream-overlay to-botright"></div>
            <div class="stream-overlay to-botleft"></div>
            <div class="stream-overlay to-main"></div>
            <div class="stream-overlay fsbtn" title="Fullscreen"></div>
            <iframe class="stream-element" marginheight="0" marginwidth="0" frameborder="0" src="http://www.twitch.tv/<?=Config::$a['twitch']['user']?>/embed" scrolling="no" seamless></iframe>
        </div>
        <div id="chat-embed">
            <iframe class="stream-element" style="border:none;" seamless="seamless" src="/embed/chat"></iframe>
        </div>
    </div>
</section>