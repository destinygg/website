<?php
use Destiny\Common\Config;
use Destiny\Common\Utils\Tpl;
?>
<?php if(!empty($this->libsynfeed) && count($this->libsynfeed) >= 3): ?>
<section class="container" id="podcasts">
    <div class="entries row no-gutters content-dark">
        <?php foreach($this->libsynfeed as $i=>$a): ?>
            <?php if($i == 3){break;}; ?>
            <div class="media">
                <a class="float-left cover-image" href="<?=$a['full_item_url']?>" style="width: 74px;">
                    <img src="<?=Config::cdnv()?>/img/podcast80.png" alt="<?=Tpl::out($a['item_title'])?>" class="media-object img_64x64 podcast-icon">
                </a>
                <div class="media-body">
                    <div class="media-heading trackname">
                        <a href="<?=$a['full_item_url']?>"><?=Tpl::out($a['item_title'])?></a>
                    </div>
                    <div class="details">
                        <small>released on</small> <?=Tpl::out($a['release_date'])?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>