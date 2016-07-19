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
<body id="amazon" class="no-brand">
    <div id="page-wrap">

        <?php include Tpl::file('seg/top.php') ?>
        <?php include Tpl::file('seg/headerband.php') ?>

        <div class="container">
            <div class="row">
                <div style="text-align: center;">
                    <div style="margin-top: 60px; margin-bottom: 40px;">
                        <a href="http://www.amazon.com/?tag=des000-20"><img alt="amazon.com" src="<?=Config::cdn()?>/web/img/amazon.png" /></a>
                    </div>
                    <div>
                        <a href="http://www.amazon.com/?tag=des000-20">US</a>
                        <span style="padding:0 5px;">&bull;</span>
                        <a href="http://www.amazon.ca/?tag=destiny0f7a-20">Canada</a>
                        <span style="padding:0 5px;">&bull;</span>
                        <a href="http://www.amazon.co.uk/?tag=destiny0f7-21">United Kingdom</a>
                        <span style="padding:0 5px;">&bull;</span>
                        <a href="http://www.amazon.de/?tag=destiny0f-21">Germany</a>
                        <span style="padding:0 5px;">&bull;</span>
                        <a href="http://www.amazon.com/?tag=des000-20">Other</a>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <?php include Tpl::file('seg/foot.php') ?>
    <?php include Tpl::file('seg/commonbottom.php') ?>
</body>
</html>