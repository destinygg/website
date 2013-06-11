<?

namespace Destiny;

use Destiny\Utils\Tpl;
use Destiny\Utils\Date;

if (! empty ( $model->events ) && ! empty ( $model->articles )) :
	?>
<section class="container">
	<div class="content content-dark content-split clearfix">

		<div id="stream-schedule" class="stream">
			<h3 class="title">
				<span>Schedule</span>
				<a class="google-calendar-link" title="Google Calendar" href="/schedule"><i class="icon-calendar icon-white subtle"></i> destiny.gg</a>
			</h3>
			<div class="entries">
				<?foreach ($model->events['data']['items'] as $event):?>
				<?$date = new \DateTime($event['when'][0]['start']); $time = $date->getTimestamp();?>
				<div class="media">
					<div class="media-body">
						<div class="clearfix">
							<div class="media-heading">
								<?=Tpl::out($event['title'])?> - <span style="color: #777;"><?=Tpl::out($event['details'])?></span>
							</div>
							<time data-moment="true" datetime="<?=$date->format(Date::FORMAT)?>"><?=$date->format(Date::FORMAT)?></time>
						</div>
					</div>
				</div>
				<?endforeach;?>
			</div>
		</div>

		<div id="stream-blog" class="stream">
			<h3 class="title">
				<span>Blog</span> <a href="http://www.destiny.gg/n/">destiny.gg</a>
			</h3>
			<div class="entries">
				<?foreach ($model->articles as $article):?>
				<div class="media">
					<div class="media-body">
						<div class="media-heading">
							<a href="<?=$article['permalink']?>"><?=$article['title']?></a>
						</div>
						<div>
							<?foreach($article['categories'] as $categories):?>
							<span><small>Posted in</small> <?=Tpl::out($categories['title'])?></span>
							<?endforeach;?>
						</div>
						<time datetime="<?=$article['date']?>" pubdate><?=Date::getDateTime($article['date'], Date::STRING_DATE_FORMAT)?></time>
					</div>
				</div>
				<?endforeach;?>
			</div>
		</div>

	</div>
</section>
<?endif;?>