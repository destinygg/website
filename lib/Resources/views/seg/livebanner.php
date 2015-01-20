<?php 
namespace Destiny;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Utils\Date;
use Destiny\Common\Config;

$isoffline = (!isset($model->streamInfo['stream']) || empty($model->streamInfo['stream'])) ? true:false;
?>
<div id="status-banners">

  <section id="online-banner-view" class="container" <?= ($isoffline) ? 'style="display: none"' : '' ?>>
    <div class="banner-view-wrap clearfix">
      <div class="banner-thumbnail">
        <a href="/bigscreen" title="<?=(isset($model->streamInfo['status'])) ? Tpl::out($model->streamInfo['status']) : ''?>" style="<?=(!empty($model->streamInfo['stream'])) ? 'background: url('. Tpl::out($model->streamInfo['stream']['preview']['medium']) .') no-repeat center center;' : ''?>"></a>
      </div>
      <div class="banner-content-wrap">
        <div>
          <h1 title="<?=Tpl::out($model->streamInfo['status'])?>"><?=Tpl::out($model->streamInfo['status'])?></h1>
          <div class="banner-content-text">
            Currently playing <strong class="live-info-game"><?=Tpl::out($model->streamInfo['game'])?></strong><br />
            Started <span class="live-info-updated"><?=Date::getElapsedTime(Date::getDateTime($model->streamInfo['lastbroadcast']))?></span><br />
            ~<span class="live-info-viewers"><?=Tpl::out((!empty($model->streamInfo['stream'])) ? $model->streamInfo['stream']['viewers'] : 0)?></span> viewers
          </div>
          <a href="/bigscreen" class="btn btn-lg btn-primary"><i style="margin-top: 2px;" class="icon-bigscreen animated"></i> Watch the live stream</a>
          <div class="banner-popout-links btn-group pull-right" data-toggle="buttons" style="margin-top: 10px;">
            <a target="_blank" class="btn btn-link popup" href="/embed/chat" data-options="<?=Tpl::out('{"height":"500","width":"420"}')?>"><i class="fa fa-comment"></i> Chat</a>
            <a target="_blank" class="btn btn-link popup" href="//www.twitch.tv/<?=Config::$a['twitch']['user']?>/popout" data-options="<?=Tpl::out('{"height":"420","width":"720"}')?>"><i class="fa fa-eye"></i> Stream</a>
          </div>
        </div>
      </div>
    </div>
  </section>
  
  <section id="offline-banner-view" class="container" <?= (!$isoffline) ? 'style="display: none"' : '' ?>>
    <div class="banner-view-wrap clearfix">
      <div class="banner-thumbnail">
        <a href="/bigscreen" title="<?=Tpl::out($model->streamInfo['status'])?>" style="background: url(<?=(!empty($model->broadcasts) && isset($model->broadcasts['videos'])) ? Tpl::out($model->broadcasts['videos'][0]['preview']) : Tpl::out($model->broadcasts['video_banner'])?>) no-repeat center center;"></a>
      </div>
      <div class="banner-content-wrap">
        <div>
          <h1>Stream currently offline</h1>
          <div class="banner-content-text">
            <span class="offline-status"><?= Tpl::out($model->streamInfo['status']) ?></span><br />
            Last broadcast ended <strong class="offline-info-lastbroadcast"><?=(isset($model->streamInfo['lastbroadcast'])) ? Date::getElapsedTime(Date::getDateTime($model->streamInfo['lastbroadcast'])) : ''?></strong><br />
            Was playing <strong class="offline-info-game"><?=(isset($model->streamInfo['game'])) ? Tpl::out($model->streamInfo['game']) : ''?></strong><br />
          </div>
          <a href="/bigscreen" class="btn btn-lg btn-primary">Join the chat while you wait!</a>
          <div class="banner-popout-links btn-group pull-right" data-toggle="buttons" style="margin-top: 10px;">
            <a target="_blank" class="btn btn-link popup" href="/embed/chat" data-options="<?=Tpl::out('{"height":"500","width":"420"}')?>"><i class="fa fa-comment"></i> Chat</a>
            <a target="_blank" class="btn btn-link popup" href="//www.twitch.tv/<?=Config::$a['twitch']['user']?>/popout" data-options="<?=Tpl::out('{"height":"420","width":"720"}')?>"><i class="fa fa-eye"></i> Stream</a>
          </div>
        </div>
      </div>
    </div>
  </section>

</div>
