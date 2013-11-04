<?
namespace Destiny;
use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Session;
use Destiny\Common\Config;
use Destiny\Common\User\UserRole;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($model->title)?></title>
<meta charset="utf-8">
<?php include Tpl::file('seg/opengraph.php') ?>
<?php include Tpl::file('seg/commontop.php') ?>
<link href="<?=Config::cdnv()?>/web/css/bigscreen.css" rel="stylesheet" media="screen">
<?php include Tpl::file('seg/google.tracker.php') ?>
</head>
<body id="bigscreen">
	<div class="page-wrap">
	
		<section id="header-band">
			<div class="container" style="position: relative;">
				<header class="hero-unit" id="overview">
					<h1><?=Config::$a['meta']['title']?></h1>
					<div id="destiny-illustration"></div>
					<div style="top:25px; right:0; width:400px; position: absolute;">
						<ul class="nav nav-pills">
							<?php if(!Session::hasRole(UserRole::USER)): ?>
							<li><a href="/login"><i class="icon-heart icon-white subtle"></i> Register</a></li>
							<?php elseif(!Session::hasRole(UserRole::SUBSCRIBER)): ?>
							<li><a href="/subscribe"><i class="icon-heart icon-white subtle"></i> Subscribe</a></li>
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
					<div class="twitch-element-wrap">
						<div class="twitch-overlay to-botright"></div>
						<div class="twitch-overlay to-botleft"></div>
						<div class="twitch-overlay to-main"></div>
						<div class="twitch-fsbtn"></div>
						<iframe class="twitch-element" src="http://www.twitch.tv/embed?channel=<?=Config::$a['twitch']['user']?>" height="100%" width="100%" style="border:none; overflow: hidden;" scrolling="no" seamless="seamless"></iframe>
					</div>
				</div>
			</div>
			<div id="chat-panel" class="pull-right">
				<div>
					<iframe class="twitch-element" style="border:none;" seamless="seamless" src="/embed/chat"></iframe>
				</div>
			</div>
		</div>
	
	</div>
	
	<?php include Tpl::file('seg/foot.php') ?>
	<?php include Tpl::file('seg/commonbottom.php') ?>
	
</body>
</html>