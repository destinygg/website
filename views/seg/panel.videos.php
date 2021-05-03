<?php
namespace Destiny;
use Destiny\Common\Config;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Utils\Date;
?>
<section class="container">
    <div class="content content-dark">
        <div class="content-split row no-gutters">
            <div id="youtube" class="col-sm-12 col-md-6 stream">
                <h3 class="title clearfix">
                    <span>Videos</span> <a href="/youtube" class="youtube-title">youtube.com</a>
                </h3>
                <?php if(isset($this->playlist) && !empty($this->playlist)): ?>
                    <ul class="thumbnails">
                        <?php foreach($this->playlist as $vid ): ?>
                            <?php $title = Tpl::out($vid['title'])?>
                            <li>
                                <div class="thumbnail" data-placement="bottom" data-toggle="tooltip" title="<?=$title?>">
                                    <a href="<?=$vid['videoUrl']?>">
                                        <img src="<?=Config::cdnv()?>/img/320x240.gif" class="img_320x240" alt="<?=$title?>" data-src="<?=$vid['thumbnailUrl']?>" />
                                    </a>
                                </div>
                            </li>
                        <?php endforeach;?>
                    </ul>
                <?php else: ?>
                    <div class="empty">
                        <p>We're still working on that playlist ...</p>
                    </div>
                <?php endif; ?>
            </div>
            <div id="broadcasts" class="col-sm-12 col-md-6 stream">
                <h3 class="title clearfix">
                    <span>Broadcasts</span> <a href="/twitch" class="twitch-title">twitch.tv</a>
                </h3>
                <?php if(!empty($this->broadcasts)) :?>
                    <ul class="thumbnails">
                        <?php foreach( $this->broadcasts as $broadcast ):?>
                            <?php $time = Date::getElapsedTime(Date::getDateTime($broadcast['startTime']))?>
                            <li>
                                <div class="thumbnail" data-placement="bottom" data-toggle="tooltip" title="<?=$time?>">
                                    <a href="<?=$broadcast['videoUrl']?>">
                                        <img src="<?=Config::cdnv()?>/img/320x240.gif" class="img_320x240" alt="<?=$time?>" data-src="<?=$broadcast['thumbnailUrl']?>" />
                                    </a>
                                </div>
                            </li>
                        <?php endforeach;?>
                    </ul>
                <?php else: ?>
                    <div class="empty">
                        <p>We're working on the latest broadcasts ...</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
