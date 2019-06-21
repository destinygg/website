<?php

use Destiny\Common\Authentication\AuthProvider;
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

    <?php if(!empty($this->warning)): ?>
    <section class="container mb-0">
        <div class="alert alert-danger alert-dismissable mb-0">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">&times;</button>
            <strong><i class="fas fa-exclamation-triangle"></i> Warning</strong>
            <div><?=Tpl::out($this->warning)?></div>
        </div>
    </section>
    <?php endif ?>

    <section class="container">
        <h3 class="in" data-toggle="collapse" data-target="#details-content">StreamElements</h3>
        <div id="details-content" class="content content-dark collapse show">
            <div class="ds-block">
                <div class="form-group">
                    <a href="/admin/user/<?=Tpl::out($this->user['userId'])?>/edit"><?=Tpl::out($this->user['username'])?></a>
                    <?php if(!empty($this->auth)): ?>
                        <span class="badge-pill badge-success">Authorized</span>
                        <p>Last updated <?=Tpl::fromNow(Date::getDateTime($this->auth['createdDate']), Date::STRING_FORMAT_YEAR)?></p>
                    <?php else: ?>
                        <span class="badge-pill badge-danger">Unauthorized</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="ds-block">
                <div class="form-group">
                    <div class="checkbox">
                        <label>
                            <input disabled type="checkbox" name="roles[]" <?=(Config::$a[AuthProvider::STREAMELEMENTS]['alert_donations'])?'checked="checked"':''?>>
                            Alert donations
                        </label>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input disabled type="checkbox" name="roles[]" <?=(Config::$a[AuthProvider::STREAMELEMENTS]['alert_subscriptions'])?'checked="checked"':''?>>
                            Alert subscriptions
                        </label>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input disabled type="checkbox" name="roles[]" <?=(Config::$a[AuthProvider::STREAMELEMENTS]['send_donations'])?'checked="checked"':''?>>
                            Send donations
                        </label>
                    </div>
                </div>
            </div>
            <div class="ds-block">
                <p>
                    <a href="/admin/streamelements/authorize" class="btn btn-primary">Authorize</a>
                    <a href="/admin/streamelements/alert/test" class="btn btn-danger <?=(empty($this->auth))? 'disabled':''?>">Send Test</a>
                </p>
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