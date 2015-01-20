<?php
use Destiny\Common\Config;
?>
<section id="dc-donate-xs" class="container visible-sm visible-xs visible-md">
    <div class="content content-dark clearfix">
        <div class="ds-block">
            <p><a title="Visit www.paypal.com" href="//www.paypal.com/">PayPal</a> donations are welcome and appreciated</p>
            <div>
                <button form="paypal-form" class="btn btn-lg btn-paypal" title="PayPal - The safer, easier way to pay online!">Donate</button>
                <i class="paypal-payment-methods" title="Payment methods"></i>
            </div>
        </div>
    </div>
</section>
<section id="dc-donate" class="container row-no-padding">
    <div id="dc-donate-wrap">
        <div id="ting-block" class="dc-donate-blk">
            <div class="dc-donate-inner">
                <img height="1" width="1" src="//ting.7eer.net/i/72409/87559/2020" border="0" style="position: absolute; top:0; left:0;" />
                <a href="http://ting.7eer.net/c/72409/87559/2020"><img src="//adn.impactradius.com/display-ad/2020-87559" border="0" alt="" width="250" height="250"/></a>
            </div>
        </div>
        <div id="donate-block" class="dc-donate-blk visible-lg">
            <div class="dc-donate-inner">
                <p><a title="Visit www.paypal.com" href="//www.paypal.com/">PayPal</a> donations are welcome<br /> and appreciated</p>
                <div>
                    <button form="paypal-form" class="btn btn-lg btn-paypal" title="PayPal - The safer, easier way to pay online!">Donate</button>
                    <i class="paypal-payment-methods" title="Payment methods"></i>
                </div>
            </div>
        </div>
        <div id="google-block" class="dc-donate-blk">
            <div class="dc-donate-inner">
                <script type="text/javascript">
                <?php foreach ( Config::$a ['googleads'] ['300x250'] as $k => $v ): ?>
                    <?= (is_int ( $v )) ? "$k = $v;\r\n" : "$k = \"$v\";"; ?>
                <?php endforeach; ?>
                </script>
                <script src="//pagead2.googlesyndication.com/pagead/show_ads.js"></script>
            </div>
        </div>
    </div>
</section>

<form id="paypal-form" action="//www.paypal.com/cgi-bin/webscr" method="post">
    <input type="hidden" name="cmd" value="_donations">
    <input type="hidden" name="business" value="<?=Config::$a['paypal']['email']?>">
    <input type="hidden" name="lc" value="US">
    <input type="hidden" name="item_name" value="<?=Config::$a['paypal']['name']?>">
    <input type="hidden" name="no_note" value="0">
    <input type="hidden" name="currency_code" value="USD"> <input type="hidden" name="bn" value="PP-DonationsBF:btn_donateCC_LG.gif:NonHostedGuest">
    <img alt="" border="0" src="//www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>