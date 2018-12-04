<?php
use Destiny\Common\Config;
use Destiny\Common\Utils\Tpl;
?>
<?php if(!empty($this->merchandise)): ?>
<section class="container">
    <div class="content content-dark">
        <div class="content-split row no-gutters">
            <div id="youtube" class="col-sm-12 stream">
                <h3 class="title clearfix">
                    <span class="mr-auto">Shop</span>
                    <a href="/shop">buy.more.stuff</a>
                </h3>
                <ul class="thumbnails row no-gutters" style="border: none;">
                    <?php foreach ($this->merchandise as $i=>$a): ?>
                        <li class="col-6 col-md-2 col-xs-6">
                            <div class="thumbnail" data-placement="bottom" data-toggle="tooltip" title="<?=Tpl::out($a['title'])?>">
                                <a href="<?=$a['url']?>">
                                    <img src="<?=Config::cdnv()?>/img/<?=$a['image']?>.png"  class="<?=$a['image']?>" alt="<?=Tpl::out($a['title'])?>" />
                                </a>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>
