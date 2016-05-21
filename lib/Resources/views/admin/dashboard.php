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
        <div class="row">
            <div class="col-md-12 col-sm-12 card">
                <div id="graph4" class="card-inner">
                    <h4></h4>
                    <div class="graph-outer">
                        <canvas height="400"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12 card">
                <div id="graph1" class="card-inner">
                    <div class="graph-outer">
                        <canvas height="300"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12 card">
                <div id="graph2" class="card-inner">
                    <div class="graph-outer">
                        <canvas height="300"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-12 col-sm-12 card">
                <div id="graph3" class="card-inner">
                    <div class="graph-outer">
                        <canvas height="300"></canvas>
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
        var graph = $('#graph4');
        var currDate = moment();
        var title = $(graph).find('h4');
        var ctx = $(graph).find('canvas').get(0).getContext("2d");
        var currChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [],
                datasets: []
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                scales: {
                    xAxes: [{
                        stacked: true
                    }],
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        },
                        stacked: true
                    }]
                }
            }
        });

        title
            .on('click', '.fa-arrow-left', function(){
                console.log(currDate.subtract(1, 'months'));
                updateGraph(currDate);
            })
            .on('click', '.fa-arrow-right', function(){
                console.log(currDate.add(1, 'months'));
                updateGraph(currDate);
            });

        var updateGraph = function(selectedDate){
            var fromDate = moment(selectedDate.format('YYYY-MM-DD')).startOf('month'),
                toDate = moment(selectedDate.format('YYYY-MM-DD')).endOf('month');

            title.html("Subscriptions <a href='#'><i class='fa fa-arrow-left'></i></a> " + toDate.format('MMMM YYYY') + " <a href='#'><i class='fa fa-arrow-right'></i></a>");
            $.ajax({
                url: '/admin/chart/NewTieredSubscribersLastXDays.json?fromDate='+ fromDate.format('YYYY-MM-DD') +'&toDate='+ toDate.format('YYYY-MM-DD'),
                success: function(data){
                    var dataSet1 = [],
                        dataSet2 = [],
                        dataSet3 = [],
                        dataSet4 = [],
                        dataLabels = [],
                        dates = [];
                    for (var m = fromDate; m.isBefore(toDate) || m.isSame(toDate); m.add(1, 'days')) {
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
                                    dataSet1[x] = parseInt(data[i]['total']);
                                    break;
                                case "2":
                                    dataSet2[x] = parseInt(data[i]['total']);
                                    break;
                                case "3":
                                    dataSet3[x] = parseInt(data[i]['total']);
                                    break;
                                case "4":
                                    dataSet4[x] = parseInt(data[i]['total']);
                                    break;
                            }
                        }
                    }

                    currChart.data.labels = dataLabels;
                    currChart.data.datasets = [
                        {
                            label: "Tier 1",
                            data: dataSet1,
                            borderWidth: 0.4,
                            backgroundColor: "rgba(51, 122, 183,0.6)",
                            borderColor: "rgba(51, 122, 183,1)",
                            pointBorderColor: "rgba(51, 122, 183,1)",
                            pointBackgroundColor: "rgba(51, 122, 183,1)"
                        },
                        {
                            label: "Tier 2",
                            data: dataSet2,
                            borderWidth: 0.4,
                            backgroundColor: "rgba(0,220,0,0.6)",
                            borderColor: "rgba(0,220,0,1)",
                            pointBorderColor: "rgba(0,220,0,1)",
                            pointBackgroundColor: "#fff"
                        },
                        {
                            label: "Tier 3",
                            data: dataSet3,
                            borderWidth: 0.4,
                            backgroundColor: "rgba(220,0,0,0.6)",
                            borderColor: "rgba(220,0,0,1)",
                            pointBorderColor: "rgba(220,0,0,1)",
                            pointBackgroundColor: "rgba(220,0,0,1)"
                        },
                        {
                            label: "Tier 4",
                            data: dataSet4,
                            borderWidth: 0.4,
                            backgroundColor: "rgba(220,0,220,0.6)",
                            borderColor: "rgba(220,0,220,1)",
                            pointBorderColor: "rgba(220,0,220,1)",
                            pointBackgroundColor: "rgba(220,0,220,1)"
                        }
                    ];
                    currChart.update();
                }
            });
        };

        updateGraph(currDate);

    })();

    (function(){
        var days = 14,
            graph = $('#graph1'),
            label = "Revenue Last "+ days +" Days";
        $(graph).find('h4').text(label);
        $.ajax({
            url: '/admin/chart/RevenueLastXDays.json?days='+days,
            success: function(data){
                data = GraphUtil.prepareGraphData(data, 'sum', days, 'days');
                var ctx = $(graph).find('canvas').get(0).getContext("2d");
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets:[{
                            label: label,
                            borderWidth: 0.4,
                            backgroundColor: "rgba(220,220,220,0.2)",
                            borderColor: "rgba(220,220,220,1)",
                            pointBorderColor: "rgba(220,220,220,1)",
                            pointBackgroundColor: "#fff",
                            pointBorderWidth: 1,
                            data: data.data
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        tooltips: {
                            callbacks: {
                                label: function(tooltipItem, data) {
                                    return GraphUtil.formatCurrency(tooltipItem.yLabel);
                                }
                            }
                        },
                        scales: {
                            yAxes: [{
                                ticks: {
                                    beginAtZero: true,
                                    callback: GraphUtil.formatCurrency
                                }
                            }]
                        }
                    }
                });
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
                var ctx = $(graph).find('canvas').get(0).getContext("2d");
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets:[{
                            label: label,
                            borderWidth: 0.4,
                            backgroundColor: "rgba(220,220,220,0.2)",
                            borderColor: "rgba(220,220,220,1)",
                            pointBorderColor: "rgba(220,220,220,1)",
                            pointBackgroundColor: "#fff",
                            pointBorderWidth: 1,
                            data: data.data
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        tooltips: {
                            callbacks: {
                                label: function(tooltipItem, data) {
                                    return GraphUtil.formatCurrency(tooltipItem.yLabel);
                                }
                            }
                        },
                        scales: {
                            yAxes: [{
                                ticks: {
                                    beginAtZero: true,
                                    callback: GraphUtil.formatCurrency
                                }
                            }]
                        }
                    }
                });
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
                var ctx = $(graph).find('canvas').get(0).getContext("2d");
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets:[{
                            label: label,
                            borderWidth: 0.4,
                            backgroundColor: "rgba(220,220,220,0.2)",
                            borderColor: "rgba(220,220,220,1)",
                            pointBorderColor: "rgba(220,220,220,1)",
                            pointBackgroundColor: "#fff",
                            pointBorderWidth: 1,
                            data: data.data
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        tooltips: {
                            callbacks: {
                                label: function(tooltipItem, data) {
                                    return GraphUtil.formatCurrency(tooltipItem.yLabel);
                                }
                            }
                        },
                        scales: {
                            yAxes: [{
                                ticks: {
                                    beginAtZero: true,
                                    callback: GraphUtil.formatCurrency
                                }
                            }]
                        }
                    }
                });
            }
        })
    })();

})(jQuery);
</script>

</body>
</html>