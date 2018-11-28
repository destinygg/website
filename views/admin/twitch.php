<?php
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
<body id="admin" class="no-contain">
<div id="page-wrap">

    <?php include 'seg/nav.php' ?>
    <?php include 'seg/alerts.php' ?>
    <?php include 'seg/admin.nav.php' ?>

    <section class="container">
        <h3 class="in" data-toggle="collapse" data-target="#details-content">Twitch</h3>
        <div id="details-content" class="content content-dark clearfix collapse in">
            <div class="ds-block">
                <div class="form-group">
                    <p><span style="display: block;">Attached profile: <a><?=Tpl::out($this->user['username'])?></a></span></p>
                    <p class="text-muted">Clicking the authorize button will attempt to grant special permissions.<br />This is for broadcasters only.</p>
                    <div>
                        <a href="/admin/twitch/authorize" class="btn btn-primary" role="button">Authorize</a>
                    </div>
                </div>
            </div>
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