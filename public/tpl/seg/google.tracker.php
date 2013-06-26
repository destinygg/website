<?php 
use Destiny\Config; 
?>
<?if(!empty(Config::$a['analytics']['account']) && !empty(Config::$a['analytics']['domainName'])):?>

<script type="text/javascript">
var _gaq = _gaq || [];
_gaq.push(['_setAccount', '<?=Config::$a['analytics']['account']?>']);
_gaq.push(['_setDomainName', '<?=Config::$a['analytics']['domainName']?>']);
_gaq.push(['_trackPageview']);
(function() {
	var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();
</script>
<?endif;?>