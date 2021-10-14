function initMarketList(){
  initMarketNav();

  $('.kr-marketlist-item').off('click').click(function(){
    changeView('coin', 'coin', {symbol:$(this).attr('kr-symbol-mm'), currency:$(this).attr('kr-symbol-tt'), market:$(this).attr('kr-symbol-market')}, null, true);
  });
}
