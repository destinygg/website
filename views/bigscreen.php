<?php
namespace Destiny;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Config;
?>
<!DOCTYPE html>
<html>
<head>
    <?=Tpl::title($this->title)?>
    <?php include 'seg/meta.php' ?>
    <?=Tpl::manifestLink('common.vendor.css')?>
    <?=Tpl::manifestLink('web.css')?>
</head>
<body id="bigscreen" class="no-contain">

<?php include 'seg/nav.php' ?>

<div id="page-content" class="container">

    <div id="stream-panel" class="left">
        <div id="stream-wrap">
            <iframe class="stream-element" marginheight="0" marginwidth="0" frameborder="0" src="<?= Config::$a['embed']['stream'] ?>" scrolling="no" seamless allowfullscreen></iframe>
        </div>
    </div>

    <div id="chat-panel" class="right">
        <div id="chat-panel-resize-bar"></div>
        <div class="panelheader clearfix">
            <div class="toolgroup clearfix">
                <div id="chat-panel-tools">
                    <a title="Refresh" id="refresh" class="pull-left"><span class="fa fa-refresh"></span></a>
                    <a title="Close" id="close" class="pull-right"><span class="fa fa-remove"></span></a>
                    <a title="Popout" id="popout" class="pull-right"><span class="fa fa-external-link-square"></span></a>
                    <a title="Swap" id="swap" class="pull-right"><span class="fa fa-exchange"></span></a>
                </div>
            </div>
        </div>
        <div id="chat-wrap">
            <iframe id="chat-frame" class="stream-element" seamless="seamless" src="<?= Config::$a['embed']['chat'] ?>?follow=<?= urlencode('/bigscreen') ?>"></iframe>
        </div>
    </div>

</div>

<?php include 'seg/tracker.php' ?>
<?php include 'seg/login.php' ?>
<?=Tpl::manifestScript('runtime.js')?>
<?=Tpl::manifestScript('common.vendor.js')?>
<?=Tpl::manifestScript('web.js')?>

</body>
</html>
