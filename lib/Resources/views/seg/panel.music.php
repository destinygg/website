<?php
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Utils\Date;
use Destiny\Common\Config;
?>
<section class="container">
  <div class="content content-dark clearfix row-no-padding">

    <div id="stream-twitter" class="media-block col-sm-6 stream">
      <h3 class="title">
        <span>Tweets</span>
        <a href="/twitter">twitter.com</a>
      </h3>
      <div class="entries">
      <?if(!empty($model->tweets)):?>
      <?foreach($model->tweets as $tweetIndex=>$tweet):?>
      <?if($tweetIndex == 3){break;};?>
        <div class="media">
          <div class="media-body">
            <div class="media-heading">
              <a target="_blank" href="//twitter.com/<?=$tweet['user']['screen_name']?>/status/<?=$tweet['id_str']?>">
                <span class="fa fa-share"></span>
              </a>
              <?=$tweet['html']?>
            </div>
            <?=Tpl::fromNow(Date::getDateTime($tweet['created_at']))?>
          </div>
        </div>
      <?endforeach;?>
      <?else:?>
        <p class="loading">Loading tweets ...</p>
      <?endif;?>
      </div>
    </div>

    <div id="stream-lastfm" class="media-block col-sm-6 stream">
      <h3 class="title">
        <span>Music</span>
        <a href="/lastfm">last.fm</a>
      </h3>
      <div class="entries">
      <?if(!empty($model->music) && isset($model->music['recenttracks']['track']) && !empty($model->music['recenttracks']['track'])):?>
      <?foreach($model->music['recenttracks']['track'] as $trackIndex=>$track):?>
      <?if($trackIndex == 3){break;};?>
        <div class="media">
          <a class="pull-left cover-image" href="<?=$track['url']?>"><img class="media-object" src="<?=Config::cdn()?>/web/img/64x64.gif" data-src="<?=$track['image'][1]['#text']?>"></a>
          <div class="media-body">
            <div class="media-heading trackname">
              <a href="<?=$track['url']?>"><?=Tpl::out($track['name'])?></a>
            </div>
            <div class="artist"><?=Tpl::out($track['artist']['#text'])?></div>
            <div class="details">
              <?if($track['date_str'] != ''):?>
              <span class="pull-right"><?=Tpl::fromNow(Date::getDateTime($track['date_str']))?></span>
              <?endif;?>
              <?if($trackIndex==0 && $track['date_str'] == ''):?>
              <span class="pull-right"><time>now playing</time></span>
              <?endif;?>
              <small class="album subtle"><?=Tpl::out($track['album']['#text'])?></small>
            </div>
          </div>
        </div>
      <?endforeach;?>
      <?else:?>
        <p class="loading">Loading music ...</p>
      <?endif;?>
      </div>
    </div>

  </div>
</section>
