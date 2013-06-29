<?php
use Destiny\Utils\Tpl;
use Destiny\Config;
?>
<link rel="shortcut icon" href="<?=Config::cdn()?>/favicon.png">
<link href="<?=Config::cdn()?>/vendor/css/bootstrap.min.css" rel="stylesheet" media="screen">
<?php if(is_file(_STATICDIR .'/web/css/style.'.Config::version().'.css')):?>
<link href="<?=Config::cdn()?>/web/css/style.<?=Config::version()?>.css" rel="stylesheet" media="screen">
<?php else: ?>
<link href="<?=Config::cdn()?>/web/css/style.css" rel="stylesheet" media="screen">
<link href="<?=Config::cdn()?>/web/css/flags.css" rel="stylesheet" media="screen">
<link href="<?=Config::cdn()?>/web/css/fantasy.css" rel="stylesheet" media="screen">
<link href="<?=Config::cdn()?>/web/css/teammaker.css" rel="stylesheet" media="screen">
<?php endif; ?>