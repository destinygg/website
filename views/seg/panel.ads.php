<?php
use Destiny\Common\Config;
use Destiny\Common\Utils\Tpl;
?>

<section id="dc-donate" class="container row-no-padding">
    <div id="dc-donate-wrap">
        <div id="ting-block" class="dc-donate-blk">
            <div class="dc-donate-inner">
                <img height="1" width="1" src="https://ting.7eer.net/i/72409/87559/2020" border="0" style="position: absolute; top:0; left:0;" />
                <a href="https://ting.7eer.net/c/72409/87559/2020"><img src="https://adn-ssl.impactradius.com/display-ad/2020-87559" border="0" alt="" width="250" height="250"/></a>
            </div>
        </div>

        <div id="gmg-block" class="dc-donate-blk">
           <a href="https://www.destiny.gg/gmg" target="_blank">
               <img src="<?=Config::cdnv()?>/img/ad.gmg.png" />
           </a>
        </div>

        <?php $ad = Config::$a['googleads']['300x250']; ?>
        <?php if(!empty($ad)): ?>
        <div id="google-block" class="dc-donate-blk">
            <div class="dc-donate-inner">
                <script type="text/javascript">
                google_ad_client = '<?= Tpl::out($ad['google_ad_client']) ?>';
                google_ad_slot   = '<?= Tpl::out($ad['google_ad_slot']) ?>';
                google_ad_width  = 300;
                google_ad_height = 250;
                </script>
                <script src="https://pagead2.googlesyndication.com/pagead/show_ads.js"></script>
            </div>
        </div>
        <?php endif ?>

    </div>
</section>