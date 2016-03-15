<?php
namespace Destiny;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Config;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($model->title)?></title>
<meta charset="utf-8">
<?php include Tpl::file('seg/opengraph.php') ?>
<?php include Tpl::file('seg/commontop.php') ?>
<?php include Tpl::file('seg/google.tracker.php') ?>
</head>
<body id="amazon">
    <div id="page-wrap">
        <?php include Tpl::file('seg/top.php') ?>
        <?php include Tpl::file('seg/headerband.php') ?>
        <div class="container">
            <div class="row">
                <div class="col-md-4 amazon-referral">
                    <a href="http://www.amazon.ca/?tag=destiny0f7a-20">
                        <h2 style="font-weight: normal;">Canada</h2>
                        <img alt="amazon.ca" width="250" height="65" src="<?=Config::cdn()?>/web/img/amazon.ca.jpg" />
                    </a>
                </div>
                <div class="col-md-4 amazon-referral">
                    <a href="http://www.amazon.com/?tag=des000-20">
                        <h2>US &amp; Other</h2>
                        <img alt="amazon.com" width="250" height="65" src="<?=Config::cdn()?>/web/img/amazon.com.jpg" />
                    </a>
                </div>
                <div class="col-md-4 amazon-referral">
                    <a href="http://www.amazon.co.uk/?tag=destiny0f7-21">
                        <h2>United Kingdom</h2>
                        <img alt="amazon.co.uk" width="250" height="65" src="<?=Config::cdn()?>/web/img/amazon.co.uk.jpg" />
                    </a>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4"></div>
                <div class="col-md-4 amazon-referral">
                    <a href="http://www.amazon.de/?tag=destiny0f-21">
                        <h2>Germany</h2>
                        <img alt="amazon.de" width="250" height="65" src="<?=Config::cdn()?>/web/img/amazon.de.jpg" />
                    </a>
                </div>
                <div class="col-md-4"></div>
            </div>
        </div>
    </div>
    <?php include Tpl::file('seg/foot.php') ?>
    <?php include Tpl::file('seg/commonbottom.php') ?>
</body>
</html>