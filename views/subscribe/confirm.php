<?php
namespace Destiny;
use Destiny\Common\Utils\Tpl;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?=Tpl::title($this->title)?>
    <?php include 'seg/meta.php' ?>
    <?=Tpl::manifestLink('web.css')?>
</head>
<body id="subscription-confirm" class="no-brand">
<div id="page-wrap">

    <?php include 'seg/nav.php' ?>
    <?php include 'seg/banner.php' ?>

    <section class="container">

        <h1 class="title">
            <span>Subscribe</span> <small>confirm your selection</small>
        </h1>

        <div class="content content-dark clearfix">

            <?php if(!empty($this->warning)): ?>
                <div class="alert alert-danger alert-dismissable" style="margin: 15px 15px 0 15px;">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">&times;</button>
                    <strong><i class="fas fa-exclamation-triangle"></i> Warning!</strong>
                    <?=Tpl::out($this->warning->getMessage())?>
                </div>
            <?php endif ?>

            <div style="width: 100%;" class="clearfix stream">
                <form id="subscribe-form" action="/subscription/create" method="post">

                    <input type="hidden" name="subscription" value="<?= $this->subscriptionType['id'] ?>">
                    <input type="hidden" name="gift" value="<?= $this->gift ?>">

                    <div class="subscription-tier ds-block">
                        <div class="subscription">
                            <h2><?=$this->subscriptionType['tierLabel']?></h2>
                            <?php if(!empty($this->gift)): ?>
                                <p><i class="fas fa-gift"></i> You are gifting this to <span class="badge badge-danger"><?=Tpl::out($this->gift)?></span></p>
                            <?php endif ?>
                            <p><span class="sub-amount">$<?=$this->subscriptionType['amount']?></span> (<?=$this->subscriptionType['billingFrequency']?> <?=strtolower($this->subscriptionType['billingPeriod'])?>)</p>
                        </div>
                    </div>

                    <div class="ds-block text-message">
                        <div>Send a message with your subscription (optional):</div>
                        <textarea name="sub-message" autocomplete="off" maxlength="250" rows="3" class="form-control" placeholder=""></textarea>
                    </div>

                    <div class="ds-block">
                        <div class="checkbox">
                            <label for="renew">
                                <span><input id="renew" type="checkbox" name="renew" value="1" /> <strong>Recurring subscription</strong></span>
                                <small>Automatically bill every <?=$this->subscriptionType['billingFrequency']?> <?=strtolower($this->subscriptionType['billingPeriod'])?>(s)</small>
                            </label>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-shopping-cart"></i> Continue</button>
                        <a href="/subscribe" class="btn btn-link">Cancel</a>
                        <p class="agreement">
                            <span>By clicking the &quot;Continue&quot; button, you are confirming that this purchase is what you wanted and that you have read the <a href="/agreement">user agreement</a>.</span>
                        </p>
                    </div>

                </form>
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