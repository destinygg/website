<?php
use Destiny\Common\Config;
?>
<link rel="shortcut icon" href="<?=Config::cdn()?>/favicon.png">
<link href="<?=Config::cdn()?>/vendor/css/bootstrap.min.css" rel="stylesheet" media="screen">
<?php if(Config::$a['useMinifiedFiles'] && is_file(_STATICDIR .'/web/css/style.min.css')):?>
<link href="<?=Config::cdnv()?>/web/css/style.min.css" rel="stylesheet" media="screen">
<?php else: ?>
<link href="<?=Config::cdnv()?>/web/css/style.css" rel="stylesheet" media="screen">
<link href="<?=Config::cdnv()?>/web/css/flags.css" rel="stylesheet" media="screen">
<link href="<?=Config::cdnv()?>/web/css/fantasy.css" rel="stylesheet" media="screen">
<link href="<?=Config::cdnv()?>/web/css/teammaker.css" rel="stylesheet" media="screen">
<?php endif; ?>