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
        <div class="row">
            <div class="col-xxl-3 col-lg-6 col-md-12">
                <div>
                    <h4>Tier I</h4>
                    <table>
                        <colgroup>
                            <col>
                            <col>
                            <col>
                            <col>
                        </colgroup>
                        <tr>
                            <th></th>
                            <th>Not Recurring</th>
                            <th>Recurring</th>
                            <th>Total</th>
                        </tr>
                        <tr>
                            <th>1 Month</th>
                            <td data-sub-type="1-MONTH-SUB" data-recurring="0">0</td>
                            <td data-sub-type="1-MONTH-SUB" data-recurring="1">0</td>
                            <td>0</td>
                        </tr>
                        <tr>
                            <th>3 Month</th>
                            <td data-sub-type="3-MONTH-SUB" data-recurring="0">0</td>
                            <td data-sub-type="3-MONTH-SUB" data-recurring="1">0</td>
                            <td>0</td>
                        </tr>
                        <tr>
                            <th>Total</th>
                            <td>0</td>
                            <td>0</td>
                            <td>0</td>
                        </tr>
                    </table>
                    <canvas data-tier="1"></canvas>
                </div>
            </div>
            <div class="col-xxl-3 col-lg-6 col-md-12">
                <div>
                    <h4>Tier II</h4>
                    <table>
                        <colgroup>
                            <col>
                            <col>
                            <col>
                            <col>
                        </colgroup>
                        <tr>
                            <th></th>
                            <th>Not Recurring</th>
                            <th>Recurring</th>
                            <th>Total</th>
                        </tr>
                        <tr>
                            <th>1 Month</th>
                            <td data-sub-type="1-MONTH-SUB2" data-recurring="0">0</td>
                            <td data-sub-type="1-MONTH-SUB2" data-recurring="1">0</td>
                            <td>0</td>
                        </tr>
                        <tr>
                            <th>3 Month</th>
                            <td data-sub-type="3-MONTH-SUB2" data-recurring="0">0</td>
                            <td data-sub-type="3-MONTH-SUB2" data-recurring="1">0</td>
                            <td>0</td>
                        </tr>
                        <tr>
                            <th>Total</th>
                            <td>0</td>
                            <td>0</td>
                            <td>0</td>
                        </tr>
                    </table>
                    <canvas data-tier="2"></canvas>
                </div>
            </div>
            <div class="col-xxl-3 col-lg-6 col-md-12">
                <div>
                    <h4>Tier III</h4>
                    <table>
                        <colgroup>
                            <col>
                            <col>
                            <col>
                            <col>
                        </colgroup>
                        <tr>
                            <th></th>
                            <th>Not Recurring</th>
                            <th>Recurring</th>
                            <th>Total</th>
                        </tr>
                        <tr>
                            <th>1 Month</th>
                            <td data-sub-type="1-MONTH-SUB3" data-recurring="0">0</td>
                            <td data-sub-type="1-MONTH-SUB3" data-recurring="1">0</td>
                            <td>0</td>
                        </tr>
                        <tr>
                            <th>3 Month</th>
                            <td data-sub-type="3-MONTH-SUB3" data-recurring="0">0</td>
                            <td data-sub-type="3-MONTH-SUB3" data-recurring="1">0</td>
                            <td>0</td>
                        </tr>
                        <tr>
                            <th>Total</th>
                            <td>0</td>
                            <td>0</td>
                            <td>0</td>
                        </tr>
                    </table>
                    <canvas data-tier="3"></canvas>
                </div>
            </div>
            <div class="col-xxl-3 col-lg-6 col-md-12">
                <div>
                    <h4>Tier IV</h4>
                    <table>
                        <colgroup>
                            <col>
                            <col>
                            <col>
                            <col>
                        </colgroup>
                        <tr>
                            <th></th>
                            <th>Not Recurring</th>
                            <th>Recurring</th>
                            <th>Total</th>
                        </tr>
                        <tr>
                            <th>1 Month</th>
                            <td data-sub-type="1-MONTH-SUB4" data-recurring="0">0</td>
                            <td data-sub-type="1-MONTH-SUB4" data-recurring="1">0</td>
                            <td>0</td>
                        </tr>
                        <tr>
                            <th>3 Month</th>
                            <td data-sub-type="3-MONTH-SUB4" data-recurring="0">0</td>
                            <td data-sub-type="3-MONTH-SUB4" data-recurring="1">0</td>
                            <td>0</td>
                        </tr>
                        <tr>
                            <th>Total</th>
                            <td>0</td>
                            <td>0</td>
                            <td>0</td>
                        </tr>
                    </table>
                    <canvas data-tier="4"></canvas>
                </div>
            </div>
        </div>
        <h3 id="income-dates">
            <span id="date-selector">
                <a href='#'><i class='fas fa-arrow-left'></i></a> <span class='date'></span> <a href='#'><i class='fas fa-arrow-right'></i></a>
            </span>
        </h3>
        <div class="row" id="income-graphs">
            <div class="col-md-6 col-sm-12">
                <div id="graph4">
                    <div class="graph-outer">
                        <canvas height="350"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-sm-12">
                <div id="graph5">
                    <div class="graph-outer">
                        <canvas height="350"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12">
                <div id="graph1">
                    <div class="graph-outer">
                        <canvas height="300"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12">
                <div id="graph2">
                    <div class="graph-outer">
                        <canvas height="300"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-12 col-sm-12">
                <div id="graph3">
                    <div class="graph-outer">
                        <canvas height="300"></canvas>
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