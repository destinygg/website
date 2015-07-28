<?php
namespace Destiny;
use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Config;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($model->title)?></title>
<meta charset="utf-8">
<?php include Tpl::file('seg/opengraph.php') ?>
<?php include Tpl::file('seg/commontop.php') ?>
<?php include Tpl::file('seg/google.tracker.php') ?>
</head>
<body id="bigscreen" class="thin">
        
    <?php include Tpl::file('seg/top.php') ?>

    <div id="page-content" class="container clearfix">

        <div id="stream-panel">

            <div class="panelheader clearfix">
                <div class="toolgroup clearfix">
                    <div class="pull-left channel-stat game">
                    <?php if(!isset($model->streamInfo['stream']) || empty($model->streamInfo['stream'])): ?>
                        <span class="fa fa-clock-o"></span>
                        <span>
                        <?php if(isset($model->streamInfo['lastbroadcast'])): ?>
                        Last broadcast ended <?=Date::getElapsedTime(Date::getDateTime($model->streamInfo['lastbroadcast']))?>
                        <?php endif; ?>
                        </span>
                        <?php else: ?>
                        <span class="fa fa-clock-o"></span> <span>Started <?=Date::getElapsedTime(Date::getDateTime($model->streamInfo['lastbroadcast']))?></span>
                        <?php if(isset($model->streamInfo['stream']) && intval($model->streamInfo['stream']['channel']['delay']) > 1): ?>
                        - <?=(intval($model->streamInfo['stream']['channel']['delay'])/60)?>m delay
                        <?php endif; ?>
                    <?php endif; ?>
                    </div>
                    <div class="pull-right channel-stat" style="text-align:right;"><?=(isset($model->streamInfo['status'])) ? Tpl::out($model->streamInfo['status']) : ''?></div>
                </div>
            </div>

            <div id="stream-wrap">
                <iframe class="stream-element" marginheight="0" marginwidth="0" frameborder="0" src="http://www.twitch.tv/<?=Config::$a['twitch']['user']?>/embed" scrolling="no" seamless allowfullscreen></iframe>
            </div>
            
        </div>

        <div id="chat-panel">
            <div id="chat-panel-resize-bar"></div>
            <div class="panelheader clearfix">
                <div class="toolgroup clearfix">
                    <div id="chat-panel-tools">
                        <a title="Refresh" id="refresh" class="pull-left"><span class="fa fa-refresh"></span></a>
                        <a title="Close" id="close" class="pull-right"><span class="fa fa-remove"></span></a>
                        <a title="Popout" id="popout" class="pull-right"><span class="fa fa-share"></span></a>
                    </div>
                </div>
            </div>
            <div id="chat-wrap">
                <iframe id="chat-frame" class="stream-element" style="border:none; width: 100%;" seamless="seamless" src="/embed/chat?follow=/bigscreen"></iframe>
            </div>
        </div>

    </div>
    
    <?php include Tpl::file('seg/commonbottom.php') ?>
    
</body>
</html>