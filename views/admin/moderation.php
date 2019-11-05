<?php
use Destiny\Common\Utils\Tpl;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?=Tpl::title($this->title)?>
    <?php include 'seg/meta.php' ?>
    <?=Tpl::manifestLink('web.css')?>
</head>
<body id="admin" class="no-contain">
<div id="page-wrap">

    <?php include 'seg/nav.php' ?>
    <?php include 'seg/admin.nav.php' ?>

    <section class="container">
        <h3 id="income-dates">
            <span id="date-selector">
                <a href='#'><i class='fas fa-arrow-left'></i></a> <span class='date'></span> <a href='#'><i class='fas fa-arrow-right'></i></a>
            </span>
        </h3>
        <div class="row" id="moderation-graphs">
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div id="graph4">
                    <div class="graph-outer">
                        <canvas height="250"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12">
                <div id="graph1">
                    <div class="graph-outer">
                        <canvas height="400"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12">
                <div id="graph2">
                    <div class="graph-outer">
                        <canvas height="400"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-12 col-sm-12">
                <div id="graph3">
                    <div class="graph-outer">
                        <canvas height="400"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </section>

</div>

<?php include 'seg/alerts.php' ?>
<?php include 'seg/foot.php' ?>
<?php include 'seg/tracker.php' ?>
<?=Tpl::manifestScript('runtime.js')?>
<?=Tpl::manifestScript('common.vendor.js')?>
<?=Tpl::manifestScript('web.js')?>
<?=Tpl::manifestScript('admin.js')?>

</body>
</html>