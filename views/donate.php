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

    <section class="container">

        <h1 class="title" style="display: flex; align-items: center;">
            <span>Donate</span>
            <small>&nbsp;(send a message too)</small>
            <span style="flex: 2; display: flex; justify-content: flex-end; align-items: center;">
                <small style="font-size: 16px; font-weight: normal;">prefer? &nbsp;</small>
                <a href="https://streamlabs.com/destiny" class="streamlabs-logo"></a>
            </span>
        </h1>

        <?php include 'seg/alerts.php' ?>

        <div class="content content-dark clearfix">
            <form id="donate-form" class="validate" action="/donate" method="post">
                <div class="ds-block">
                    <div id="donation-username" class="form-group form-group-lg">
                        <input class="form-control required" id="username" name="username" placeholder="Enter a username ..." value="<?=Tpl::out($this->username)?>" autofocus />
                    </div>
                </div>
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
                    <button class="btn btn-primary btn-lg"><span class="fa fa-shopping-cart"></span> Continue</button>
                    <a href="/" class="btn btn-link">Cancel</a>
                    <p class="agreement">
                        <span>By clicking the &quot;Continue&quot; button, you are confirming that this purchase is what you wanted and that you have read the <a href="/agreement">user agreement</a>.</span>
                    </p>
                </div>
            </form>
        </div>

    </section>

</div>
<?php include 'seg/foot.php' ?>
<?php include 'seg/tracker.php' ?>
<script src="<?=Config::cdnv()?>/web.js"></script>
</body>
</html>