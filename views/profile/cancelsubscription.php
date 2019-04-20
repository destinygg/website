<?php
use Destiny\Common\Config;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Utils\Date;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?=Tpl::title($this->title)?>
    <?php include 'seg/meta.php' ?>
    <?=Tpl::manifestLink('web.css')?>
</head>
<body id="subscription" class="no-contain">
<div id="page-wrap">

    <?php include 'seg/nav.php' ?>

    <section class="container">
        <h1 class="page-title">
            <span>Cancel</span>
            <small>subscription</small>
        </h1>
        <?php include 'seg/alerts.php' ?>
    </section>

    <section class="container">
        <div class="content content-dark clearfix">

            <form action="/subscription/cancel" method="post" autocomplete="off">

                <input type="hidden" name="subscriptionId" value="<?=Tpl::out($this->subscription['subscriptionId'])?>" />

                <div class="ds-block">
                    <div class="form-group">
                        <dl class="dl-horizontal">
                            <dt>Status:</dt>
                            <dd>
                                <span class="badge badge-<?=($this->subscription['status'] == 'Active') ? 'success':'warning'?>"><?=Tpl::out($this->subscription['status'])?></span>
                                <?php if($this->subscription['recurring']):?>
                                    <span class="badge badge-warning" title="This subscription is automatically renewed">Recurring</span>
                                <?php endif ?>
                            </dd>

                            <dt>Source:</dt>
                            <dd><?=Tpl::out($this->subscription['subscriptionSource'])?></dd>
                            <dt>Created date:</dt>
                            <dd><?=Tpl::moment(Date::getDateTime($this->subscription['createdDate']), Date::STRING_FORMAT_YEAR)?></dd>
                            <dt>End date:</dt>
                            <dd><?=Tpl::moment(Date::getDateTime($this->subscription['endDate']), Date::STRING_FORMAT_YEAR)?></dd>
                            <dt>Time remaining:</dt>
                            <dd><?=Date::getRemainingTime(Date::getDateTime($this->subscription['endDate']))?></dd>

                            <?php if(!empty($this->giftee)): ?>
                                <dt>Gifted to:</dt>
                                <dd><?=Tpl::out( $this->giftee['username'] )?></dd>
                            <?php endif ?>

                        </dl>
                    </div>
                    <?php if($this->subscription['status'] == 'Active'): ?>
                    <div class="g-recaptcha" data-theme="dark" data-sitekey="<?= Config::$a ['g-recaptcha'] ['key'] ?>"></div>
                    <?php endif; ?>
                </div>

                <div class="form-actions">
                    <?php if($this->subscription['status'] == 'Active' && $this->subscription['recurring'] == '0'): ?>
                        <button type="submit" id="cancelSubscriptionBtn" class="btn btn-danger">Remove Subscription</button>
                    <?php endif ?>
                    <?php if($this->subscription['recurring'] == '1'): ?>
                        <button type="submit" id="cancelSubscriptionBtn" class="btn btn-danger">Cancel Subscription</button>
                    <?php endif ?>
                    <a class="btn btn-dark" href="/profile">Back to profile</a>
                </div>
            </form>

        </div>
    </section>
</div>

<?php include 'seg/foot.php' ?>
<?php include 'seg/tracker.php' ?>
<?=Tpl::manifestScript('runtime.js')?>
<?=Tpl::manifestScript('common.vendor.js')?>
<?=Tpl::manifestScript('web.js')?>
<script src="https://www.google.com/recaptcha/api.js"></script>

</body>
</html>