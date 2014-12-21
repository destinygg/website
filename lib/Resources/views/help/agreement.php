<?php
namespace Destiny;
use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\Tpl;
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
<body id="agreement">
    <div id="page-wrap">

        <?php include Tpl::file('seg/top.php') ?>
        <?php include Tpl::file('seg/headerband.php') ?>
        
        <section class="container">
            <h1 class="title">
                <small class="subtle pull-right" style="font-size:14px; margin-top:20px;">Last update: <?=Date::getDateTime(filemtime(__FILE__))->format(Date::STRING_FORMAT)?></small>
                <span>User agreement</span>
            </h1>
            <div class="content content-dark clearfix">
                <div class="ds-block">
                    <p>There is no user agreement.</p>
                </div>
            </div>
        </section>
        
        <?php include Tpl::file('seg/panel.ads.php') ?>
    </div>

    <?php include Tpl::file('seg/foot.php') ?>
    <?php include Tpl::file('seg/commonbottom.php') ?>
</body>
</html>