<?php
use Destiny\Common\Application;
use Destiny\Common\Config;
use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\Tpl;
use Destiny\Twitch\TwitchApiService;

$cache = Application::instance()->getCacheDriver();
$streaminfo = $cache->contains('streamstatus') ? $cache->fetch('streamstatus') : TwitchApiService::$STREAM_INFO;
?>
<section id="header-band">
    <div class="container">

        <header id="brand">
            <h1><a href="/"><?=Config::$a['meta']['title']?></a></h1>
        </header>

        <div id="stream-status" class=" <?= ($streaminfo['live']) ? 'online':'offline' ?>">
            <div id="stream-status-info-offline">
                <h3>Stream offline</h3>
                <p>Ended <span id="stream-status-end"><?= Tpl::fromNow(Date::getDateTime($streaminfo['ended_at'])) ?></span>.<br />Join the <a href="/bigscreen">chat</a> while you wait.</p>
            </div>
            <div id="stream-status-info-online">
                <h3>Stream online</h3>
                <p>Started <span id="stream-status-start"><?= Tpl::fromNow(Date::getDateTime($streaminfo['started_at'])) ?></span>.<br />Watch on the <a class="critical" href="/bigscreen">Bigscreen</a></p>
            </div>
            <a id="stream-status-preview" href="/bigscreen">
                <img src="<?= Tpl::out($streaminfo['preview']) ?>" data-animated="<?= Tpl::out($streaminfo['animated_preview']) ?>">
            </a>
        </div>

    </div>
    <div class="shadow"></div>
</section>