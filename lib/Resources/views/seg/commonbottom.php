<?php
use Destiny\Common\Config;
?>
<script src="<?=Config::cdnvf('1.1.0')?>/vendor/libs.min.js"></script>
<script src="<?=Config::cdnv()?>/vendor/jquery.validate/jquery.validate.min.js"></script>
<script src="<?=Config::cdnv()?>/web/js/destiny.min.js"></script>
<script>destiny.init({cdn:'<?=Config::cdn()?>'});</script>
