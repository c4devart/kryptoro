function loadChartOrder(container, symbol, amount, date, type){

  var opt = chartList[container]['option'];

  let chartDate = opt['xAxis'][0]['data'];
  let indexDate = jQuery.inArray(date, chartDate);

  if(indexDate == -1) return true;

  let priceChart = opt['series'][0]['data'];

  opt.series[0].markPoint.data.push({
      value: amount,
      symbolSize: 40,
      coord: [indexDate, priceChart[indexDate][3]],
      itemStyle: {
        color: (type == "SELL" ? 'rgba(223,35,35,1)' : 'rgba(41,195,89,1)')
      }
  });


chartList[container]['graph'].setOption(chartList[container]['option']);

}

function addInternalChartOrder(container, symbol, name, picture, date, type, orderid){
  return false;
  var opt = chartList[container]['option'];
  let countDate = 1;

  let chartDate = opt['xAxis'][0]['data'];
  let indexDate = jQuery.inArray(date, chartDate);

  if(indexDate == -1) return true;

  let priceChart = opt['series'][0]['data'];



  $.each(opt.series[1].markPoint.data, function(k, v){
    if(v.coord[0] == indexDate) countDate++;
  });

  opt.series[1].markPoint.data.push({
      value: name,
      valueDim: orderid,
      symbol: 'image://https://krypto.dev.ovrley.com/public/user/6090/15adb057c9704a-t%C3%A9l%C3%A9chargement.jpg',
      symbolSize: [17, 17],
      coord: [indexDate, priceChart[indexDate][3]],
      y: '100%',
      symbolOffset: [0, -13 + (-22 * countDate)],
      label: {
        position:'right',
        fontSize:10,
        color: (type == "SELL" ? 'rgba(223,35,35,1)' : 'rgba(41,195,89,1)')
      }
  });

  let lineMarkFind = false;
  $.each(opt.series[1].markLine.data, function(k, mk){
    if(mk.xAxis == indexDate) lineMarkFind = true;
  });

  if(!lineMarkFind){
    opt.series[1].markLine.data.push({
      name: 'notification-dzdz',
      xAxis: indexDate,
      symbol: 'circle',
      symbolSize: [0,0],
      lineStyle: {
        color: '#f4f6f9',
        width: 1,
        type: 'solid'
      }
    });
  }

  chartList[container]['graph'].setOption(chartList[container]['option']);

}
