<? namespace Destiny; ?>
<?if(preg_match('/^local[.*]+/i', $_SERVER['HTTP_HOST']) <= 0):?>
<script type="text/javascript">
var _gaq = _gaq || [];
_gaq.push(['_setAccount', 'UA-37443504-1']);
_gaq.push(['_setDomainName', 'www.destiny.gg']);
_gaq.push(['_trackPageview']);
(function() {
	var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();
</script>
<?endif;?>