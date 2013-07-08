<?php
use Destiny\Utils\Tpl;
use Destiny\Config;
?>	
<script src="<?=Config::cdn()?>/vendor/js/jquery-1.10.1.min.js"></script>
<script src="<?=Config::cdn()?>/vendor/js/bootstrap.js"></script>
<script src="<?=Config::cdn()?>/vendor/js/moment.js"></script>
<script src="<?=Config::cdn()?>/vendor/js/jquery.cookie.js"></script>
<?php if(is_file(_STATICDIR .'/web/js/destiny.min.js')):?>
<script src="<?=Config::cdnv()?>/web/js/destiny.min.js"></script>
<?php else: ?>
<script src="<?=Config::cdnv()?>/web/js/utils.js"></script>
<script src="<?=Config::cdnv()?>/web/js/destiny.js"></script>
<script src="<?=Config::cdnv()?>/web/js/feed.js"></script>
<script src="<?=Config::cdnv()?>/web/js/profile.js"></script>
<script src="<?=Config::cdnv()?>/web/js/twitch.js"></script>
<script src="<?=Config::cdnv()?>/web/js/teambar.js"></script>
<script src="<?=Config::cdnv()?>/web/js/teamcreator.js"></script>
<script src="<?=Config::cdnv()?>/web/js/challenger.js"></script>
<script src="<?=Config::cdnv()?>/web/js/ui.js"></script>
<?php endif; ?>
<script>destiny.init({cdn:'<?=Config::cdn()?>'});</script>