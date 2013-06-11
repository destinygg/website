<?
namespace Destiny;
use Destiny\Utils\Tpl;
use Destiny\Utils\String;
?>
<ul class="nav nav-tabs" style="margin-bottom: 0; border-bottom: 0;">
	<li class="active"><a href="#LogInfo" data-toggle="tab">Details</a></li>
	<li><a href="#Events" data-toggle="tab">Events</a></li>
	<li><a href="#Cron" data-toggle="tab">Cron</a></li>
</ul>
<div class="content content-dark clearfix"
	style="border-top-left-radius: 0; border-top-right-radius: 0;">
	<div style="width: 100%;" class="clearfix stream">
		<div class="tab-content">

			<div class="tab-pane active clearfix" id="LogInfo">
				<div style="margin: 20px;">
					<p>Error logs show potentially sensitive information</p>
					<dl>
						<dt>Cron</dt>
						<dd><?=number_format(@filesize(Config::$a ['log'] ['path'] . 'cron.log')/1024,2)?> kbytes</dd>
						<dt>Events</dt>
						<dd><?=number_format(@filesize(Config::$a ['log'] ['path'] . 'events.log')/1024,2)?> kbytes</dd>
					</dl>
				</div>
			</div>

			<div class="tab-pane clearfix" id="Events">
				<pre class="logtext" style="margin: 20px;"><?=Tpl::out(String::fileTail(Config::$a ['log'] ['path'] . 'events.log', 100))?></pre>
			</div>

			<div class="tab-pane clearfix" id="Cron">
				<pre class="logtext" style="margin: 20px;"><?=Tpl::out(String::fileTail(Config::$a ['log'] ['path'] . 'cron.log', 100))?></pre>
			</div>

		</div>

	</div>
</div>