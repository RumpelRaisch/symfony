$(() =>
{
    const $charts    = $('canvas[data-print="chart"]');
    const printChart = (chartElem, chartData) =>
    {
        const chartLabels = ['week 12','week 11','week 10','week 9','week 8','week 7','week 6','week 5','week 4','week 3','week 2','this week'];

        chartData   = chartData.split(',');
        chartData   = chartData.slice(Math.max(chartData.length - 12, 0));

        const gradientChartOptionsConfiguration = {
            maintainAspectRatio: false,
            legend: {
                display: false
            },
            tooltips: {
                backgroundColor: '#fff',
                titleFontColor: '#333',
                bodyFontColor: '#666',
                bodySpacing: 4,
                xPadding: 12,
                mode: "nearest",
                intersect: 0,
                position: "nearest"
            },
            responsive: true,
            scales:{
                yAxes: [{
                    barPercentage: 1.6,
                    gridLines: {
                        drawBorder: false,
                        color: 'rgba(29,140,248,0.0)',
                        zeroLineColor: "transparent",
                    },
                    ticks: {
                        suggestedMin: 0,
                        suggestedMax: Math.ceil((Math.max.apply(null, chartData)+1)/10)*10,
                        padding: 20,
                        fontColor: "#9a9a9a"
                    }
                }],
                xAxes: [{
                    barPercentage: 1.6,
                    gridLines: {
                        drawBorder: false,
                        color: 'rgba(220,53,69,0.1)',
                        zeroLineColor: "transparent",
                    },
                    ticks: {
                        padding: 20,
                        fontColor: "#9a9a9a"
                    }
                    // ticks: {
                    //     display: false //this will remove only the label
                    // }
                }]/*,
                xAxes: [{
                    display: false //this will remove all the x-axis grid lines
                }]*/
            }
        };

        const ctx = chartElem.getContext("2d");
        const gradientStroke = ctx.createLinearGradient(0,230,0,50);

        gradientStroke.addColorStop(1, 'rgba(72,72,176,0.2)');
        gradientStroke.addColorStop(0.2, 'rgba(72,72,176,0.0)');
        gradientStroke.addColorStop(0, 'rgba(119,52,169,0)'); //purple colors

        const data = {
            labels: chartLabels,
            datasets: [{
                label: "Data",
                fill: true,
                backgroundColor: gradientStroke,
                borderColor: '#d048b6',
                borderWidth: 2,
                borderDash: [],
                borderDashOffset: 0.0,
                pointBackgroundColor: '#d048b6',
                pointBorderColor:'rgba(255,255,255,0)',
                pointHoverBackgroundColor: '#d048b6',
                pointBorderWidth: 20,
                pointHoverRadius: 2,
                pointHoverBorderWidth: 15,
                pointRadius: 2,
                data: chartData,
            }]
        };

        const chart = new Chart(ctx, {
            type: 'line',
            data: data,
            options: gradientChartOptionsConfiguration
        });
    };

    $charts.each((i, elem) =>
    {
        const $this = $(elem);

        printChart(elem, $this.data('chart-data'));
    });
});
