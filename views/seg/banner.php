<?php
use Destiny\Common\Application;
use Destiny\Common\Config;
use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\Tpl;

$cache = Application::getNsCache();
$info = $cache->contains('streamstatus') ? $cache->fetch('streamstatus') : null;
?>
<section id="header-band">
    <div class="container">

        <header id="brand">
            <h1><a href="/"><?=Config::$a['meta']['title']?></a></h1>
        </header>

        <div id="discord-status">
            <a title="Join discord" href="/discord" class="discord-logo">
                <i class="fab fa-discord"></i>
            </a>
            <div>
                <h3>Debate me</h3>
                <p>Join the <a href="/discord">discord</a>. <br/>You're welcome!</p>
            </div>
        </div>

        <?php if(!empty($info)): ?>
        <div id="stream-status" class="<?= (!empty($info['host'])) ? 'hosting': (($info['live']) ? 'online':'offline') ?>">
            <div id="stream-status-info-offline">
                <h3>Stream offline</h3>
                <p>Ended <span id="stream-status-end"><?= Tpl::fromNow(Date::getDateTime($info['ended_at'])) ?></span>.
                    <br />Join <a class="badge badge-secondary" href="/bigscreen">chat</a> while you wait.</p>
            </div>
            <div id="stream-status-info-online">
                <h3>Stream online</h3>
                <p>Started <span id="stream-status-start"><?= Tpl::fromNow(Date::getDateTime($info['started_at'])) ?></span>.
                    <br />Watch on <a class="badge badge-danger" href="/bigscreen">Bigscreen</a></p>
            </div>
            <div id="stream-status-info-host">
                <h3>Stream host</h3>
                <p>Watch <a id="stream-status-host" href="<?= (!empty($info['host'])) ? Tpl::out($info['host']['url']):'' ?>" target="_blank"><?= (!empty($info['host'])) ? Tpl::out($info['host']['display_name']):'' ?></a>!
                    <br /> Join the <a class="badge badge-secondary" href="/bigscreen">chat</a>.</p>
            </div>
            <div id="stream-status-preview">
                <a href="/bigscreen" style="background-image: url('<?= Tpl::out($info['preview']) ?>');"></a>
                <div class="dropdown">
                    <span class="dropdown-toggle fas fa-clone fa-flip-horizontal" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></span>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
                        <a class="dropdown-item popup" target="_blank" href="/embed/chat" data-options="<?=Tpl::out('{"height":"500","width":"420"}')?>">Chat</a>
                        <a class="dropdown-item popup" target="_blank" href="https://www.twitch.tv/<?=Config::$a['twitch']['user']?>/popout" data-options="<?=Tpl::out('{"height":"420","width":"720"}')?>">Stream</a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>
</section>