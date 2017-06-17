<?php
use Destiny\Common\Application;
use Destiny\Common\Config;
use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\Tpl;
use Destiny\Twitch\TwitchApiService;

$cache = Application::instance()->getCache();
$streaminfo = $cache->contains('streamstatus') ? $cache->fetch('streamstatus') : TwitchApiService::$STREAM_INFO;
?>
<section id="header-band">
    <div class="container">

        <header id="brand">
            <h1><a href="/"><?=Config::$a['meta']['title']?></a></h1>
        </header>

        <div id="stream-status" class=" <?= (!empty($streaminfo['host'])) ? 'hosting': (($streaminfo['live']) ? 'online':'offline') ?>">
            <div id="stream-status-info-offline">
                <h3>Stream offline</h3>
                <p>Ended <span id="stream-status-end"><?= Tpl::fromNow(Date::getDateTime($streaminfo['ended_at'])) ?></span>.<br />Join the <a href="/bigscreen">chat</a> while you wait.</p>
            </div>
            <div id="stream-status-info-online">
                <h3>Stream online</h3>
                <p>Started <span id="stream-status-start"><?= Tpl::fromNow(Date::getDateTime($streaminfo['started_at'])) ?></span>.<br />Watch on the <a class="critical" href="/bigscreen">Bigscreen</a></p>
            </div>
            <div id="stream-status-info-host">
                <h3>Stream host</h3>
                <p>Hosting <a id="stream-status-host" href="<?= (!empty($streaminfo['host'])) ? Tpl::out($streaminfo['host']['url']):'' ?>" target="_blank"><?= (!empty($streaminfo['host'])) ? Tpl::out($streaminfo['host']['display_name']):'' ?></a> check it out! <br /> or join the <a href="/bigscreen">chat</a> while you wait.</p>
            </div>
            <div id="stream-status-preview">
                <a href="/bigscreen" style="background-image: url('<?= Tpl::out($streaminfo['preview']) ?>');" data-animated="<?= Tpl::out($streaminfo['animated_preview']) ?>"></a>
                <div class="dropdown">
                    <i data-toggle="dropdown" class="dropdown-toggle fa fa-clone fa-flip-horizontal"></i>
                    <ul class="dropdown-menu dropdown-menu-right">
                        <li><a target="_blank" class="popup" href="/embed/chat" data-options="<?=Tpl::out('{"height":"500","width":"420"}')?>">Chat</a></li>
                        <li><a target="_blank" class="popup" href="//www.twitch.tv/<?=Config::$a['twitch']['user']?>/popout" data-options="<?=Tpl::out('{"height":"420","width":"720"}')?>">Stream</a></li>
                    </ul>
                </div>
            </div>
        </div>

    </div>
    <div class="shadow"></div>
</section>