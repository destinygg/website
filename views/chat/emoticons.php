<?php
namespace Destiny;
use Destiny\Common\Config;
use Destiny\Common\Utils\Tpl;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($this->title)?></title>
<meta charset="utf-8">
<?php include 'seg/opengraph.php' ?>
<?php include 'seg/commontop.php' ?>
<?php include 'seg/google.tracker.php' ?>
<link href="<?=Config::cdnv()?>/chat/css/style.min.css" rel="stylesheet" media="screen">
</head>
<body id="emoticons" class="no-brand">
  <div id="page-wrap">

    <?php include 'seg/top.php' ?>
    <?php include 'seg/headerband.php' ?>

    <section class="container">
      <h1 class="title">Emoticons</h1>
      <div class="content content-dark">
        <div class="emoticons clearfix">
          <?php foreach( $this->emoticons as $trigger ): ?>
          <div class="emote">
            <div>
              <div class="chat-emote chat-emote-<?=$trigger?>" title="<?=$trigger?>"></div>
              <a class="emote-label"><?=Tpl::out($trigger)?></a>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <section class="container">
      <h1 class="title">Twitch Emotes (usable if you are a Twitch sub)</h1>
      <div class="content content-dark">
        <div class="emoticons clearfix">
          <?php foreach( $this->twitchemotes as $trigger ): ?>
          <div class="emote">
            <div>
              <div class="chat-emote chat-emote-<?=$trigger?>" title="<?=$trigger?>"></div>
              <a class="emote-label"><?=Tpl::out($trigger)?></a>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

  </div>

  <?php include 'seg/foot.php' ?>
  <?php include 'seg/commonbottom.php' ?>
  
</body>
</html>