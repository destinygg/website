<?php
namespace Destiny;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Config;
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
            <?php foreach (Config::$a['embed']['stream'] as $streamDetails): ?>
                <?php $twitchParents = !empty($streamDetails['twitchParents']) ? Tpl::arrayOut($streamDetails['twitchParents']) : ''; ?>
                <div class="stream-details" data-platform="<?= $streamDetails['platform'] ?>" data-name="<?= $streamDetails['name'] ?>" data-twitch-parents="<?= $twitchParents ?>"></div>
            <?php endforeach; ?>
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
