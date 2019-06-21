<?php
namespace Destiny;
use Destiny\Common\Utils\Tpl;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?=Tpl::title($this->title)?>
    <?php include 'seg/meta.php' ?>
    <?=Tpl::manifestLink('web.css')?>
</head>
<body id="inbox" class="no-contain">
<div id="page-wrap">

    <?php include 'seg/nav.php' ?>
    <?php include 'menu.php' ?>

    <section id="inbox-tools" class="container">
        <button id="inbox-scroll-bottom" title="Scroll down" class="btn btn btn-dark"><i class="fas fa-angle-double-down"></i></button>
        <button id="inbox-message-reply" accesskey="r" class="btn btn-primary" data-replyto="<?= Tpl::out($this->targetuser['username']) ?>" data-toggle="modal" data-target="#compose">Reply</button>
        <button id="inbox-delete-selected" class="btn btn-danger float-right" data-toggle="modal" data-target="#inbox-modal-delete">Delete conversation</button>
        <form id="inbox-tools-form" method="post"></form>
    </section>

    <section class="container">
        <h3 data-toggle="collapse" data-target="#message-list">
            <span>Messages from</span>
            <em><?= Tpl::out($this->targetuser['username']) ?></em>
            <i id="inbox-loading" class="fas fa-cog fa-spin" style="display: none;"></i>
        </h3>
        <div id="message-list" class="content content-dark collapse show" data-userid="<?= $this->targetuser['userId'] ?>" data-username="<?= $this->targetuser['username'] ?>">
            <table id="inbox-message-grid" class="grid messages">
                <tbody></tbody>
            </table>
            <table id="inbox-empty" class="grid messages" style="display: none;">
                <tbody>
                <tr>
                    <td colspan="4">You have no messages</td>
                </tr>
                </tbody>
            </table>
        </div>
    </section>

    <section class="container">
        <button id="inbox-scroll-top" title="Scroll up" class="btn btn btn-dark"><i class="fas fa-angle-double-up"></i></button>
        <button id="inbox-list-more" class="btn btn-primary" accesskey="m" style="display: none;">Show older messages</button>
    </section>

</div>

<?php include 'modal.compose.php' ?>
<?php include 'seg/alerts.php' ?>
<?php include 'seg/foot.php' ?>
<?php include 'seg/tracker.php' ?>
<?=Tpl::manifestScript('runtime.js')?>
<?=Tpl::manifestScript('common.vendor.js')?>
<?=Tpl::manifestScript('chat.vendor.js')?>
<?=Tpl::manifestScript('web.js')?>
<?=Tpl::manifestScript('profile.js')?>

</body>
</html>