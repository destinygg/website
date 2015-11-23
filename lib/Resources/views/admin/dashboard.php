<?php
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Config;
use Destiny\Common\Utils\Date;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($model->title)?></title>
<meta charset="utf-8">
<?php include Tpl::file('seg/commontop.php') ?>
</head>
<body id="admin" class="thin">

    <?php include Tpl::file('seg/top.php') ?>

    <?php include Tpl::file('seg/admin.nav.php') ?>

    <section class="container">
        <h3>Latest Broadcasts</h3>
        <div class="content content-dark clearfix">
            <?php if(!empty($model->broadcasts)): ?>
            <div id="broadcast-context" class="ds-block">
                <ul class="unstyled" style="padding: 0;">
                    <?php foreach($model->broadcasts as $line): ?>
                        <li style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            <small class="subtle"><?=Tpl::moment(Date::getDateTime($line['timestamp']), Date::STRING_FORMAT, 'h:mm:ss')?></small>
                            <span><?=Tpl::out($line['data'])?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            <div></div>
        </div>
    </section>

    <section class="container">
        <div class="row">
            <div class="col-lg-4 col-md-6 col-sm-12 card">
                <div id="graph1" class="card-inner">
                    <h4></h4>
                    <div class="graph-outer">
                        <canvas></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12 card">
                <div id="graph2" class="card-inner">
                    <h4></h4>
                    <div class="graph-outer">
                        <canvas></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-12 col-sm-12 card">
                <div id="graph3" class="card-inner">
                    <h4></h4>
                    <div class="graph-outer">
                        <canvas></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-12 col-sm-12 card">
                <div id="graph4" class="card-inner">
                    <h4></h4>
                    <div class="graph-outer">
                        <div class="graph-legend">
                            <span class="t1"><i class="tier-block"></i> Tier 1</span>
                            <span class="t2"><i class="tier-block"></i> Tier 2</span>
                            <span class="t3"><i class="tier-block"></i> Tier 3</span>
                            <span class="t4"><i class="tier-block"></i> Tier 4</span>
                        </div>
                        <canvas style="height: 300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="container">
        <div class="alert alert-danger" style="margin:0;">
            <strong>Note!</strong>
            Data shown here does NOT take into account fees and taxes.
        </div>
    </section>

    <br /><br />

    <?php include Tpl::file('seg/commonbottom.php') ?>
    <script src="<?=Config::cdnv()?>/web/js/admin.js"></script>

<script>
(function($){

    (function(){
        var days = 14,
            graph = $('#graph1'),
            label = "Revenue Last "+ days +" Days";
        $(graph).find('h4').text(label);
        $.ajax({
            url: '/admin/chart/RevenueLastXDays.json?days='+days,
            success: function(data){
                data = GraphUtil.prepareGraphData(data, 'sum', days, 'days');
                var canvas = $(graph).find('canvas').get(0);
                new Chart(canvas.getContext("2d")).Line({
                    labels: data.labels,
                    datasets:[$.extend({
                        label: label,
                        data: data.data
                    }, GraphUtil.defaultDataSet)]
                }, $.extend({}, GraphUtil.defaultGraph, {
                    scaleLabel: GraphUtil.formatCurrency
                }));
            }
        })
    })();

    (function(){
        var months = 12,
            graph = $('#graph2'),
            label = "Revenue Last "+ months +" Months";
        $(graph).find('h4').text(label);
        $.ajax({
            url: '/admin/chart/RevenueLastXMonths.json?months='+months,
            success: function(data){
                data = GraphUtil.prepareGraphData(data, 'sum', months, 'months');
                var canvas = $(graph).find('canvas').get(0);
                new Chart(canvas.getContext("2d")).Line({
                    labels: data.labels,
                    datasets:[$.extend({
                        label: label,
                        data: data.data
                    }, GraphUtil.defaultDataSet)]
                }, $.extend({}, GraphUtil.defaultGraph, {
                    scaleLabel: GraphUtil.formatCurrency
                }));
            }
        })
    })();

    (function(){
        var years = 5,
            graph = $('#graph3'),
            label = "Revenue Last "+ years +" Years";
        $(graph).find('h4').text(label);
        $.ajax({
            url: '/admin/chart/RevenueLastXYears.json?years='+years,
            success: function(data){
                data = GraphUtil.prepareGraphData(data, 'sum', years, 'years');
                var canvas = $(graph).find('canvas').get(0);
                new Chart(canvas.getContext("2d")).Line({
                    labels: data.labels,
                    datasets:[$.extend({
                        label: label,
                        data: data.data
                    }, GraphUtil.defaultDataSet)]
                }, $.extend({}, GraphUtil.defaultGraph, {
                    scaleLabel: GraphUtil.formatCurrency
                }));
            }
        })
    })();

    (function(){
        var days = 30,
            graph = $('#graph4'),
            label = "Subscriptions Last "+ days +" Days";
        $(graph).find('h4').text(label);
        $.ajax({
            url: '/admin/chart/NewTieredSubscribersLastXDays.json?days='+days,
            success: function(data){
                var dataSet1 = [],
                    dataSet2 = [],
                    dataSet3 = [],
                    dataSet4 = [],
                    dataLabels = [],
                    dates = [],
                    a = moment({hour: 1}).subtract(days, 'days'),
                    b = moment({hour: 1});
                for (var m = a; m.isBefore(b) || m.isSame(b); m.add(1, 'days')) {
                    dates.push(m.format('YYYY-MM-DD'));
                    dataLabels.push(m.format('MM/D'));
                    dataSet1.push(0);
                    dataSet2.push(0);
                    dataSet3.push(0);
                    dataSet4.push(0);
                }
                for(var i=0; i<data.length; ++i){
                    var x = dates.indexOf(data[i].date);
                    if(x != -1){
                        switch(data[i]['subscriptionTier']){
                            case "1":
                                dataSet1[x] = data[i]['total'];
                                break;
                            case "2":
                                dataSet2[x] = data[i]['total'];
                                break;
                            case "3":
                                dataSet3[x] = data[i]['total'];
                                break;
                            case "4":
                                dataSet4[x] = data[i]['total'];
                                break;
                        }
                    }
                }
                var canvas = $(graph).find('canvas').get(0);
                new Chart(canvas.getContext("2d")).Line({
                    labels: dataLabels,
                    datasets:[
                        $.extend({}, GraphUtil.defaultDataSet, {
                            label: "Tier 1",
                            data: dataSet1,
                            fillColor: "rgba(0,0,220,0.1)",
                            strokeColor: "rgba(0,0,220,1)",
                            pointColor: "rgba(0,0,220,1)",
                            pointStrokeColor: "#fff",
                            pointHighlightFill: "#fff",
                            pointHighlightStroke: "rgba(0,0,220,1)"
                        }),
                        $.extend({}, GraphUtil.defaultDataSet, {
                            label: "Tier 2",
                            data: dataSet2,
                            fillColor: "rgba(0,220,0,0.1)",
                            strokeColor: "rgba(0,220,0,1)",
                            pointColor: "rgba(0,220,0,1)",
                            pointStrokeColor: "#fff",
                            pointHighlightFill: "#fff",
                            pointHighlightStroke: "rgba(0,220,0,1)"
                        }),
                        $.extend({}, GraphUtil.defaultDataSet, {
                            label: "Tier 3",
                            data: dataSet3,
                            fillColor: "rgba(220,0,0,0.1)",
                            strokeColor: "rgba(220,0,0,1)",
                            pointColor: "rgba(220,0,0,1)",
                            pointStrokeColor: "#fff",
                            pointHighlightFill: "#fff",
                            pointHighlightStroke: "rgba(220,0,0,1)"
                        }),
                        $.extend({}, GraphUtil.defaultDataSet, {
                            label: "Tier 4",
                            data: dataSet4,
                            fillColor: "rgba(220,0,220,0.1)",
                            strokeColor: "rgba(220,0,220,1)",
                            pointColor: "rgba(220,0,220,1)",
                            pointStrokeColor: "#fff",
                            pointHighlightFill: "#fff",
                            pointHighlightStroke: "rgba(220,0,220,1)"
                        })
                    ]
                }, GraphUtil.defaultGraph);
            }
        })
    })();

})(jQuery);
</script>

</body>
</html>