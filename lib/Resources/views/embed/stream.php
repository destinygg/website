<?php
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Config;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($model->title)?></title>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" charset="utf-8">
<?php include Tpl::file('seg/commontop.php') ?>
<?php include Tpl::file('seg/google.tracker.php') ?>
</head>
<body class="embed">
	<iframe class="stream-element" marginheight="0" marginwidth="0" frameborder="0" src="http://www.twitch.tv/embed?channel=<?=Config::$a['twitch']['user']?>" scrolling="no" seamless></iframe>
	<?php include Tpl::file('seg/commonbottom.php') ?>
</body>
</html>