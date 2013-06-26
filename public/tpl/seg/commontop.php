<?php
use Destiny\Utils\Tpl;
use Destiny\Config;
?>
<link href="<?=Config::cdn()?>/css/vendor/bootstrap.min.css" rel="stylesheet" media="screen">
<?php if(Config::$a['compressed']):?>
<link href="<?=Config::cdn()?>/css/destiny.<?=Config::version()?>.css" rel="stylesheet" media="screen">
<?php else: ?>
<?=Tpl::eachResource('/css/*.css', function($file){ return sprintf('<link href="%s/css/%s" rel="stylesheet" media="screen">', Config::cdn (), basename ( $file )) . PHP_EOL; })?>
<?php endif; ?>
<link rel="shortcut icon" href="<?=Config::cdn()?>/favicon.png">