<?php
use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Config;
?>
<!DOCTYPE html>
<html>
<head>
    <title><?=Tpl::title($this->title)?></title>
    <?php include 'seg/meta.php' ?>
    <link href="<?=Config::cdnv()?>/web.css" rel="stylesheet" media="screen">
</head>
<body id="admin" class="no-contain">
<div id="page-wrap">

    <?php include 'seg/nav.php' ?>
    <?php include 'seg/alerts.php' ?>
    <?php include 'seg/admin.nav.php' ?>

    <section class="container">
        <h3 class="in" data-toggle="collapse" data-target="#details-content">StreamLabs</h3>
        <div id="details-content" class="content content-dark clearfix collapse in">
            <div class="ds-block">
                <div class="form-group">
                    Attached profile: <a><?=Tpl::out($this->user['username'])?></a>
                    <?php if(!empty($this->auth)): ?>
                    <span class="label label-default">Authorized</span>
                    <p>Next auth code renew <?= Tpl::fromNow(Date::getDateTime($this->auth['createdDate'])->add(new DateInterval('PT3600S'))) ?></p>
                    <?php else: ?>
                    <span class="label label-danger">Unauthorized</span>
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
<script src="<?=Config::cdnv()?>/web.js"></script>
<script src="<?=Config::cdnv()?>/admin.js"></script>
<script>
$(function(){

    $('#authBtn').on('click', function(){
        window.location.href = '/streamlabs/authorize';
    })
    $('#testBtn').on('click', function(){
        window.location.href = '/streamlabs/alert/test';
    })
})
</script>

</body>
</html>