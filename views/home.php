<?php
namespace Destiny;
use Destiny\Common\Utils\Tpl;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?=Tpl::title($this->title)?>
    <?php include 'seg/meta.php' ?>
    <?=Tpl::manifestLink('web.css')?>
</head>
<body id="home" class="no-brand">
<div id="page-wrap">
    <?php include 'seg/nav.php' ?>
    <?php include 'seg/banner.php' ?>
    <?php include 'seg/panel.shop.php' ?>
    <?php include 'seg/panel.podcasts.php' ?>
    <?php include 'seg/panel.reddit.php' ?>
    <?php include 'seg/panel.videos.php' ?>
    <?php include 'seg/panel.music.php' ?>
    <?php/* include 'seg/panel.ads.php' */?>
</div>
<?php include 'seg/foot.php' ?>
<?php include 'seg/tracker.php' ?>
<?=Tpl::manifestScript('runtime.js')?>
<?=Tpl::manifestScript('common.vendor.js')?>
<?=Tpl::manifestScript('web.js')?>
</body>
</html>