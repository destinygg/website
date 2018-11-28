<?php
namespace Destiny;
use Destiny\Common\Utils\Tpl;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?=Tpl::title($this->title)?>
    <?php include 'seg/meta.php' ?>
    <?=Tpl::manifestLink('common.vendor.css')?>
    <?=Tpl::manifestLink('web.css')?>
</head>
<body id="message" class="no-contain">
<div id="page-wrap">

    <div id="alerts-container"></div>
    <?php include 'seg/nav.php' ?>
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
<?php include 'seg/tracker.php' ?>
<?=Tpl::manifestScript('runtime.js')?>
<?=Tpl::manifestScript('common.vendor.js')?>
<?=Tpl::manifestScript('chat.vendor.js')?>
<?=Tpl::manifestScript('web.js')?>
<?=Tpl::manifestScript('profile.js')?>

</body>
</html>