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
                <span>User agreement</span>
            </h1>
            <div class="content content-dark clearfix">
                <div class="ds-block">

                    <h3>Your Acknowledgement</h3>
                    <p>Any data stored in this website has the potential to be accessed by a 3rd party through some unforeseen security breach. We do our best to maintain security.</p>

                    <h3>Cookies</h3>
                    <p>When you have a logged in session with the website, a cookie is used to track who you are; it contains a simple unique string used to identify your requests. There is no way around this.</p>
                    <p>If a 3rd person gets a hold of this cookie, they can imitate your user, as well as gain access to your account. It is as secure as your browser is.</p>

                    <h3>Local Storage</h3>
                    <p>We use local storage to hold data that we deem of low security. Things like your chat ignore list.</p>
                    <p>These are as secure as you make your browser and we are not responsible for its privacy.</p>

                    <h3>IP Addresses</h3>
                    <p>Your IP address is recorded for a time period with your session; and is accessible to admins of the website.</p>

                    <h3>Reserved Rights</h3>
                    <p>We reserve the right to block or delete your account by any means we feel appropriate.</p>
                    <p>We reserve the right to not issue refunds on subscriptions or donations when deemed appropriate.</p>
                    <p>We reserve the right to respond or not respond, within whatever time frame we can afford, to questions and or bug reports regarding this website.</p>

                    <h3>Rules</h3>
                    <p>Do not attempt to gain access to data or user accounts that are not yours.</p>
                    <p>Do not create user profiles using automated code.</p>
                    <p>Do not try to impersonate anyone.</p>

                </div>
            </div>
        </section>
        
        <?php include Tpl::file('seg/panel.ads.php') ?>
    </div>

    <?php include Tpl::file('seg/foot.php') ?>
    <?php include Tpl::file('seg/commonbottom.php') ?>
</body>
</html>