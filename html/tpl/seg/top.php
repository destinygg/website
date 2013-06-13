<? namespace Destiny; ?>
<div class="navbar navbar-inverse navbar-fixed-top">
	<div class="navbar-inner">
		<div class="container">
			<div class="nav-collapse collapse">
				<ul id="top-left-nav" class="nav">
					<li><a title="Home Page" href="/"><i class="icon-home icon-white subtle"></i></a></li>
					<li><a title="Blog @ destiny.gg" href="<?=Config::$a['nav']['blog']?>">Blog</a></li>
					<li><a title="twitter.com" href="<?=Config::$a['nav']['twitter']?>">Twitter</a></li>
					<li><a title="youtube.com" href="<?=Config::$a['nav']['youtube']?>">Youtube</a></li>
					<li><a title="reddit.com" href="<?=Config::$a['nav']['reddit']?>">Reddit</a></li>
					<li><a title="facebook.com" href="<?=Config::$a['nav']['facebook']?>">Facebook</a></li>
					<li class="divider-vertical"></li>
					<li><a href="/league" rel="league">Fantasy League</a></li>
				</ul>
				<?if(Session::authorized()):?>
				<ul class="nav pull-right">
					<?if(Session::hasRole('admin')):?>
					<li><a href="/admin" rel="admin">Admin</i></a></li>
					<?endif;?>
					<li><a href="/profile" rel="profile">Profile</span></a></li>
					<li><a href="#" rel="signout" title="Sign out"><i class="icon-off icon-white subtle"></i></a></li>
				</ul>
				<?else:?>
				<form class="navbar-form pull-right">
					<button title="Login with your twitch account" type="button" rel="twitchlogin" class="btn btn-inverse" data-request-perms="<?=Config::$a['twitch']['request_perms']?>" data-redirect-uri="<?=urlencode(Config::$a['twitch']['redirect_uri'])?>" data-client-id="<?=Config::$a['twitch']['client_id']?>"><i class="icon-check icon-white"></i> Login</button>
				</form>
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