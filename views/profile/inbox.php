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

    <div id="alerts-container"></div>
    <?php include 'seg/nav.php' ?>
    <?php include 'seg/alerts.php' ?>
    <?php include 'menu.php' ?>

    <section id="inbox-tools" class="container">
        <button id="inbox-toggle-select" title="Toggle selection" class="btn btn btn-dark"><i class="far fa-circle"></i></button>
        <button id="inbox-new-message" accesskey="n" class="btn btn-primary" data-toggle="modal" data-target="#compose">New Message</button>
        <button id="inbox-read-selected" accesskey="o" class="btn btn-info" disabled>Mark as Read</button>
        <button id="inbox-delete-selected" accesskey="d" class="btn btn-danger float-right" data-toggle="modal" data-target="#inbox-modal-delete" disabled>Delete conversation</button>
        <form id="inbox-tools-form" method="post"></form>
    </section>

    <section class="container">
        <h3 data-toggle="collapse" data-target="#inbox-list">
            <span>Inbox</span>
            <i id="inbox-loading" class="fas fa-cog fa-spin" style="display: none;"></i>
        </h3>
        <div id="inbox-list" class="content content-dark collapse show">
            <table id="inbox-message-grid" class="grid messages">
                <colgroup>
                    <col class="c1">
                    <col class="c2">
                    <col class="c3">
                    <col class="c4">
                </colgroup>
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
        <button id="inbox-list-more" class="btn btn-primary" accesskey="m" style="display: none;">Show more messages</button>
    </section>

</div>

<?php include 'modal.compose.php' ?>
<?php include 'seg/foot.php' ?>
<?php include 'seg/tracker.php' ?>
<?=Tpl::manifestScript('runtime.js')?>
<?=Tpl::manifestScript('common.vendor.js')?>
<?=Tpl::manifestScript('chat.vendor.js')?>
<?=Tpl::manifestScript('web.js')?>
<?=Tpl::manifestScript('profile.js')?>

</body>
</html>