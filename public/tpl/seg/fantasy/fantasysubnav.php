<?php 
use Destiny\Common\Config;
?>
<div class="navbar navbar-inverse navbar-subnav">
	<div class="navbar-inner">
		<ul class="nav">
			<li><a href="/league" rel="games" title="Games">Games</a></li>
			<li><a href="/league/group" rel="group" title="Challenge group">Group</a></li>
			<li><a href="/league/invites" rel="invites" title="Challenge group invites">Invites<?=(count($model->invites) > 0)? ' <span class="badge badge-success">'. count($model->invites) .'</span>':''?></a></li>
			<li><a href="/league/help" rel="help" title="Help &amp; About">Scoring</a></li>
		</ul>
		<ul class="nav pull-right">
			<li id="serverStatus">
				<strong>Servers:</strong>
				<?if(is_array($model->leagueServers)):?>
				<?foreach ($model->leagueServers as $leagueServer):?>
				<?if(in_array(strtolower($leagueServer['server']), Config::$a['lol']['trackedRegions'])):?>
				<?if($leagueServer['status'] == 'OK'):?>
				<span class="online"><?=$leagueServer['server']?></span>
				<?endif;?>
				<?if($leagueServer['status'] == 'OFFLINE'):?>
				<span class="offline"><?=$leagueServer['server']?></span>
				<?endif;?>
				<?endif;?>
				<?endforeach;?>
				<?else:?>
				<span class="offline">NONE</span>
				<?endif;?>
			</li>
		</ul>
	</div>
</div>