<?php
namespace Destiny;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Config;
?>
<!DOCTYPE html>
<html>
<head>
    <title><?=Tpl::title($this->title)?></title>
    <meta name="description" content="<?=Config::$a['meta']['description']?>">
    <meta name="keywords" content="<?=Config::$a['meta']['keywords']?>">
    <meta name="author" content="<?=Config::$a['meta']['author']?>">
    <?php include 'seg/meta.php' ?>
    <link href="<?=Config::cdnv()?>/web.css" rel="stylesheet" media="screen">
</head>
<body id="donate" class="no-brand">
<div id="page-wrap">

    <?php include 'seg/nav.php' ?>
    <?php include 'seg/banner.php' ?>

    <section class="container">

        <h1 class="title">
            <span>Donate</span> <small>send a message too</small>
        </h1>

        <?php include 'seg/alerts.php' ?>

        <div class="content content-dark clearfix">
            <div class="ui-step-legend-wrap clearfix">
                <div class="ui-step-legend clearfix">
                    <ul>
                        <li class="active"><a>Setup</a><i class="arrow"></i></li>
                        <li><a>Payment</a></li>
                        <li><a>Complete</a></li>
                    </ul>
                </div>
            </div>

            <form id="donate-form" class="validate" action="/donate" method="post">
                <div class="ds-block">
                    <p>Donation amount</p>
                    <div id="donation-amount" class="form-group form-group-lg">
                        <label class="sr-only" for="amount">Amount (in dollars)</label>
                        <input class="form-control required number" id="amount" name="amount" placeholder="Enter an amount ..." autofocus />
                        <div id="donation-amount-currency">$</div>
                    </div>
                </div>
                <div class="ds-block text-message">
                    <div>Send a message with your donation (optional):</div>
                    <textarea name="message" autocomplete="off" maxlength="250" rows="5" class="form-control" placeholder="Write a message ..."></textarea>
                </div>
                <div class="form-actions">
                    <a class="pull-right powered-paypal" title="Powered by Paypal" href="https://www.paypal.com" target="_blank">Paypal</a>
                    <button class="btn btn-primary btn-lg"><span class="fa fa-shopping-cart"></span> Donate</button>
                    <a href="/" class="btn btn-link">Cancel</a>
                    <p class="agreement">
                        <span>By clicking the &quot;Donate&quot; button, you are confirming that this purchase is what you wanted and that you have read the <a href="/agreement">user agreement</a>.</span>
                    </p>
                </div>
            </form>
        </div>

    </section>

    <?php include 'seg/panel.ads.php' ?>
</div>
<?php include 'seg/foot.php' ?>
<?php include 'seg/tracker.php' ?>
<script src="<?=Config::cdnv()?>/web.js"></script>
</body>
</html>