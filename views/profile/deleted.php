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
<body id="profile" class="error no-brand">
<div id="page-wrap">

    <?php include 'seg/nav.php' ?>
    <?php include 'seg/banner.php' ?>

    <section id="error-container" class="container">
        <div class="mt-3 mb-3" style="text-align: center;">
            <h1 class="display-1">Account Delete</h1>
            <p class="lead">
                Your account has been disabled and marked for deletion.<br />
                Thank you for your support. Good luck to you!
            </p>
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