<?php

use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Config;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?=Tpl::title($this->title)?>
    <?php include 'seg/meta.php' ?>
    <?=Tpl::manifestLink('web.css')?>
</head>
<body id="admin" class="no-contain">
<div id="page-wrap">

    <?php include 'seg/nav.php' ?>
    <?php include 'seg/admin.nav.php' ?>

    <section class="container">
        <div class="content content-dark filter-form">
            <div class="form-inline filters">
                <div class="form-group" style="flex: 1; display: flex;">
                    <a href="/admin/themes/new" id="themeNewBtn" class="btn btn-primary mr-3">Theme <i class="fas fa-fw fa-plus"></i></a>
                </div>
            </div>
        </div>
    </section>

    <section class="container">
        <div class="content content-dark">
        <div class="stream stream-grid" style="width:100%;">
        <table class="grid">
            <thead>
            <tr>
                <td>Theme</td>
                <td>Prefix</td>
                <td>Created on</td>
                <td></td>
            </tr>
            </thead>
            <tbody>
            <?php foreach($this->themes as $theme): ?>
                <tr>
                    <td><i style="color: <?=$theme['color']?>" class="fas fa-circle mr-3"></i> <a href="/admin/themes/<?=$theme['id']?>/edit"><?=Tpl::out($theme['label'])?></a> <?php if(boolval($theme['active'])): ?><span class="ml-3 badge badge-primary">active</span><?php endif; ?></td>
                    <td><?=$theme['prefix']?></td>
                    <td><?=Tpl::moment(Date::getDateTime($theme['createdDate']), Date::STRING_FORMAT)?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        </div>
    </section>

</div>

<?php include 'seg/alerts.php' ?>
<?php include 'seg/foot.php' ?>
<?php include 'seg/tracker.php' ?>
<?=Tpl::manifestScript('runtime.js')?>
<?=Tpl::manifestScript('common.vendor.js')?>
<?=Tpl::manifestScript('web.js')?>
<?=Tpl::manifestScript('admin.js')?>

</body>
</html>