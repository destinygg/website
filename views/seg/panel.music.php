<?php
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Utils\Date;
?>
<section class="container">
  <div class="content content-dark clearfix row-no-padding">

      <div class="media-block col-sm-6 stream">
          <h3 class="title">
              <span>Blog</span> <a href="/blog">destiny.gg</a>
          </h3>
          <?php if(!empty($this->articles)): ?>
          <div class="entries">
              <?php for ($i=0; $i<min($this->articles, 3); ++$i):?>
                  <?php $article = $this->articles[$i] ?>
                  <div class="media">
                      <div class="media-body">
                          <div class="media-heading">
                              <a href="<?=$article['permalink']?>"><?=$article['title']?></a>
                          </div>
                          <?php if(count($article['categories']) > 0): ?>
                              <div>
                                  <small>in</small> <?= join(", ", $article['categories']); ?>
                              </div>
                          <?php endif ?>
                          <?=Tpl::moment(Date::getDateTime($article['date']),'F jS Y', 'MMMM Do YYYY')?>
                      </div>
                  </div>
              <?php endfor;?>
          </div>
          <?php else: ?>
          <div class="empty">
              <p>We're still working on the blog posts...</p>
          </div>
          <?php endif; ?>
      </div>

      <div id="stream-lastfm" class="media-block col-sm-6 stream">
          <h3 class="title">
              <span>Music</span>
              <a href="/lastfm">last.fm</a>
          </h3>
          <?php if(!empty($this->recenttracks) && isset($this->recenttracks['recenttracks']['track']) && !empty($this->recenttracks['recenttracks']['track'])): ?>
          <div class="entries">
              <?php foreach($this->recenttracks['recenttracks']['track'] as $trackIndex=>$track): ?>
                  <?php if($trackIndex == 3){break;}; ?>
                  <div class="media">
                      <a class="pull-left cover-image" href="<?=$track['url']?>"><img class="media-object img_64x64" data-src="<?=$track['image'][1]['#text']?>"></a>
                      <div class="media-body">
                          <div class="media-heading trackname">
                              <a href="<?=$track['url']?>"><?=Tpl::out($track['name'])?></a>
                          </div>
                          <div class="artist"><?=Tpl::out($track['artist']['#text'])?></div>
                          <div class="details">
                              <?php if($track['date_str'] != ''):?>
                                  <span class="pull-right"><?=Tpl::fromNow(Date::getDateTime($track['date_str']))?></span>
                              <?php endif ?>
                              <?php if($trackIndex==0 && $track['date_str'] == ''): ?>
                                  <span class="pull-right"><time>now playing</time></span>
                              <?php endif ?>
                              <small class="album"><?=Tpl::out($track['album']['#text'])?></small>
                          </div>
                      </div>
                  </div>
              <?php endforeach; ?>
          </div>
          <?php else: ?>
          <div class="empty">
              <p>We're still working on the playlist...</p>
          </div>
          <?php endif; ?>
      </div>

  </div>
</section>
