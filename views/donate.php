<?php
namespace Destiny;
use Destiny\Common\Session\Session;
use Destiny\Common\User\UserRole;
use Destiny\Common\Utils\Tpl;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?=Tpl::title($this->title)?>
    <?php include 'seg/meta.php' ?>
    <?=Tpl::manifestLink('web.css')?>
</head>
<body id="donate">
<div id="page-wrap">

    <?php include 'seg/nav.php' ?>

    <section class="container">

        <h1 class="title" style="display: flex; align-items: center;">
            <span>Donate</span>
            <small>&nbsp;(send a message too)</small>
            <span style="flex: 2; display: flex; justify-content: flex-end; align-items: center;">
                <small>prefer? &nbsp;</small>
                <a href="https://streamlabs.com/destiny" class="streamlabs-logo"></a>
            </span>
        </h1>

        <div class="content content-dark clearfix">
            <form id="donateform" class="validate" action="/donate" method="post">
                <?php if(!Session::hasRole(UserRole::USER)): ?>
                    <div class="ds-block">
                        <div id="donation-username" class="form-group form-group-lg">
                            <input class="form-control required" id="username" name="username" placeholder="Enter a username ..." value="<?=Tpl::out($this->username)?>" autofocus />
                        </div>
                    </div>
                <?php else: ?>
                    <div class="ds-block">
                        <div id="donation-username" class="form-group form-group-lg">
                            <input readonly="readonly" class="form-control" value="<?=Tpl::out($this->username)?>" autofocus />
                        </div>
                    </div>
                <?php endif; ?>
                <div class="ds-block">
                    <div id="donation-amount" class="form-group form-group-lg">
                        <label class="sr-only" for="amount">Amount (in dollars)</label>
                        <input class="form-control required number" id="amount" name="amount" placeholder="5.00" autofocus />
                        <div id="donation-amount-currency">$</div>
                    </div>
                </div>
                <div class="ds-block text-message">
                    <textarea name="message" autocomplete="off" maxlength="200" rows="3" class="form-control" placeholder="Write a message ..."></textarea>
                </div>
                <div class="form-actions">
                    <button class="btn btn-primary btn-lg"><i class="fas fa-shopping-cart"></i> Continue</button>
                    <a href="/" class="btn btn-dark">Cancel</a>
                </div>
            </form>
        </div>

        <p class="agreement">By clicking the &quot;Continue&quot; button, you are confirming that this purchase is what you wanted and that you have read the <a href="/agreement">user agreement</a>.</p>

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