<?

namespace Destiny;

use Destiny\Utils\Tpl;

?>
<section class="container" id="scheduleCalendarTimezone">
	<div class="content clearfix">
		<div style="padding: 15px; margin: 0;">
			<div>
				<strong>Note!</strong> The calendar is showing times with a time zone offset of UTC
				<button title="Change" class="btn btn-mini timezone">UTC</button>
			</div>
			<form id="scheduleCalendarForm" style="padding-top: 10px; margin: 0; display: none;">
				<select style="margin: 0;" class="timezone">
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
	</div>
</section>

<section class="container">
	<div id="scheduleCalendar" class="content content-embed clearfix">
		<iframe data-src="<?="https://www.google.com/calendar/embed?src=". urlencode(Config::$a['google']['calendar']['id'])?>" style="width: 100%; height: 640px; border: 0;"></iframe>
	</div>
</section>