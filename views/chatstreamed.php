<?php
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Config;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($this->title)?></title>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" charset="utf-8">
<meta name="referrer" content="no-referrer">
<?php include 'seg/meta.php' ?>
<link href="<?=Config::cdnv()?>/streamchat.css" rel="stylesheet" media="screen">
<style id="chat-styles" type="text/css"></style>
</head>
<body class="embed onstream">

<div id="chat" class="chat chat-icons chat-onstream">
    <div id="chat-output-frame"></div>
</div>

<?php include 'seg/tracker.php' ?>
<script src="<?=Config::cdnv()?>/streamchat.js"></script>

</body>
</html>
