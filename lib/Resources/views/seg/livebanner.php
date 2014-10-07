<?php 
namespace Destiny;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Utils\Date;
?>
<div id="status-banners">

  <section id="online-banner-view" class="container" <?= (!isset($model->streamInfo['stream']) || empty($model->streamInfo['stream'])) ? 'style="display: none"' : '' ?>>
    <div class="content">
      <div id="live-banner">
        <div class="preview">
          <a href="/bigscreen" title="<?=(isset($model->streamInfo['status'])) ? Tpl::out($model->streamInfo['status']) : ''?>" style="<?=(!empty($model->streamInfo['stream'])) ? 'background: url('. Tpl::out($model->streamInfo['stream']['preview']['medium']) .') no-repeat center center;' : ''?>"></a>
        </div>
        <div id="live-info-wrap">
          <div>
            <h1 title="<?=Tpl::out($model->streamInfo['status'])?>"><?=Tpl::out($model->streamInfo['status'])?></h1>
            <div id="live-info">
              Currently playing <strong class="live-info-game"><?=Tpl::out($model->streamInfo['game'])?></strong><br />
              Started <span class="live-info-updated"><?=Date::getElapsedTime(Date::getDateTime($model->streamInfo['lastbroadcast']))?></span><br />
              ~<span class="live-info-viewers"><?=Tpl::out((!empty($model->streamInfo['stream'])) ? $model->streamInfo['stream']['viewers'] : 0)?></span> viewers
            </div>
            <a id="live-link" href="/bigscreen" class="btn btn-lg btn-primary"><i style="margin-top: 2px;" class="icon-bigscreen animated"></i> Watch the live stream</a>
            <small class="hidden-xs">&nbsp; Prefer the old layout? <a href="/screen">Try this</a></small>
          </div>
        </div>
        <div id="live-popouts" class="hidden-xs">
          <a target="_blank" href="/embed/chat" class="popup btn btn-xs btn-link" data-options="<?=Tpl::out('{"height":"500","width":"420"}')?>">Pop-out chat</a>
          <a target="_blank" href="http://www.twitch.tv/destiny/popout" class="popup btn btn-xs btn-link" data-options="<?=Tpl::out('{"height":"420","width":"720"}')?>">Pop-out stream</a>
        </div>
      </div>
    </div>
  </section>
  
  <section id="offline-banner-view" class="container" <?= (!empty($model->streamInfo['stream'])) ? 'style="display: none"' : '' ?>>
    <div class="content">
      <div id="live-banner">
        <div class="preview">
          <a href="/bigscreen" title="<?=Tpl::out($model->streamInfo['status'])?>" style="background: url(<?=(!empty($model->broadcasts) && isset($model->broadcasts['videos'])) ? Tpl::out($model->broadcasts['videos'][0]['preview']) : Tpl::out($model->broadcasts['video_banner'])?>) no-repeat center center;"></a>
        </div>
        <div id="live-info-wrap">
          <div>
            <h1>Stream currently offline</h1>
            <div id="live-info">
              <span class="offline-status"><?= Tpl::out($model->streamInfo['status']) ?></span><br />
              Last broadcast ended <strong class="offline-info-lastbroadcast"><?=(isset($model->streamInfo['lastbroadcast'])) ? Date::getElapsedTime(Date::getDateTime($model->streamInfo['lastbroadcast'])) : ''?></strong><br />
              Was playing <strong class="offline-info-game"><?=(isset($model->streamInfo['game'])) ? Tpl::out($model->streamInfo['game']) : ''?></strong><br />
            </div>
            <a href="/bigscreen" class="btn btn-lg btn-primary">Join the chat while you wait!</a>
            <small class="hidden-xs">&nbsp; Prefer the old layout? <a href="/screen">Try this</a></small>
          </div>
        </div>
        <div id="live-popouts" class="hidden-xs">
          <a target="_blank" href="/embed/chat" class="popup btn btn-xs btn-link" data-options="<?=Tpl::out('{"height":"500","width":"420"}')?>">Pop-out chat</a>
          <a target="_blank" href="http://www.twitch.tv/destiny/popout" class="popup btn btn-xs btn-link" data-options="<?=Tpl::out('{"height":"420","width":"720"}')?>">Pop-out stream</a>
        </div>
      </div>
    </div>
  </section>

</div>
