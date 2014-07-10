<?
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
<body id="home">

    <?php include Tpl::file('seg/headerband.php') ?>

    <section class="container">
        <h1 class="title">You are being redirected <small id="timeleft">in 3 seconds</small></h1>
        <div class="content content-dark clearfix">
            <div class="ds-block">
                <a href="<?= Tpl::out($model->url) ?>">
                    <span class="glyphicon glyphicon-share"></span>
                </a>
                <?= Tpl::out($model->url) ?>
            </div>
        </div>
    </section>

    <?php include Tpl::file('seg/commonbottom.php') ?>

    <script>
    var cnt = 3,
        url = '<?= Tpl::out($model->url) ?>';
    _gaq.push(['_trackEvent', 'outbound', 'redirect', url]);
    _gaq.push(function() {
        var interval = setInterval(function() {
            cnt--;
            if (cnt == 0) {
                $('#timeleft').text('now ...');
                window.location.replace(url);
                clearInterval(interval);
            }else{
                $('#timeleft').text('in '+ cnt +' seconds');
            }
        }, 1000);
    });
    </script>

</body>
</html>