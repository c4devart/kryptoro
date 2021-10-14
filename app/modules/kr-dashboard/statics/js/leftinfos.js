$(document).ready(function(){
  //loadLeftInfosCoin("BTC", "USDT", "Binance");



});

let loadInfosInitied = false;

function loadLeftInfosIsInitied(){ return loadInfosInitied; }

function loadLeftInfosCoin(symbol, currency, market = "CCCAGG"){

  if(symbol.toUpperCase() == "NOT_INIT" || currency.toUpperCase() == "NOT_INIT") return false;

  clearTimeout(TimeoutOrderListGraph);
  TimeoutOrderListGraph = null;

  updateOrderGraphList(symbol,
                      currency,
                      market);

  if($('header[kr-leftinfoisp]').attr('kr-leftinfoisp') == market + ":" + symbol + "" + currency) return false;
  orderBookData = {"bids": {}, "asks": {}};
  $.each($('.kr-infoscurrencylf-orderbook').find('> div'), function(){ $(this).html(''); })
  $('.kr-dash-orderlistpassed-lst').html('');
  loadInfosInitied = true;
  $.post($('body').attr('hrefapp') + '/app/modules/kr-dashboard/src/actions/loadLeftInfosCoin.php', {symbol:symbol, currency:currency, market:market}).done(function(data){
    $('.kr-infoscurrencylf').html(data);
    addSubscribtion(symbol, currency, 2, market);
    subscribeStreamerCallback(function(dataCoin){
      if($('[kr-leftinfoisp]').attr('kr-leftinfoisp') != dataCoin.MARKET.toUpperCase() + ":" + dataCoin.FROMSYMBOL + dataCoin.TOSYMBOL) return false;
      if(dataCoin.FROMSYMBOL == symbol && currency == dataCoin.TOSYMBOL && market.toLowerCase() == dataCoin.MARKET.toLowerCase()){
        _highlightNumber(KRformatNumber(dataCoin.PRICE, dataCoin.PRICE > 1 ? 2 : 5), $('span.kr-infoscurrencylf-price-cp'));
        var change24 = dataCoin.CHANGE24HOUR.match(/(.)[\d\.]+/g);
        if(change24 != null) $('.kr-infoscurrencylf-price-evolv').html(KRformatNumber(change24, (change24 > 1 ? 2 : 5)).toString().replace(" ", "") + " (" + KRformatNumber(dataCoin.CHANGE24HOURPCT, 2) + "%)");
        $('.kr-infoscurrencylf-range-infos-low').html(KRformatNumber(dataCoin.LOW24HOUR, (dataCoin.LOW24HOUR > 1 ? 2 : 5)));
        $('.kr-infoscurrencylf-range-infos-high').html(KRformatNumber(dataCoin.HIGH24HOUR, (dataCoin.HIGH24HOUR > 1 ? 2 : 5)));

        let percentage = 0;
        if(dataCoin.HIGH24HOUR > dataCoin.LOW24HOUR){
          let max = dataCoin.HIGH24HOUR - dataCoin.LOW24HOUR;
          percentage = 100 - Math.abs((((dataCoin.PRICE - dataCoin.LOW24HOUR) - max) / max) * 100);
        }
        $('.kr-infoscurrencylf-range-bar > div').css('width', percentage + "%");
      }
    }, 2);

    if($('.kr-wtchl').hasClass('kr-wtchl-lowd')){
      loadLeftInfosMoreDetails();
    }





  }).fail(function(){
    showAlert("Oops", "Fail to load left infos coin", "error");
  });


}

function loadLeftInfosMoreDetails(){
  $('.kr-wtchl').addClass('kr-wtchl-lowd');
  $('.kr-infoscurrencylf').addClass('kr-infoscurrencylf-moredetails');
  startLeftInfosOrderBookSync($('[kr-leftinfois-makr]').attr('kr-leftinfois-makr'),
                              $('[kr-leftinfois-makr]').attr('kr-leftinfois-symbol'),
                              $('[kr-leftinfois-makr]').attr('kr-leftinfois-currency'));
}

function hideLeftInfosMoreDetails(){
  $('.kr-wtchl').removeClass('kr-wtchl-lowd');
  $('.kr-infoscurrencylf').removeClass('kr-infoscurrencylf-moredetails');
  stopLeftInfosOrderBookSync();
  $('.kr-infoscurrencylf-orderbook').find('section').html('');
  $('.kr-dash-orderlistpassed-lst').html('');
}


function startLeftInfosOrderBookSync(market = "binance", symbol = "ETH", currency = "BTC"){
  stopLeftInfosOrderBookSync();
  updateLeftInfosOrderBook(market, symbol, currency);
}

function stopLeftInfosOrderBookSync(){
  clearTimeout(orderBookLeftInfosTO);
  orderBookLeftInfosTO = null;

}

function updateOrderGraphList(symbol, currency, market){
  loadOrderGraphList(symbol, currency, market);
}

orderBookLeftInfosTO = null;
function updateLeftInfosOrderBook(market = "binance", symbol = "ETH", currency = "BTC"){

  stopLeftInfosOrderBookSync();
  if($('.kr-wtchl').hasClass('kr-wtchl-lowd') || $('.kr-infoscurrencylf-orderbook-coin').attr('kr-ob-force') == "true"){

    $.get($('body').attr('hrefapp') + '/app/modules/kr-dashboard/src/actions/getOrderBook.php', {market:market, symbol:symbol, currency:currency}).done(function(data){
      let infos = jQuery.parseJSON(data);

      _changeLeftInfosOrderBook("bids", infos.bids);
      _changeLeftInfosOrderBook("asks", infos.asks);

      if(market.toLowerCase() == "binance"){
        _startBinanceSocketOrderBook(symbol, currency);
      } else {
        orderBookLeftInfosTO = setTimeout(function(){
          updateLeftInfosOrderBook(market, symbol, currency);
        }, 2500);
      }


    }).fail(function(){
      showAlert('Oops', 'Fail to load order book list (404, 505)', 'error');
    });
  } else {
    stopLeftInfosOrderBookSync();
  }
}

function sortOnKeys(dict) {

    var sorted = [];
    for(var key in dict) {
        sorted[sorted.length] = key;
    }
    sorted.sort();

    var tempDict = {};
    for(var i = 0; i < sorted.length; i++) {
        tempDict[sorted[i]] = dict[sorted[i]];
    }

    return tempDict;
}


let orderBookData = {'bids': {}, 'asks': {}};
function _changeLeftInfosOrderBook(side = "bids", data = null, deletenexist = true){

  let orderBookSideContent = $('section[kr-orderbook-side="' + side + '"]');
  if($('.kr-infoscurrencylf-orderbook-coin').length > 0){
    orderBookSideContent = $('.kr-infoscurrencylf-orderbook-coin section[kr-orderbook-side="' + side + '"]');
  }


  let divAdded = false;
  let nData = [];
  $.each(data, function(k, v){
    if(parseFloat(v[1]).toFixed(6) == 0){
      orderBookSideContent.find('div[kr-orderbook-side-p="' + parseFloat(v[0]).toFixed(6) + '"]').remove();
      delete orderBookData[side][parseFloat(v[0]).toFixed(6)];
    } else {
      orderBookData[side][parseFloat(v[0]).toFixed(6)] = parseFloat(v[1]).toFixed(6);
    }
  });

  let sortedBookData = {};
  $.each(Object.keys(orderBookData[side]).sort(), function(k, v){
    sortedBookData[v] = orderBookData[side][v];
  });

  orderBookData[side] = sortedBookData;

  let totalFetched = 0;
  $.each(orderBookData[side], function(k, v){
    totalFetched += parseFloat(v);
  });

  oldKey = null;
  let amountFetched = (side == "asks" ? 0 : 0);

  $.each(orderBookData[side], function(k, v){
    if(side == "asks") amountFetched += parseFloat(v);
    else amountFetched += parseFloat(v);

    if(orderBookSideContent.find('div[kr-orderbook-side-p="' + k + '"]').length > 0) {
      orderBookSideContent.find('div[kr-orderbook-side-p="' + k + '"]').find("li[kr-orderbook-i='amount']").html(parseFloat(v).toFixed(6));
      orderBookSideContent.find('div[kr-orderbook-side-p="' + k + '"]').find("li[kr-orderbook-i='sum']").html(KRformatNumber(amountFetched, 3));
      orderBookSideContent.find('div[kr-orderbook-side-p="' + k + '"]').find('div').css('width', (((amountFetched / totalFetched) * 100) / 1.5) + '%');
    } else {

      let OrderBookLineContent = $("<div kr-orderbook-side-p='" + k + "' kr-orderbook-side-ps='" + v + "'><ul><li kr-orderbook-i='amount'>" + v + "</li><li kr-orderbook-i='sum'>" + KRformatNumber(amountFetched, 3) + "</li><li>" + KRformatNumber(k, 6) +
                                  "</li></ul><div style='width:" + (((amountFetched / totalFetched) * 100) / 1.5) + "%;'></div></div>");

      if($('[name="price_limit"]').length > 0){
        OrderBookLineContent.off('click').click(function(){
          $('[name="price_limit"]').val(k);
          reloadTotalAmount();
        });
      }
      if(orderBookSideContent.find('div').length == 0 || oldKey == null){
        orderBookSideContent.append(OrderBookLineContent);
      } else {
        if(oldKey > v) orderBookSideContent.find('div[kr-orderbook-side-p="' + oldKey + '"]').before(OrderBookLineContent);
        else orderBookSideContent.find('div[kr-orderbook-side-p="' + oldKey + '"]').after(OrderBookLineContent);
      }
    }

    oldKey = k;

  });

  return false;

}

let TimeoutOrderListGraph = null;
function loadOrderGraphList(symbol, currency, market = null){

  clearTimeout(TimeoutOrderListGraph);
  TimeoutOrderListGraph = null;

  $.each($('.kr-dash-orderlistpassed-lst').find('> li'), function(){
    if($(this).attr('kr-orderlist-pair') != symbol + "" + currency) $(this).remove();
  });

  $.get($('body').attr('hrefapp') + '/app/modules/kr-trade/src/actions/getOrderList.php', {symbol:symbol, currency:currency, market:market}).done(function(data){
    let objReturn = jQuery.parseJSON(data);
    if(objReturn.error == 1){
      showAlert('Oops', objReturn.msg, 'error');
    } else {
      objReturn.native = objReturn.native == 1;
      applyOrderGraphList(objReturn.orders, objReturn.show_market);

      TimeoutOrderListGraph = setTimeout(function(){
        updateOrderGraphList(symbol, currency, market);
      }, 2500);

      $('.kr-dash-orderlistpassed-pairname').html(" - " + (!objReturn.show_market ? objReturn.market + " - " : "") + objReturn.pair);
    }
  }).fail(function(){
    showAlert('Oops', 'Fail to load order list graph', 'error');
  })
}

function applyOrderGraphList(orders, native = false){

  $.each(orders, function(k, v){

    if($('header[kr-leftinfoisp]').length == 0) return false;
    if($('header[kr-leftinfoisp]').attr('kr-leftinfoisp').indexOf(":" + v.symbol + v.currency) == -1) return false;

    if($('[kr-orderlist-i="' + v.id + '"]').length > 0) return true;
    let orderDate = new Date(v.date * 1000);
    let winSymbol = v.currency;
    let minusQtd = KRformatNumber(v.amount, 8) + " " + v.symbol;
    let winQtd = KRformatNumber(v.usd_amount, 8) + " " + v.currency;
    if(v.side == "BUY"){
      minusQtd = KRformatNumber(v.amount, 8) + " " + v.currency;
      winQtd = KRformatNumber(v.usd_amount, 8) + " " + v.symbol;
      winSymbol = v.symbol;
    }

    let orderObject = $("<li kr-orderlist-i='" + v.id + "' kr-orderlist-pair='" + v.symbol + v.currency + "'> <div>" + (orderDate.getDate() < 10 ? "0" + orderDate.getDate() : orderDate.getDate()) + "/" + ((orderDate.getMonth() + 1) < 10 ? "0" + (orderDate.getMonth() + 1) : (orderDate.getMonth() + 1) ) +
                        "/" + orderDate.getFullYear() +
                        " " + (orderDate.getHours() < 10 ? "0" + orderDate.getHours() : orderDate.getHours()) + ":" + (orderDate.getMinutes() < 10 ? "0" + orderDate.getMinutes() : orderDate.getMinutes()) + ":" + (orderDate.getSeconds() < 10 ? "0" + orderDate.getSeconds() : orderDate.getSeconds()) +
                        "</div> <div>" + v.symbol + "/" + v.currency + "</div>" +
                        "<div>" + v.type.toUpperCase() + "</div>" +
                        "" + (!native ? "<div>" + v.exchange + "</div>" : "") + " <div class='" + (v.side == "BUY" ? "kr-dash-orderlistpassed-lst-cgreen" : "kr-dash-orderlistpassed-lst-cred") + "'>" + v.side + "</div>" +
                        "<div>" + minusQtd + "</div> <div>" + winQtd + "</div> <div>" + KRformatNumber(v.fees, 8) + " " + winSymbol + "</div> <div>" + (KRformatNumber((v.side == "BUY" ? v.amount : v.usd_amount) - v.fees, 8)) + " " + winSymbol + "</div> <div>" + v.evolv +
                        "</div><div>" + (v.status == "0" ? "<input type='button' onclick='_cancelOrder(\"" + v.id + "\");return false;' class='btn btn-exsmall btn-grey' value='Cancel'/>" : "") + "</div></li>");

    orderObject.off('click').click(function(){
      showOrderInfos(v.id_encrypted);
    });
    $('.kr-dash-orderlistpassed-lst').prepend(orderObject);
  });

}

function toggleClassOrderGraphList(){
  // $('.kr-dashboard').toggleClass('kr-orderlist-shown-graph');
  // return $('.kr-dashboard').hasClass('kr-orderlist-shown-graph'));
}

function _toggleOrderGraphList(dnc = false){
  if(!dnc) $('.kr-dash-orderlistpassed').toggleClass('kr-dash-orderlistpassed-hide');
  if(!$('.kr-dash-orderlistpassed').hasClass('kr-dash-orderlistpassed-hide') && !$('.kr-dash-orderlistpassed').hasClass('kr-dash-orderlistpassed-layer')){
    $('.kr-dashboard').addClass('kr-orderlist-shown-graph');
    updateUserSettings('orderlist_show', 'true');
  } else {
    $('.kr-dashboard').removeClass('kr-orderlist-shown-graph');
    updateUserSettings('orderlist_show', 'false');
  }
  checkGraphResize();
}

function _toggleLayerOrderGraphList(dnc = false){
  if(!dnc) $('.kr-dash-orderlistpassed').toggleClass('kr-dash-orderlistpassed-layer');
  if($('.kr-dash-orderlistpassed').hasClass('kr-dash-orderlistpassed-hide')) return false;
  if($('.kr-dash-orderlistpassed').hasClass('kr-dash-orderlistpassed-layer')){
    $('.kr-dashboard').removeClass('kr-orderlist-shown-graph');
    updateUserSettings('orderlist_layer', 'true');
  } else {
    $('.kr-dashboard').addClass('kr-orderlist-shown-graph');
    updateUserSettings('orderlist_layer', 'false');
  }
  checkGraphResize();
}

function _toogleLeftSide(){
  $('.kr-leftside').toggleClass('kr-leftside-hide');
  if($('.kr-leftside').hasClass('kr-leftside-hide')){
    $('.kr-leftside > .kr-leftside-hide-controller').html('<svg class="lnr lnr-chevron-right"><use xlink:href="#lnr-chevron-right"></use></svg>');
  } else {
    $('.kr-leftside > .kr-leftside-hide-controller').html('<svg class="lnr lnr-chevron-left"><use xlink:href="#lnr-chevron-left"></use></svg>');
  }
  checkGraphResize();
}

let socketBinanceBookOrder = null;
function _startBinanceSocketOrderBook(symbol, currency, depth = ""){
  socketBinanceBookOrder = new WebSocket('wss://stream.binance.com:9443/ws/' + symbol.toLowerCase() + '' + currency.toLowerCase() + '@depth' + depth);
  socketBinanceBookOrder.onmessage = function (event) {
    let jsonResult = jQuery.parseJSON(event.data);
    _changeLeftInfosOrderBook('asks', jsonResult.a, false);
    _changeLeftInfosOrderBook('bids', jsonResult.b, false);
  }
}
