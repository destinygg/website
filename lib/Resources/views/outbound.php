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
<?php include Tpl::file('seg/google.tracker.php') ?>
<script>
var url = '<?= $model->url ?>';
_gaq.push(['_trackEvent', 'outbound', 'redirect', url]);
_gaq.push(function(){
    window.location.replace(url);
});
</script>
</head>
<body>
    <p>Please wait while we redirect you &hellip;</p>
    <noscript>
       <p>No javascript present >:( &hellip; Click the link <a rel="nofollow" href="<?= $model->url ?>"><?= Tpl::out($model->url) ?></a></p>
    </noscript>
</body>
</html>