<?php
use Destiny\Common\Config;
?>	
<script src="<?=Config::cdn()?>/vendor/jquery/jquery-1.10.2.min.js"></script>
<script src="<?=Config::cdn()?>/vendor/jquery.cookie/jquery.cookie.js"></script>
<script src="<?=Config::cdn()?>/vendor/moment/moment-2.4.0.min.js"></script>
<script src="<?=Config::cdn()?>/vendor/bootstrap/bootstrap.js"></script>
<script src="<?=Config::cdnv()?>/web/js/destiny.min.js"></script>
<script>destiny.init({cdn:'<?=Config::cdn()?>'});</script>
