<?php
namespace Destiny;
use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Config;
?>
<!DOCTYPE html>
<html>
<head>
    <title><?=Tpl::title($this->title)?></title>
    <?php include 'seg/meta.php' ?>
    <link href="<?=Config::cdnv()?>/web.css" rel="stylesheet" media="screen">
</head>
<body id="donations" class="no-contain">
<div id="page-wrap">

    <div id="alerts-container"></div>
    <?php include 'seg/nav.php' ?>
    <?php include 'seg/alerts.php' ?>
    <?php include 'menu.php' ?>
    <?php include 'profile/userinfo.php' ?>

    <section class="container">

        <h3 data-toggle="collapse" data-target="#donations-content">Donations</h3>
        <div id="donations-content" class="content collapse in">
            <div class="content-dark clearfix">

                <?php if(!empty($this->donations)): ?>
                    <table class="grid">
                        <thead>
                        <tr>
                            <td>Amount</td>
                            <td>Created</td>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach($this->donations as $donation): ?>
                            <tr>
                                <td><?=Tpl::out($donation['currency'])?> <?=Tpl::out($donation['amount'])?></td>
                                <td><?=Tpl::moment(Date::getDateTime($donation['timestamp']), Date::STRING_FORMAT_YEAR)?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="ds-block">
                        <p>No donations</p>
                    </div>
                <?php endif ?>

            </div>
        </div>

    </section>
</div>

<?php include 'seg/foot.php' ?>
<?php include 'seg/tracker.php' ?>
<script src="<?=Config::cdnv()?>/web.js"></script>

</body>
</html>
