/**
 * Change graph
 * @param  {Array}  dataCoin         New coin data
 * @param  {String}  currency        Currency symbol (ex : USD)
 * @param  {String}  container       Container id
 * @param  {Boolean} [save=true]     Save change
 * @param  {Boolean} [replace=false] Replace graph
 */
function changeGraph(dataCoin, currency, container, topitem, replace = false, market="CCCAGG"){

  // Change graph text
  $('#' + container).find('.kr-dash-pan-graph').html('');
  $('#' + container).addClass('kr-dash-pan-cry-vsbl');
  $('#' + container).html('');
  $('#' + container).attr('symbol', dataCoin.symbol);
  $('#' + container).attr('currency', currency);
  $('#' + container).attr('market', market);

  $('#' + container).find('.kr-dash-pan-tb-nopt-icon').html(dataCoin.icon);
  $('#' + container).find('.kr-dash-pan-tb-nopt-n > label').html(dataCoin.name);
  $('#' + container).find('.kr-dash-pan-tb-nopt-n > span').html(dataCoin.symbol);

  $('#' + container).attr('chart-init', 'true');

  $('.kr-top-graphlist-item-selected').removeClass('kr-top-graphlist-item-selected');
  $('.kr-top-graphlist-item[container="' + container + '"]').addClass('kr-top-graphlist-item-selected');
  loadLeftInfosCoin(dataCoin.symbol, currency, market);


  loadChart(dataCoin.symbol, null, container, currency, market);

  if(replace){
    $('.kr-top-graphlist-item[topitem="' + topitem + '"]').attr('symbol', dataCoin.symbol);
    $('.kr-top-graphlist-item[topitem="' + topitem + '"]').attr('market', market);
    $('.kr-top-graphlist-item[topitem="' + topitem + '"]').attr('currency', currency);
    $('.kr-top-graphlist-item[topitem="' + topitem + '"]').find('[kr-data="CHANGE24HOURPCT"]').html('~');
    $('.kr-top-graphlist-item[topitem="' + topitem + '"]').find('[kr-data="CHANGE24HOURPCT"]').removeClass('kr-top-graphlist-item-evl-up');
    $('.kr-top-graphlist-item[topitem="' + topitem + '"]').find('[kr-data="CHANGE24HOURPCT"]').removeClass('kr-top-graphlist-item-evl-down');
    $('.kr-top-graphlist-item[topitem="' + topitem + '"]').find('.kr-top-graphlist-inf').find('label').html(($('.kr-wallet-top-real').length > 0 ? market + ':' : '') + dataCoin.symbol + '/' + currency);
    $('.kr-top-graphlist-item[topitem="' + topitem + '"]').find('.kr-top-graphlist-pic').html(dataCoin.icon);
    addSubscribtion(dataCoin.icon, currency, 5, market);
  }

  reloadTopListGraph();

  // Save new chart data
  $.post($('body').attr('hrefapp') + "/app/modules/kr-dashboard/src/actions/changeGraph.php", {container:container, topitem:topitem, coinsymbol:dataCoin.symbol, currency:currency, market:market}).done(function(data){
    // Parse json data
    let response = jQuery.parseJSON(data);
    if(response.error == 1){
      //showAlert('Oops', response.msg, 'error');
    }
  }).fail(function(){
    showAlert('Ooops', 'Fail to save graph', 'error');
  });

}

let oldNChart = null;
/**
 * Toggle chart fullscreen
 * @param  {String} container Graph container ID
 */
function toggleGraphFullScreen(container, forceshow = false){

  $('.kr-dash-pan-cry').removeClass('animated').removeClass('bounceIn');
  if($('.kr-dash-pannel').hasClass('kr-dash-pannel-fls') || forceshow){ // Remove fullscreen
    $('.kr-dash-pannel').removeClass('kr-dash-pannel-fls');
    $('.kr-dash-pannel').attr('nchart', oldNChart);
    $('.kr-dash-pan-cry').show(); // Show all graph
    oldNChart = null;
  } else { // Set full screen
    $('.kr-dash-pannel').addClass('kr-dash-pannel-fls');

    oldNChart = $('.kr-dash-pannel').attr('nchart');
    $('.kr-dash-pannel').attr('nchart', '1_single');

    $('.kr-dash-pan-cry').hide(); // hide all chart
    $(container).show(); // Show chart fullscreen selected

  }

  checkGraphResize();
}

function removeGraph(container){

  if(oldNChart != null) toggleGraphFullScreen(null, true);

  $.post($('body').attr('hrefapp') + '/app/modules/kr-dashboard/src/actions/deleteGraph.php', {container:container}).done(function(data){
    let response = jQuery.parseJSON(data);
    if(response.error == 1){
      showAlert('Oops', response.msg, 'error');
    }
  }).fail(function(){
    showAlert('Oops', 'Fail to delete graph (404, 500)', 'error');
  });

  $('.kr-top-graphlist-item[container="' + container + '"]').remove();

  $('.kr-dash-pan-cry-selected').removeClass('kr-dash-pan-cry-selected');
  $('#' + container).addClass('kr-dash-pan-cry-selected');

  let f = false;
  $.each($('.kr-top-graphlist-item'), function(){
    if(!$(this).hasClass('kr-top-graphlist-item-view')){
      $(this).trigger('click');
      f = true;
      return false;
    }
  });

  if(!f){
    $('#' + container).attr('symbol', 'not_init');
    $('#' + container).attr('chart-init', 'false');
    $('#' + container).html('<div class="kr-dash-pan-lgl" onclick="showBigSearch(\'addGraphDashboardNotInit\');">' +
      '<div class="kr-dash-pan-cry-select" graph="' + $('#' + container).attr('id') + '" style="display: none;">' +
        '<header>' +
          '<input type="text" name="" graph="' + $('#' + container).attr('id') + '" placeholder="Search by name or symbol" value="">' +
        '</header>' +
        '<ul class="kr-dash-pan-cry-select-lst">' +
        '</ul>' +
      '</div>' +
      '<img src="' + $('body').attr('hrefapp') + $('body').attr('logopath') + '" alt="">' +
    '</div>');
    initCoinGraphControllers();
  }


}


let listNotificationChart = [];
let listOrderChart = [];
let listInternalOrderChart = [];

let chartList = [];
let chartIndicator = [];

var currentValueSelected = null;
var currentDateSelected = null;

var dataGraphic = [["08/06/2018 14:30:00", 7595]];

/**
 * Load chart
 * @param  {String}   symbol    Chart symbol (ex : BTC)
 * @param  {Function} callback  Chart callback loaded
 * @param  {String}   container Chart container
 */
function loadChart(symbol, callback, container, currency, market = "CCCAGG"){

  reloadTopListGraph();

  if(!loadLeftInfosIsInitied()) loadLeftInfosCoin(symbol, currency, market);

  // Load chart content
  $.get($('body').attr('hrefapp') + '/app/modules/kr-dashboard/src/actions/loadChartContent.php', {coin:symbol, container:container, currency:currency, market:market}).done(function(data){

    if($('#' + container).attr('symbol') == "not_init") return false;

    try { // Try to parse data in JSON -> true = error
      let response = jQuery.parseJSON(data);
      if(response.error == 1){
        showAlert('Oops', response.msg, 'error');
      }
    } catch (e) {

      $.when($('#' + container).html(data)).then(function(){ // Add graph in container & load data

        loadStaticSearchBox();

        $('.kr-watching-list-exist-addaction').off('click').click(function(){
          toggleWatchingList($(this).attr('symbol'), $(this).attr('currency'), $(this).attr('market'));
        });

        $('#' + container).attr('symbol', symbol);
        $('#' + container).attr('currency', currency);
        $('#' + container).attr('market', market);

        loadChartData(symbol, $('#' + container).attr('currency'), $('#' + container).attr('market'), function(data){

          // Init chart data
          chartIndicator[container] = {};

          let vListChart = [];
          $.each(data.candles, function(k, v){
            vListChart.push(v.close);
          });

          if($('#graph-' + container).length > 0){
          chartList[container] = {
            'symbol': symbol,
            'graph': echarts.init(document.getElementById('graph-' + container)),
            'option': null,
            'data': vListChart,
            'data_candles': data.candles
          };

          var dates = [];

          var candlestickData = [];
          let lineData = [];
          let volumeData = [];
          $.each(data.candles, function(k, v){
            dates.push(v.date);
            lineData.push(v.close);
            volumeData.push(v.volume);
            candlestickData.push([
              v.open,
              v.close,
              v.low,
              v.high
            ]);

          });

          chartList[container]['option'] = {
              animation: false,
              backgroundColor: '#f1f5f8',
              tooltip: {
                  trigger: 'axis',
                  backgroundColor: 'rgba(255, 255, 255, 0.8)',
                  textStyle: {
                    color: '#1f1f1f'
                  },
                  axisPointer: {
                      animation: false,
                      type: 'cross',
                      lineStyle: {
                          color: '#d7dce0',
                          width: 1,
                          opacity: 1,
                          type: 'solid'
                      }
                  },
                  extraCssText: 'border-radius:1px;box-shadow: 0 2px 3px rgba(0, 0, 0, 0.2);',
                  padding: [6, 8]
              },
              xAxis: [{
                  type: 'category',
                  data: dates,
                  gridIndex: 0,
                  axisLabel: {
                      margin: 8,
                      textStyle: {
                          fontSize: '18px',
                          color: '#abafb3'
                      }
                  },
                  axisPointer: {
                    show: true,
                    lineStyle: {
                        color: '#d7dce0',
                        type: 'solid'
                    },
                    label: {
                      shadowBlur: 0,
                      margin:0,
                      backgroundColor: '#4f576d',
                      color:'#f7f8f9'
                    }
                  },
                  axisTick: {
                      show: false
                  },
                  splitLine: {
                      show: true,
                      lineStyle: {
                          color: '#d7dce0',
                          type: 'solid'
                      }
                  }
              },
              {
                gridIndex: 0,
                show:false,
                type: 'category',
                data: dates,
                nameGap: 0,
                axisLabel: {
                    margin: 0,
                    show:false,
                    textStyle: {
                        fontSize: '18px',
                        color: '#abafb3'
                    }
                },
                axisPointer: {
                  show: false,
                  lineStyle: {
                      color: '#d7dce0',
                      type: 'solid'
                  },
                  label: {
                    shadowBlur: 0,
                    margin:0,
                    backgroundColor: '#4f576d',
                    color:'#f7f8f9'
                  }
                },
                axisTick: {
                    show: false
                },
                splitLine: {
                    show: false,
                    lineStyle: {
                        color: '#d7dce0',
                        type: 'solid'
                    }
                }
              }],
              yAxis: [{
                  scale: true,
                  gridIndex: 0,
                  position: 'right',
                  boundaryGap: ['10%', '10%'],
                  triggerEvent: true,
                  nameGap: 100,
                  splitNumber: 7,
                  axisLabel: {
                    inside:false,
                    textStyle: {
                        fontSize: '.18rem',
                        color: '#abafb3'
                    },
                  },
                  axisTick: {
                      show: false,
                      length: 7
                  },
                  axisLine: {
                      show: false,
                  },
                  axisPointer: {
                    show: true,
                    lineStyle: {
                        color: '#d7dce0',
                        type: 'solid'
                    },
                    label: {
                      shadowBlur: 0,
                      margin:0,
                      backgroundColor: '#4f576d',
                      color:'#f7f8f9'
                    }
                  },
                  splitLine: {
                      show: true,
                      lineStyle: {
                          color: '#d7dce0',
                          type: 'solid'
                      }
                  }
              },
              {
                  scale: true,
                  gridIndex: 0,
                  position: 'left',
                  boundaryGap: ['0%', '70%'],
                  triggerEvent: true,
                  nameGap: 0,
                  show:false,
                  axisTick: { show: false },
                  axisLine: { show: false },
                  splitLine: { show: false },
                  axisLabel: { show:false },
                  axisPointer: { show:false }
              }
              ],
              grid: [{
                  left: '0px',
                  right: '25px',
                  top: '0px',
                  height: '100%',
                  containLabel: true,
                  borderColor: '#d7dce0',
                  show: true,
                  tooltip: {
                    trigger: 'axis',
                  }
              }],
              dataZoom: [{
                  type: 'inside',
                  xAxisIndex: [0, 0],
                  start: 96,
                  end: 100,
                  zoomOnMouseWheel: true
              },
              {
                  type: 'inside',
                  xAxisIndex: [0, 1],
                  start: 96,
                  end: 100,
                  zoomOnMouseWheel: true
              }
            ],
              progressive: 100,
              series: [
              {
                  type: 'candlestick',
                  name: '',
                  data: candlestickData,
                  xAxisIndex: 0,
                  yAxisIndex: 0,
                  //progressive: 5000,
                  //progressiveThreshold: 50,
                  //progressiveChunkMode: 'mod',
                  //hoverAnimation: false,
                  animation:false,
                  large: true,
                  progressive: 50000,
                  animationDuration:0,
                  itemStyle: {
                      normal: {
                          opacity:($('#' + container).attr('type-graph') == "candlestick" ? 1 : 0),
                          color: '#29c359',
                          color0: '#df2323',
                          borderColor: '#29c359',
                          borderColor0: '#df2323'
                      }
                  },
                  markPoint : {
                      data : []
                  },
                  markLine: {
                     symbol: ['none', 'none'],
                     precision: (data.current_price > 10 ? 2 : 5),
                      data: [
                        {
                          name: 'Current price',
                          yAxis: data.current_price,
                          symbol: ['none', 'none'],
                          symbolSize: [0,0],
                          lineStyle: {
                            color: '#fe4f00',
                            width: 1,
                            type: 'solid'
                          },
                          label: {
                            middle: 'middle'
                          }
                        }
                      ],
                      animation:false
                  },
              },
              {
                type:'line',
                name: '',
                data:lineData,
                xAxisIndex: 0,
                yAxisIndex: 0,
                symbolSize: 0,
                lineStyle: {
                  color:'#29c359',
                  opacity:($('#' + container).attr('type-graph') == "candlestick" ? 0 : 1)
                },
                markPoint : {
                  data : []
                },
                markLine: {
                  symbol: ['none', 'none'],
                  animation: false,
                  lineStyle: {
                    opacity:0.4,
                    type: "dashed"
                  },
                  data: []
                },
                areaStyle: {
                      normal: {
                          opacity:($('#' + container).attr('type-graph') == "candlestick" ? 0 : 1),
                          color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
                              offset: 0,
                              color: 'rgba(41, 195, 89, 0.25)'
                          }, {
                              offset: 1,
                              color: 'rgba(41, 195, 89, 0)'
                          }], false),
                          shadowColor: 'rgba(0, 0, 0, 0.1)',
                          shadowBlur: 10
                      }
                  }
              },
              {
                name: 'Volume',
                type: 'bar',
                data: volumeData,
                itemStyle: {
                  //color:'rgba(255,255,255,0.1)'
                  normal: {
                      color: function(params) {
                        if (volumeData[params.dataIndex - 1]>volumeData[params.dataIndex]) return '#ef232a';
                        else return colorList = '#14b143';
                      },
                      opacity:0.2
                  },
                },
                yAxisIndex: 1,
                xAxisIndex: 1
              }
            ]
          };

          _defineContainerColor(container);


          chartList[container]['graph'].setOption(chartList[container]['option']);

          chartList[container]['graphic'] = [];
          //
          //addTrendLine(container);
          //addText(container);

          //addIcon(container);



          // Add notification action chart
          $('#graph-' + container).dblclick(function(){
            createNewNotification(symbol, $('#' + container).attr('currency'), currentValueSelected, $('#' + container).attr('market'));
          });

          $('#graph-' + container).click(function(){
            if(objectClick != null){
              objectClick();
              unselectSelectedGraphic();
              objectClick = null;
            }
          });

          // Change cursor value for indicator
          chartList[container]['graph'].on('updateaxispointer', function (params) {
              $.each(params.axesInfo, function(k, v){
                if(v.axisDim == "y") currentValueSelected = v.value;
                if(v.axisDim == "x") currentDateSelected = v.value;
              });
          });

          chartList[container]['graph'].on('datazoom', function (params) {

            $.each(chartList[container]['option'].dataZoom, function(k, v){
              if(chartList[container]['option'].dataZoom[k].type != "slider" && (chartList[container]['option'].dataZoom[k].hasOwnProperty('start') || chartList[container]['option'].dataZoom[k].hasOwnProperty('startValue'))){

                chartList[container]['option'].dataZoom[k].start = params.batch[0].start;
                chartList[container]['option'].dataZoom[k].end = params.batch[0].end;

              }
            });

            updateToolboxElementGraphZoomSingle(container);

          });

          // Init chart controllers
          initCoinGraphControllers();

          // Enable sync chart for live data
          syncChart(container);

          // Init indicator graph action
          initIndicatorsGraph(container);

          // Init tendance Graph
          initTendanceGraph(container);

          // Init range pan bottom graph
          initRangePan(container);

          // Init toolbox systeem
          initToolboxChartControler();

          addSubscribtion($('#' + container).attr('symbol'), $('#' + container).attr('currency'), 2, $('#' + container).attr('market'));


          // Subscribe stream symbol
          subscribeStreamerCallback(function(dataCoin){
            // Check data validity

            if(isNaN(dataCoin.CHANGE24HOURPCT)) return false;
            if(dataCoin.FROMSYMBOL == $('#' + container).attr('symbol') && $('#' + container).attr('currency') == dataCoin.TOSYMBOL && $('#' + container).attr('market').toLowerCase() == dataCoin.MARKET.toLowerCase()){

              // Update chart data
              let lastCandle = chartList[container]['option'].series[0].data[chartList[container]['option'].series[0].data.length - 1];
              let price = parseFloat(dataCoin.PRICE);
              if(price > parseFloat(lastCandle[3])) lastCandle[3] = dataCoin.PRICE;
              if(price < parseFloat(lastCandle[2])) lastCandle[2] = dataCoin.PRICE;
              lastCandle[1] = price;
              chartList[container]['option'].series[0].data[chartList[container]['option'].series[0].data.length - 1] = lastCandle;
              chartList[container]['option'].series[0].markLine.data[0].yAxis = price;
              chartList[container]['option'].series[0].markLine.precision = (price > 10 ? 2 : 5);

              let lastLineV = chartList[container]['option'].series[1].data[chartList[container]['option'].series[1].data.length - 1];
              lastLineV = dataCoin.PRICE;
              chartList[container]['option'].series[1].data[chartList[container]['option'].series[1].data.length - 1] = lastLineV;

              //chartList[container]['graph'].clear();
              setTimeout(function(){
                chartList[container]['graph'].setOption(chartList[container]['option']);
              }, 10);

              if($('#' + container).hasClass('kr-dash-pan-cry-selected') || ($('.kr-dash-pan-cry-selected').length == 0 && $('.kr-dash-pan-cry').index($('#' + container)) == 0)){
                // Change page title
                appendPageTitle(dataCoin.FROMSYMBOL + '/' + dataCoin.TOSYMBOL + ': ' + dataCoin.PRICE + ' ' + (parseFloat(dataCoin.CHANGE24HOURPCT) < 0 ? '▼' : '▲') + ' ' + dataCoin.CHANGE24HOURPCT + '%');
              }

            }
          }, 2);



          // Load chart notification
          $.each(listNotificationChart[container], function(k, v){
            loadChartNotification(container, symbol, v.value, v.type, v.id);
          });

          $.each(listInternalOrderChart[container], function(k, v){
            if(v.me){
              loadChartOrder(container, symbol, v.amount, v.date, v.type);
            } else {
              addInternalChartOrder(container, symbol, v.name, v.picture, v.date, v.type, v.order_id);
            }
          });
          $.each(listOrderChart[container], function(k, v){
            loadChartOrder(container, symbol, v.amount, v.time, v.type);
          });
        }

          // Init Traiding
          initTrading(container);



        }, container);
      });
    }
  });

}

function _reloadContainerColor(){
  for (let containerFetched in chartList) {
    _defineContainerColor(containerFetched, true);
  }
}

function _defineContainerColor(container, reload = false){

  let colorSettings = {
    'light': {
      'backgroundColor': '#f1f5f8',
      'border_color': '#f1f5f8',
      'tooltip': {
        'text_color': '#1f1f1f',
        'backgroundColor': 'rgba(255, 255, 255, 0.8)',
        'axis_point_color': '#d7dce0'
      },
      'axisLabel': {
        'label_color': '#abafb3',
        'color': '#d7dce0',
        'axis_pointer': '#d7dce0',
        'label': '#f7f8f9',
        'label_background': '#4f576d',
        'split_line': '#d7dce0',
        'pointer_color': '#f7f8f9',
      },
      'current_price_color': '#fe4f00'
    },
    'dark': {
      'backgroundColor': '#1d2435',
      'border_color': '#1d2435',
      'tooltip': {
        'text_color': '#fff',
        'backgroundColor': 'rgba(20, 27, 41, 0.8)',
        'axis_point_color': '#fff'
      },
      'axisLabel': {
        'label_color': '#a1a1a1',
        'color': '#a1a1a1',
        'axis_pointer': '#4a505f',
        'label': '#fff',
        'split_line': '#303745',
        'pointer_color': '#f7f8f9',
      },
      'current_price_color': '#fff'
    }
  }

  let colorChoice = 'dark';
  if($('body').attr('kr-theme') == "light"){
    colorChoice = 'light';
  }

  chartList[container]['option'].backgroundColor = colorSettings[colorChoice].backgroundColor;
  chartList[container]['option'].tooltip.backgroundColor = colorSettings[colorChoice].tooltip.backgroundColor;
  chartList[container]['option'].tooltip.textStyle.color = colorSettings[colorChoice].tooltip.text_color;
  chartList[container]['option'].tooltip.axisPointer.lineStyle.color = colorSettings[colorChoice].tooltip.axis_point_color;

  $.each(chartList[container]['option'].xAxis, function(k, v){
    chartList[container]['option'].xAxis[k].axisLabel.textStyle.color = colorSettings[colorChoice].axisLabel.label_color;
    chartList[container]['option'].xAxis[k].axisPointer.lineStyle.color = colorSettings[colorChoice].axisLabel.color;
    chartList[container]['option'].xAxis[k].axisPointer.label.backgroundColor = colorSettings[colorChoice].axisLabel.label_background;
    chartList[container]['option'].xAxis[k].axisPointer.label.color = colorSettings[colorChoice].axisLabel.label;

    chartList[container]['option'].xAxis[k].splitLine.lineStyle.color = colorSettings[colorChoice].axisLabel.split_line;
  });

  $.each(chartList[container]['option'].yAxis, function(k, v){
    if(chartList[container]['option'].yAxis[k].axisPointer.show){
      chartList[container]['option'].yAxis[k].axisLabel.textStyle.color = colorSettings[colorChoice].axisLabel.label_color;
      chartList[container]['option'].yAxis[k].axisPointer.lineStyle.color = colorSettings[colorChoice].axisLabel.color;
      chartList[container]['option'].yAxis[k].axisPointer.label.backgroundColor = colorSettings[colorChoice].axisLabel.label_background;
      chartList[container]['option'].yAxis[k].axisPointer.label.color = colorSettings[colorChoice].axisLabel.label;
    }

    if(chartList[container]['option'].yAxis[k].splitLine.show){
      chartList[container]['option'].yAxis[k].splitLine.lineStyle.color = colorSettings[colorChoice].axisLabel.split_line;
    }
  });

  $.each(chartList[container]['option'].grid, function(k, v){
    chartList[container]['option'].grid[k].borderColor = colorSettings[colorChoice].border_color;
  });

  chartList[container]['option'].series[0].markLine.data[0].lineStyle.color = colorSettings[colorChoice].current_price_color;
  chartList[container]['option'].series[0].markLine.precision = (colorSettings[colorChoice].current_price_color > 10 ? 2 : 5);

  if(reload){
    chartList[container]['graph'].setOption(chartList[container]['option']);
  }



}

/**
 * Load chart data
 * @param  {String} symbol       Chart symbol
 * @param  {Function} [fnc=null] Callback function
 * @param  {String} container    Container graph
 */
function loadChartData(symbol, currency, market, fnc = null, container){
  // Get chart data
  $.get($('body').attr('hrefapp') + '/app/modules/kr-dashboard/src/actions/loadChart.php', {coin:symbol, currency:currency, market:market, type:'load'}).done(function(data){
    // Parse result in JSON
    let dataParsed = jQuery.parseJSON(data);
    if(dataParsed.error == 0){ // Check not error

      listNotificationChart[container] = [];
      listOrderChart[container] = [];
      listInternalOrderChart[container] = [];

      // Add notification
      $.each(dataParsed.notification_list, function(k ,v){
        listNotificationChart[container].push({
          symb:symbol,
          value:v.value,
          type:v.type,
          id:v.id
        });
      });

      // Add order
      $.each(dataParsed.order_list, function(k , v){
        listOrderChart[container].push({
          time:v.time_order,
          type:v.type_order,
          amount:v.amount_order
        });
      });

      $.each(dataParsed.internal_order, function(k , v){
        listInternalOrderChart[container].push({
          name:v.name,
          picture:v.picture,
          type:v.type,
          date:v.date,
          order_id:v.order_id,
          me:v.me,
          amount:v.amount
        });
      });


      // Call callback
      if(fnc != null) fnc(dataParsed);

    } else {
      showAlert('Oops', dataParsed.msg, 'error');
    }
  }).fail(function(){
    showAlert('Ooops', 'Fail to load chart data', 'error');
  });
}

/**
 * Sync chart
 * @param  {String} container Graph container ID
 */
let syncContainer = {};

function syncChart(container){
  if($('#' + container).length == 0) return false;
  let symbol = $('#' + container).attr('symbol');

  if(syncContainer.hasOwnProperty(container)){
    clearTimeout(syncContainer[container]);
    syncContainer[container] = null;
  }

  // Get graph data
  $.get($('body').attr('hrefapp') + '/app/modules/kr-dashboard/src/actions/loadChart.php', {coin:symbol, type:'update', market:$('#' + container).attr('market'), currency:$('#' + container).attr('currency')}).done(function(data){

    // Parse json response
    let dataParsed = jQuery.parseJSON(data);

    if(dataParsed.error == 0){
      let vListChart = [];

      // List all data candle
      $.each(dataParsed.candles, function(k, v){ vListChart.push(v.close); });


      if(jQuery.inArray(dataParsed.candles[dataParsed.candles.length - 1].date, chartList[container]['option'].xAxis[0].data) === -1){
        chartList[container]['option'].series[0].data = chartList[container]['option'].series[0].data.slice(0, -4);
        chartList[container]['option'].series[1].data = chartList[container]['option'].series[1].data.slice(0, -4);
        chartList[container]['option'].xAxis[0].data = chartList[container]['option'].xAxis[0].data.slice(0, -4);

        chartList[container]['data_candles'] = chartList[container]['data_candles'].slice(0, -4);
        chartList[container]['data_candles'] = $.merge(chartList[container]['data_candles'], dataParsed.candles);

        chartList[container]['data'] = chartList[container]['data'].slice(0, -4);
        chartList[container]['data'] = $.merge(chartList[container]['data'], vListChart);

        $.each(dataParsed.candles, function(k, v){

          chartList[container]['option'].xAxis[0].data.push(v.date);

          chartList[container]['option'].series[0].data.push([
            v.open,
            v.close,
            v.low,
            v.high
          ]);

          if(k == dataParsed.candles.length - 1){
            chartList[container]['option'].series[1].data.push(v.open);
          } else {
            chartList[container]['option'].series[1].data.push(v.close);
          }

        });

        // Change actual price graph
        chartList[container]['option'].series[0].markLine.data[0].yAxis = dataParsed.current_price;
        chartList[container]['option'].series[0].markLine.precision = (dataParsed.current_price > 10 ? 2 : 5);

        // Update graph indicator
        updateGraphIndicator(container);

        // Reload graph
        chartList[container]['graph'].setOption(chartList[container]['option']);

        reloadContainerGraphic(container);
      }
    } else {
      //showAlert('Oops', dataParsed.msg, 'error');
    }

    syncContainer[container] = setTimeout(function(){
      if($('#' + container).attr('symbol') != "not_init"){
        syncChart(container);
      }
    }, 3000);
  }).fail(function(){
    showAlert('Ooops', 'Fail to load chart content', 'error');
  });
}
