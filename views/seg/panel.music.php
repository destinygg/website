<?php
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Utils\Date;
?>
<section class="container mb-3">
    <div class="content content-dark">
        <div class="content-split row no-gutters">
            <div class="media-block col-sm-6 stream">
                <h3 class="title">
                    <span>Top Tracks</span>
                    <a href="/lastfm">last.fm</a>
                </h3>
                <?php if(!empty($this->toptracks) && isset($this->toptracks['toptracks']['track']) && !empty($this->toptracks['toptracks']['track'])): ?>
                    <div class="entries">
                        <?php foreach($this->toptracks['toptracks']['track'] as $trackIndex=>$track): ?>
                            <?php if($trackIndex == 3){break;}; ?>
                            <div class="media">
                                <a class="float-left cover-image" href="<?=$track['url']?>" style="width: 74px; height: 64px;"><img alt="<?=Tpl::out($track['name'])?>" class="media-object img_64x64" data-src="<?=$track['image'][1]['#text']?>"></a>
                                <div class="media-body">
                                    <div class="media-heading trackname">
                                        <a href="<?=$track['url']?>"><?=Tpl::out($track['name'])?></a>
                                    </div>
                                    <div class="artist"><?=Tpl::out($track['artist']['name'])?></div>
                                    <div class="details">
                                        <small class="album">Played <?=Tpl::out($track['playcount'])?> times</small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty">
                        <p>We're still working on the top tracks...</p>
                    </div>
                <?php endif; ?>
            </div>
            <div class="media-block col-sm-6 stream">
                <h3 class="title">
                    <span>Recently Played</span>
                    <a href="/lastfm">last.fm</a>
                </h3>
                <?php if(!empty($this->recenttracks) && isset($this->recenttracks['recenttracks']['track']) && !empty($this->recenttracks['recenttracks']['track'])): ?>
                    <div class="entries">
                        <?php foreach($this->recenttracks['recenttracks']['track'] as $trackIndex=>$track): ?>
                            <?php if($trackIndex == 3){break;}; ?>
                            <div class="media">
                                <a class="float-left cover-image" href="<?=$track['url']?>" style="width: 74px; height: 64px;"><img class="media-object img_64x64" data-src="<?=$track['image'][1]['#text']?>"></a>
                                <div class="media-body">
                                    <div class="media-heading trackname">
                                        <a href="<?=$track['url']?>"><?=Tpl::out($track['name'])?></a>
                                    </div>
                                    <div class="artist"><?=Tpl::out($track['artist']['#text'])?></div>
                                    <div class="details">
                                        <?php if($track['date_str'] != ''):?>
                                            <span><?=Tpl::fromNow(Date::getDateTime($track['date_str']))?></span>
                                        <?php endif ?>
                                        <?php if($trackIndex==0 && $track['date_str'] == ''): ?>
                                            <span><time>now playing</time></span>
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
    </div>
</section>
