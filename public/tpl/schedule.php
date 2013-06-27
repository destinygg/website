<?php
namespace Destiny;
use Destiny\Utils\Tpl;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($model->title)?></title>
<meta charset="utf-8">
<?include'./tpl/seg/commontop.php'?>
<?include'./tpl/seg/google.tracker.php'?>
</head>
<body id="schedule">
	<?include'./tpl/seg/top.php'?>
	
	<section class="container" id="scheduleCalendarTimezone">
		<div class="content clearfix">
			<div>
				<strong>Note!</strong> The calendar is showing times with a time zone offset of UTC
				<span class="timezone"></span> <button class="btn btn-mini change-timezone">Change timezone</button>
			</div>
			<form id="scheduleCalendarForm" style="display: none;">
				<select class="timezone">
					<option value="">Select your time zone</option>
					<?foreach (Config::$a['regions'] as $name => $mask):?>
						<?$tzlist = \DateTimeZone::listIdentifiers($mask);?>
						<optgroup label="<?=Tpl::out($name)?>">
						<?foreach($tzlist as $tz):?>
							<option value="<?=Tpl::out($tz)?>"><?=Tpl::out($tz)?></option>
						<?endforeach;?>
						</optgroup>
					<?endforeach;?>
				</select>
			</form>
		</div>
	</section>
	
	<section class="container">
		<div id="scheduleCalendar" class="content content-embed clearfix">
			<iframe data-src="<?="https://www.google.com/calendar/embed?src=". urlencode(Config::$a['calendar'])?>" style="width: 100%; height: 640px; border: 0;"></iframe>
		</div>
	</section>
	
	<?include'./tpl/seg/panel.calendar.php'?>
	<?include'./tpl/seg/panel.videos.php'?>
	<?include'./tpl/seg/panel.ads.php'?>
	<?include'./tpl/seg/foot.php'?>
	<?include'./tpl/seg/commonbottom.php'?>
</body>
</html>