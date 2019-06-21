<?php
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Utils\Date;
?>
<section class="container">
    <div class="content content-dark">
        <div class="content-split row no-gutters">
            <div class="media-block col-sm-6 stream">
                <h3 class="title">
                    <span>Reddit</span> <a href="/reddit">reddit.com</a>
                </h3>
                <?php if(!empty($this->posts)): ?>
                    <div class="entries">
                        <?php foreach(array_slice($this->posts, 0, 2) as $i=>$a): ?>
                            <div class="media">
                                <div class="media-body">
                                    <div class="media-heading">
                                        <a title="<?=Tpl::out($a['title'])?>" target="_blank" href="<?=$a['permalink']?>">
                                            <i class="fas fa-share"></i>
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
            <div class="media-block col-sm-6 stream">
                <h3 class="title">
                    <span>More</span> <a href="/reddit">reddit.com</a>
                </h3>
                <?php if(!empty($this->posts)): ?>
                    <div class="entries">
                        <?php foreach(array_slice($this->posts, 2, 2) as $i=>$a): ?>
                            <div class="media">
                                <div class="media-body">
                                    <div class="media-heading">
                                        <a title="<?=Tpl::out($a['title'])?>" target="_blank" href="<?=$a['permalink']?>">
                                            <i class="fas fa-share"></i>
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
    </div>
</section>