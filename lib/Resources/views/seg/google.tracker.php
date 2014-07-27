<?php 
use Destiny\Common\Config;
?>

<script>
var _gaq = _gaq || [];
<?if(!empty(Config::$a['analytics']['account']) && !empty(Config::$a['analytics']['domainName'])):?>
_gaq.push(['_setAccount', '<?=Config::$a['analytics']['account']?>']);
_gaq.push(['_setDomainName', '<?=Config::$a['analytics']['domainName']?>']);
_gaq.push(['_trackPageview']);
<?endif;?>
(function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();
</script>

<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', '<?=Config::$a['analytics']['account']?>', 'auto');
  ga('send', 'pageview');
</script>