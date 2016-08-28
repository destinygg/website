<?php
namespace Destiny;
use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\Tpl;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($model->title)?></title>
<meta charset="utf-8">
<?php include Tpl::file('seg/opengraph.php') ?>
<?php include Tpl::file('seg/commontop.php') ?>
<?php include Tpl::file('seg/google.tracker.php') ?>
</head>
<body id="agreement" class="no-brand">
    <div id="page-wrap">

        <?php include Tpl::file('seg/top.php') ?>
        <?php include Tpl::file('seg/headerband.php') ?>
        
        <section class="container">
            <h1 class="title">
                <small class="subtle pull-right" style="font-size:14px; margin-top:20px;">Last update: <?=Date::getDateTime(filemtime(__FILE__))->format(Date::STRING_FORMAT)?></small>
                <h1>Disclaimer</h1>
            </h1>
            <div class="content content-dark clearfix">
                <div class="ds-block">
                    <h4>Cookies</h4>
                    <p>When you are signed in, a <a target="_blank" href="https://en.wikipedia.org/wiki/HTTP_cookie">cookie</a> is used to track who you are; it contains a simple unique string used to identify your requests.</p>
                    <p>If a 3rd person gets a hold of this cookie, they can gain access to your account. It is as secure as your browser is.</p>
                    <h4>Local Storage</h4>
                    <p>We use local storage to hold data that we deem low security.</p>
                    <h4>IP Addresses</h4>
                    <p>Your IP address is recorded with your chat messages for a time period; it is accessible to admins of the website.</p>
                </div>
            </div>
            <h2>User Agreement</h2>
            <div class="content content-dark clearfix">
                <div class="ds-block">
                    <p>You must not attempt to gain access to data or user accounts that are not yours.</p>
                    <p>You must not create user profiles using automated code.</p>
                </div>
            </div>
            <h2>Rights Reserved</h2>
            <div class="content content-dark clearfix">
                <div class="ds-block">
                    <p>We may change these and others rules on the website.</p>
                    <p>We may block or delete your account when appropriate.</p>
                    <p>We may or may not issue refunds on subscriptions or donations.</p>
                    <p>We may or may not respond to questions and or bug reports regarding this website, within whatever time frame we can afford.</p>
                </div>
            </div>
            <br /><br />
        </section>
    </div>

    <?php include Tpl::file('seg/foot.php') ?>
    <?php include Tpl::file('seg/commonbottom.php') ?>
</body>
</html>