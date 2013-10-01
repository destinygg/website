<? 
namespace Destiny; 
use Destiny\Common\User\UserRole;
use Destiny\Common\Session; 
use Destiny\Common\Config;
?>
<div id="main-nav" class="navbar navbar-inverse navbar-fixed-top">
	<div class="navbar-inner">
		<div class="container">
			<div class="nav-collapse collapse">
				<ul id="top-left-nav" class="nav">
					<li><a title="Home Page" href="/"><i class="icon-home icon-white subtle"></i></a></li>
					<li><a title="Blog @ destiny.gg" href="http://blog.destiny.gg">Blog</a></li>
					<li><a title="twitter.com" href="https://twitter.com/Steven_Bonnell/">Twitter</a></li>
					<li><a title="youtube.com" href="http://www.youtube.com/user/Destiny">Youtube</a></li>
					<li><a title="reddit.com" href="http://www.reddit.com/r/Destiny/">Reddit</a></li>
					<li><a title="facebook.com" href="https://www.facebook.com/Steven.Bonnell.II">Facebook</a></li>
					<?if(!Session::hasRole(UserRole::SUBSCRIBER)):?>
					<li class="divider-vertical"></li>
					<li class="subscribe"><a href="/subscribe" rel="subscribe">Subscribe</a></li>
					<?php endif; ?>
					<?if(Session::hasRole(UserRole::SUBSCRIBER)):?>
					<li class="divider-vertical"></li>
					<li class="subscribe"><a href="/subscribe" rel="subscribe" title="You have an active subscription!">Subscribed</a></li>
					<?php endif; ?>
				</ul>
				<ul class="nav pull-right">
					<?if(!Session::hasRole(UserRole::USER)):?>
					<li><a href="/login" rel="login">Login - Register</a></li>
					<?endif;?>
					<?if(Session::hasRole(UserRole::USER)):?>
					<li><a href="#" rel="signout" title="Sign out"><i class="icon-off icon-white subtle"></i></a></li>
					<?php endif; ?>
				</ul>
				<ul class="nav pull-right">
					<?if(Session::hasRole(UserRole::USER)):?>
					<li><a href="/profile" rel="profile">Profile</a></li>
					<?endif;?>
				</ul>
			</div>
		</div>
	</div>
</div>

<section id="header-band">
	<div class="container">
		<header class="hero-unit" id="overview">
			<h1><?=Config::$a['meta']['title']?></h1>
			<div id="destiny-illustration"></div>
		</header>
	</div>
</section>
