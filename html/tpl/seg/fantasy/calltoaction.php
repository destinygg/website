<?namespace Destiny;?>
<section class="container">
	<div id="fantasycalltoaction" class="content content-dark">
		<h3>Destiny Fantasy League</h3>
		<div>
			<p class="pull-left">
				Login, create a team, challenge a friend<br /> and watch the stream
				to earn points. <br />Play for free. Play for fun.<br />
			</p>
			<a class="btn btn-large btn-primary pull-left" rel="twitchlogin" data-request-perms="<?=Config::$a['twitch']['request_perms']?>" data-redirect-uri="<?=urlencode(Config::$a['twitch']['redirect_uri'])?>" data-client-id="<?=Config::$a['twitch']['client_id']?>">Join Now!</a></div>
	</div>
</section>