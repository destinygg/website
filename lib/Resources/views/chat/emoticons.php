<?php
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
</head>
<body id="emoticons">

	<?php include Tpl::file('seg/top.php') ?>
	<?php include Tpl::file('seg/headerband.php') ?>

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