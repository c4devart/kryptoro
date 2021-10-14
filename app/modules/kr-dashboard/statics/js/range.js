
/**
 * Init range graph pannel
 * @param  {String} container Graph container
 */
function initRangePan(container){
  // Change range graph
  changeRangeGraph(container, 120, false);
  $('#' + container).find('.kr-dash-pan-ranges').find('li').off('click').click(function(){
    changeRangeGraph(container, parseInt($(this).attr('rangemax')));
  });
}

/**
 * Change range graph action
 * @param  {String}  container     Graph container
 * @param  {Int}  timeRange        Range time (in minutes)
 * @param  {Boolean} [change=true] Reload graph
 */
function changeRangeGraph(container, timeRange, change = true){

  // Get current date, make starting date
  let currentDate = chartList[container]['option'].xAxis[0].data[chartList[container]['option'].xAxis[0].data.length - 1];
  if(currentDate == null) return false;
  currentDate = currentDate.split(' ');
  let currentDay = currentDate[0].split('/');
  let currentHours = currentDate[1].split(':');
  currentDate = new Date(parseInt(currentDay[2]), parseInt(currentDay[1]) - 1, parseInt(currentDay[0]), parseInt(currentHours[0]), parseInt(currentHours[1]), 0);
  let startingDate = new Date();

  startingDate.setTime(((currentDate.getTime() / 1000) - (parseInt(timeRange) * 60)) * 1000);

  // Create formated date
  let formatedDate = (startingDate.getDate() < 10 ? '0' + startingDate.getDate() : startingDate.getDate()) + '/' +
              ((startingDate.getMonth() + 1) < 10 ? '0' + (startingDate.getMonth() + 1) : (startingDate.getMonth() + 1)) + '/' +
              startingDate.getFullYear() + ' ' +
              (startingDate.getHours() < 10 ? '0' + startingDate.getHours() : startingDate.getHours()) + ':' +
              (startingDate.getMinutes() < 10 ? '0' + startingDate.getMinutes() : startingDate.getMinutes()) + ':00';

  // Check if range select is in graph, else try to get without minutes
  if($.inArray(formatedDate, chartList[container]['option'].xAxis[0].data) === -1){
    formatedDate = (startingDate.getDate() < 10 ? '0' + startingDate.getDate() : startingDate.getDate()) + '/' +
                  ((startingDate.getMonth() + 1) < 10 ? '0' + (startingDate.getMonth() + 1) : (startingDate.getMonth() + 1)) + '/' +
                  startingDate.getFullYear() + ' ' +
                  (startingDate.getHours() < 10 ? '0' + startingDate.getHours() : startingDate.getHours()) + ':00:00';
  }

  // Change graph zoom
  $.each(chartList[container]['option'].dataZoom, function(k, v){
    if(chartList[container]['option'].dataZoom[k].type != "slider" && (chartList[container]['option'].dataZoom[k].hasOwnProperty('start') || chartList[container]['option'].dataZoom[k].hasOwnProperty('startValue'))) {
      chartList[container]['option'].dataZoom[k] = {
        type: 'inside',
        xAxisIndex: chartList[container]['option'].dataZoom[k].xAxisIndex,
        startValue: formatedDate
      };
    }

  });

  // If need to be changed, reload graph
  if(change) chartList[container]['graph'].setOption(chartList[container]['option']);
}
