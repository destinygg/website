<?php
namespace Destiny;
use Destiny\Commerce\SubPurchaseType;
use Destiny\Common\Utils\Tpl;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?=Tpl::title($this->title)?>
    <?php include 'seg/meta.php' ?>
    <?=Tpl::manifestLink('web.css')?>
</head>
<body id="subscription-confirm">
<div id="page-wrap">

    <?php include 'seg/nav.php' ?>

    <section class="container vertical-contain">
        <div style="flex: 1;">

            <h1 class="title">
                <span>Subscribe</span> <small>confirm your selection</small>
            </h1>

            <div class="content content-dark clearfix">

                <?php if(!empty($this->warning)): ?>
                    <section class="container mb-0">
                        <div class="alert alert-danger alert-dismissable mb-0">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">&times;</button>
                            <strong><i class="fas fa-exclamation-triangle"></i> Warning</strong>
                            <div><?=Tpl::out($this->warning)?></div>
                        </div>
                    </section>
                <?php endif ?>

                <div style="width: 100%;" class="clearfix stream">
                    <form id="subscribe-form" action="/subscription/create" method="post">
                        <input type="hidden" name="purchaseType" value="<?= $this->purchaseType ?>">
                        <input type="hidden" name="subscriptionId" value="<?= $this->subscriptionType['id'] ?>">
                        <input type="hidden" name="giftee" value="<?= $this->giftee ?>">
                        <input type="hidden" name="quantity" value="<?= $this->quantity ?>">

                        <div class="ds-block">
                            <h4>Summary</h4>
                            <table id="transaction-summary">
                                <colgroup>
                                    <col class="quantity">
                                    <col>
                                    <col class="price">
                                </colgroup>
                                <tr>
                                    <th>Quantity</th>
                                    <th>Description</th>
                                    <th>Price</th>
                                </tr>
                                <tr>
                                    <td><?= $this->quantity ?></td>
                                    <td>
                                        <?= $this->subscriptionType['itemLabel'] ?>
                                        <?php if (!empty($this->giftee)): ?>
                                            <span class="badge badge-danger"><i class="fas fa-gift"></i> <?= Tpl::out($this->giftee) ?></span>
                                        <?php endif ?>
                                    </td>
                                    <td>$<?= $this->subscriptionType['amount'] ?></td>
                                </tr>
                                <tr>
                                    <th colspan="2" class="text-right">Order Total</th>
                                    <td class="font-weight-bold">$<?= number_format(floatval($this->subscriptionType['amount']) * $this->quantity, 2) ?></td>
                                </tr>
                            </table>
                        </div>

                        <div class="ds-block text-message">
                            <div>Why are you subscribing? where did you hear about Destiny? (optional)</div>
                            <textarea name="sub-note" autocomplete="off" maxlength="250" rows="1" class="form-control" placeholder=""></textarea>
                        </div>

                        <div class="ds-block text-message">
                            <div>Send a broadcast message with your subscription (optional)</div>
                            <textarea name="sub-message" autocomplete="off" maxlength="250" rows="3" class="form-control" placeholder=""></textarea>
                        </div>

                        <?php if ($this->purchaseType !== SubPurchaseType::MASS_GIFT): ?>
                            <div class="ds-block">
                                <div class="checkbox">
                                    <label for="recurring">
                                        <span><input id="recurring" type="checkbox" name="recurring" value="1" /> <strong>Recurring subscription</strong></span>
                                        <small>Automatically bill every <?=$this->subscriptionType['billingFrequency']?> <?=strtolower($this->subscriptionType['billingPeriod'])?>(s)</small>
                                    </label>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-shopping-cart"></i> Continue</button>
                            <a href="/subscribe" class="btn btn-dark">Cancel</a>
                        </div>

                    </form>
                </div>
            </div>

            <p class="agreement">By clicking the &quot;Continue&quot; button, you are confirming that this purchase is what you wanted and that you have read the <a href="/agreement">user agreement</a>.</p>
        </div>
    </section>
</div>
<?php include 'seg/alerts.php' ?>
<?php include 'seg/foot.php' ?>
<?php include 'seg/tracker.php' ?>
<?=Tpl::manifestScript('runtime.js')?>
<?=Tpl::manifestScript('common.vendor.js')?>
<?=Tpl::manifestScript('web.js')?>

</body>
</html>