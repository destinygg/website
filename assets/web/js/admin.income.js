import jQuery from 'jquery'
import moment from 'moment'
import Chart from 'chart.js'

(function($){

    $('#income-graphs').each(function(){

        const currDate = moment()
        const dates = $('#income-dates')
        const datesin = dates.find('span.date')
        const months = 12
        const days = 14

        dates
            .on('click', '.fa-arrow-left', () => {
                currDate.subtract(1, 'months')
                datesin.text(currDate.format('MMMM YYYY'))
                dates.triggerHandler('date', currDate)
            })
            .on('click', '.fa-arrow-right', () => {
                currDate.add(1, 'months')
                datesin.text(currDate.format('MMMM YYYY'))
                dates.triggerHandler('date', currDate)
            })
        datesin.text(currDate.format('MMMM YYYY'));

        $('#graph4').each(function(){
            const graph = $(this)
            const currChart = new Chart(graph.find('canvas').get(0).getContext('2d'), {
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

            const updateGraph4 = function(selectedDate){
                const fromDate = moment(selectedDate.format('YYYY-MM-DD')).startOf('month'),
                    toDate = moment(selectedDate.format('YYYY-MM-DD')).endOf('month');
                $.ajax({
                    url: '/admin/chart/finance/NewTieredSubscribersLastXDays.json?fromDate='+ fromDate.format('YYYY-MM-DD') +'&toDate='+ toDate.format('YYYY-MM-DD'),
                    success: function(data){
                        const dataSet1 = [],
                            dataSet2 = [],
                            dataSet3 = [],
                            dataSet4 = [],
                            dataLabels = [],
                            dates = [];
                        for (let m = fromDate; m.isBefore(toDate) || m.isSame(toDate); m.add(1, 'days')) {
                            dates.push(m.format('YYYY-MM-DD'));
                            dataLabels.push(m.format('D'));
                            dataSet1.push(0);
                            dataSet2.push(0);
                            dataSet3.push(0);
                            dataSet4.push(0);
                        }
                        for(let i=0; i<data.length; ++i){
                            const x = dates.indexOf(data[i].date);
                            if(x !== -1){
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

            dates.on('date', () => updateGraph4(currDate))
            updateGraph4(currDate);
        });

        $('#graph1').each(function(){
            const graph = $(this),
                label = "Revenue Last "+ days +" Days";
            $.ajax({
                url: '/admin/chart/finance/RevenueLastXDays.json?days='+days,
                success: function(data){
                    data = GraphUtil.prepareGraphData(data, 'sum', days, 'days');
                    new Chart(graph.find('canvas').get(0).getContext("2d"), {
                        type: 'bar',
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
                                    label: function(tooltipItem) {
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
        });

        $('#graph2').each(function(){
            const graph = $(this),
                label = "Revenue Last "+ months +" Months";
            $.ajax({
                url: '/admin/chart/finance/RevenueLastXMonths.json?months='+months,
                success: function(data){
                    data = GraphUtil.prepareGraphData(data, 'sum', months, 'months');
                    new Chart(graph.find('canvas').get(0).getContext("2d"), {
                        type: 'bar',
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
                                    label: function(tooltipItem) {
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
        });

        $('#graph3').each(function(){
            const years = 5,
                graph = $(this),
                label = "Revenue Last "+ years +" Years";
            $.ajax({
                url: '/admin/chart/finance/RevenueLastXYears.json?years='+years,
                success: function(data){
                    data = GraphUtil.prepareGraphData(data, 'sum', years, 'years');
                    new Chart(graph.find('canvas').get(0).getContext("2d"), {
                        type: 'bar',
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
                                    label: function(tooltipItem) {
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
        });

        $('#graph5').each(function(){
            const graph = $(this)
            const currChart = new Chart(graph.find('canvas').get(0).getContext('2d'), {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: []
                },
                options: {
                    maintainAspectRatio: false,
                    tooltips: {
                        callbacks: {
                            label: function(t) {
                                return GraphUtil.formatCurrency(t['yLabel']);
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
            const updateGraph5 = function(selectedDate) {
                const fromDate = moment(selectedDate.format('YYYY-MM-DD')).startOf('month'),
                    toDate = moment(selectedDate.format('YYYY-MM-DD')).endOf('month');
                $.ajax({
                    url: '/admin/chart/finance/NewDonationsLastXDays.json?fromDate='+ fromDate.format('YYYY-MM-DD') +'&toDate='+ toDate.format('YYYY-MM-DD'),
                    success: function (data) {
                        const label = "Donations "+ currDate.format('MMMM YYYY');
                        const dataSet = [],
                            dataLabels = [],
                            dates = [];
                        for (let m = fromDate; m.isBefore(toDate) || m.isSame(toDate); m.add(1, 'days')) {
                            dates.push(m.format('YYYY-MM-DD'));
                            dataLabels.push(m.format('D'));
                            dataSet.push(0);
                        }
                        for(let i=0; i<data.length; ++i) {
                            const x = dates.indexOf(data[i].date);
                            dataSet[x] = parseInt(data[i]['total'])
                        }
                        currChart.label = label;
                        currChart.data.labels = dataLabels;
                        currChart.data.datasets = [{
                            label: label,
                            borderWidth: 0.4,
                            backgroundColor: "rgba(220,220,220,0.2)",
                            borderColor: "rgba(220,220,220,1)",
                            pointBorderColor: "rgba(220,220,220,1)",
                            pointBackgroundColor: "#fff",
                            pointBorderWidth: 1,
                            data: dataSet
                        }];
                        currChart.update();
                    }
                })
            }
            dates.on('date', () => updateGraph5(currDate))
            updateGraph5(currDate);
        });

        const updateActiveSubCountTables = data => {
            data.forEach(countRecord => {
                const {subscriptionType, recurring, count} = countRecord
                $(`td[data-sub-type='${subscriptionType}'][data-recurring='${recurring}']`).text(count)
            })

            // Add values across each row.
            $('td:last-child').each((_, e) => {
                const $total = $(e)
                $total.text(0)

                let count = 0
                $total.siblings('td').each((_, e) => {
                    count += parseInt($(e).text())
                })
                $total.text(count)
            })

            // Add values down each column.
            $('tr:last-child td').each((_, e) => {
                const $total = $(e)
                $total.text(0)

                let count = 0
                const $table = $total.parents('table')
                $table.find(`td:nth-child(${$total.index() + 1})`).each((_, e) => {
                    count += parseInt($(e).text())
                })
                $total.text(count)
            })
        }

        const fetchActiveSubCounts = () => {
            $.ajax({
                url: '/admin/chart/finance/CurrentActiveSubs.json',
                success: data => {
                    updateActiveSubCountTables(data)
                }
            })
        }

        fetchActiveSubCounts()
    });
})(jQuery)
