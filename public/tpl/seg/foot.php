<?php
namespace Destiny; 
?>
<section id="footer" class="container">
	<footer class="clearfix">
		<p class="pull-left">
			<span><?=Config::$a['meta']['shortName']?> &copy; <?=date('Y')?> </span>
			<a><i title="There are no limits here" class="icon-bobross"></i></a>
			<?if(Session::hasRole(\Destiny\UserRole::ADMIN)):?>
			<span>- <a href="/admin" rel="admin">Admin</i></a></span>
			<?endif;?>
			<br>www.reddit.com<a title="www.reddit.com" href="http://www.reddit.com/r/Destiny">/r/Destiny</a>
		</p>
		<p class="pull-right" style="text-align: right;">
			Illustration feature by <a title="Many thanks!" href="http://guilhemsalines.blogspot.com/">@elevencyan</a> 
			<br>League of Legends images owned by Riot inc.
		</p>
	</footer>
</section>