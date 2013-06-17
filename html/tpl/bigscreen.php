<?
namespace Destiny;
use Destiny\Service\Settings;
use Destiny\Utils\Http;
use Destiny\Utils\Date;
use Destiny\Utils\Tpl;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($model->title)?></title>
<meta charset="utf-8">
<meta name="description" content="<?=Config::$a['meta']['description']?>">
<meta name="keywords" content="<?=Config::$a['meta']['keywords']?>">
<meta name="author" content="<?=Config::$a['meta']['author']?>">
<meta property="og:site_name" content="<?=Config::$a['meta']['shortName']?>" />
<meta property="og:title" content="<?=Config::$a['meta']['title']?>" />
<meta property="og:description"	content="<?=Config::$a['meta']['description']?>" />
<meta property="og:image" content="<?=Config::cdn()?>/img/destinyspash600x600.png" />
<meta property="og:url" content="<?=Http::getBaseUrl()?>" />
<meta property="og:type" content="video.other" />
<meta property="og:video" content="<?=Config::$a['meta']['video']?>" />
<meta property="og:video:secure_url" content="<?=Config::$a['meta']['videoSecureUrl']?>" />
<meta property="og:video:type" content="application/x-shockwave-flash" />
<meta property="og:video:height" content="259" />
<meta property="og:video:width" content="398" />
<link href="<?=Config::cdn()?>/css/bootstrap.min.css" rel="stylesheet" media="screen">
<link href="<?=Config::cdn()?>/css/destiny.<?=Config::version()?>.css" rel="stylesheet" media="screen">
<link rel="shortcut icon" href="<?=Config::cdn()?>/favicon.png">
<?include'seg/google.tracker.php'?>
</head>
<body id="bigscreen">
	<div class="page-wrap">
	
		<section id="header-band">
			<div class="container" style="position: relative;">
				<header class="hero-unit" id="overview">
					<h1><?=Config::$a['meta']['title']?></h1>
					<div id="destiny-illustration"></div>
				</header>
				<div style="top:25px; right:0; margin-right:315px; position: absolute;">
					<ul class="nav nav-pills">
						<li><a href="/"><i class="icon-home icon-white subtle"></i> Home</a></li>
					</ul>
				</div>
			</div>
		</section>
		
		<div class="page-content container clearfix">
		
			<div id="twitch-stream-wrap" class="pull-left">
				<div>
					<div class="panelheader clearfix">
						<div class="toolgroup clearfix">
							<?php if($model->streamInfo['stream'] == null): ?>
							<div class="pull-left channel-stat game">
								<i class="icon-time icon-white subtle"></i> <span>Last broadcast ended <?=Date::getElapsedTime(Date::getDateTime($model->streamInfo['lastbroadcast']))?></span>
							</div>
							<?php else: ?>
							<div class="pull-left channel-stat game">
								<i class="icon-time icon-white subtle"></i> <span>Started <?=Date::getElapsedTime(Date::getDateTime($model->streamInfo['stream']['channel']['updated_at']))?></span>
								<?php if(intval($model->streamInfo['stream']['channel']['delay']) > 1): ?>
								<?=(intval($model->streamInfo['stream']['channel']['delay'])/60)?>m delay
								<?php endif; ?>
							</div>
							<?php endif; ?>
							<div class="pull-right channel-stat" style="text-align:right;"><?=Tpl::out($model->streamInfo['status'])?></div>
						</div>
					</div>
					<object class="twitch-element" type="application/x-shockwave-flash" id="live_embed_player_flash" data="http://www.twitch.tv/widgets/live_embed_player.swf?channel=<?=Config::$a['twitch']['user']?>" bgcolor="#000000">
						<param name="allowFullScreen" value="true" />
						<param name="allowScriptAccess" value="always" />
						<param name="allowNetworking" value="all" />
						<param name="movie" value="http://www.twitch.tv/widgets/live_embed_player.swf" />
						<param name="flashvars" value="hostname=www.twitch.tv&channel=<?=Config::$a['twitch']['user']?>&auto_play=true" />
					</object>
				</div>
			</div>
			<div id="twitch-chat-wrap" class="pull-right">
				<div>
					<iframe class="twitch-element" frameborder="0" scrolling="no" id="chat_embed" src="http://www.twitch.tv/chat/embed?channel=<?=Config::$a['twitch']['user']?>&amp;popout_chat=true"></iframe>
				</div>
			</div>
		</div>
	
	</div>
	
	<?include'seg/foot.php'?>
	
	<script src="<?=Config::cdn()?>/js/vendor/jquery-1.9.1.min.js"></script>
	<script src="<?=Config::cdn()?>/js/vendor/jquery.cookie.js"></script>
	<script src="<?=Config::cdn()?>/js/vendor/bootstrap.js"></script>
	<script src="<?=Config::cdn()?>/js/vendor/moment.js"></script>
	<script src="<?=Config::cdn()?>/js/destiny.<?=Config::version()?>.js"></script>
	<script>destiny.init({cdn:'<?=Config::cdn()?>'});</script>
	
</body>
</html>