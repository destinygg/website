<?php
use Destiny\Common\Config;
?>
<script>
window.ga=window.ga||function(){(ga.q=ga.q||[]).push(arguments)};ga.l=+new Date;
ga('create', '<?=Config::$a['analytics']['account']?>', 'auto');
ga('send', 'pageview');
</script>
<script async src="https://www.google-analytics.com/analytics.js"></script>