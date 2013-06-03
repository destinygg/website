<?
namespace Destiny;
use Destiny\Utils\String;
?>
<?$rowLimit = 20;?>
<ul class="nav nav-tabs" style="margin-bottom:0; border-bottom:0;">
	<li class="active"><a href="#LogInfo" data-toggle="tab">Logs</a></li>
	<li><a href="#Errors" data-toggle="tab">Errors</a></li>
	<li><a href="#Aggregate" data-toggle="tab">Aggregate</a></li>
	<li><a href="#Recentgames" data-toggle="tab">Recent games</a></li>
	<li><a href="#Ingame" data-toggle="tab">In game</a></li>
	<li><a href="#Sessiongc" data-toggle="tab">Session GC</a></li>
	<li><a href="#Freechamps" data-toggle="tab">Free Champs</a></li>
	<li><a href="#Subscriptions" data-toggle="tab">Subscriptions</a></li>
</ul>
<div class="content content-dark clearfix" style="border-top-left-radius:0; border-top-right-radius:0;">
	<div style="width:100%;" class="clearfix stream">
		<div class="tab-content">
		
			<div class="tab-pane active clearfix" id="LogInfo">
				<div style="margin:20px;">
					<p>Error logs show sensitive information</p>
				</div>
			</div>
		
			<div class="tab-pane clearfix" id="Errors">
				<div style="margin:20px;">
					<p><a href="/admin/" class="btn btn-inverse"><i class="icon-refresh icon-white"></i> Refresh</a></p>
				</div>
				<pre class="logtext" style="margin:20px;"><?= String::fileTail(Config::$a ['log'] ['path'] . strtolower ( 'error' ) . '.log', $rowLimit);?></pre>
			</div>
			
			<div class="tab-pane clearfix" id="Aggregate">
				<div style="margin:20px;">
					<p><button rel="Aggregate" class="btn btn-cron-action btn-primary"><i class="icon-refresh icon-white"></i> Aggregate</button></p>
				</div>
				<pre class="logtext" style="margin:20px;"><?= String::fileTail(Config::$a ['log'] ['path'] . strtolower ( 'Aggregate' ) . '.log', $rowLimit);?></pre>
			</div>
			
			<div class="tab-pane clearfix" id="Recentgames">
				<div style="margin:20px;">
					<p><button rel="Recentgames" class="btn btn-cron-action btn-primary"><i class="icon-refresh icon-white"></i> Check recent games</button></p>
				</div>
				<pre class="logtext" style="margin:20px;"><?= String::fileTail(Config::$a ['log'] ['path'] . strtolower ( 'Recentgames' ) . '.log', $rowLimit);?></pre>
			</div>
			
			<div class="tab-pane clearfix" id="Ingame">
				<div style="margin:20px;">
					<p><button rel="Ingame" class="btn btn-cron-action btn-primary"><i class="icon-refresh icon-white"></i> Check ingame progress</button></p>
				</div>
				<pre class="logtext" style="margin:20px;"><?= String::fileTail(Config::$a ['log'] ['path'] . strtolower ( 'Ingame' ) . '.log', $rowLimit);?></pre>
			</div>
			
			<div class="tab-pane clearfix" id="Sessiongc">
				<div style="margin:20px;">
					<p><button rel="Sessiongc" class="btn btn-cron-action btn-primary"><i class="icon-refresh icon-white"></i> Clear session expiration</button></p>
				</div>
				<pre class="logtext" style="margin:20px;"><?= String::fileTail(Config::$a ['log'] ['path'] . strtolower ( 'Sessiongc' ) . '.log', $rowLimit);?></pre>
			</div>
			
			<div class="tab-pane clearfix" id="Freechamps">
				<div style="margin:20px;">
					<p><button rel="Freechamps" class="btn btn-cron-action btn-danger"><i class="icon-refresh icon-white"></i> Rotate free champions</button></p>
				</div>
				<pre class="logtext" style="margin:20px;"><?= String::fileTail(Config::$a ['log'] ['path'] . strtolower ( 'Freechamps' ) . '.log', $rowLimit);?></pre>
			</div>
			
			<div class="tab-pane clearfix" id="Subscriptions">
				<div style="margin:20px;">
					<p><button rel="Subscriptions" class="btn btn-cron-action btn-danger"><i class="icon-refresh icon-white"></i> Check twitch subscriptions</button></p>
				</div>
				<pre class="logtext" style="margin:20px;"><?= String::fileTail(Config::$a ['log'] ['path'] . strtolower ( 'Subscriptions' ) . '.log', $rowLimit);?></pre>
			</div>
			
		</div>
		
	</div>
</div>