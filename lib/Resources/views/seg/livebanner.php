<?php 
namespace Destiny;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Utils\Date;
use Destiny\Common\Config;

$isoffline = !($model->streaminfo['live']);
?>
<div id="status-banners">

  <section id="online-banner-view" class="container" <?= ($isoffline) ? 'style="display: none"' : '' ?>>
    <div class="banner-view-wrap clearfix">
      <div class="banner-thumbnail">
        <a href="/bigscreen" title="<?=(isset($model->streaminfo['status'])) ? Tpl::out($model->streaminfo['status']) : ''?>" style="<?=(!empty($model->streaminfo['preview']['medium'])) ? 'background: url('. Tpl::out($model->streaminfo['preview']['medium']) .') no-repeat center center;' : ''?>"></a>
      </div>
      <div class="banner-content-wrap">
        <div>
          <h1 title="<?=Tpl::out($model->streaminfo['status'])?>"><?=Tpl::out($model->streaminfo['status'])?></h1>
          <div class="banner-content-text">
            Currently playing <strong class="live-info-game"><?=Tpl::out($model->streaminfo['game'])?></strong><br />
            <?php if(!empty($model->lastbroadcast)): ?>
            Started <span class="live-info-updated"><?=Date::getElapsedTime(Date::getDateTime($model->streaminfo['created_at']))?></span><br />
            <?php endif; ?>
            <?php if(intval($model->streaminfo['viewers']) > 0): ?>
            ~<span class="live-info-viewers"><?=Tpl::out($model->streaminfo['viewers'])?></span> viewers
            <? endif; ?>
          </div>
          <a href="/bigscreen" class="btn btn-lg btn-primary"><i style="margin-top: 2px;" class="icon-bigscreen animated"></i> Watch the live stream</a>
          <div class="banner-popout-links btn-group pull-right" data-toggle="buttons" style="margin-top: 10px;">
            <a target="_blank" class="btn btn-link popup" href="/embed/chat" data-options="<?=Tpl::out('{"height":"500","width":"420"}')?>"><i class="fa fa-comment"></i> Chat</a>
            <a target="_blank" class="btn btn-link popup" href="//player.twitch.tv/?channel=<?=Config::$a['twitch']['user']?>" data-options="<?=Tpl::out('{"height":"420","width":"720"}')?>"><i class="fa fa-eye"></i> Stream</a>
          </div>
        </div>
      </div>
    </div>
  </section>
  
  <section id="offline-banner-view" class="container" <?= (!$isoffline) ? 'style="display: none"' : '' ?>>
    <div class="banner-view-wrap clearfix">
      <div class="banner-thumbnail">
        <a href="/bigscreen" title="<?=Tpl::out($model->streaminfo['status'])?>" style="background: url(<?=(!empty($model->broadcasts) && isset($model->broadcasts['videos'])) ? Tpl::out($model->broadcasts['videos'][0]['preview']) : Tpl::out($model->broadcasts['video_banner'])?>) no-repeat center center;"></a>
      </div>
      <div class="banner-content-wrap">
        <div>
          <h1>Stream currently offline</h1>
          <div class="banner-content-text">
            <span class="offline-status"><?= Tpl::out($model->streaminfo['status']) ?></span><br />
            <?php if(!empty($model->lastbroadcast)): ?>
            Last broadcast ended <strong class="offline-info-lastbroadcast"><?=Date::getElapsedTime(Date::getDateTime($model->lastbroadcast))?></strong><br />
            <?php endif; ?>
            <?php if(isset($model->streaminfo['game']) && !empty($model->streaminfo['game'])): ?>
            Was playing <strong class="offline-info-game"><?=Tpl::out($model->streaminfo['game'])?></strong><br />
            <?php endif; ?>
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
