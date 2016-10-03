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
<?php include Tpl::file('seg/opengraph.php') ?>
<?php include Tpl::file('seg/commontop.php') ?>
<?php include Tpl::file('seg/google.tracker.php') ?>
</head>
<body id="error-401" class="error no-brand">
    <div id="page-wrap">
        <?php include Tpl::file('seg/top.php') ?>
        <?php include Tpl::file('seg/headerband.php') ?>
        <section id="error-container" class="container">
            <a title="Rick and Morty" href="http://www.adultswim.com/videos/rick-and-morty/" target="_blank" id="mortyface"></a>
            <h1>Aw geez, Rick!</h1>
            <p>You must be authenticated to view this page. Go to the <a href="/login">sign in</a> page</p>
        </section>
    </div>
    <?php include Tpl::file('seg/foot.php') ?>
    <?php include Tpl::file('seg/commonbottom.php') ?>
</body>
</html>