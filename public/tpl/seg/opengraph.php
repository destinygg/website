<?php 
use Destiny\Common\Utils\Http;
use Destiny\Common\Config;
?>
<meta property="og:site_name" content="<?=Config::$a['meta']['shortName']?>" />
<meta property="og:title" content="<?=Config::$a['meta']['title']?>" />
<meta property="og:description"	content="<?=Config::$a['meta']['description']?>" />
<meta property="og:image" content="<?=Config::cdn()?>/web/img/destinyspash600x600.png" />
<meta property="og:url" content="<?=Http::getBaseUrl()?>" />
<meta property="og:type" content="video.other" />
<meta property="og:video" content="<?=Config::$a['meta']['video']?>" />
<meta property="og:video:secure_url" content="<?=Config::$a['meta']['videoSecureUrl']?>" />
<meta property="og:video:type" content="application/x-shockwave-flash" />
<meta property="og:video:height" content="259" />
<meta property="og:video:width" content="398" />
