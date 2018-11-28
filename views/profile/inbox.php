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
<body id="messages" class="no-contain">
<div id="page-wrap">

    <div id="alerts-container"></div>
    <?php include 'seg/nav.php' ?>
    <?php include 'seg/alerts.php' ?>
    <?php include 'menu.php' ?>

    <section class="container">
        <button accesskey="n" class="btn btn-default btn-primary" data-toggle="modal" data-target="#compose">New Message</button>
        <button class="btn btn-danger" id="mark-all">Mark All Read</button>
    </section>

    <section class="container">
        <h3 data-toggle="collapse" data-target="#inbox-content">Inbox <i id="inbox-loading" class="fa fa-cog fa-spin" style="display: none;"></i></h3>
        <div id="inbox-content" class="content content-dark collapse in clearfix">
            <table id="inbox" class="grid messages">
                <colgroup>
                    <col class="c2">
                    <col class="c3">
                    <col class="c4">
                </colgroup>
                <tbody>
                </tbody>
            </table>
            <table id="inbox-empty" class="grid messages" style="display: none;">
                <tbody>
                <tr>
                    <td>You have no messages</td>
                </tr>
                </tbody>
            </table>
        </div>
    </section>

    <div style="text-align: center; clear: both;">
        <a class="btn btn-primary" accesskey="m" id="inbox-show-more" style="display: none;">
            Show more messages
        </a>
    </div>

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