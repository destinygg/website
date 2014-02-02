<?
namespace Destiny;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Config;
use Destiny\Common\Utils\Date;
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
	<?php include Tpl::file('seg/headerband.php') ?>
	
	<section id="live-banner-view" class="container" <?php (empty($model->streamInfo['stream'])) ? 'style="display: none"' : '' ?>>
		<div class="content">
			<div id="live-banner">
				<div id="live-preview">
					<a href="/bigscreen" title="<?=Tpl::out($model->streamInfo['status'])?>">
						<img src="<?=Tpl::out((!empty($model->streamInfo['stream'])) ? $model->streamInfo['stream']['preview']['medium'] : '')?>" />
					</a>
				</div>
				<div id="live-info-wrap">
					<div>
						<h1 title="<?=Tpl::out($model->streamInfo['status'])?>"><?=Tpl::out($model->streamInfo['status'])?></h1>
						<div id="live-info">
							Currently playing <strong class="live-info-game"><?=Tpl::out($model->streamInfo['game'])?></strong><br />
							Started <span class="live-info-updated"><?=(!empty($model->streamInfo['stream'])) ? Date::getElapsedTime(Date::getDateTime($model->streamInfo['stream']['channel']['updated_at'])) : ''?></span><br />
							~<span class="live-info-viewers"><?=Tpl::out((!empty($model->streamInfo['stream'])) ? $model->streamInfo['stream']['viewers'] : 0)?></span> viewers
						</div>
						<a id="live-link" href="/bigscreen" class="btn btn-primary btn-large"><i style="margin-top: 2px;" class="icon-bigscreen animated"></i> Watch the live stream</a>
					</div>
				</div>
			</div>
		</div>
	</section>
	
	<?php include Tpl::file('seg/panel.videos.php') ?>
	<?php include Tpl::file('seg/panel.music.php') ?>
	<?php include Tpl::file('seg/panel.calendar.php') ?>
	<?php include Tpl::file('seg/panel.ads.php') ?>
	<?php include Tpl::file('seg/foot.php') ?>
	<?php include Tpl::file('seg/commonbottom.php') ?>
</body>
</html>