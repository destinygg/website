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
	<object class="twitch-element" type="application/x-shockwave-flash" id="live_embed_player_flash" data="http://www.twitch.tv/widgets/live_embed_player.swf?channel=<?=Config::$a['twitch']['user']?>" height="100%" width="100%">
		<param name="allowFullScreen" value="true">
		<param name="allowScriptAccess" value="always">
		<param name="allowNetworking" value="all">
		<param name="movie" value="http://www.twitch.tv/widgets/live_embed_player.swf">
		<param name="flashvars" value="hostname=www.twitch.tv&amp;channel=<?=Config::$a['twitch']['user']?>&amp;auto_play=true&amp">
	</object>
	<?php include Tpl::file('seg/commonbottom.php') ?>
</body>
</html>