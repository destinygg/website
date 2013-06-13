<?
namespace Destiny;
use Destiny\Utils\Tpl;
use Destiny\Utils\String;
?>
<div class="content content-dark clearfix">
	<div style="width: 100%;" class="clearfix stream">
		<h3 class="title">Logs</h3>
		<div class="tab-content" style="padding: 10px 20px 20px 20px; border-top: 1px solid #222;">
		
			<ul class="nav nav-pills">
				<li class="active"><a href="#LogInfo" data-toggle="tab">Details</a></li>
				<li><a href="#Events" data-toggle="tab">Events</a></li>
				<li><a href="#Cron" data-toggle="tab">Cron</a></li>
			</ul>

			<div class="tab-pane active clearfix" id="LogInfo">
				<p>Error logs show potentially sensitive information</p>
			</div>

			<div class="tab-pane clearfix" id="Events">
				<pre class="logtext"><?=Tpl::out(String::fileTail(Config::$a ['log'] ['path'] . 'events.log', 30))?></pre>
			</div>

			<div class="tab-pane clearfix" id="Cron">
				<pre class="logtext"><?=Tpl::out(String::fileTail(Config::$a ['log'] ['path'] . 'cron.log', 30))?></pre>
			</div>

		</div>

	</div>
</div>