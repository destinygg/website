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
    <link rel="shortcut icon" href="/favicon.ico?v3">
