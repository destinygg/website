<?
namespace Destiny;
use Destiny\Utils\Http;
use Destiny\Utils\Date;
use Destiny\Utils\Tpl;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($model->title)?></title>
<meta charset="utf-8">
<?include'./tpl/seg/opengraph.php'?>
<link href="<?=Config::cdn()?>/web/css/bigscreen.css" rel="stylesheet" media="screen">
<?include'./tpl/seg/commontop.php'?>
<?include'./tpl/seg/google.tracker.php'?>
</head>
<body id="bigscreen">
	<div class="page-wrap">
	
		<section id="header-band">
			<div class="container" style="position: relative;">
				<header class="hero-unit" id="overview">
					<h1><?=Config::$a['meta']['title']?></h1>
					<div id="destiny-illustration"></div>
					<div style="top:25px; right:0; margin-right:312px; position: absolute;">
						<ul class="nav nav-pills">
							<?php if(!Session::hasRole(\Destiny\UserRole::USER)): ?>
							<li><a href="/login"><i class="icon-heart icon-white subtle"></i> Register</a></li>
							<?php endif; ?>
							<li><a href="/"><i class="icon-home icon-white subtle"></i> Home</a></li>
						</ul>
					</div>
				</header>
			</div>
		</section>
		
		<div class="page-content container clearfix">
		
			<div id="twitch-stream-wrap" class="pull-left">
				<div>
					<div class="panelheader clearfix">
						<div class="toolgroup clearfix">
							<div class="pull-left channel-stat game">
								<?php if($model->streamInfo['stream'] == null): ?>
								<i class="icon-time icon-white subtle"></i> <span>Last broadcast ended <?=Date::getElapsedTime(Date::getDateTime($model->streamInfo['lastbroadcast']))?></span>
								<?php else: ?>
								<i class="icon-time icon-white subtle"></i> <span>Started <?=Date::getElapsedTime(Date::getDateTime($model->streamInfo['stream']['channel']['updated_at']))?></span>
								<?php if(intval($model->streamInfo['stream']['channel']['delay']) > 1): ?>
								- <?=(intval($model->streamInfo['stream']['channel']['delay'])/60)?>m delay
								<?php endif; ?>
							<?php endif; ?>
							</div>
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
	
	<?include'./tpl/seg/foot.php'?>
	<?include'./tpl/seg/commonbottom.php'?>
	
</body>
</html>