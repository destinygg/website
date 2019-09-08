<?php
use Destiny\Common\Utils\Tpl;
use Destiny\Commerce\SubscriptionStatus;
use Destiny\Common\Utils\Date;
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
        <h3>Subscription <small>(<a href="/admin/user/<?=Tpl::out($this->user['userId'])?>/edit"><?=Tpl::out($this->user['username'])?></a>)</small></h3>
        <div class="content content-dark clearfix">

            <?php
            $url = '/admin/user/'. urlencode($this->user['userId']) .'/subscription/save';
            if(!empty($this->subscription) && !empty($this->subscription['subscriptionId'])){
                $url = '/admin/user/'. urlencode($this->user['userId']) .'/subscription/'. urlencode($this->subscription['subscriptionId']) . '/save';
            }
            ?>

            <form action="<?=$url?>" method="post">

                <?php if(!empty($this->subscription['cancelDate'])): ?>
                    <div class="ds-block">
                        <p>Cancelled on <strong><?=Tpl::moment(Date::getDateTime($this->subscription['cancelDate']), Date::STRING_FORMAT)?></strong></p>
                    </div>
                <?php endif; ?>

                <div class="ds-block">

                    <div class="form-group">
                        <label>Type</label>
                        <select name="subscriptionType" class="form-control">
                            <option value="">Select a subscription type</option>
                            <option value="">&nbsp;</option>
                            <?php foreach($this->subscriptions as $sub): ?>
                                <option value="<?=Tpl::out($sub['id'])?>" <?=(strcasecmp($this->subscription['subscriptionType'], $sub['id']) === 0) ? 'selected="selected"':''?>><?=Tpl::out($sub['itemLabel'])?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Recurring</label>
                        <input type="text" class="form-control" value="<?=($this->subscription['recurring'] == '1') ? 'Yes':'No'?>" readonly />
                    </div>

                    <div class="form-group">
                        <label class="control-label" for="inputGifter">Gifter</label>
                        <div class="controls">
                            <input type="text" class="form-control" name="gifter" id="inputGifter" value="<?=Tpl::out($this->subscription['gifter'])?>" placeholder="Gifter user id or username">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="<?=SubscriptionStatus::ACTIVE?>" <?=(strcasecmp($this->subscription['status'], SubscriptionStatus::ACTIVE) === 0) ? 'selected="selected"':''?>><?=SubscriptionStatus::ACTIVE?></option>
                            <option value="<?=SubscriptionStatus::CANCELLED?>" <?=(strcasecmp($this->subscription['status'], SubscriptionStatus::CANCELLED) === 0) ? 'selected="selected"':''?>><?=SubscriptionStatus::CANCELLED?></option>
                            <option value="<?=SubscriptionStatus::EXPIRED?>" <?=(strcasecmp($this->subscription['status'], SubscriptionStatus::EXPIRED) === 0) ? 'selected="selected"':''?>><?=SubscriptionStatus::EXPIRED?></option>
                            <option value="<?=SubscriptionStatus::PENDING?>" <?=(strcasecmp($this->subscription['status'], SubscriptionStatus::PENDING) === 0) ? 'selected="selected"':''?>><?=SubscriptionStatus::PENDING?></option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="control-label" for="inputStarttimestamp">Start <small>(time specified in UCT)</small></label>
                        <div class="controls">
                            <input type="text" class="form-control" name="createdDate" id="inputStarttimestamp" value="<?=Tpl::out($this->subscription['createdDate'])?>" placeholder="Y-m-d H:i:s">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label" for="inputEndtimestamp">End  <small>(time specified in UCT)</small></label>
                        <div class="controls">
                            <input type="text" class="form-control" name="endDate" id="inputEndtimestamp" value="<?=Tpl::out($this->subscription['endDate'])?>" placeholder="Y-m-d H:i:s">
                        </div>
                    </div>

                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <a href="/admin/user/<?=Tpl::out($this->user['userId'])?>/edit" class="btn btn-dark">Cancel</a>
                </div>

            </form>
        </div>
    </section>

    <?php if(!empty($this->subscription['paymentProfileId'])): ?>
        <section class="container">
            <h3>Payment Status</h3>
            <div class="content content-dark clearfix">
                <table class="grid">
                    <thead>
                    <tr>
                        <td>Status</td>
                        <td>Payment Profile Id</td>
                        <td>Start Payment Date</td>
                        <td>Next Payment Date</td>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td><?=Tpl::out($this->subscription['paymentStatus'])?></td>
                        <td><?=Tpl::out($this->subscription['paymentProfileId'])?></td>
                        <td><?=Tpl::moment(Date::getDateTime($this->subscription['billingStartDate']), Date::STRING_FORMAT_YEAR)?></td>
                        <td><?=Tpl::moment(Date::getDateTime($this->subscription['billingNextDate']), Date::STRING_FORMAT_YEAR)?></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </section>
    <?php endif ?>

    <?php if(!empty($this->payments)): ?>
        <section class="container">
            <h3>Payments</h3>
            <div class="content content-dark clearfix">
                <table class="grid">
                    <thead>
                    <tr>
                        <td>Id</td>
                        <td>Amount</td>
                        <td>Created</td>
                        <td>Transaction Id</td>
                        <td>Transaction Type</td>
                        <td>Payment Type</td>
                        <td>Payer Id</td>
                        <td>Status</td>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach($this->payments as $payment): ?>
                        <tr>
                            <td><?=Tpl::out($payment['paymentId'])?></td>
                            <td><?=Tpl::out($payment['amount'])?> <?=Tpl::out($payment['currency'])?></td>
                            <td><?=Tpl::moment(Date::getDateTime($payment['paymentDate']), Date::STRING_FORMAT_YEAR)?></td>
                            <td><?=Tpl::out($payment['transactionId'])?></td>
                            <td><?=Tpl::out($payment['transactionType'])?></td>
                            <td><?=Tpl::out($payment['paymentType'])?></td>
                            <td><?=Tpl::out($payment['payerId'])?></td>
                            <td><?=Tpl::out($payment['paymentStatus'])?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php endif ?>

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