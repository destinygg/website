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
				<?include'./tpl/seg/fantasy/serverstatus.php'?>
			</li>
		</ul>
	</div>
</div>