<?
namespace Destiny;
use Destiny\Common\Config;
use Destiny\Common\Utils\Tpl;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($model->title)?></title>
<meta charset="utf-8">
<?php include Tpl::file('seg/opengraph.php') ?>
<?php include Tpl::file('seg/commontop.php') ?>
<?php include Tpl::file('seg/google.tracker.php') ?>
<link href="<?=Config::cdnv()?>/chat/css/emoticons.css" rel="stylesheet" media="screen">
<style>
.content {
	background-color: #080808;
}
.emoticons {
	margin: 30px 0;
}
.emote {
	height: 85px;
	width: 20%;
	float: left;
}
.emote > div {
	margin: 10px;
}
.chat-emote {
	top: auto;
	position: static;
	display: block;
	margin: 0 auto !important;
}
.emote-label {
	font-size: 12px;
	text-align: center;
	color: rgba(255,255,255,0.7);
	text-overflow: ellipsis;
	overflow: hidden;
	margin-top: 5px;
	line-height: 30px;
	display: block;
}
.emote-label:hover {
	color: white;
}
</style>
</head>
<body id="emoticons">
	<?php include Tpl::file('seg/top.php') ?>

	<section class="container">
		<h1 class="title">Emoticons</h1>
		<div class="content content-dark">
			<div class="emoticons clearfix">
				<?php foreach( $model->emoticons as $trigger ): ?>
				<div class="emote">
					<div>
						<div class="chat-emote chat-emote-<?=$trigger?>" title="<?=$trigger?>"></div>
						<a class="emote-label"><?=Tpl::out($trigger)?></a>
					</div>
				</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<?php include Tpl::file('seg/foot.php') ?>
	<?php include Tpl::file('seg/commonbottom.php') ?>
</body>
</html>