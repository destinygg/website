<?php
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Config;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?=Tpl::title($this->title)?>
    <?php include 'seg/meta.php' ?>
    <meta name="referrer" content="no-referrer">
    <?=Tpl::manifestLink('chat.vendor.css')?>
</head>
<body>
<?=Tpl::manifestScript('runtime.js')?>
<?=Tpl::manifestScript('common.vendor.js')?>
<?=Tpl::manifestScript('chat.vendor.js')?>
<?=Tpl::manifestScript('streamchat.js', [
    'id' => 'chat-include',
    'data-ws-url' => Config::$a['chatWebSocketUrl'],
    'data-cache-key' => $this->cacheKey,
    'data-cdn' => Config::cdnv(),
    'data-ban-appeal-url' => Config::$a['banAppealUrl']
])?>
<?php include 'seg/tracker.php' ?>
</body>
</html>
