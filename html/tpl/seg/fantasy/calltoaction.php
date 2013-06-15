<?namespace Destiny;?>
<section class="container">
	<h1 class="page-title">Don't have a fantasy team? <small>make one...</small></h1>
	<hr size="1">
	<p>
		Join the Destiny fantasy league. Play for free. Play for fun.
		<br>Login, create a team, challenge a friend and watch the stream to earn points.
	</p>
	<p>
		<a class="btn btn-primary pull-left" rel="twitchlogin" data-request-perms="<?=Config::$a['twitch']['request_perms']?>" data-redirect-uri="<?=urlencode(Config::$a['twitch']['redirect_uri'])?>" data-client-id="<?=Config::$a['twitch']['client_id']?>">Join Now!</a>
		<br><br>
	</p>
</section>