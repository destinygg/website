<? namespace Destiny; ?>
<div class="navbar navbar-inverse navbar-fixed-top">
	<div class="navbar-inner">
		<div class="container">
			<div class="nav-collapse collapse">
				<ul id="top-left-nav" class="nav">
					<li><a title="Home Page" href="/"><i class="icon-home icon-white subtle"></i></a></li>
					<li><a title="Blog @ destiny.gg" href="<?=Config::$a['nav']['blog']?>">Blog</a></li>
					<li><a title="Schedule @ destiny.gg" href="<?=Config::$a['nav']['schedule']?>">Schedule</a></li>
					<li><a title="twitter.com" href="<?=Config::$a['nav']['twitter']?>">Twitter</a></li>
					<li><a title="youtube.com" href="<?=Config::$a['nav']['youtube']?>">Youtube</a></li>
					<li><a title="reddit.com" href="<?=Config::$a['nav']['reddit']?>">Reddit</a></li>
					<li><a title="facebook.com" href="<?=Config::$a['nav']['facebook']?>">Facebook</a></li>
				</ul>
				<?if(Session::getAuthorized()):?>
				<ul id="top-right-nav" class="nav pull-right">
					<?if(!Session::hasRole('subscriber')):?>
					<li><a title="twitch.tv" href="/subscribe">Subscribe <i title="There are no limits here" class="icon-bobross"></i></a></li>
					<?endif;?>
					<li><a href="/league">League <i class="icon-globe icon-white subtle"></i></a></li>
					<li class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#"><span id="usernamenav"><?if(Session::hasRole('subscriber')):?><i title="Subscribed" class="icon-bobross"></i> <?endif;?>Account</span> <b class="caret"></b></a>
						<ul class="dropdown-menu">
							<li><a href="/profile"><i class="icon-cog"></i> Profile</i></a></li>
							<?if(Session::hasRole('admin')):?>
							<li><a href="/admin"><i class="icon-wrench"></i> <strong style="color:red;">Admin</strong></i></a></li>
							<?endif;?>
							<li class="divider"></li>
							<li><a href="#" rel="signout"><i class="icon-off"></i> Sign out</a></li>
						</ul>
					</li>
				</ul>
				<?else:?>
				<ul id="top-right-nav" class="nav pull-right">
					<?if(!Session::hasRole('subscriber')):?>
					<li><a title="twitch.tv" href="http://www.twitch.tv/<?=Config::$a['twitch']['user']?>/subscribe">Subscribe <i title="There are no limits here" class="icon-bobross"></i></a></li>
					<?endif;?>
					<li><a href="/league">League <i class="icon-globe icon-white subtle"></i></a></li>
					<li><a title="Login with your twitch account" href="#" rel="twitchlogin" data-request-perms="<?=Config::$a['twitch']['request_perms']?>" data-redirect-uri="<?=urlencode(Config::$a['twitch']['redirect_uri'])?>" data-client-id="<?=Config::$a['twitch']['client_id']?>">Login <i class="icon-user icon-white subtle"></i></a></li>
				</ul>
				<?endif;?>
			</div>
		</div>
	</div>
</div>

<section id="header-band">
	<div class="container">
		<header class="hero-unit" id="overview">
			<div class="hero-snippet">
				<h1><?=Config::$a['meta']['title']?></h1>
			</div>
			<div id="destiny-illustration"></div>
		</header>
	</div>
</section>