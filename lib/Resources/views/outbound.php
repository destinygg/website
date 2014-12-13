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
<script src="<?=Config::cdnvf('1.1.0')?>/vendor/libs.min.js"></script>
<?php include Tpl::file('seg/google.tracker.php') ?>
<script>

// Make sure the user is redirected (for those who block ga.js)
var to = window.setTimeout(function(){
    window.location.replace(url);
}, 3500);

// Normal analytics
var url = '<?= $model->url ?>';
_gaq.push(['_trackEvent', 'outbound', 'redirect', url]);
_gaq.push(function(){
    window.clearTimeout(to);
    window.location.replace(url);
});

</script>
</head>
<body>
    <p>Please wait while we redirect you to <a rel="nofollow" href="<?= $model->url ?>"><?= Tpl::out($model->url) ?></a> &hellip;</p>
    <noscript>
       <p>No javascript present >:( &hellip; Click the link <a rel="nofollow" href="<?= $model->url ?>"><?= Tpl::out($model->url) ?></a></p>
    </noscript>
</body>
</html>