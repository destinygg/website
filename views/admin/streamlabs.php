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
    <?php include 'seg/alerts.contained.php' ?>
    <?php include 'seg/admin.nav.php' ?>

    <section class="container">
        <h3 class="in" data-toggle="collapse" data-target="#details-content">StreamLabs</h3>
        <div id="details-content" class="content content-dark collapse show">
            <div class="ds-block">
                <div class="form-group">
                    Attached profile: <a><?=Tpl::out($this->user['username'])?></a>
                    <?php if(!empty($this->auth)): ?>
                        <span class="badge badge-default">Authorized</span>
                        <p>Next auth code renew <?= Tpl::fromNow(Date::getDateTimePlusSeconds($this->auth['createdDate'], 3600)) ?></p>
                    <?php else: ?>
                        <span class="badge badge-danger">Unauthorized</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="ds-block">
                <div class="form-group">
                    <label>Settings</label>
                    <div class="checkbox">
                        <label>
                            <input disabled type="checkbox" name="roles[]" <?=(Config::$a['streamlabs']['alert_donations'])?'checked="checked"':''?>>
                            Alert donations
                        </label>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input disabled type="checkbox" name="roles[]" <?=(Config::$a['streamlabs']['alert_subscriptions'])?'checked="checked"':''?>>
                            Alert subscriptions
                        </label>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input disabled type="checkbox" name="roles[]" <?=(Config::$a['streamlabs']['send_donations'])?'checked="checked"':''?>>
                            Send donations
                        </label>
                    </div>
                </div>
            </div>
            <div class="ds-block">
                <p>
                    <button id="authBtn" class="btn btn-primary">Authorize</button>
                    <button id="testBtn" class="btn btn-default test" <?=(empty($this->auth))? 'disabled':''?>>Send Test</button>
                </p>
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