
// Karbo Markets Core

var loop_interval = 2;
var last_tab_show = 'view';
var app_name = '';
var app_ver = '';
var app_status = false;
var chart = undefined;
var ChartsData = undefined;
var ChartsDataOctet = undefined;
var ChartsDataZoom = undefined;
var DataFirstTime = 0;
var DataLastTime = 0;
var DataLastTimeOld = 0;
var NewOctetTriger = false;
var NewZoomTriger = false;
var CursorMinTime = 0;
var CursorMaxTime = 0;
var StartLimit = 0;
var EndLimit = 0;
var ChartUpdateTriger = true;
var ChartLoadTriger = true;
var ChartLoadTrigerOctet = true;
var ChartLoadTrigerZoom = true;
var viewInit = true;


function ChartsDataCompile(){
  var arr_len = ChartsData[0].data.length;
  var ZoomFirstTime = 0;
  var ZoomLastTime = 0;
  var ZoomLenTime = 0;
  if (NewZoomTriger){
    ZoomFirstTime = ChartsDataZoom[0].data[0][0];
    ZoomLastTime = ChartsDataZoom[0].data[ChartsDataZoom[0].data.length - 1][0];
    ZoomLenTime = ChartsDataZoom[0].data.length - 1;
  }
  for (var a=0; a < 4; a++){
    var data = new Array();
    var n_o = 0;
    for (var n=0; n < arr_len; n++){
      if (NewZoomTriger){
        if (ChartsData[a].data[n][0] > ZoomFirstTime) break;
      }
      data[n] = [ChartsData[a].data[n][0], ChartsData[a].data[n][1]];
      n_o++;
    }
    if (NewZoomTriger){
      for (var z=0; z < ZoomLenTime; z++){
        data[n_o] = [ChartsDataZoom[a].data[z][0], ChartsDataZoom[a].data[z][1]];
        n_o++;
      }
      for (var n=0; n < arr_len; n++){
        if (ZoomLastTime < ChartsData[a].data[n][0]){
          data[n_o] = [ChartsData[a].data[n][0], ChartsData[a].data[n][1]];
          n_o++;
        }        
      }
      if (a == 3) NewZoomTriger = false;
    }
    if (NewOctetTriger){
      data[data.length] = [ChartsDataOctet[a].data[0][0], ChartsDataOctet[a].data[0][1]];
      if (a == 3) NewOctetTriger = false;
    }
    ChartsData[a].data = data;
  }
  chart.series[0].setData(ChartsData[0].data);
  chart.series[1].setData(ChartsData[1].data);
  chart.series[2].setData(ChartsData[2].data);
  chart.series[3].setData(ChartsData[3].data);
}

function ChartLoader(NewDataFirstTime, NewDataLastTime, arr_targ){
  NewDataFirstTime = Math.round(NewDataFirstTime / 1000);
  NewDataLastTime = Math.round(NewDataLastTime / 1000);
  ChartLoadTriger = false;
  if (arr_targ == 'octet'){
    ChartLoadTrigerOctet = false;
  }
  if (arr_targ == 'zoom'){
    ChartLoadTrigerZoom = false;
  }
  $.getJSON('api.php?action=charts&start=' + NewDataFirstTime + '&end=' + NewDataLastTime, function(data){
    if (arr_targ == 'octet'){
      ChartsDataOctet = null;
      ChartsDataOctet = data;
      ChartLoadTrigerOctet = true;
    }
    if (arr_targ == 'zoom'){
      ChartsDataZoom = null;
      ChartsDataZoom = data;
      ChartLoadTrigerZoom = true;
    }
    ChartLoadTriger = true;
  });
}

function DetectNewOctet(){
  if (DataLastTimeOld == 0) DataLastTimeOld = DataLastTime; 
  if (DataLastTime > DataLastTimeOld){
    console.log('view: new octet found');
    NewOctetTriger = true;
  }
  if (NewOctetTriger && DataLastTime > DataLastTimeOld){
    DataLastTimeOld = DataLastTime;
    ChartLoader(DataLastTime, DataLastTime, 'octet');
  }
}

function DetectNewZoom(){
  NewZoomTriger = true;
  ChartLoader(CursorMinTime, CursorMaxTime, 'zoom');
}

function NewOctetAdd(){
  if (NewOctetTriger && ChartLoadTrigerOctet){
    ChartsDataCompile();
    console.log('view: new octet add');
  }
}

function NewZoomAdd(){
  if (NewZoomTriger && ChartLoadTrigerZoom){
    ChartsDataCompile();
    console.log('view: new zoom add');
  }
}

function charts_init(){
  $.getJSON('api.php?action=charts&start=' + Math.round(DataFirstTime / 1000) + '&end=' + Math.round(DataLastTime / 1000), function (data){
    ChartsData = data;
    Highcharts.setOptions({ global: { useUTC: false } });
    chart =  Highcharts.stockChart('charts', {
      rangeSelector: {
        selected: 1,
        buttons: [
                  {
	           type: 'hour',
	           count: 1,
	           text: '1h'
                  },

                  {
	           type: 'hour',
	           count: 6,
	           text: '6h'
                  },

                  {
	           type: 'hour',
	           count: 24,
	           text: '24h'
                  },

                  {
	           type: 'day',
	           count: 14,
	           text: '14d'
                  },

                  {
	           type: 'month',
	           count: 1,
	           text: '1m'
                  },

                  {
	           type: 'month',
	           count: 3,
	           text: '3m'
                  },

                  {
	           type: 'all',
	           text: 'All'
                  }
                ]
      },
      legend: {
        enabled: true
      },
      title: {
        text: 'KRB Stock Price'
      },
      xAxis: {
        minRange: 3600 * 1000,
        scrollbar: {
          enabled: false
        },
        events: {
          afterSetExtremes: function(e){ CursorMinTime = e.min; CursorMaxTime = e.max }
        }
      },
      yAxis: {
        floor: 0
      },
      series: data
    });
  });
}

function view_show(){
  viewInit = false;
}

function view_loop(){
  if (app_status && !viewInit){
    charts_init();
    viewInit = true;
  }
  if (app_status){
    NewZoomAdd();
    NewOctetAdd();
    DetectNewOctet();
  }
}

function view_hide(){
  chart.destroy();
  viewInit = true;
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


function getStat(){
  $.getJSON('api.php?action=page', function (data){
    app_name = data.name;
    app_ver = data.ver;
    if (data.status){
      app_status = true;
      DataFirstTime = data.size_markets.first;
      DataLastTime = data.size_markets.last;
      } else {
      app_status = false;
      DataFirstTime = 0;
      DataLastTime = 0;
    }
  });
}

function TabDispatcher(page, action){
  var func_name = page + '_' + action;
  //console.log('Dispatcher: ' + func_name + '()');
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
  getStat();
  TabDispatcher(last_tab_show, 'loop');
  setTimeout(loop, loop_interval * 1000);
}

$(document).ready(function(){
  init();
  TabDispatcher('view', 'show');
  loop();
});

