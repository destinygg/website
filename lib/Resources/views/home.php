<?
namespace Destiny;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Session;
use Destiny\Common\Config;
use Destiny\User\UserRole;
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
	<?php include Tpl::file('seg/top.php') ?>
	
	<?if(!Session::hasRole(UserRole::SUBSCRIBER) && (!isset($_COOKIE['alert-dismissed-subscription-cta']) || $_COOKIE['alert-dismissed-subscription-cta'] != true)):?>
	<div class="container">
		<div id="subscription-cta" class="alert alert-info" style="margin:10px 0 0 0;">
			<button type="button" class="close persist" data-dismiss="alert">&times;</button>
			<h4>Subscriptions now available!</h4>
			<?php if(Session::hasRole(UserRole::USER)): ?>
			<div><a href="/subscribe"><i class="icon-bobross" title="There are no limits here!"></i> Want to contribute?</a> Well now you can! Become the owner of your own Destiny subscription. <a target="_blank" href="http://www.reddit.com/r/Destiny/comments/1hn15x/new_subscription_system_launched/">Feedback and FAQ</a></div>
			<?php else: ?>
			<div><a href="/login"><i class="icon-bobross" title="There are no limits here!"></i> Want to contribute?</a> Well now you can! Create an account and become the owner of your own Destiny subscription. <a target="_blank" href="http://www.reddit.com/r/Destiny/comments/1hn15x/new_subscription_system_launched/">Feedback and FAQ</a></div>
			<?php endif; ?>
		</div>
	</div>
	<?php endif; ?>
	
	<?php include Tpl::file('seg/panel.twitch.php') ?>
	<?php include Tpl::file('seg/panel.lol.php') ?>
	<?php include Tpl::file('seg/panel.videos.php') ?>
	<?php include Tpl::file('seg/panel.music.php') ?>
	<?php include Tpl::file('seg/panel.calendar.php') ?>
	<?php include Tpl::file('seg/panel.ads.php') ?>
	<?php include Tpl::file('seg/foot.php') ?>
	<?php include Tpl::file('seg/commonbottom.php') ?>
</body>
</html>