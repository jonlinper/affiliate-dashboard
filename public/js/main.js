(function ($) {
    "use strict";
    $(function () {
        if (!$('#performance_chart').length) {
            return;
        }

        var visitsChartCanvas = $('#performance_chart').get(0).getContext('2d');

        var data = {
            labels: performanceChartLabels,
            datasets: [{
                label: visitsChartLabel,
                data: visitsChartData,
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1,
                fill: false,
                minBarLength: 1
            }, {
                label: salesChartLabel,
                data: salesChartData,
                backgroundColor: 'rgba(255, 206, 86, 0.2)',
                borderColor: 'rgba(255, 206, 86, 1)',
                borderWidth: 1,
                fill: false,
                minBarLength: 1
            }]
        };

        var visitsChart = new Chart(visitsChartCanvas, {
            type: 'bar',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    xAxes: [{
                        gridLines: {
                            display: false
                        },
                        ticks: {
                            maxTicksLimit: 10
                        }
                    }],
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            maxTicksLimit: 6,
                            suggestedMax: 5
                        },
                    }],
                },
                legend:false,
                legendCallback: function(chart) {
                    var text = [];
                    text.push('<div class="chartjs-legend"><ul>');
                    for (var i = 0; i < chart.data.datasets.length; i++) {
                        text.push('<li class="text-muted text-small">');
                        text.push('<span style="background-color:' + chart.data.datasets[i].borderColor + '">' + '</span>');
                        text.push(chart.data.datasets[i].label);
                        text.push('</li>');
                    }
                    text.push('</ul></div>');
                    return text.join('');
                }
            }
        });

        $('#performance_legend')[0].innerHTML = visitsChart.generateLegend();
    });
})(jQuery);
