<html>
  <head>
    <title>
      UAH to KRB exchange rate over time
    </title>
  </head>
  <body>
<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>

<div id="container" style="min-width: 310px; max-width: 1440px; height: 600px; margin: 0 auto"></div>

<script>
$.getJSON('http://www.titan-machine.pp.ua/scripts/dev/markets/api.php?action=charts', function (data) {

    Highcharts.setOptions({ global: { useUTC: false } });

    Highcharts.chart('container', {
        chart: {
            zoomType: 'x'
        },
        title: {
            text: 'UAH to KRB exchange rate over time'
        },
        subtitle: {
            text: document.ontouchstart === undefined ?
                    'Click and drag in the plot area to zoom in' : 'Pinch the chart to zoom in'
        },
        xAxis: {
            type: 'datetime'
        },
        yAxis: {
            title: {
                text: 'Exchange rate'
            }
        },
        legend: {
            enabled: true
        },
        plotOptions: {
            area: {
                fillColor: {
                    linearGradient: {
                        x1: 0,
                        y1: 0,
                        x2: 0,
                        y2: 1
                    },
                    stops: [
                        [0, Highcharts.getOptions().colors[0]],
                        [1, Highcharts.Color(Highcharts.getOptions().colors[0]).setOpacity(0).get('rgba')]
                    ]
                },
                marker: {
                    radius: 2
                },
                lineWidth: 1,
                states: {
                    hover: {
                        lineWidth: 1
                    }
                },
                threshold: null
            }
        },

        series: data
    });
});
</script>
</body>
</html>
