<?php

use Destiny\Commerce\SubscriptionStatus;
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
                                <span class="badge badge-<?=($this->subscription['status'] == SubscriptionStatus::ACTIVE) ? 'success':'warning'?>"><?=Tpl::out($this->subscription['status'])?></span>
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

                </div>

                <div class="form-actions">
                    <?php if($this->subscription['status'] == SubscriptionStatus::ACTIVE && $this->subscription['recurring'] == '0'): ?>
                        <button type="button" id="cancelSubscriptionBtn" class="btn btn-danger" data-toggle="modal" data-target="#confirmCancelSubscription">Remove Subscription</button>
                    <?php endif ?>
                    <?php if($this->subscription['recurring'] == '1'): ?>
                        <button type="button" id="cancelSubscriptionBtn" class="btn btn-danger" data-toggle="modal" data-target="#confirmCancelSubscription">Cancel Subscription</button>
                    <?php endif ?>
                    <a class="btn btn-dark" href="/profile">Back to profile</a>
                </div>
            </form>

        </div>
    </section>
</div>

<div class="modal" id="confirmCancelSubscription" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="/subscription/cancel" method="post" autocomplete="off" class="form-alt">
                <div class="modal-header">
                    <?php if($this->subscription['status'] == SubscriptionStatus::ACTIVE && $this->subscription['recurring'] == '0'): ?>
                        <span class="modal-title">Confirm subscription removal</span>
                    <?php endif ?>
                    <?php if($this->subscription['recurring'] == '1'): ?>
                        <span class="modal-title">Confirm subscription cancellation</span>
                    <?php endif ?>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="subscriptionId" value="<?=Tpl::out($this->subscription['subscriptionId'])?>" />

                    <?php if($this->subscription['recurring'] == '1'): ?>
                    <div class="form-group">
                        <p>This will stop the recurring payment for this subscription. If you want to remove the subscription entirely. Do this process again after the recurring payment has been cancelled.</p>
                    </div>
                    <?php endif ?>
                    <?php if($this->subscription['recurring'] == '0'): ?>
                    <div class="form-group">
                        <div>Why are you un-subscribing? (optional)</div>
                        <textarea name="message" autocomplete="off" maxlength="250" rows="3" class="form-control" placeholder="" autofocus></textarea>
                        <label class="error hidden"></label>
                    </div>
                    <?php endif ?>

                    <?php if($this->subscription['status'] == SubscriptionStatus::ACTIVE): ?>
                        <div class="g-recaptcha" data-theme="light" data-sitekey="<?= Config::$a ['g-recaptcha'] ['key'] ?>"></div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal" id="userSearchCancel">Cancel</button>
                    <button type="submit" id="cancelSubscriptionBtn" class="btn btn-danger">Confirm</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'seg/alerts.php' ?>
<?php include 'seg/foot.php' ?>
<?php include 'seg/tracker.php' ?>
<?=Tpl::manifestScript('runtime.js')?>
<?=Tpl::manifestScript('common.vendor.js')?>
<?=Tpl::manifestScript('web.js')?>
<script src="https://www.google.com/recaptcha/api.js"></script>

</body>
</html>