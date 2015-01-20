<?php
use Destiny\Common\Config;
?>
<div id="footer">
    <div id="footer-inner" class="container">
        <div id="footer-copyright" class="col-sm-5">
            <h3>Destiny.gg <small><sup>&copy;</sup></small></h3>
            <p><?=Config::$a['meta']['shortdescription']?></p>
            <p>
                The code for this website is open source and hosted on <a href="https://github.com/destinygg">Github</a>
                <br />please feel free to contribute!
            </p>
            <p>
                www.destiny.gg
                <br /><a href="mailto:steven.bonnell.ii@gmail.com" title="Email Destiny">steven.bonnell.ii@gmail.com</a>
            </p>
        </div>
        <div class="col-sm-7">
            <div id="footer-links" class="row">
                <div id="footer-badges" class="col-sm-7">
                    <h3>Community</h3>
                    <p>
                        Illustration feature by <a title="Many thanks!" href="http://guilhemsalines.blogspot.com/">@elevencyan</a>
                        <br />Source code for <a href="https://github.com/destinygg/website">website</a> and <a href="https://github.com/destinygg/chat">chat</a>
                        <br />The Destiny sub-reddit <a title="www.reddit.com" href="//www.reddit.com/r/Destiny">/r/Destiny</a>
                    </p>
                </div>
                <div id="footer-thanks" class="col-sm-5">
                    <h3>Get the App</h3>
                    <a target="_blank" title="Destiny.gg for Android" href="https://play.google.com/store/apps/details?id=gg.destiny.app.chat">
                        <img width="197" height="59" src="<?=Config::cdn()?>/web/img/google-play-icon.png" />
                    </a>
                </div>
            </div>
            <div class="row" style="margin-top: 20px;">
                <div class="col-sm-7">
                    All payments on this website use <a title="Paypal" target="_blank" rel="nofollow" href="http://www.paypal.com/">Paypal</a>
                </div>
                <div class="col-sm-5">
                    www.destiny.gg &copy; <?=date('Y')?>
                </div>
            </div>
        </div>
    </div>
</div>