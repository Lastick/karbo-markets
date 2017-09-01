$(document).ready(function(){
  $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
    console.log(e.target.href);
  });



$.getJSON('api.php?action=charts', function (data) {

    Highcharts.setOptions({ global: { useUTC: false } });

    Highcharts.stockChart('charts', {


        rangeSelector: {
            selected: 1
        },

        legend: {
            enabled: true
        },

        title: {
            text: 'KRB Stock Price'
        },

        series: data
    });
});

});
