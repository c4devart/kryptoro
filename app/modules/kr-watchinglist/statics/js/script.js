let graphListView = [];

window.onload = function() {

  checkWatchingListSymbol();

  // Subscribe to data coin update


};

/**
 * Add coin value
 * @param {Float} value   Coin value
 * @param {String} symbol Coin symbol
 */
function addCoinValue(value, symbol) {

  let chart = graphListView[symbol];

  // Remove first graph element
  chart.data.labels.shift();
  chart.data.datasets.forEach((dataset) => {
    dataset.data.shift();
  });

  // Add new value
  chart.data.labels.push(value);
  chart.data.datasets.forEach((dataset) => {
    dataset.data.push(value);
  });

  // Update graph
  chart.update();
}

/**
 * Load wathcing list item
 */
function checkWatchingListSymbol() {
  $.get($('body').attr('hrefapp') + '/app/modules/kr-watchinglist/src/actions/getWatchingListSymbol.php').done(function(data) {
    // Parse respond from JSON
    let respond = jQuery.parseJSON(data);

    // Check error
    if (respond.error == 0) {
      $.each(respond.item, function(k, v) {
        setTimeout(function() {
          // Add wathcing list item
          addWatchingListItem(v.symbol, v.currency, "load", false, v.market);
        }, 1);
      });
    } else {
      showAlert('Oops', respond.msg, 'error');
    }
  }).fail(function(){
    showAlert('Ooops', 'Fail to get watching list symbol', 'error');
  });
}

let chartUpdateCoin = null;

/**
 * Update watching list item
 * @param  {Array} data    Coin data
 * @param  {String} symbol Coin symbol
 */
function updateWatchCoinItem(data, symbol, currency, market = "CCCAGG") {

  let watchingItem = $("[kr-watchinglistpair='" + market + ":" + symbol + "/" + currency + "']");
  if(watchingItem.length > 0){
    watchingItem.find('.kr-watchinglistpair-evolv').html(KRformatNumber(data.CHANGE24HOURPCT, 2) + "%");
    _highlightNumber(KRformatNumber(data.PRICE, (data.PRICE > 10 ? 2 : 5)), watchingItem.find('.kr-watchinglistpair-price'));
  }

}

/**
 * Toggle watching list item
 * @param  {String} symbol   Item symbol (ex : BTC)
 * @param  {String} currency Item currency (ex : USD)
 */
function toggleWatchingList(symbol, currency, market = "CCCAGG") {
  // If watching list item found = remvoe
  if ($('[kr-watchinglistpair="' + market.toUpperCase() + ":" + symbol + '/' + currency + '"]').length > 0) removeWatchingListItem(symbol, currency, market);
  else addWatchingListItem(symbol, currency, "add", false, market); // Else add watching list item
}

/**
 * Add watching list item
 * @param {String} symbol        Item symbol (ex : BTC)
 * @param {String} currency      Item currency (ex : USD)
 * @param {String} [type="load"] Type add
 */
function addWatchingListItem(symbol, currency, type = "load", first = false, market = "CCCAGG") {
  if ($('[kr-watchinglistpair="' + market.toUpperCase() + ":" + symbol + '/' + currency + '"]').length > 0) return false;
  $('.kr-dash-pan-cry-select-lst-tdn[symbol="' + symbol + '"]').addClass('watching-list-present');

  // Get watching list item data
  $.get($('body').attr('hrefapp') + '/app/modules/kr-watchinglist/src/actions/getWatchingItem.php', {
    symb: symbol,
    currency:currency,
    market:market,
    t: type
  }).done(function(data) {

    // Try to parse respond to json = success = error
    try {
      let respond = jQuery.parseJSON(data);
      if (respond.error == 1) showAlert('Oops', respond.msg, 'error');
    } catch (e) {
      let elemWatching = $(data);
      elemWatching.find('.kr-wtchl-lst-remove').off('click').click(function(e){
        removeWatchingListItem(symbol, currency, market);
        e.preventDefault();
        return false;
      });
      elemWatching.click(function() {

        // Change graph
        $('.kr-leftside').removeClass('kr-leftside-resp-on');

        // Data coin
        let coinWatchingList = {
          'symbol': symbol,
          'name': "",
          'icon': "",
          'currency': currency,
          "market": market.toUpperCase()
        }

        if($('.kr-dash-chart-n').length > 0){
          // Change graph & att top list item
          if ($('.kr-top-graphlist-item[symbol="' + coinWatchingList.symbol + '"][currency="' + coinWatchingList.currency + '"][market="' + coinWatchingList.market.toUpperCase() + '"]').length > 0) {
            $('.kr-top-graphlist-item[symbol="' + coinWatchingList.symbol + '"][currency="' + coinWatchingList.currency + '"][market="' + coinWatchingList.market.toUpperCase() + '"]').trigger('click');
          } else {
            addGraphDashboard(coinWatchingList, coinWatchingList.currency, coinWatchingList.market.toUpperCase());
          }
        } else {
          changeView('coin', 'coin', {symbol:coin.symbol, currency:coinWatchingList.currency, market:coinWatchingList.market.toUpperCase()});
        }
        loadLeftInfosCoin(coinWatchingList.symbol, coinWatchingList.currency, coinWatchingList.market.toUpperCase());

      });
      if(first){
        $('.kr-wtchl').find('ul.kr-wtchl-lst').prepend(elemWatching);
      } else {
        $('.kr-wtchl').find('ul.kr-wtchl-lst').append(elemWatching);
      }

      $('.kr-wtchl-lst').sortable({
        axis: "y"
      });
    }
  }).fail(function(){
    showAlert('Ooops', 'Fail to load watching item', 'error');
  });

  // Add subscribtion to item symbol

  if(market != "CCCAGG") market = market.substr(0,1).toUpperCase()+market.substr(1).toLowerCase();

  subscribeStreamerCallback(function(dataCoin) {
    if(dataCoin.FROMSYMBOL == symbol && dataCoin.TOSYMBOL == currency && dataCoin.MARKET == market){
      updateWatchCoinItem(dataCoin, dataCoin.FROMSYMBOL, dataCoin.TOSYMBOL, dataCoin.MARKET.toUpperCase());
    }

  }, (market == "CCCAGG" ? 5 : 2), market);

  addSubscribtion(symbol, currency, (market == "CCCAGG" ? 5 : 2), market);
}

/**
 * Remove wathcing item
 * @param  {String} symbol Item symbol (ex : BTC)
 */
function removeWatchingListItem(symbol, currency, market) {
  $('[kr-watchinglistpair="' + market.toUpperCase() + ':' + symbol + '/' + currency + '"]').remove();
  $('.kr-dash-pan-cry-select-lst-tdn[symbol="' + symbol + '"][currency="' + currency + '"][market="' + market + '"]').removeClass('watching-list-present');
  // Remove item in DB
  $.post($('body').attr('hrefapp') + '/app/modules/kr-watchinglist/src/actions/removeWatchingListItem.php', {
    symb: symbol,
    currency:currency,
    market: market
  }).done(function(data) {

    // Parse result
    let respond = jQuery.parseJSON(data);
    if (respond.error == 1) showAlert('Oops', respond.msg, 'error');
  });
}

function addWatchingListSearch(symbol, currency, market){
  addWatchingListItem(symbol, currency, "add", true, market);
}
