<? 
namespace Destiny; 
use Destiny\Common\User\UserRole;
use Destiny\Common\Session; 
?>
<div id="main-nav" class="navbar navbar-inverse ">
	<div class="navbar-inner">
		<div class="container">
			<div class="nav-collapse collapse">
				<a class="brand pull-left" href="/">Destiny.gg</a>
				<ul id="top-left-nav" class="nav">
					<li class="home"><a title="Home Page" href="/"><i class="icon-home icon-white subtle"></i></a></li>
					<li><a title="Blog @ destiny.gg" href="http://blog.destiny.gg">Blog</a></li>
					<li><a title="twitter.com" href="https://twitter.com/Steven_Bonnell/">Twitter</a></li>
					<li><a title="youtube.com" href="http://www.youtube.com/user/Destiny">Youtube</a></li>
					<li><a title="reddit.com" href="http://www.reddit.com/r/Destiny/">Reddit</a></li>
					<li><a title="facebook.com" href="https://www.facebook.com/Steven.Bonnell.II">Facebook</a></li>
					<?if(!Session::hasRole(UserRole::SUBSCRIBER)):?>
					<li class="divider-vertical"></li>
					<li class="subscribe"><a href="/subscribe" rel="subscribe" title="Get your own destiny.gg subscription"><span>Subscribe</span></a></li>
					<?php endif; ?>
					<?if(Session::hasRole(UserRole::SUBSCRIBER)):?>
					<li class="divider-vertical"></li>
					<li class="subscribed"><a href="/subscribe" rel="subscribe" title="You have an active subscription!"><span>Subscribed</span></a></li>
					<?php endif; ?>
				</ul>
				<ul class="nav pull-right">
					<li class="bigscreen"><a title="So. Much. Girth." href="/bigscreen" rel="bigscreen"><i class="icon-bigscreen"></i> Big screen</a></li>
					<li class="divider-vertical"></li>
					<?php if(Session::hasRole(UserRole::USER)): ?>
					<li><a href="/profile" rel="profile">Profile</a></li>
					<li><a href="#" rel="signout" title="Sign out"><i class="icon-off icon-white subtle"></i></a></li>
					<?php else:?>
					<li><a href="/login" rel="login">Sign In</a></li>
					<?php endif; ?>
				</ul>
			</div>
		</div>
	</div>
</div>
