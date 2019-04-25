<?php

use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\Tpl;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?=Tpl::title($this->title)?>
    <meta http-equiv="refresh" content="30">
    <?php include 'seg/meta.php' ?>
    <?=Tpl::manifestLink('web.css')?>
</head>
<body id="admin" class="no-contain">
<div id="page-wrap">

    <?php include 'seg/nav.php' ?>
    <?php include 'seg/admin.nav.php' ?>

    <section class="container">
        <h3>Audit Log</h3>
        <div class="content content-dark clearfix">
            <table id="auditlog-grid" class="grid messages">
                <thead>
                    <tr>
                        <td>User</td>
                        <td>Url</td>
                        <td>Time</td>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->logs as $log): ?>
                        <tr>
                            <td>
                                <a href="/admin/user/<?=$log['userid']?>/edit"><?=Tpl::out($log['username']) ?></a>
                            </td>
                            <td><?=Tpl::out($log['requesturi']) ?></td>
                            <td><span class="text-muted">(<?=Tpl::fromNow(Date::getDateTime($log['timestamp']), Date::STRING_FORMAT) ?>)</span> <?=Tpl::moment(Date::getDateTime($log['timestamp']), Date::STRING_FORMAT) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if(empty($this->logs)): ?>
                    <tr>
                        <td colspan="3">There are no audit logs</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

</div>

<?php include 'seg/foot.php' ?>
<?php include 'seg/tracker.php' ?>
<?=Tpl::manifestScript('runtime.js')?>
<?=Tpl::manifestScript('common.vendor.js')?>
<?=Tpl::manifestScript('web.js')?>
<?=Tpl::manifestScript('admin.js')?>

</body>
</html>