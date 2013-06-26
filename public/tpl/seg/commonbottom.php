<?php
use Destiny\Utils\Tpl;
use Destiny\Config;
?>	
<script src="<?=Config::cdn()?>/js/vendor/jquery-1.9.1.min.js"></script>
<script src="<?=Config::cdn()?>/js/vendor/jquery.cookie.js"></script>
<script src="<?=Config::cdn()?>/js/vendor/bootstrap.js"></script>
<script src="<?=Config::cdn()?>/js/vendor/moment.js"></script>
<?php if(Config::$a['compressed']):?>
<script src="<?=Config::cdn()?>/js/destiny.<?=Config::version()?>.js"></script>
<?php else: ?>
<?=Tpl::eachResource('/js/*.js', function($file){ return sprintf('<script src="%s/js/%s"></script>', Config::cdn (), basename ( $file )) . PHP_EOL; })?>
<?php endif; ?>
<script>destiny.init({cdn:'<?=Config::cdn()?>'});</script>