<?php
use Destiny\Common\Config;
use Destiny\Common\Session\Session;
use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\Tpl;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?= Tpl::title($this->title) ?>
    <?php include 'seg/meta.php' ?>
    <?= Tpl::manifestLink('web.css') ?>
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
                <div><?= Tpl::out($this->warning) ?></div>
            </div>
        </section>
    <?php endif ?>

    <section class="container">
        <h3 class="in" data-toggle="collapse" data-target="#details-content">YouTube Integration</h3>
        <div id="details-content" class="content content-dark collapse show">
            <div class="ds-block">
                <div class="form-group">
                    <a href="/admin/user/<?= Tpl::out($this->user['userId']) ?>/edit"><?= Tpl::out($this->user['username']) ?></a>
                    <?php if(!empty($this->auth)): ?>
                        <span class="badge-pill badge-success">Authorized</span>
                        <p>Last updated <?= Tpl::fromNow(Date::getDateTime($this->auth['createdDate']), Date::STRING_FORMAT_YEAR) ?></p>
                    <?php else: ?>
                        <span class="badge-pill badge-danger">Unauthorized</span>
                    <?php endif; ?>
                </div>
            </div>

            <?php if(!empty($this->auth) && $this->auth['userId'] == Session::getCredentials()->getUserId()): ?>
                <div class="ds-block">
                    <div>
                        Access Token: <a href="#accessToken" data-toggle="show">(show)</a>
                        <code class="collapse" id="accessToken"><?= Tpl::out($this->auth['accessToken']) ?></code>
                    </div>
                    <div>
                        Refresh Token: <a href="#refreshToken" data-toggle="show">(show)</a>
                        <code class="collapse" id="refreshToken"><?= Tpl::out($this->auth['refreshToken']) ?></code>
                    </div>
                </div>
            <?php endif; ?>

            <div class="ds-block">
                <a href="/admin/youtube/authorize" class="btn btn-primary">Authorize</a>
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
