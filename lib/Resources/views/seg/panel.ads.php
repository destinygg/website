<?php
use Destiny\Common\Config;
?>

<section id="dc-donate" class="container row-no-padding">
    <div id="dc-donate-wrap">
        <div id="ting-block" class="dc-donate-blk">
            <div class="dc-donate-inner">
                <img height="1" width="1" src="//ting.7eer.net/i/72409/87559/2020" border="0" style="position: absolute; top:0; left:0;" />
                <a href="http://ting.7eer.net/c/72409/87559/2020"><img src="//adn.impactradius.com/display-ad/2020-87559" border="0" alt="" width="250" height="250"/></a>
            </div>
        </div>
        <div id="google-block" class="dc-donate-blk">
            <div class="dc-donate-inner">
                <script type="text/javascript">
                <?php foreach ( Config::$a ['googleads'] ['300x250'] as $k => $v ): ?>
                    <?= (is_int ( $v )) ? "$k = $v;\r\n" : "$k = \"$v\";"; ?>
                <?php endforeach; ?>
                </script>
                <script src="https://pagead2.googlesyndication.com/pagead/show_ads.js"></script>
            </div>
        </div>
    </div>
</section>