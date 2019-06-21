<?php
namespace Destiny;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Session\Session;
use Destiny\Common\User\UserRole;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?=Tpl::title($this->title)?>
    <?php include 'seg/meta.php' ?>
    <?=Tpl::manifestLink('web.css')?>
</head>
<body id="subscribe">
<div id="page-wrap">

    <?php include 'seg/nav.php' ?>

    <section class="container vertical-contain">
        <div style="flex: 1;">

            <h1 class="title">
                <span>Subscribe</span>
                <small>send a message too</small>
            </h1>
            <br />

            <?php if(!empty($this->pending)): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert" style="text-align: center;">
                <strong>Warning!</strong> You have an existing subscription in a <strong>pending</strong> state.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php endif; ?>

            <?php if(Session::hasRole(UserRole::USER)): ?>
                <div id="giftSubscriptionSelect" class="alert alert-info" style="text-align: center;">
                    Would you like to gift someone a subscription?
                    <button class="btn btn-primary" data-toggle="modal" data-target="#usersearchmodal">Yes, gift a subscription <i class="fas fa-gift"></i></button>
                </div>

                <div id="giftSubscriptionConfirm" class="alert alert-info hidden" style="text-align: center;">
                    You are gifting your subscription to <strong id="subscriptionGiftUsername"></strong>!
                    <button class="btn btn-primary" id="selectGiftSubscription" data-toggle="modal" data-target="#usersearchmodal">Change <i class="fas fa-gift"></i></button>
                    <button class="btn btn-default" id="cancelGiftSubscription">Abort!</button>
                </div>
            <?php endif ?>

            <div class="subfeature">
                <div class="subfeature-desc">
                    <h1>Tier IV</h1>
                    <p>Know in your heart you have made the right choice here.</p>
                </div>
                <div class="subfeature-options">
                    <div class="subfeature-t1">
                        <?php $sub = $this->subscriptions['1-MONTH-SUB4']?>
                        <form action="/subscription/confirm" method="post">
                            <input type="hidden" name="subscription" value="<?=$sub['id']?>" />
                            <input type="hidden" name="gift" value="" />
                            <input type="hidden" name="gift-message" value="" />
                            <div class="subfeature-info">
                                <div class="subfeature-price">$<?=floatval($sub['amount'])?></div>
                            </div>
                            <div class="subfeature-info">
                                <div class="subfeature-period"><?=$sub['billingFrequency']?> <?=strtolower($sub['billingPeriod'])?></div>
                                <button type="submit" class="btn btn-primary">Choose</button>
                            </div>
                        </form>
                    </div>
                    <div class="subfeature-t2">
                        <?php $sub = $this->subscriptions['3-MONTH-SUB4']?>
                        <form action="/subscription/confirm" method="post">
                            <input type="hidden" name="subscription" value="<?=$sub['id']?>" />
                            <input type="hidden" name="gift" value="" />
                            <input type="hidden" name="gift-message" value="" />
                            <div class="subfeature-info">
                                <div class="subfeature-price">$<?=floatval($sub['amount'])?></div>
                            </div>
                            <div class="subfeature-info">
                                <div class="subfeature-period"><?=$sub['billingFrequency']?> <?=strtolower($sub['billingPeriod'])?></div>
                                <button type="submit" class="btn btn-primary">Choose</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="subfeature">
                <div class="subfeature-desc">
                    <h1>Tier III</h1>
                    <p>Wow such value so prestige you should purchase immediately.</p>
                </div>
                <div class="subfeature-options">
                    <div class="subfeature-t1">
                        <?php $sub = $this->subscriptions['1-MONTH-SUB3']?>
                        <form action="/subscription/confirm" method="post">
                            <input type="hidden" name="subscription" value="<?=$sub['id']?>" />
                            <input type="hidden" name="gift" value="" />
                            <input type="hidden" name="gift-message" value="" />
                            <div class="subfeature-info">
                                <div class="subfeature-price">$<?=floatval($sub['amount'])?></div>
                            </div>
                            <div class="subfeature-info">
                                <div class="subfeature-period"><?=$sub['billingFrequency']?> <?=strtolower($sub['billingPeriod'])?></div>
                                <button type="submit" class="btn btn-primary">Choose</button>
                            </div>
                        </form>
                    </div>
                    <div class="subfeature-t2">
                        <?php $sub = $this->subscriptions['3-MONTH-SUB3']?>
                        <form action="/subscription/confirm" method="post">
                            <input type="hidden" name="subscription" value="<?=$sub['id']?>" />
                            <input type="hidden" name="gift" value="" />
                            <input type="hidden" name="gift-message" value="" />
                            <div class="subfeature-info">
                                <div class="subfeature-price">$<?=floatval($sub['amount'])?></div>
                            </div>
                            <div class="subfeature-info">
                                <div class="subfeature-period"><?=$sub['billingFrequency']?> <?=strtolower($sub['billingPeriod'])?></div>
                                <button type="submit" class="btn btn-primary">Choose</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="subfeature">
                <div class="subfeature-desc">
                    <h1>Tier II</h1>
                    <p>Got a bit more to contribute? Probably the best investment of all time.</p>
                </div>
                <div class="subfeature-options">
                    <div class="subfeature-t1">
                        <?php $sub = $this->subscriptions['1-MONTH-SUB2']?>
                        <form action="/subscription/confirm" method="post">
                            <input type="hidden" name="subscription" value="<?=$sub['id']?>" />
                            <input type="hidden" name="gift" value="" />
                            <input type="hidden" name="gift-message" value="" />
                            <div class="subfeature-info">
                                <div class="subfeature-price">$<?=floatval($sub['amount'])?></div>
                            </div>
                            <div class="subfeature-info">
                                <div class="subfeature-period"><?=$sub['billingFrequency']?> <?=strtolower($sub['billingPeriod'])?></div>
                                <button type="submit" class="btn btn-primary">Choose</button>
                            </div>
                        </form>
                    </div>
                    <div class="subfeature-t2">
                        <?php $sub = $this->subscriptions['3-MONTH-SUB2']?>
                        <form action="/subscription/confirm" method="post">
                            <input type="hidden" name="subscription" value="<?=$sub['id']?>" />
                            <input type="hidden" name="gift" value="" />
                            <input type="hidden" name="gift-message" value="" />
                            <div class="subfeature-info">
                                <div class="subfeature-price">$<?=floatval($sub['amount'])?></div>
                            </div>
                            <div class="subfeature-info">
                                <div class="subfeature-period"><?=$sub['billingFrequency']?> <?=strtolower($sub['billingPeriod'])?></div>
                                <button type="submit" class="btn btn-primary">Choose</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="subfeature">
                <div class="subfeature-desc">
                    <h1>Tier I</h1>
                    <p>Get access to chat features and be eligible for future subscriber events!</p>
                </div>
                <div class="subfeature-options">
                    <div class="subfeature-t1">
                        <?php $sub = $this->subscriptions['1-MONTH-SUB']?>
                        <form action="/subscription/confirm" method="post">
                            <input type="hidden" name="subscription" value="<?=$sub['id']?>" />
                            <input type="hidden" name="gift" value="" />
                            <input type="hidden" name="gift-message" value="" />
                            <div class="subfeature-info">
                                <div class="subfeature-price">$<?=floatval($sub['amount'])?></div>
                            </div>
                            <div class="subfeature-info">
                                <div class="subfeature-period"><?=$sub['billingFrequency']?> <?=strtolower($sub['billingPeriod'])?></div>
                                <button type="submit" class="btn btn-primary">Choose</button>
                            </div>
                        </form>
                    </div>
                    <div class="subfeature-t2">
                        <?php $sub = $this->subscriptions['3-MONTH-SUB']?>
                        <form action="/subscription/confirm" method="post">
                            <input type="hidden" name="subscription" value="<?=$sub['id']?>" />
                            <input type="hidden" name="gift" value="" />
                            <input type="hidden" name="gift-message" value="" />
                            <div class="subfeature-info">
                                <div class="subfeature-price">$<?=floatval($sub['amount'])?></div>
                            </div>
                            <div class="subfeature-info">
                                <div class="subfeature-period"><?=$sub['billingFrequency']?> <?=strtolower($sub['billingPeriod'])?></div>
                                <button type="submit" class="btn btn-primary">Choose</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

</div>

<div class="modal" id="usersearchmodal" tabindex="-1" role="dialog" aria-labelledby="userSearchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <form id="userSearchForm" class="form-alt">
                    <div class="form-group">
                        <label>Who do you want to gift?</label>
                        <input tabindex="1" type="text" id="userSearchInput" class="form-control" placeholder="E.g. Destiny" />
                        <label class="error hidden"></label>
                    </div>
                    <button tabindex="4" type="button" class="btn btn-default" data-dismiss="modal" id="userSearchCancel">Cancel</button>
                    <button tabindex="3" type="submit" class="btn btn-primary" id="userSearchSelect" data-loading-text="Checking user...">Select</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'seg/foot.php' ?>
<?php include 'seg/tracker.php' ?>
<?=Tpl::manifestScript('runtime.js')?>
<?=Tpl::manifestScript('common.vendor.js')?>
<?=Tpl::manifestScript('web.js')?>

</body>
</html>