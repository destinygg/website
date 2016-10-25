<?php
namespace Destiny;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Config;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($this->title)?></title>
<meta charset="utf-8">
<?php include 'seg/commontop.php' ?>
<link href="<?=Config::cdnv()?>/web.css" rel="stylesheet" media="screen">
</head>
<body id="amazon" class="no-brand">
    <div id="page-wrap">

        <?php include 'seg/top.php' ?>
        <?php include 'seg/headerband.php'?>

        <div class="container">
            <div class="row">
                <div style="text-align: center;">
                    <div style="margin-top: 60px; margin-bottom: 40px;">
                        <a title="Amazon" class="amazon-logo" href="http://www.amazon.com/?tag=des000-20"></a>
                    </div>
                    <div style="margin: 30px 0 60px 0;">
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

    <?php include 'seg/foot.php' ?>
    <?php include 'seg/commonbottom.php' ?>
    <script src="<?=Config::cdnv()?>/web.js"></script>
</body>
</html>