<?namespace Destiny;?>
<section class="container">
	<div class="content clearfix">
		<h3 class="title" style="text-align: center; margin-top:10px; margin-bottom:10px; font-weight: normal;">
			<span>Login with your twich account</span><br />
			<a title="Connect with Twitch" rel="twitchlogin" data-request-perms="<?=Config::$a['twitch']['request_perms']?>" data-redirect-uri="<?=urlencode(Config::$a['twitch']['redirect_uri'])?>" data-client-id="<?=Config::$a['twitch']['client_id']?>" href="#" style="background: url(<?=Config::cdn()?>/img/twitchconnectdark.png) no-repeat center center; display: inline-block; width: 172px; height: 40px; text-indent:-999em;">Connect with Twitch</a>
		</h3>
	</div>
</section>