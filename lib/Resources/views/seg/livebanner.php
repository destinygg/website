<?php 
namespace Destiny;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Utils\Date;
?>
<section id="live-banner-view" class="container" <?= (empty($model->streamInfo['stream'])) ? 'style="display: none"' : '' ?>>
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