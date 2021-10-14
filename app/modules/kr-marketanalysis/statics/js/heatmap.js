/**
 * Heatmap init function
 */
function initHeatmap(){

  // Init topmovers
  //initTopmovers();

  // Init market analytic navigation
  initMarketNav();

  // Subscribe coin update
  subscribeStreamerCallback(function(dataCoin){
    //updateTopMovers(dataCoin);
    updateHeatMap(dataCoin);
  });

  // Init heat map
  initSubHeatmap();
}

/**
 * Init Sub Heatmap
 */
function initSubHeatmap(){

  $('.kr-marketa-currency').find('li').each(function(){
    let symbolTo = $(this).attr('symbol');
    let symbolFrom = $(this).attr('fromsymbol');
    //addSubscribtion(symbolFrom, symbolTo);
  });

}

/**
 * Update heatmap
 * @param  {Array} dataCoin Data coin
 */
function updateHeatMap(dataCoin){
  if(isNaN(dataCoin.CHANGE24HOURPCT)) return false;
  let elementHeatmap = $('.kr-marketa-currency').find('li[symbol="' + dataCoin.TOSYMBOL + '"][fromsymbol="' + dataCoin.FROMSYMBOL + '"]');
  elementHeatmap.find('span').html(dataCoin.CHANGE24HOURPCT + '%');
}
