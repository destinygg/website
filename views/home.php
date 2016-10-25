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
<meta name="description" content="<?=Config::$a['meta']['description']?>">
<meta name="keywords" content="<?=Config::$a['meta']['keywords']?>">
<meta name="author" content="<?=Config::$a['meta']['author']?>">
<?php include 'seg/commontop.php' ?>
<link href="<?=Config::cdnv()?>/web.css" rel="stylesheet" media="screen">
</head>
<body id="home" class="no-brand">
    <div id="page-wrap">
        <?php include 'seg/top.php' ?>
        <?php include 'seg/headerband.php' ?>
        <?php include 'seg/panel.videos.php' ?>
        <?php include 'seg/panel.music.php' ?>
        <?php include 'seg/panel.blog.php' ?>
        <?php include 'seg/panel.ads.php' ?>
    </div>
    <?php include 'seg/foot.php' ?>
    <?php include 'seg/commonbottom.php' ?>
    <script src="<?=Config::cdnv()?>/web.js"></script>
</body>
</html>