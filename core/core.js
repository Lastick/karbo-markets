
var chart = undefined;

function view_show(){
  $.getJSON('api.php?action=charts', function (data){
    charts_init(data);
  });
}

function charts_init(data){ 
  Highcharts.setOptions({ global: { useUTC: false } });
  chart =  Highcharts.stockChart('charts', {
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
}

function view_loop(){
}

function view_hide(){
  chart.destroy();
}

function about_show(){
}

function about_loop(){
}

function about_hide(){
}

function contact_show(){
}

function contact_loop(){
}

function contact_hide(){
}


var loop_interval = 2000;
var last_tab_show = 'view';

function TabDispatcher(page, action){
  var func_name = page + '_' + action;
  console.log('Dispatcher: ' + func_name + '()');
  try {
    window[func_name]();
    if (action == 'show') last_tab_show = page;
    } catch(e) {
    alert('Dispatcher: ' + e.message); 
  }
}

function init(){
  $('a[data-toggle="tab"]').on('show.bs.tab', function(e){
    TabDispatcher(e.target.href.split('#')[1], 'show');
  });
  $('a[data-toggle="tab"]').on('hide.bs.tab', function(e){
    TabDispatcher(e.target.href.split('#')[1], 'hide');
  });
}

function loop(){
  TabDispatcher(last_tab_show, 'loop');
  setTimeout(loop, loop_interval);
}

$(document).ready(function(){
  init();
  TabDispatcher('view', 'show');
  loop();
});

