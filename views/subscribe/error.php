<?php
namespace Destiny;
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
<body id="subscription-error" class="no-brand">
<div id="page-wrap">

    <?php include 'seg/nav.php' ?>
    <?php include 'seg/banner.php' ?>

    <section class="container">

        <h1 class="title">
            <span>Subscribe</span> <small>become one of the brave</small>
        </h1>

        <?php include 'seg/alerts.php' ?>

        <div class="content content-dark clearfix">

            <div class="ui-step-legend-wrap clearfix">
                <div class="ui-step-legend clearfix">
                    <ul>
                        <li style="width: 25%;"><a>Select a subscription</a></li>
                        <li style="width: 25%;"><a>Confirmation</a></li>
                        <li style="width: 25%;"><a>Pay subscription</a></li>
                        <li style="width: 25%;"><a>Complete</a></li>
                    </ul>
                </div>
            </div>

            <div class="ds-block">

                <p>An error has occurred during the subscription process.

                    <?php if(!empty($this->subscription)): ?>
                        <br />Your reference is #<?=Tpl::out($this->subscription['subscriptionId'])?>
                    <?php endif ?>

                    <br>Please start again or email <a href="mailto:<?=Config::$a['support_email']?>"><?=Config::$a['support_email']?></a> for queries.
                </p>

            </div>

            <div class="form-actions">
                <a href="/subscribe" class="btn btn-dark">Subscriptions</a>
                <a href="/profile" class="btn btn-dark">Back to profile</a>
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