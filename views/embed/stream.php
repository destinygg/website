<?php
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Config;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($this->title)?></title>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" charset="utf-8">
<?php include 'seg/meta.php' ?>
<link href="<?=Config::cdnv()?>/web.css" rel="stylesheet" media="screen">
</head>
<body class="embed">
    <iframe class="stream-element" marginheight="0" marginwidth="0" frameborder="0" src="http://www.twitch.tv/<?=Config::$a['twitch']['user']?>/embed" scrolling="no" seamless></iframe>
    <?php include 'seg/tracker.php' ?>
    <script src="<?=Config::cdnv()?>/web.js"></script>
    <script>
    $(window).on('beforeunload', function(e){
        var confirmationMessage = "( ͡° ͜ʖ ͡°)";
        (e || window.event).returnValue = confirmationMessage;
        return confirmationMessage;
    });
    </script>
</body>
</html>