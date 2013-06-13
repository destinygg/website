<?
namespace Destiny;
$words = include 'words.php';
$word = $words [array_rand ( $words, 1 )];
if (preg_match ( '/^local/i', $_SERVER ['HTTP_HOST'] ) > 0) {
	$cdn = '//local.destiny.cdn';
} else {
	$cdn = '//cdn.destiny.gg';
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Error : Authentication required</title>
<link href="<?=$cdn?>/css/bootstrap.min.css" rel="stylesheet" media="screen">
<link href="<?=$cdn?>/errors/errors.css" rel="stylesheet" media="screen">
<link rel="shortcut icon" href="<?=$cdn?>/favicon.png">
<?include'ga.php'?>
</head>
<body class="error forbidden">

	<section id="header-band">
		<div class="container">
			<header class="hero-unit" id="overview">
				<div class="clearfix">
					<h1><strong><?=$word?>!</strong> Authentication required</h1>
					<p>Are you looking for the <strong>fantasy league?</strong>. <br />Click here to <a title="Login with your twitch account" href="#" rel="twitchlogin" data-request-perms="<?=Config::$a['twitch']['request_perms']?>" data-redirect-uri="<?=urlencode(Config::$a['twitch']['redirect_uri'])?>" data-client-id="<?=Config::$a['twitch']['client_id']?>"><i class="icon-user icon-white subtle"></i> Login</a></p>
				</div>
				<div id="destiny-illustration"></div>
			</header>
		</div>
	</section>

	<?include'foot.php'?>

	<script src="<?=$cdn?>/js/vendor/jquery-1.9.1.min.js"></script>
	<script src="<?=$cdn?>/js/vendor/bootstrap.js"></script>
	<script>
	// Twitch Connect button
	$('a[rel="twitchlogin"]').on('click', function(){
		var url = 'https://api.twitch.tv/kraken/oauth2/authorize?response_type=code&client_id='+$(this).data('client-id')+'&redirect_uri='+$(this).data('redirect-uri')+'&scope='+$(this).data('request-perms');
		window.location.href = url;
		return false;
	});
	</script>

</body>
</html>