<?php
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Utils\Date;
?>
<section class="container">
    <div class="content content-dark content-split clearfix row-no-padding">

        <div class="media-block col-sm-6 stream">
            <h3 class="title">
                <span>Tweets</span>
                <a href="/twitter">twitter.com</a>
            </h3>
            <?php if(!empty($this->tweets)): ?>
            <div class="entries">
                <?php foreach($this->tweets as $i=>$tweet): ?>
                <?php if($i == 4){break;}; ?>
                    <div class="media">
                        <div class="media-body">
                            <div class="media-heading">
                                <a target="_blank" href="https://twitter.com/<?=$tweet['user']['screen_name']?>/status/<?=$tweet['id_str']?>">
                                    <span class="fa fa-share"></span>
                                </a>
                                <?=$tweet['html']?>
                            </div>
                            <?=Tpl::fromNow(Date::getDateTime($tweet['created_at']))?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="empty">
                <p>We're working on those tweets ...</p>
            </div>
            <?php endif; ?>
        </div>

        <div class="media-block col-sm-6 stream">
            <h3 class="title">
                <span>Reddit</span> <a href="/reddit">reddit.com</a>
            </h3>
            <?php if(!empty($this->posts)): ?>
            <div class="entries">
                <?php foreach($this->posts as $i=>$a): ?>
                <?php if($i == 4){break;}; ?>
                <div class="media">
                    <div class="media-body">
                        <div class="media-heading">
                            <a title="<?=Tpl::out($a['title'])?>" target="_blank" href="<?=$a['permalink']?>">
                                <span class="fa fa-share"></span>
                            </a>
                            <?=Tpl::out($a['title'])?>
                        </div>
                        <div>
                            <small>By</small> <?=Tpl::out($a['author'])?> <?=Tpl::fromNow(Date::getDateTime($a['created']))?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="empty">
                <p>We're getting those latest reddit posts ...</p>
            </div>
            <?php endif; ?>
        </div>

    </div>
</section>