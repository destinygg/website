<?php
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Config;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($model->title)?></title>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" charset="utf-8">
<meta name="referrer" content="no-referrer">
<?php include Tpl::file('seg/commontop.php') ?>
<link href="<?=Config::cdnv()?>/chat/css/style.min.css" rel="stylesheet" media="screen">
<link href="<?=Config::cdnv()?>/chat/css/onstream.min.css" rel="stylesheet" media="screen">
<style id="chat-styles" type="text/css"></style>
<?php include Tpl::file('seg/google.tracker.php') ?>
</head>
<body class="embed">

    <div id="destinychat" class="chat chat-icons">

        <div id="chat-output-frame">
            <div id="chat-output" class="nano">
              <div id="chat-lines" class="overthrow nano-content"></div>
            </div>
        </div>

    </div>

    <?php include Tpl::file('seg/commonbottom.php') ?>

    <script src="<?=Config::cdnv()?>/chat/js/chat.min.js"></script>
    <script src="/chat/onstream/init"></script>

</body>
</html>
