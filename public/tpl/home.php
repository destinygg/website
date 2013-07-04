<?
namespace Destiny;
use Destiny\Utils\Date;
use Destiny\Utils\Http;
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
<?include'./tpl/seg/opengraph.php'?>
<?include'./tpl/seg/commontop.php'?>
<?include'./tpl/seg/google.tracker.php'?>
</head>
<body id="home">
	<?include'./tpl/seg/top.php'?>
	
	<?if(!Session::hasRole(\Destiny\UserRole::SUBSCRIBER)):?>
	<section class="container">
		<div id="subscription-cta" class="alert alert-info" style="margin:0;">
			<h4>Subscriptions now available!</h4>
			<?php if(Session::hasRole(\Destiny\UserRole::USER)): ?>
			<div><a href="/subscribe"><i class="icon-bobross" title="There are no limits here!"></i> Want to contribute?</a> well now you can! become an owner of your own Destiny subscription.</div>
			<?php else: ?>
			<div><a href="/login"><i class="icon-bobross" title="There are no limits here!"></i> Want to contribute?</a> well now you can! create an account and become an owner of your own Destiny subscription.</div>
			<?php endif; ?>
		</div>
	</section>
	<?php endif; ?>
	
	<?if(Session::hasRole(\Destiny\UserRole::USER) && Session::hasFeature(\Destiny\UserFeature::STICKY_TEAMBAR)):?>
	<?include'./tpl/seg/fantasy/teambar.php'?>
	<?include'./tpl/seg/fantasy/teammaker.php'?>
	<?endif;?>
	
	<?include'./tpl/seg/panel.twitch.php'?>
	<?include'./tpl/seg/panel.lol.php'?>
	<?include'./tpl/seg/panel.videos.php'?>
	<?include'./tpl/seg/panel.music.php'?>
	<?include'./tpl/seg/panel.calendar.php'?>
	<?include'./tpl/seg/panel.ads.php'?>
	<?include'./tpl/seg/foot.php'?>
	<?include'./tpl/seg/commonbottom.php'?>
</body>
</html>