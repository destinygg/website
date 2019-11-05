import jQuery from 'jquery'
import moment from 'moment'
import Chart from 'chart.js'

(function($){

    $('#moderation-graphs').each(function(){

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
                    url: '/admin/chart/users/NewUsersAndBansLastXDays.json?fromDate='+ fromDate.format('YYYY-MM-DD') +'&toDate='+ toDate.format('YYYY-MM-DD'),
                    success: function(data){
                        const dataSet1 = [],
                            dataSet2 = [],
                            dataLabels = [],
                            dates = [];
                        for (let m = fromDate; m.isBefore(toDate) || m.isSame(toDate); m.add(1, 'days')) {
                            dates.push(m.format('YYYY-MM-DD'));
                            dataLabels.push(m.format('D'));
                            dataSet1.push(0);
                            dataSet2.push(0);
                        }

                        for(let y=0; y<data.length; ++y){
                            for(let i=0; i<data[y].length; ++i) {

                                const x = dates.indexOf(data[y][i].date);
                                console.log(data[y][i], x);
                                if (x !== -1) {
                                    switch (y) {
                                        case 0:
                                            dataSet1[x] = parseInt(data[y][i]['total']);
                                            break;
                                        case 1:
                                            dataSet2[x] = parseInt(data[y][i]['total']);
                                            break;
                                    }
                                }
                            }
                        }

                        currChart.data.labels = dataLabels;
                        currChart.data.datasets = [
                            {
                                label: "Users",
                                data: dataSet1,
                                borderWidth: 0.4,
                                backgroundColor: "rgba(51, 122, 183,0.6)",
                                borderColor: "rgba(51, 122, 183,1)",
                                pointBorderColor: "rgba(51, 122, 183,1)",
                                pointBackgroundColor: "rgba(51, 122, 183,1)"
                            },
                            {
                                label: "Bans",
                                data: dataSet2,
                                borderWidth: 0.4,
                                backgroundColor: "rgba(220,0,0,0.6)",
                                borderColor: "rgba(220,0,0,1)",
                                pointBorderColor: "rgba(220,0,0,1)",
                                pointBackgroundColor: "rgba(220,0,0,1)"
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
                label = "Users Last "+ days +" Days";
            const fromDate = moment(currDate.format('YYYY-MM-DD')).subtract('days', days),
                toDate = moment(currDate.format('YYYY-MM-DD'));
            $.ajax({
                url: '/admin/chart/users/NewUsersLastXDays.json?fromDate='+ fromDate.format('YYYY-MM-DD') +'&toDate='+ toDate.format('YYYY-MM-DD'),
                success: function(data){
                    data = GraphUtil.prepareGraphData(data, 'total', days, 'days');
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
                                        return tooltipItem.yLabel;
                                    }
                                }
                            },
                            scales: {
                                yAxes: [{
                                    ticks: {
                                        beginAtZero: true
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
                label = "Users Last "+ months +" Months";
            const fromDate = moment(currDate.format('YYYY-MM-DD')).subtract('months', months),
                toDate = moment(currDate.format('YYYY-MM-DD'));
            $.ajax({
                url: '/admin/chart/users/NewUsersLastXMonths.json?fromDate='+ fromDate.format('YYYY-MM-DD') +'&toDate='+ toDate.format('YYYY-MM-DD'),
                success: function(data){
                    data = GraphUtil.prepareGraphData(data, 'total', months, 'months');
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
                                        return tooltipItem.yLabel;
                                    }
                                }
                            },
                            scales: {
                                yAxes: [{
                                    ticks: {
                                        beginAtZero: true
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
                label = "Users "+ years +" Years";
            const fromDate = moment(currDate.format('YYYY-MM-DD')).subtract('years', years),
                toDate = moment(currDate.format('YYYY-MM-DD'));
            $.ajax({
                url: '/admin/chart/users/NewUsersLastXYears.json?fromDate='+ fromDate.format('YYYY-MM-DD') +'&toDate='+ toDate.format('YYYY-MM-DD'),
                success: function(data){
                    data = GraphUtil.prepareGraphData(data, 'total', years, 'years');
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
                                        return tooltipItem.yLabel;
                                    }
                                }
                            },
                            scales: {
                                yAxes: [{
                                    ticks: {
                                        beginAtZero: true
                                    }
                                }]
                            }
                        }
                    });
                }
            })
        });

    });

})(jQuery)