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
<meta name="description" content="<?=Config::$a['meta']['description']?>">
<meta name="keywords" content="<?=Config::$a['meta']['keywords']?>">
<meta name="author" content="<?=Config::$a['meta']['author']?>">
<?php include 'seg/opengraph.php' ?>
<?php include 'seg/commontop.php' ?>
<?php include 'seg/google.tracker.php' ?>
</head>
<body id="error-404" class="error no-brand">
    <div id="page-wrap">
        <?php include 'seg/top.php' ?>
        <?php include 'seg/headerband.php' ?>
        <section id="error-container" class="container">
            <a title="Rick and Morty" href="http://www.adultswim.com/videos/rick-and-morty/" target="_blank" id="mortyface"></a>
            <h1>Aw geez, Rick!</h1>
            <p>We could'nt find the page you were looking for. <br />Would you like to <a href="/">return to the start</a>?</p>
        </section>
    </div>
    <?php include 'seg/foot.php' ?>
    <?php include 'seg/commonbottom.php' ?>
</body>
</html>