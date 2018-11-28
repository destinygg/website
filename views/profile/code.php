<?php
namespace Destiny;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Config;
?>
<!DOCTYPE html>
<html>
<head>
    <?=Tpl::title($this->title)?>
    <?php include 'seg/meta.php' ?>
    <?=Tpl::manifestLink('common.vendor.css')?>
    <?=Tpl::manifestLink('web.css')?>
</head>
<body id="code" class="no-brand">
<div id="page-wrap">

    <?php include 'seg/nav.php' ?>

    <section class="container">

        <h1 class="title">
            <span>Success!</span>
            <small>You have successfully authorized yourself</small>
        </h1>

        <?php if(!empty($this->error)): ?>
            <div class="alert alert-danger">
                <strong>Error!</strong>
                <?=Tpl::out($this->error->getMessage())?>
            </div>
        <?php endif ?>

        <div class="content content-dark clearfix">
            <div class="ds-block">
                <p>Now you must retrieve the access token <strong>/oauth/token</strong></p>
                <p>Alternatively you can exchange the token, for a login session</p>
            </div>
            <div class="ds-block">
                <img src="https://chart.googleapis.com/chart?chs=300x300&choe=UTF-8&cht=qr&chl=<?=urlencode($this->token)?>" title="Link to Google.com" />
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