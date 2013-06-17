<?
namespace Destiny;
use Destiny\Utils\Tpl;
use Destiny\Utils\Date;
?>
<?if((bool) Config::$a['blocks']['videos']):?>
<section class="container">
	<div class="content content-dark content-split clearfix">
		<div id="youtube" class="stream">
			<h3 class="title clearfix">
				<span>Videos</span> <a href="http://www.youtube.com/user/<?=Config::$a['youtube']['user']?>/videos?view=0" class="youtube-title">youtube.com</a>
			</h3>
			<ul class="thumbnails">
			<?if(isset($model->playlist['items']) && !empty($model->playlist['items'])):?>
			<?foreach($model->playlist['items'] as $vid ):?>
				<?$title = Tpl::out($vid['snippet']['title'])?>
				<li>
					<div class="thumbnail" data-placement="bottom" rel="tooltip" title="<?=$title?>">
						<a
							href="http://www.youtube.com/watch?v=<?=$vid['snippet']['resourceId']['videoId']?>">
							<img alt="<?=$title?>" src="<?=Config::cdn()?>/img/320x240.gif"
							data-src="https://i.ytimg.com/vi/<?=$vid['snippet']['resourceId']['videoId']?>/default.jpg" />
						</a>
					</div>
				</li>
			<?endforeach;?>
			<?else:?>
				<p class="loading">Loading videos ...</p>
			<?endif;?>
			</ul>
		</div>
		<div id="broadcasts" class="stream">
			<h3 class="title clearfix">
				<span>Broadcasts</span> <a href="http://www.twitch.tv/<?=Config::$a['twitch']['user']?>/videos?kind=past_broadcasts" class="twitch-title">twitch.tv</a>
			</h3>
			<ul class="thumbnails">
			<?if(isset($model->broadcasts) && !empty($model->broadcasts['videos'])):?>
			<?foreach( $model->broadcasts['videos'] as $broadcast ):?>
				<?$time = Date::getElapsedTime(Date::getDateTime($broadcast['recorded_at']))?>
				<li>
					<div class="thumbnail" data-placement="bottom" rel="tooltip" title="<?=$time?>">
						<a href="<?=$broadcast['url']?>"> <img alt="<?=$time?>" src="<?=Config::cdn()?>/img/320x240.gif" data-src="<?=$broadcast['preview']?>" /></a>
					</div>
				</li>
			<?endforeach;?>
			<?else:?>
				<p class="loading">Loading broadcasts ...</p>
			<?endif;?>
			</ul>
		</div>
	</div>
</section>
<?endif;?>