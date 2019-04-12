<?php
use Destiny\Common\Config;
use Destiny\Common\Utils\Http;
?><meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" charset="utf-8">
    <meta property="og:site_name" content="<?=Config::$a ['meta'] ['shortName']?>" />
    <meta property="og:title" content="<?=Config::$a ['meta'] ['title']?>" />
    <meta property="og:description" content="<?=Config::$a['meta']['description']?>" />
    <meta property="og:image" content="<?=Config::$a['meta']['image']?>" />
    <meta property="og:url" content="<?=Http::getBaseUrl()?>" />
    <meta property="og:type" content="video.other" />
    <meta property="og:video" content="<?=Config::$a['meta']['video']?>" />
    <meta property="og:video:secure_url" content="<?=Config::$a['meta']['videoSecureUrl']?>" />
    <meta property="og:video:type" content="application/x-shockwave-flash" />
    <meta property="og:video:height" content="260" />
    <meta property="og:video:width" content="340" />
    <meta name="google-play-app" content="app-id=<?=Config::$a['android']['app']?>">
    <meta name="google-site-verification" content="<?=Config::$a['google-verification']?>">
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <link rel="dns-prefetch" href="//www.google-analytics.com">
    <link rel="preconnect" href="<?=Config::cdn()?>">
    <link rel="apple-touch-icon-precomposed" sizes="57x57" href="apple-touch-icon-57x57.png" />
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="apple-touch-icon-114x114.png" />
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="apple-touch-icon-72x72.png" />
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="apple-touch-icon-144x144.png" />
    <link rel="apple-touch-icon-precomposed" sizes="60x60" href="apple-touch-icon-60x60.png" />
    <link rel="apple-touch-icon-precomposed" sizes="120x120" href="apple-touch-icon-120x120.png" />
    <link rel="apple-touch-icon-precomposed" sizes="76x76" href="apple-touch-icon-76x76.png" />
    <link rel="apple-touch-icon-precomposed" sizes="152x152" href="apple-touch-icon-152x152.png" />
    <link rel="icon" type="image/png" href="favicon-196x196.png" sizes="196x196" />
    <link rel="icon" type="image/png" href="favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/png" href="favicon-32x32.png" sizes="32x32" />
    <link rel="icon" type="image/png" href="favicon-16x16.png" sizes="16x16" />
    <link rel="icon" type="image/png" href="favicon-128.png" sizes="128x128" />
    <link rel="shortcut icon" href="/favicon.ico?v4">
