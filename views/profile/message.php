<?php
namespace Destiny;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Config;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($this->title)?></title>
<meta charset="utf-8">
<?php include 'seg/commontop.php' ?>
<link href="<?=Config::cdnv()?>/web.css" rel="stylesheet" media="screen">
</head>
<body id="message" class="no-contain">
    <div id="page-wrap">

        <?php include 'seg/top.php' ?>
        <?php include 'seg/alerts.php' ?>
        <?php include 'menu.php' ?>
        
        <section id="message-list" class="container active" data-userid="<?= $this->targetuser['userId'] ?>" data-username="<?= $this->targetuser['username'] ?>">
            <h4 id="message-list-title">
                Messages between you and <em><?= Tpl::out($this->targetuser['username']) ?></em> ... <i id="message-list-loading" class="fa fa-cog fa-spin"  style="display: none;"></i>
            </h4>
            <hr />
            <div style="text-align: center">
                <a class="btn btn-link" accesskey="m" id="message-list-more" style="display: none;">
                    Show older messages
                </a>
            </div>
            <div id="message-container"></div>
            <div style="margin: 20px 0;">
                <button id="message-reply" accesskey="r" class="btn btn-primary" data-replyto="<?= Tpl::out($this->targetuser['username']) ?>" data-toggle="modal" data-target="#compose">Reply</button>
            </div>
        </section>
    </div>

    <?php include 'compose.php' ?>
    <?php include 'seg/foot.php' ?>
    <?php include 'seg/commonbottom.php' ?>
    <script src="<?=Config::cdnv()?>/web.js"></script>
    <script src="<?=Config::cdnv()?>/messages.js"></script>

</body>
</html>