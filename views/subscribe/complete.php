<?php
namespace Destiny;
use Destiny\Commerce\SubPurchaseType;
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
<body id="subscription-complete">
<div id="page-wrap">

    <?php include 'seg/nav.php' ?>

    <section class="container vertical-contain">
        <div style="flex: 1;">
            <h1 class="title">
                <span>Complete</span> <small>successful</small>
            </h1>

            <div class="content content-dark clearfix">

                <div class="ui-step-legend-wrap clearfix">
                    <div class="ui-step-legend clearfix">
                        <ul>
                            <li><a>Select a subscription</a></li>
                            <li><a>Confirmation</a></li>
                            <li><a>Pay subscription</a></li>
                            <li class="active"><a>Complete</a><i class="arrow"></i></li>
                        </ul>
                    </div>
                </div>

                <div class="ds-block">
                    <p>
                        Your order was successful.<br>

                        <?php if (!empty($this->transactionId)): ?>
                            The PayPal transaction ID for your payment is <em><?= $this->transactionId ?></em>.<br>
                        <?php else: ?>
                            The PayPal transaction is still pending. Your sub will activate when it's processed.<br>
                        <?php endif; ?>

                        <?php if (!empty(Config::$a['support_email'])): ?>
                            Please email <a href="mailto:<?= Config::$a['support_email'] ?>"><?= Config::$a['support_email'] ?></a> if you have any questions or concerns.<br>
                        <?php endif; ?>

                        <br>
                        Thank you for your support!
                    </p>
                </div>

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
                                <?php if ($this->purchaseType === SubPurchaseType::MASS_GIFT): ?>
                                    <span class="badge badge-danger"><i class="fas fa-gifts"></i></span>
                                <?php endif ?>
                                <?php if (!empty($this->giftee)): ?>
                                    <span class="badge badge-danger"><i class="fas fa-gift"></i> <?= Tpl::out($this->giftee) ?></span>
                                <?php endif ?>
                                <?php if ($this->recurring): ?>
                                    <span class="badge badge-danger"> Recurring</span>
                                <?php endif; ?>
                            </td>
                            <td>$<?= $this->subscriptionType['amount'] ?></td>
                        </tr>
                        <tr>
                            <th colspan="2" class="text-right">Order Total</th>
                            <td class="font-weight-bold">$<?= $this->orderTotal ?></td>
                        </tr>
                    </table>
                </div>

                <div class="ds-block">
                    <a href="/profile" class="btn btn-primary">Back to profile</a>
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

</body>
</html>