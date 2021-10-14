
/**
 * Generate indicator index
 * @return {Int} Random number
 */
function generateIndicatorIndex(){
  return Math.floor((Math.random() * 500000) + 1);
}

/**
 * Generate empty data
 * @param  {Int} s      Start index
 * @param  {End} end    End index
 * @return {Array}      Empty data array
 */
function generateEmptyDataStart(s, end){
  let arrayAdded = [];
  for (var i = s; i < end; i++) { arrayAdded.push(0); }
  return arrayAdded;
}

/**
 * Create new indicator to graph
 * @param  {String} container Container graph
 * @param  {String} type      Type indicator
 */
function createIndicator(container, type){

  let dataIndicator = null;

  if(type == 'EMA') dataIndicator = addEMA(container);
  if(type == 'SMA') dataIndicator = addSMA(container);
  if(type == 'BBANDS') dataIndicator = addBBANDS(container);
  if(type == 'ATR') dataIndicator = addATR(container);
  if(type == 'MACD') dataIndicator = addMACD(container);
  if(type == 'SO') dataIndicator = addSO(container);
  if(type == 'RSI') dataIndicator = addRSI(container);
  if(type == 'CCI') dataIndicator = addCCI(container);
  if(type == 'ROC') dataIndicator = addROC(container);
  if(type == 'ADX') dataIndicator = addADX(container);

  // Create element indicator to add to graph indicator list
  let elemIndicator = $('<li kr-cid="' + dataIndicator.index + '" kr-tid="' + type + '">' +
                            '<span>' + dataIndicator.title + '</span>' +
                            '<ul></ul>' +
                        '</li>');

  $('#' + container).find('.kr-dash-pan-ads-i > ul').append(elemIndicator);

  // Edit indicator object
  let elemIndicatorEdit = $('<li><svg class="lnr lnr-cog"><use xlink:href="#lnr-cog"></use></svg></li>');
  elemIndicatorEdit.click(function(){
    editIndicator(container, type, dataIndicator.index);
  });
  $('#' + container).find('.kr-dash-pan-ads-i > ul > li[kr-cid="' + dataIndicator.index + '"] > ul').append(elemIndicatorEdit);

  // Toggle view indicator object
  let elemIndicatorView = $('<li><svg class="lnr lnr-eye"><use xlink:href="#lnr-eye"></use></svg></li>');
  elemIndicatorView.click(function(){ toggleIndicatorGraph(container, (dataIndicator.index - 1), type); });
  $('#' + container).find('.kr-dash-pan-ads-i > ul > li[kr-cid="' + dataIndicator.index + '"] > ul').append(elemIndicatorView);

  // Delete indicator object
  let elemIndicatorDelete = $('<li><svg class="lnr lnr-cog"><use xlink:href="#lnr-trash"></use></svg></li>');
  elemIndicatorDelete.click(function(){ deleteIndicatorGraph(container, (dataIndicator.index - 1), type); });
  $('#' + container).find('.kr-dash-pan-ads-i > ul > li[kr-cid="' + dataIndicator.index + '"] > ul').append(elemIndicatorDelete);

  // Save indicator to database
  $.post($('body').attr('hrefapp') + '/app/modules/kr-dashboard/src/actions/addIndicator.php', {chart: container, indic: type, key: dataIndicator.index, title:dataIndicator.title}).done(function(data){
    let response = jQuery.parseJSON(data);
    if(response.error == 1){
      showAlert('Oops', response.msg, 'error');
    }
  }).fail(function(){
    showAlert('Ooops', 'Fail to add indicator', 'error');
  });

}

/**
 * Format data graph
 * @param  {Array} data  Graph data
 * @return {Array}       Formated data
 */
function formatDataGraph(data){
  return jQuery.map(data, function(valData){
    if(valData > 10) return valData.toFixed(2);
    if(valData % 1 !== 0){
      let decimal = (valData + "").split(".");
      if(decimal.length < 2) return valData;
      if(decimal[1].length > 5) return valData.toFixed(5);
    }
    return valData;
  });
}

/**
 * Add indicator object graph
 * @param {String} container     Graph container ID
 * @param {[type]} data          Indicator data
 * @param {String} color         Indicator color
 * @param {Int} thickness        Indicator thickness
 * @param {String} title         Indicator title
 * @param {Number} [gridindex=0] Grid index to add indicator
 * @param {String} [type="line"] Type indicator
 * @param {String} [fill=null]   Fill color indicator
 */
function addIndicatorGraph(container, data, color, thickness, title, gridindex = 0, type = "line", fill = null){

  let arrayAdded = generateEmptyDataStart(data.length, chartList[container]['data'].length);

  // Indicator type line
  if(type == "line"){

    data = formatDataGraph(data);

    // Add indicator to graph
    chartList[container]['option'].series.push({
      name: title,
      type: type,
      xAxisIndex: gridindex,
      yAxisIndex: gridindex,
      symbolSize: 5,
      data: $.merge(arrayAdded, data),
      smooth: false,
      symbolSize: 0,
      lineStyle: {
          color: color,
          width: thickness
      },
      markLine: {
          symbol: ['none', 'none'],
          data: [],
          animation:false
      },
      itemStyle: {
        color: color
      },
      areaStyle : {
        color: color,
        opacity: (fill == null ? 0 : 0.6)
      }
    });
  } else if(type == "bar"){ // Indicator type bar
    chartList[container]['option'].series.push({
      name: 'MACD',
      type: 'bar',
      xAxisIndex: gridindex,
      yAxisIndex: gridindex,
      data: $.merge(arrayAdded, data),
      markLine: {
          data: [],
          animation:false
      },
      itemStyle: {
    	  normal: {
	          color: color
	      },
        opacity:1
      }
    });
  }

  // Reload chart
  chartList[container]['graph'].setOption(chartList[container]['option']);
}

/**
 * Refresh graph size
 * @param  {String}  container          Container graph name
 * @param  {Boolean} [reloadchart=true]
 */
function refreshGridSize(container, reloadchart = true){

  let gridListIndex = [];
  $.each(chartList[container]['option'].grid, function(k, v){
    if(chartList[container]['option'].grid[k].show) gridListIndex.push(k);
    if(k > 0 && !chartList[container]['option'].grid[k].show){
      chartList[container]['option'].grid[k].height = '0%';
      chartList[container]['option'].grid[k].top = '200%';
    }
  });

  if(gridListIndex.length == 1){
    chartList[container]['option'].grid[0].height = '100%';
  } else if(gridListIndex.length == 2){
    chartList[container]['option'].grid[0].height = '65%';
    chartList[container]['option'].grid[gridListIndex[1]].top = '70%';
    chartList[container]['option'].grid[gridListIndex[1]].height = '30%';
  } else {
    chartList[container]['option'].grid[0].height = '50%';
    for (var k = 1; k < gridListIndex.length; k++) {
      let heightGrid = ((50 / (gridListIndex.length - 1)));
      let topPos = (55 + (heightGrid * (k - 1)));
      chartList[container]['option'].grid[gridListIndex[k]].height = (heightGrid - 5) + '%';
      chartList[container]['option'].grid[gridListIndex[k]].top = topPos + '%';
    }
  }

  if(reloadchart) chartList[container]['graph'].setOption(chartList[container]['option']);

}

/**
 * Add grid graph
 * @param {String} container Graph container
 */
function addGrid(container){

  chartList[container]['option'].grid.push({
    left: '0px',
    right: '30px',
    top: '75%',
    height: '25%',
    containLabel: true,
    borderColor: '#1d2435',
    show: true,
    tooltip: {
      trigger: 'axis'
    }
  });

  // Add xAxis grid
  chartList[container]['option'].xAxis.push({
      type: 'category',
      data: chartList[container]['option'].xAxis[0].data,
      gridIndex: chartList[container]['option'].grid.length - 1,
      axisLabel: {
          margin: 8,
          textStyle: {
              fontSize: '18px',
              color: '#a1a1a1'
          }
      },
      axisTick: {
          show: false
      },
      axisPointer: {
        show: true,
        lineStyle: {
            color: '#4a505f',
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
              color: '#303745',
              type: 'solid'
          }
      }
  });

  // add yAxis grid
  chartList[container]['option'].yAxis.push({
      scale: true,
      gridIndex: chartList[container]['option'].grid.length - 1,
      position: 'right',
      boundaryGap: ['10%', '10%'],
      triggerEvent: true,
      nameGap: 100,
      axisLabel: {
        textStyle: {
            fontSize: '.18rem',
            color: '#a1a1a1'
        },
        align: 'left'
      },
      axisTick: {
          show: false
      },
      axisLine: {
          show: false,
          lineStyle: {
              color: '#3d4554'
          }
      },
      axisPointer: {
        show: true,
        lineStyle: {
            color: '#4a505f',
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
              color: '#303745',
              type: 'solid'
          }
      }
  });

  chartList[container]['option'].dataZoom.push({
      type: 'inside',
      xAxisIndex: [0, chartList[container]['option'].grid.length],
      startValue: chartList[container]['data'].length - 100,
      endValue: chartList[container]['data'].length
  });

  refreshGridSize(container, false);

  _defineContainerColor(container, false);

  // Reload graph
  chartList[container]['graph'].setOption(chartList[container]['option']);

  return chartList[container]['option'].grid.length;
}

/**
 * Add static line graph
 * @param {String} container      Graph container id
 * @param {String} color          Color line
 * @param {Float} value           Value yAxis line
 * @param {Int} gridindex         Grid index
 * @param {Number} [thickness=2]  Line thickness
 * @param {String} [type='solid'] Type line (solid, dashed, ...)
 */
function addLineChart(container, color, value, gridindex, thickness = 2, type = 'solid'){
  chartList[container]['option'].series[gridindex].markLine.data.push({
    yAxis: value,
    symbol: 'circle',
    symbolSize: [0,0],
    lineStyle: {
      color: color,
      width: thickness,
      type: type
    }
  });
  chartList[container]['graph'].setOption(chartList[container]['option']);
}

function addEMA(container, indexIndicator = generateIndicatorIndex(), period = 14, colour = '#c21b26', thickness = 1, update = false){

  if(update) return EMA.calculate({period : period, values : chartList[container]['data']});

  chartIndicator[container][indexIndicator] = {
    indicator: 'EMA',
    serieIndex: chartList[container]['option'].series.length,
    gridIndex: 0,
    parms: {  period:period, colour:colour, thickness:thickness }
  };

  addIndicatorGraph(container,
                  EMA.calculate({period : period, values : chartList[container]['data']}),
                  colour,
                  thickness,
                  "EMA (" + period + ")");

  return {
    index: indexIndicator,
    title: "EMA (" + period + ")"
  }

}

function updateEMA(container, indicatorID){
  let dataIndicator = chartIndicator[container][indicatorID];
  let valIndicator = addEMA(container, indicatorID, dataIndicator.parms.period,
                                                    dataIndicator.parms.colour,
                                                    dataIndicator.parms.thickness, true);

  valIndicator = formatDataGraph(valIndicator);

  chartList[container]['option'].series[parseInt(dataIndicator.serieIndex)].data = $.merge(generateEmptyDataStart(valIndicator.length, chartList[container]['data_candles'].length), valIndicator);
}

function changeEMA(container, indicatorID, dataStyle){

  chartIndicator[container][indicatorID].parms.period = parseInt(dataStyle.period);
  chartIndicator[container][indicatorID].parms.thickness = dataStyle.thickness;
  chartIndicator[container][indicatorID].parms.colour = dataStyle.colour;

  let dataIndicator = chartIndicator[container][indicatorID];

  chartList[container]['option'].series[dataIndicator.serieIndex].lineStyle = { color: dataStyle.colour, width: dataStyle.thickness };
  chartList[container]['option'].series[dataIndicator.serieIndex].name = 'EMA (' + dataStyle.period + ')';

  $('[kr-tid="EMA"][kr-cid="' + indicatorID + '"]').find('span').html('EMA (' + dataStyle.period + ')');

  updateSMA(container, indicatorID);

  chartList[container]['graph'].setOption(chartList[container]['option']);

}

function addSMA(container, indexIndicator = generateIndicatorIndex(), period = 14, colour = '#da4931', thickness = 1, update = false){

  if(update) return SMA.calculate({period : period, values : chartList[container]['data']});

  chartIndicator[container][indexIndicator] = {
    indicator: 'SMA',
    gridIndex: 0,
    serieIndex: chartList[container]['option'].series.length,
    parms: {  period:period, colour:colour, thickness:thickness }
  };

  addIndicatorGraph(container,
                  SMA.calculate({period : period, values : chartList[container]['data']}),
                  colour,
                  thickness,
                  "SMA (" + period + ")");

  return {
    index: indexIndicator,
    title: "SMA (" + period + ")"
  }

}

function updateSMA(container, indicatorID){
  let dataIndicator = chartIndicator[container][indicatorID];
  let valIndicator = addSMA(container, indicatorID, dataIndicator.parms.period,
                                                    dataIndicator.parms.colour,
                                                    dataIndicator.parms.thickness, true);

  valIndicator = formatDataGraph(valIndicator);

  chartList[container]['option'].series[parseInt(dataIndicator.serieIndex)].data = $.merge(generateEmptyDataStart(valIndicator.length, chartList[container]['data_candles'].length), valIndicator);
}

function changeSMA(container, indicatorID, dataStyle){

  chartIndicator[container][indicatorID].parms.period = parseInt(dataStyle.period);
  chartIndicator[container][indicatorID].parms.thickness = dataStyle.thickness;
  chartIndicator[container][indicatorID].parms.colour = dataStyle.colour;

  let dataIndicator = chartIndicator[container][indicatorID];

  chartList[container]['option'].series[dataIndicator.serieIndex].lineStyle = { color: dataStyle.colour, width: dataStyle.thickness };
  chartList[container]['option'].series[dataIndicator.serieIndex].name = 'SMA (' + dataStyle.period + ')';

  $('[kr-tid="SMA"][kr-cid="' + indicatorID + '"]').find('span').html('SMA (' + dataStyle.period + ')');

  updateSMA(container, indicatorID);

  chartList[container]['graph'].setOption(chartList[container]['option']);

}

function addBBANDS(container, indexIndicator = generateIndicatorIndex(), period = 20, deviation = 2,
                              uppercolor = "#5ff347", upperthickness = 1,
                              middlecolor = "#18dae6", middlethickness = 1,
                              lowercolor = "#c21b26", lowerthickness = 1, update = false){

  let middle = [], upper = [], lower = [];
  $.each(BollingerBands.calculate({period : period, values : chartList[container]['data'], stdDev:deviation}), function(k, v){
    middle.push(v.middle); upper.push(v.upper); lower.push(v.lower);
  });

  if(update){
    return {middle:middle, upper:upper, lower:lower};
  }

  chartIndicator[container][indexIndicator] = {
      indicator: 'BBANDS',
      gridIndex: 0,
      serieIndex: chartList[container]['option'].series.length,
      parms: {  period:period, deviation:deviation, uppercolor:uppercolor,
                upperthickness:upperthickness, middlecolor:middlecolor, middlethickness:middlethickness,
                lowercolor:lowercolor, lowerthickness:lowerthickness }
  };

  addIndicatorGraph(container, upper, uppercolor, upperthickness, "BBANDS Upper (" + period + ", " + deviation + ")");
  addIndicatorGraph(container, middle, middlecolor, middlethickness, "BBANDS Middle (" + period + ", " + deviation + ")");
  addIndicatorGraph(container, lower, lowercolor, lowerthickness, "BBANDS Lower (" + period + ", " + deviation + ")");

  return {
    index: indexIndicator,
    title: "BBANDS (" + period + ", " + deviation + ")"
  }

}

function updateBBANDS(container, indicatorID){
  let dataIndicator = chartIndicator[container][indicatorID];
  let valIndicator = addBBANDS(container, indicatorID, dataIndicator.parms.period,
                                                    dataIndicator.parms.deviation,
                                                    dataIndicator.parms.uppercolor,
                                                    dataIndicator.parms.upperthickness,
                                                    dataIndicator.parms.middlecolor,
                                                    dataIndicator.parms.middlethickness,
                                                    dataIndicator.parms.lowercolor,
                                                    dataIndicator.parms.lowerthickness, true);

  valIndicator.upper = formatDataGraph(valIndicator.upper);
  valIndicator.middle = formatDataGraph(valIndicator.middle);
  valIndicator.lower = formatDataGraph(valIndicator.lower);

  chartList[container]['option'].series[parseInt(dataIndicator.serieIndex)].data = $.merge(generateEmptyDataStart(valIndicator.upper.length, chartList[container]['data_candles'].length), valIndicator.upper);
  chartList[container]['option'].series[parseInt(dataIndicator.serieIndex) + 1].data = $.merge(generateEmptyDataStart(valIndicator.middle.length, chartList[container]['data_candles'].length), valIndicator.middle);
  chartList[container]['option'].series[parseInt(dataIndicator.serieIndex) + 2].data = $.merge(generateEmptyDataStart(valIndicator.lower.length, chartList[container]['data_candles'].length), valIndicator.lower);
}

function changeBBANDS(container, indicatorID, dataStyle){

  chartIndicator[container][indicatorID].parms.deviation = parseInt(dataStyle.deviation);
  chartIndicator[container][indicatorID].parms.lowercolor = dataStyle.lower_colour;
  chartIndicator[container][indicatorID].parms.lowerthickness = dataStyle.thickness;
  chartIndicator[container][indicatorID].parms.middlecolor = dataStyle.middle_colour;
  chartIndicator[container][indicatorID].parms.middlethickness = dataStyle.thickness;
  chartIndicator[container][indicatorID].parms.period = parseInt(dataStyle.period);
  chartIndicator[container][indicatorID].parms.uppercolor = dataStyle.upper_colour;
  chartIndicator[container][indicatorID].parms.upperthickness = dataStyle.thickness;

  let dataIndicator = chartIndicator[container][indicatorID];

  chartList[container]['option'].series[dataIndicator.serieIndex].lineStyle = { color: dataStyle.upper_colour, width: dataStyle.thickness };
  chartList[container]['option'].series[dataIndicator.serieIndex].name = 'BBANDS Upper (' + dataStyle.period + ', ' + dataStyle.deviation + ')';

  chartList[container]['option'].series[dataIndicator.serieIndex + 1].lineStyle = { color: dataStyle.middle_colour, width: dataStyle.thickness };
  chartList[container]['option'].series[dataIndicator.serieIndex + 1].name = 'BBANDS Middle (' + dataStyle.period + ', ' + dataStyle.deviation + ')';

  chartList[container]['option'].series[dataIndicator.serieIndex + 2].lineStyle = { color: dataStyle.lower_colour, width: dataStyle.thickness };
  chartList[container]['option'].series[dataIndicator.serieIndex + 2].name = 'BBANDS Lower (' + dataStyle.period + ', ' + dataStyle.deviation + ')';

  $('[kr-tid="BBANDS"][kr-cid="' + indicatorID + '"]').find('span').html('BBANDS (' + dataStyle.period + ', ' + dataStyle.deviation + ')');

  updateBBANDS(container, indicatorID);

  chartList[container]['graph'].setOption(chartList[container]['option']);
}

function addMACD(container, indexIndicator = generateIndicatorIndex(), fastperiod = 12, slowPeriod = 26, signalPeriod = 9, thickness = 1,
                                                                        macd_colour = '#5ff347', signal_colour = '#18dae6',
                                                                        historgram_up_colour = '#df2323', historgram_low_colour = '#29c359', update = false){

  let gridNum = null;
  if(!update) gridNum = addGrid(container);

  let signalData = [], macd = [], histogram = [];

  $.each(MACD.calculate({
      values            : chartList[container]['data'],
      fastPeriod        : fastperiod,
      slowPeriod        : slowPeriod,
      signalPeriod      : signalPeriod,
      SimpleMAOscillator: false,
      SimpleMASignal    : false
    }), function(k, v){
      signalData.push(v.signal);
      macd.push(v.MACD);
      histogram.push(v.histogram);
    });

  if(update) return {signalData:signalData, macd:macd, histogram:histogram};

  chartIndicator[container][indexIndicator] = {
    indicator: 'MACD',
    gridIndex: gridNum,
    serieIndex: chartList[container]['option'].series.length,
    parms: {  fastperiod:fastperiod, slowPeriod:slowPeriod, signalPeriod:signalPeriod,
              thickness:thickness, macd_colour:macd_colour, signal_colour:signal_colour,
              historgram_up_colour:historgram_up_colour, historgram_low_colour:historgram_low_colour }
  };

  addIndicatorGraph(container, histogram, function(params) {
		              if (params.data >= 0) return historgram_up_colour;
                  return historgram_low_colour;
		          }, 1, "MACD (" + fastperiod + ", " + slowPeriod + ", " + signalPeriod + ")", gridNum, 'bar');

  addIndicatorGraph(container, macd, macd_colour, thickness, "DEA (" + fastperiod + ", " + slowPeriod + ", " + signalPeriod + ")", gridNum, 'line');
  addIndicatorGraph(container, signalData, signal_colour, thickness, "DIF (" + fastperiod + ", " + slowPeriod + ", " + signalPeriod + ")", gridNum, 'line');

  return {
    index: indexIndicator,
    title: "MACD (" + fastperiod + ", " + slowPeriod + ", " + signalPeriod + ")"
  }

}

function updateMACD(container, indicatorID){
  let dataIndicator = chartIndicator[container][indicatorID];
  let valIndicator = addMACD(container, indicatorID, dataIndicator.parms.fastperiod,
                                                    dataIndicator.parms.slowPeriod,
                                                    dataIndicator.parms.signalPeriod,
                                                    dataIndicator.parms.thickness,
                                                    dataIndicator.parms.macd_colour,
                                                    dataIndicator.parms.signal_colour,
                                                    dataIndicator.parms.historgram_up_colour,
                                                    dataIndicator.parms.historgram_low_colour, true);

  valIndicator.macd = formatDataGraph(valIndicator.macd);
  valIndicator.signalData = formatDataGraph(valIndicator.signalData);

  chartList[container]['option'].series[parseInt(dataIndicator.serieIndex)].data = $.merge(generateEmptyDataStart(valIndicator.histogram.length, chartList[container]['data_candles'].length), valIndicator.histogram);
  chartList[container]['option'].series[parseInt(dataIndicator.serieIndex) + 1].data = $.merge(generateEmptyDataStart(valIndicator.macd.length, chartList[container]['data_candles'].length), valIndicator.macd);
  chartList[container]['option'].series[parseInt(dataIndicator.serieIndex) + 2].data = $.merge(generateEmptyDataStart(valIndicator.signalData.length, chartList[container]['data_candles'].length), valIndicator.signalData);
}

function changeMACD(container, indicatorID, dataStyle){

  chartIndicator[container][indicatorID].parms.fastperiod = parseInt(dataStyle.fastperiod);
  chartIndicator[container][indicatorID].parms.slowPeriod = parseInt(dataStyle.slowperiod);
  chartIndicator[container][indicatorID].parms.signalPeriod = parseInt(dataStyle.signalperiod);
  chartIndicator[container][indicatorID].parms.thickness = dataStyle.thickness;
  chartIndicator[container][indicatorID].parms.macd_colour = dataStyle.macd_colour;
  chartIndicator[container][indicatorID].parms.signal_colour = dataStyle.signal_colour;

  let dataIndicator = chartIndicator[container][indicatorID];

  chartList[container]['option'].series[dataIndicator.serieIndex + 1].lineStyle = { color: dataStyle.macd_colour, width: dataStyle.thickness };
  chartList[container]['option'].series[dataIndicator.serieIndex + 1].name = 'DEA (' + dataStyle.fastperiod + ', ' + dataStyle.slowperiod + ', ' + dataStyle.signalperiod + ')';

  chartList[container]['option'].series[dataIndicator.serieIndex + 2].lineStyle = { color: dataStyle.signal_colour, width: dataStyle.thickness };
  chartList[container]['option'].series[dataIndicator.serieIndex + 2].name = 'DIF (' + dataStyle.fastperiod + ', ' + dataStyle.slowperiod + ', ' + dataStyle.signalperiod + ')';

  $('[kr-tid="MACD"][kr-cid="' + indicatorID + '"]').find('span').html('MACD (' + dataStyle.fastperiod + ', ' + dataStyle.slowperiod + ', ' + dataStyle.signalperiod + ')');

  updateMACD(container, indicatorID);

  chartList[container]['graph'].setOption(chartList[container]['option']);
}

function addATR(container, indexIndicator = generateIndicatorIndex(), period = 14, colour = '#ef642e', thickness = 1, update = false){

  let gridNum = null;
  if(!update) gridNum = addGrid(container);

  let atrArgs = { high : [], low : [], close : [], period: period};

  $.each(chartList[container]['data_candles'], function(k, v){
    atrArgs['high'].push(v.high); atrArgs['low'].push(v.low); atrArgs['close'].push(v.close);
  });

  if(update) return ATR.calculate(atrArgs);

  chartIndicator[container][indexIndicator] = {
    indicator: 'ATR',
    gridIndex: gridNum,
    serieIndex: chartList[container]['option'].series.length,
    parms: { period:period, colour:colour, thickness:thickness }
  };

  addIndicatorGraph(container,
                  ATR.calculate(atrArgs),
                  colour,
                  thickness,
                  "ATR (" + period + ")", gridNum, 'line');

  return {
    index: indexIndicator,
    title: "ATR (" + period + ")"
  }

}

function updateATR(container, indicatorID){
  let dataIndicator = chartIndicator[container][indicatorID];
  let valIndicator = addATR(container, indicatorID, dataIndicator.parms.period,
                                                    dataIndicator.parms.color,
                                                    dataIndicator.parms.thickness, true);

  valIndicator = formatDataGraph(valIndicator);

  chartList[container]['option'].series[parseInt(dataIndicator.serieIndex)].data = $.merge(generateEmptyDataStart(valIndicator.length, chartList[container]['data_candles'].length), valIndicator);
}

function changeATR(container, indicatorID, dataStyle){

  chartIndicator[container][indicatorID].parms.period = parseInt(dataStyle.period);
  chartIndicator[container][indicatorID].parms.thickness = dataStyle.thickness;
  chartIndicator[container][indicatorID].parms.colour = dataStyle.colour;

  let dataIndicator = chartIndicator[container][indicatorID];

  chartList[container]['option'].series[dataIndicator.serieIndex].lineStyle = { color: dataStyle.colour, width: dataStyle.thickness };
  chartList[container]['option'].series[dataIndicator.serieIndex].name = 'ATR (' + dataStyle.period + ')';

  $('[kr-tid="ATR"][kr-cid="' + indicatorID + '"]').find('span').html('ATR (' + dataStyle.period + ')');

  updateSMA(container, indicatorID);

  chartList[container]['graph'].setOption(chartList[container]['option']);

}

function addSO(container, indexIndicator = generateIndicatorIndex(), kPeriod = 14, dPeriod = 3, thickness = 1, overbuy = 80,
                                  overbuy_color = "#5ff347", oversell = 20, oversell_color = "#c21b26", kseries_color = "#d0e521", dseries_color = "#18dae6", update = false){

  let gridNum = null;
  if(!update) gridNum = addGrid(container);

  let soArgs = { high : [], low : [], close : [], period: kPeriod, signalPeriod:dPeriod};

  $.each(chartList[container]['data_candles'], function(k, v){
    soArgs['high'].push(v.high); soArgs['low'].push(v.low); soArgs['close'].push(v.close);
  });

  let k = [], d = [];

  $.each(Stochastic.calculate(soArgs), function(ks, vs){
    k.push(vs.k); d.push(vs.d);
  });

  if(update) return {k:k, d:d};

  chartIndicator[container][indexIndicator] = {
    indicator: 'SO',
    gridIndex: gridNum,
    serieIndex: chartList[container]['option'].series.length,
    parms: { kPeriod:kPeriod, dPeriod:dPeriod, thickness:thickness, overbuy:overbuy,
            overbuy_color:overbuy_color, oversell:oversell, oversell_color:oversell_color,
            kseries_color:kseries_color, dseries_color:dseries_color }
  };

  addIndicatorGraph(container, k, kseries_color,  thickness, "SO K (" + kPeriod + ", " + dPeriod + ")", gridNum, 'line');

  addIndicatorGraph(container, d, dseries_color, thickness, "SO D (" + kPeriod + ", " + dPeriod + ")", gridNum, 'line');

  addLineChart(container, overbuy_color, overbuy, chartList[container]['option'].series.length - 1);
  addLineChart(container, oversell_color, oversell, chartList[container]['option'].series.length - 1);

  return {
    index: indexIndicator,
    title: "SO (" + kPeriod + ", " + dPeriod + ")"
  }

}

function updateSO(container, indicatorID){
  let dataIndicator = chartIndicator[container][indicatorID];
  let valIndicator = addSO(container, indicatorID, dataIndicator.parms.kPeriod,
                                                    dataIndicator.parms.dPeriod,
                                                    dataIndicator.parms.thickness,
                                                    dataIndicator.parms.overbuy,
                                                    dataIndicator.parms.overbuy_color,
                                                    dataIndicator.parms.oversell,
                                                    dataIndicator.parms.oversell_color,
                                                    dataIndicator.parms.kseries_color,
                                                    dataIndicator.parms.dseries_color, true);

  valIndicator.d = formatDataGraph(valIndicator.d);
  valIndicator.k = formatDataGraph(valIndicator.k);

  chartList[container]['option'].series[parseInt(dataIndicator.serieIndex)].data = $.merge(generateEmptyDataStart(valIndicator.d.length, chartList[container]['data_candles'].length), valIndicator.d);
  chartList[container]['option'].series[parseInt(dataIndicator.serieIndex) + 1].data = $.merge(generateEmptyDataStart(valIndicator.k.length, chartList[container]['data_candles'].length), valIndicator.k);

}

function changeSO(container, indicatorID, dataStyle){

  chartIndicator[container][indicatorID].parms.kPeriod = parseInt(dataStyle.kperiod);
  chartIndicator[container][indicatorID].parms.dPeriod = parseInt(dataStyle.dperiod);
  chartIndicator[container][indicatorID].parms.thickness = dataStyle.thickness;
  chartIndicator[container][indicatorID].parms.overbuy = dataStyle.overbuy_value;
  chartIndicator[container][indicatorID].parms.overbuy_color = dataStyle.overbuy_color;
  chartIndicator[container][indicatorID].parms.oversell = dataStyle.oversold_value;
  chartIndicator[container][indicatorID].parms.oversell_color = dataStyle.oversold_color;
  chartIndicator[container][indicatorID].parms.kseries_color = dataStyle.kseries_color;
  chartIndicator[container][indicatorID].parms.dseries_color = dataStyle.dseries_color;

  let dataIndicator = chartIndicator[container][indicatorID];

  chartList[container]['option'].series[dataIndicator.serieIndex].lineStyle = { color: dataStyle.dseries_color, width: dataStyle.thickness };
  chartList[container]['option'].series[dataIndicator.serieIndex].name = 'SO D (' + dataStyle.kperiod + ', ' + dataStyle.dperiod + ')';

  chartList[container]['option'].series[dataIndicator.serieIndex + 1].lineStyle = { color: dataStyle.kseries_color, width: dataStyle.thickness };
  chartList[container]['option'].series[dataIndicator.serieIndex + 1].name = 'SO K (' + dataStyle.kperiod + ', ' + dataStyle.dperiod + ')';

  chartList[container]['option'].series[dataIndicator.serieIndex + 1].markLine.data[0].lineStyle.color = dataStyle.overbuy_color;
  chartList[container]['option'].series[dataIndicator.serieIndex + 1].markLine.data[0].yAxis = dataStyle.overbuy_value;

  chartList[container]['option'].series[dataIndicator.serieIndex + 1].markLine.data[1].lineStyle.color = dataStyle.oversold_color;
  chartList[container]['option'].series[dataIndicator.serieIndex + 1].markLine.data[1].yAxis = dataStyle.oversold_value;

  $('[kr-tid="SO"][kr-cid="' + indicatorID + '"]').find('span').html('SO (' + dataStyle.kperiod + ', ' + dataStyle.dperiod + ')');

  updateSO(container, indicatorID);

  chartList[container]['graph'].setOption(chartList[container]['option']);
}

function addRSI(container, indexIndicator = generateIndicatorIndex(), period = 14, color = '#18dae6', thickness = 1, over = 70, over_color = '#5ff347', under = 30, under_color = '#c21b26', update = false){

  let gridNum = null;
  if(!update) gridNum = addGrid(container);

  if(update) return RSI.calculate({values: chartList[container]['data'], period: period});

  chartIndicator[container][indexIndicator] = {
    indicator: 'RSI',
    gridIndex: gridNum,
    serieIndex: chartList[container]['option'].series.length,
    parms: { period:period, color:color, thickness:thickness, over:over, over_color:over_color, under:under, under_color:under_color }
  };

  addIndicatorGraph(container,
                  RSI.calculate({values: chartList[container]['data'], period: period}),
                  color,
                  thickness,
                  "RSI (" + period + ")", gridNum, 'line');

  addLineChart(container, over_color, over, chartList[container]['option'].series.length - 1);
  addLineChart(container, under_color, under, chartList[container]['option'].series.length - 1);

  return {
    index: indexIndicator,
    title: "RSI (" + period + ")"
  }

}

function updateRSI(container, indicatorID){
  let dataIndicator = chartIndicator[container][indicatorID];
  let valIndicator = addRSI(container, indicatorID, dataIndicator.parms.period,
                                                    dataIndicator.parms.color,
                                                    dataIndicator.parms.thickness,
                                                    dataIndicator.parms.over,
                                                    dataIndicator.parms.over_color,
                                                    dataIndicator.parms.under,
                                                    dataIndicator.parms.under_color, true);

  valIndicator = formatDataGraph(valIndicator);

  chartList[container]['option'].series[parseInt(dataIndicator.serieIndex)].data = $.merge(generateEmptyDataStart(valIndicator.length, chartList[container]['data_candles'].length), valIndicator);
}

function changeRSI(container, indicatorID, dataStyle){

  chartIndicator[container][indicatorID].parms.period = parseInt(dataStyle.period);
  chartIndicator[container][indicatorID].parms.color = dataStyle.colour;
  chartIndicator[container][indicatorID].parms.thickness = dataStyle.thickness;
  chartIndicator[container][indicatorID].parms.over = dataStyle.over_value;
  chartIndicator[container][indicatorID].parms.over_color = dataStyle.over_color;
  chartIndicator[container][indicatorID].parms.under = dataStyle.under_value;
  chartIndicator[container][indicatorID].parms.under_color = dataStyle.under_color;

  let dataIndicator = chartIndicator[container][indicatorID];

  chartList[container]['option'].series[dataIndicator.serieIndex].lineStyle = { color: dataStyle.colour, width: dataStyle.thickness };
  chartList[container]['option'].series[dataIndicator.serieIndex].name = 'RSI (' + dataStyle.period + ')';

  chartList[container]['option'].series[dataIndicator.gridIndex].markLine.data[0].lineStyle.color = dataStyle.over_color;
  chartList[container]['option'].series[dataIndicator.gridIndex].markLine.data[0].yAxis = dataStyle.over_value;

  chartList[container]['option'].series[dataIndicator.serieIndex].markLine.data[1].lineStyle.color = dataStyle.under_color;
  chartList[container]['option'].series[dataIndicator.serieIndex].markLine.data[1].yAxis = dataStyle.under_value;

  $('[kr-tid="RSI"][kr-cid="' + indicatorID + '"]').find('span').html('SO (' + dataStyle.period + ')');

  updateRSI(container, indicatorID);

  chartList[container]['graph'].setOption(chartList[container]['option']);
}

function addCCI(container, indexIndicator = generateIndicatorIndex(), period = 14, color = '#5ff347', thickness = 1, color_trend = '#eda129', update = false){

  let gridNum = null;
  if(!update) gridNum = addGrid(container);

  let cciArgs = { open: [], high : [], low : [], close : [], period: period};

  $.each(chartList[container]['data_candles'], function(k, v){
    cciArgs['high'].push(v.high); cciArgs['low'].push(v.low); cciArgs['close'].push(v.close);
    cciArgs['open'].push(v.open);
  });

  if(update) return CCI.calculate(cciArgs);

  chartIndicator[container][indexIndicator] = {
    indicator: 'CCI',
    gridIndex: gridNum,
    serieIndex: chartList[container]['option'].series.length,
    parms: { period:period, color:color, thickness:thickness, color_trend:color_trend }
  };

  addIndicatorGraph(container,
            CCI.calculate(cciArgs),
            color,
            thickness,
            "CCI (" + period + ")", gridNum, 'line');

  addLineChart(container, color_trend, 100, chartList[container]['option'].series.length - 1, 1);
  addLineChart(container, color_trend, -100, chartList[container]['option'].series.length - 1, 1);

  return {
    index: indexIndicator,
    title: "CCI (" + period + ")"
  }

}

function updateCCI(container, indicatorID){
  let dataIndicator = chartIndicator[container][indicatorID];
  let valIndicator = addCCI(container, indicatorID, dataIndicator.parms.period,
                                                    dataIndicator.parms.color,
                                                    dataIndicator.parms.thickness,
                                                    dataIndicator.parms.color_trend, true);

  valIndicator = formatDataGraph(valIndicator);

  chartList[container]['option'].series[parseInt(dataIndicator.serieIndex)].data = $.merge(generateEmptyDataStart(valIndicator.length, chartList[container]['data_candles'].length), valIndicator);
}

function changeCCI(container, indicatorID, dataStyle){

  chartIndicator[container][indicatorID].parms.period = parseInt(dataStyle.period);
  chartIndicator[container][indicatorID].parms.color = dataStyle.colour;
  chartIndicator[container][indicatorID].parms.thickness = dataStyle.thickness;
  chartIndicator[container][indicatorID].parms.color_trend = dataStyle.colour_trend;

  let dataIndicator = chartIndicator[container][indicatorID];

  chartList[container]['option'].series[dataIndicator.serieIndex].lineStyle = { color: dataStyle.colour, width: dataStyle.thickness };
  chartList[container]['option'].series[dataIndicator.serieIndex].name = 'CCI (' + dataStyle.period + ')';

  chartList[container]['option'].series[dataIndicator.gridIndex].markLine.data[0].lineStyle.color = dataStyle.colour_trend;

  chartList[container]['option'].series[dataIndicator.serieIndex].markLine.data[1].lineStyle.color = dataStyle.colour_trend;

  $('[kr-tid="CCI"][kr-cid="' + indicatorID + '"]').find('span').html('CCI (' + dataStyle.period + ')');

  updateCCI(container, indicatorID);

  chartList[container]['graph'].setOption(chartList[container]['option']);
}

function addROC(container, indexIndicator = generateIndicatorIndex(), period = 12, color = '#5ff347', thickness = 1, update = false){

  let gridNum = null;
  if(!update) gridNum = addGrid(container);

  if(update) return ROC.calculate({values:chartList[container]['data'], period:period});

  chartIndicator[container][indexIndicator] = {
    indicator: 'ROC',
    gridIndex: gridNum,
    serieIndex: chartList[container]['option'].series.length,
    parms: { period:period, color:color, thickness:thickness }
  };

  addIndicatorGraph(container,
            ROC.calculate({values:chartList[container]['data'], period:period}),
            color,
            thickness,
            "ROC (" + period + ")", gridNum, 'line');

  addLineChart(container, '#f5f5f5', 0, chartList[container]['option'].series.length - 1, 1, 'dashed');

  return {
    index: indexIndicator,
    title: "ROC (" + period + ")"
  }

}

function updateROC(container, indicatorID){
  let dataIndicator = chartIndicator[container][indicatorID];
  let valIndicator = addROC(container, indicatorID, dataIndicator.parms.period,
                                                    dataIndicator.parms.color,
                                                    dataIndicator.parms.thickness, true);
  valIndicator = formatDataGraph(valIndicator);

  chartList[container]['option'].series[parseInt(dataIndicator.serieIndex)].data = $.merge(generateEmptyDataStart(valIndicator.length, chartList[container]['data_candles'].length), valIndicator);
}

function changeROC(container, indicatorID, dataStyle){

  chartIndicator[container][indicatorID].parms.period = parseInt(dataStyle.period);
  chartIndicator[container][indicatorID].parms.color = dataStyle.colour;
  chartIndicator[container][indicatorID].parms.thickness = dataStyle.thickness;

  let dataIndicator = chartIndicator[container][indicatorID];

  chartList[container]['option'].series[dataIndicator.serieIndex].lineStyle = { color: dataStyle.colour, width: dataStyle.thickness };
  chartList[container]['option'].series[dataIndicator.serieIndex].name = 'ROC (' + dataStyle.period + ')';

  $('[kr-tid="ROC"][kr-cid="' + indicatorID + '"]').find('span').html('ROC (' + dataStyle.period + ')');

  updateROC(container, indicatorID);

  chartList[container]['graph'].setOption(chartList[container]['option']);
}

function addADX(container, indexIndicator = generateIndicatorIndex(), period = 14, thickness = 1, adxcolor = '#5ff347', mdicolor = '#da4931', pdicolor = '#c21b26', update = false){

  let gridNum = null;
  if(!update) gridNum = addGrid(container);

  let adxArgs = { high : [], low : [], close : [], period: period};

  $.each(chartList[container]['data_candles'], function(k, v){
    adxArgs['high'].push(v.high); adxArgs['low'].push(v.low); adxArgs['close'].push(v.close);
  });

  let adx = [], mdi = [], pdi = [];

  $.each(ADX.calculate(adxArgs), function(k, v){
    adx.push(v.adx); mdi.push(v.mdi); pdi.push(v.pdi);
  });

  if(update) return {adx:adx, mdi:mdi, pdi:pdi};

  chartIndicator[container][indexIndicator] = {
    indicator: 'ADX',
    gridIndex: gridNum,
    serieIndex: chartList[container]['option'].series.length,
    parms: { period:period, thickness:thickness, adxcolor:adxcolor, mdicolor:mdicolor, pdicolor:pdicolor }
  };

  addIndicatorGraph(container, adx, adxcolor, thickness, "ADX (" + period + ")", gridNum, 'line');
  addIndicatorGraph(container, mdi, mdicolor, thickness, "MDI (" + period + ")", gridNum, 'line');
  addIndicatorGraph(container, pdi, pdicolor, thickness, "PDI (" + period + ")", gridNum, 'line');

  return {
    index: indexIndicator,
    title: "ADX (" + period + ")"
  }

}

function updateADX(container, indicatorID){
  let dataIndicator = chartIndicator[container][indicatorID];
  let valIndicator = addADX(container, indicatorID, dataIndicator.parms.period,
                                                    dataIndicator.parms.thickness,
                                                    dataIndicator.parms.adxcolor,
                                                    dataIndicator.parms.mdicolor,
                                                    dataIndicator.parms.pdicolor, true);

  valIndicator.adx = formatDataGraph(valIndicator.adx);
  valIndicator.mdi = formatDataGraph(valIndicator.mdi);
  valIndicator.pdi = formatDataGraph(valIndicator.pdi);

  chartList[container]['option'].series[parseInt(dataIndicator.serieIndex)].data = $.merge(generateEmptyDataStart(valIndicator.adx.length, chartList[container]['data_candles'].length), valIndicator.adx);
  chartList[container]['option'].series[parseInt(dataIndicator.serieIndex) + 1].data = $.merge(generateEmptyDataStart(valIndicator.adx.length, chartList[container]['data_candles'].length), valIndicator.mdi);
  chartList[container]['option'].series[parseInt(dataIndicator.serieIndex) + 2].data = $.merge(generateEmptyDataStart(valIndicator.adx.length, chartList[container]['data_candles'].length), valIndicator.pdi);
}

function changeADX(container, indicatorID, dataStyle){

  chartIndicator[container][indicatorID].parms.period = parseInt(dataStyle.period);
  chartIndicator[container][indicatorID].parms.thickness = dataStyle.thickness;
  chartIndicator[container][indicatorID].parms.adxcolor = dataStyle.adxseries_color;
  chartIndicator[container][indicatorID].parms.mdicolor = dataStyle.ndi_color;
  chartIndicator[container][indicatorID].parms.pdicolor = dataStyle.pdi_color;

  let dataIndicator = chartIndicator[container][indicatorID];


  chartList[container]['option'].series[dataIndicator.serieIndex].lineStyle = { color: dataStyle.adxseries_color, width: dataStyle.thickness };
  chartList[container]['option'].series[dataIndicator.serieIndex].name = 'ADX (' + dataStyle.period + ')';

  chartList[container]['option'].series[dataIndicator.serieIndex + 1].lineStyle = { color: dataStyle.ndi_color, width: dataStyle.thickness };
  chartList[container]['option'].series[dataIndicator.serieIndex + 1].name = 'MDI (' + dataStyle.period + ')';

  chartList[container]['option'].series[dataIndicator.serieIndex + 2].lineStyle = { color: dataStyle.pdi_color, width: dataStyle.thickness };
  chartList[container]['option'].series[dataIndicator.serieIndex + 2].name = 'PDI (' + dataStyle.period + ')';

  $('[kr-tid="ADX"][kr-cid="' + indicatorID + '"]').find('span').html('ADX (' + dataStyle.period + ')');

  updateADX(container, indicatorID);

  chartList[container]['graph'].setOption(chartList[container]['option']);
}

/**
 * Update graph indicator
 * @param  {String} container Graph container id
 */
function updateGraphIndicator(container){
  $.each(chartIndicator[container], function(k, v){
    if(v.indicator == 'EMA') updateEMA(container, k);
    if(v.indicator == 'SMA') updateSMA(container, k);
    if(v.indicator == 'BBANDS') updateBBANDS(container, k);
    if(v.indicator == 'ATR') updateATR(container, k);
    if(v.indicator == 'MACD') updateMACD(container, k);
    if(v.indicator == 'SO') updateSO(container, k);
    if(v.indicator == 'CMF') updateCMF(container, k);
    if(v.indicator == 'RSI') updateRSI(container, k);
    if(v.indicator == 'CCI') updateCCI(container, k);
    if(v.indicator == 'ROC') updateROC(container, k);
    if(v.indicator == 'ADX') updateADX(container, k);
  });
}

/**
 * Init indicator graph element
 * @param  {String} graphElement Graph container
 */
function initIndicatorsGraph(graphElement){
  graphElement = $('.kr-dash-pan-cry[id="' + graphElement + '"]');
  graphElement.find('.kr-dash-pan-ads-i > ul > li').each(function(){
    let indicatorElement = $(this);

    // Init indicator
    let argsList = $(this).attr('kr-id-args').split(',');

    if($(this).attr('kr-id-args').length == 0) argsList = [];


    if(indicatorElement.attr('kr-tid') == "EMA") addEMA(graphElement.attr('id'), indicatorElement.attr('kr-cid'), parseInt(argsList[0]), argsList[1], argsList[2]);
    if(indicatorElement.attr('kr-tid') == "SMA") addSMA(graphElement.attr('id'), indicatorElement.attr('kr-cid'), parseInt(argsList[0]), argsList[1], argsList[2]);
    if(indicatorElement.attr('kr-tid') == "BBANDS") addBBANDS(graphElement.attr('id'), indicatorElement.attr('kr-cid'), parseInt(argsList[0]), parseInt(argsList[1]), argsList[3], argsList[2], argsList[4], argsList[2], argsList[5], argsList[2]);
    if(indicatorElement.attr('kr-tid') == "ATR") addATR(graphElement.attr('id'), indicatorElement.attr('kr-cid'), parseInt(argsList[0]), argsList[1], argsList[2]);
    if(indicatorElement.attr('kr-tid') == "MACD") addMACD(graphElement.attr('id'), indicatorElement.attr('kr-cid'), parseInt(argsList[0]), parseInt(argsList[1]), parseInt(argsList[2]), parseInt(argsList[3]), argsList[4], argsList[5], argsList[6]);
    if(indicatorElement.attr('kr-tid') == "SO") addSO(graphElement.attr('id'), indicatorElement.attr('kr-cid'), parseInt(argsList[0]), parseInt(argsList[1]), parseInt(argsList[2]), argsList[3], argsList[4], argsList[5], argsList[6], argsList[7], argsList[8], argsList[9]);
    if(indicatorElement.attr('kr-tid') == "CCI") addCCI(graphElement.attr('id'), indicatorElement.attr('kr-cid'), parseInt(argsList[0]), argsList[1], argsList[2], argsList[3]);
    if(indicatorElement.attr('kr-tid') == "ROC") addROC(graphElement.attr('id'), indicatorElement.attr('kr-cid'), parseInt(argsList[0]), argsList[1], argsList[2]);
    if(indicatorElement.attr('kr-tid') == "ADX") addADX(graphElement.attr('id'), indicatorElement.attr('kr-cid'), parseInt(argsList[0]), argsList[1], argsList[2], argsList[3], argsList[4], argsList[5]);
    if(indicatorElement.attr('kr-tid') == "RSI") addRSI(graphElement.attr('id'), indicatorElement.attr('kr-cid'), parseInt(argsList[0]), argsList[1], argsList[2], argsList[3], argsList[4], argsList[5], argsList[6]);

    $(this).find('.lnr-cog').off('click').click(function(){
      editIndicator(graphElement.attr('id'), indicatorElement.attr('kr-tid'), indicatorElement.attr('kr-cid'));
    });

    $(this).find('.lnr-eye').off('click').click(function(){
      toggleIndicatorGraph(graphElement.attr('id'), parseInt(indicatorElement.attr('kr-cid')) - 1, indicatorElement.attr('kr-tid'));
    });

    $(this).find('.lnr-trash').off('click').click(function(){
      deleteIndicatorGraph(graphElement.attr('id'), parseInt(indicatorElement.attr('kr-cid')) - 1, indicatorElement.attr('kr-tid'));
    });



  });
}

/**
 * Update indicator graph
 * @param  {Array} dataIndicator  Data indicator
 */
function updateIndicatorStyle(dataIndicator){
  if(dataIndicator.indicator == "EMA") changeEMA(dataIndicator.container, dataIndicator.indicator_key, dataIndicator.data);
  if(dataIndicator.indicator == "SMA") changeSMA(dataIndicator.container, dataIndicator.indicator_key, dataIndicator.data);
  if(dataIndicator.indicator == "BBANDS") changeBBANDS(dataIndicator.container, dataIndicator.indicator_key, dataIndicator.data);
  if(dataIndicator.indicator == "ATR") changeATR(dataIndicator.container, dataIndicator.indicator_key, dataIndicator.data);
  if(dataIndicator.indicator == "MACD") changeMACD(dataIndicator.container, dataIndicator.indicator_key, dataIndicator.data);
  if(dataIndicator.indicator == "SO") changeSO(dataIndicator.container, dataIndicator.indicator_key, dataIndicator.data);
  if(dataIndicator.indicator == "CCI") changeCCI(dataIndicator.container, dataIndicator.indicator_key, dataIndicator.data);
  if(dataIndicator.indicator == "ROC") changeROC(dataIndicator.container, dataIndicator.indicator_key, dataIndicator.data);
  if(dataIndicator.indicator == "ADX") changeADX(dataIndicator.container, dataIndicator.indicator_key, dataIndicator.data);
  if(dataIndicator.indicator == "RSI") changeRSI(dataIndicator.container, dataIndicator.indicator_key, dataIndicator.data);
}

/**
 * Toggle graph indicator visible
 * @param  {String} container          Graph container id
 * @param  {String} indexIndicator     Index indicator
 * @param  {String} typeIndicator      Type indicator
 * @param  {Boolean} [forcehide=false] Force to hide indicator
 */
function toggleIndicatorGraph(container, indexIndicator, typeIndicator, forcehide = false){

  let dataIndicator = chartIndicator[container][indexIndicator + 1];

  nSeries = 1;
  if(typeIndicator == "BBANDS") nSeries = 3;
  if(typeIndicator == "MACD") nSeries = 3;
  if(typeIndicator == "SO") nSeries = 2;
  if(typeIndicator == "ADX") nSeries = 3;

  let copacity = 1;
  try {
    copacity = chartList[container]['option'].series[dataIndicator.serieIndex].lineStyle.opacity;
  } catch (e) {
    copacity = chartList[container]['option'].series[dataIndicator.serieIndex].itemStyle.opacity;
  }

  if(forcehide) copacity = 1;

  for (var i = dataIndicator.serieIndex; i <= dataIndicator.serieIndex + (nSeries - 1); i++) {
    try {
      chartList[container]['option'].series[i].lineStyle.opacity = (copacity == 0 ? 1 : 0);
      chartList[container]['option'].series[i].symbolSize = (copacity == 0 ? 5 : 0);
    } catch (e) {
      chartList[container]['option'].series[i].itemStyle.opacity = (copacity == 0 ? 1 : 0);
    }
    $('[kr-cid="' + (indexIndicator + 1) + '"][kr-tid="' + typeIndicator + '"]').find('.lnr-eye').css('fill', (copacity == 0 ? '#f4f6ff' : '#515461'));
  }

  if(dataIndicator.gridIndex != 0){
    chartList[container]['option'].grid[dataIndicator.gridIndex - 1].show = (copacity == 0 ? true : false);
    refreshGridSize(container, false);
  }

  chartList[container]['graph'].setOption(chartList[container]['option']);

}

/**
 * Delete indicator graph
 * @param  {String} container      Graph container id
 * @param  {String} indexIndicator Index indicator
 * @param  {String} typeIndicator  Type indicator
 */
function deleteIndicatorGraph(container, indexIndicator, typeIndicator){

  toggleIndicatorGraph(container, indexIndicator, typeIndicator, true);

  $('li[kr-cid="' + (indexIndicator + 1) + '"]').remove();

  $.post($('body').attr('hrefapp') + '/app/modules/kr-dashboard/src/actions/removeIndicator.php', {chart: container, indic: typeIndicator, key: indexIndicator}).done(function(data){
    let response = jQuery.parseJSON(data);
    if(response.error == 1){
      showAlert('Oops', response.msg, 'error');
    }
  }).fail(function(){
    showAlert('Ooops', 'Fail to delete indicator', 'error');
  });


}
