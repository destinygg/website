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
                        <?php foreach($this->posts as $i=>$a): ?>
                            <?php if($i == 2){break;}; ?>
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
                    <span>Blog</span> <a href="/blog">destiny.gg</a>
                </h3>
                <?php if(!empty($this->articles)): ?>
                    <div class="entries">
                        <?php for ($i=0; $i<min($this->articles, 2); ++$i):?>
                            <?php $article = $this->articles[$i] ?>
                            <div class="media">
                                <div class="media-body">
                                    <div class="media-heading">
                                        <a href="<?=$article['permalink']?>"><?=$article['title']?></a>
                                    </div>
                                    <div>
                                        <?php if(count($article['categories']) > 0): ?>
                                            <small>in</small> <?= join(", ", $article['categories']); ?>
                                        <?php endif ?>
                                        <?=Tpl::moment(Date::getDateTime($article['date']),'F jS Y', 'MMMM Do YYYY')?>
                                    </div>
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
        </div>
    </div>
</section>