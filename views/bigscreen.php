<?php
namespace Destiny;
use Destiny\Common\Application;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Config;
use Destiny\Tasks\YouTubeTasks;

$cache = Application::getNsCache();
$youTubeStreamInfo = $cache->fetch(YouTubeTasks::CACHE_KEY_YOUTUBE_LIVESTREAM_STATUS);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?=Tpl::title($this->title)?>
    <?php include 'seg/meta.php' ?>
    <?=Tpl::manifestLink('web.css')?>
</head>
<body id="bigscreen" class="no-contain">
<div id="page-wrap">

    <?php include 'seg/nav.php' ?>

    <div id="bigscreen-layout">
        <div id="stream-panel">
            <div
                class="streams-metadata"
                data-embed-twitch-stream="<?= Config::$a['embed']['embedTwitchStream'] ?>"
                data-embed-youtube-stream="<?= Config::$a['embed']['embedYouTubeStream'] ?>"
                data-twitch-channel-name="<?= Config::$a['twitch']['user'] ?>"
                data-youtube-stream-video-id="<?= !empty($youTubeStreamInfo) && !empty($youTubeStreamInfo['videoId']) ? $youTubeStreamInfo['videoId'] : null ?>"
                data-display-name="<?= Config::$a['embed']['displayName'] ?>"
                data-twitch-parents="<?= Tpl::arrayOut(Config::$a['embed']['twitchParents']) ?>"
            >
            </div>
            <div id="stream-wrap">
                <iframe seamless="seamless" allowfullscreen></iframe>
            </div>
        </div>
        <div id="chat-panel">
            <div id="chat-panel-resize-bar"></div>
            <div id="chat-panel-tools">
                <a title="Refresh" id="refresh" class="float-left"><i class="fas fa-sync"></i></a>
                <a title="Close" id="close" class="float-right"><i class="fas fa-times"></i></a>
                <a title="Popout" id="popout" class="float-right"><i class="fas fa-external-link-square-alt"></i></a>
                <a title="Swap" id="swap" class="float-right"><i class="fas fa-exchange-alt"></i></a>
            </div>
            <div id="chat-wrap">
                <iframe seamless="seamless" src="<?= Config::$a['embed']['chat'] ?>?follow=<?= urlencode('/bigscreen') ?>"></iframe>
            </div>
        </div>
    </div>

</div>
<?php include 'seg/tracker.php' ?>
<?php include 'seg/login.php' ?>
<?=Tpl::manifestScript('runtime.js')?>
<?=Tpl::manifestScript('common.vendor.js')?>
<?=Tpl::manifestScript('web.js')?>
<?=Tpl::manifestScript('bigscreen.js')?>

</body>
</html>
