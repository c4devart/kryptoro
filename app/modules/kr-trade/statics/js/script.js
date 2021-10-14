$(document).ready(function(){

  $('[kr-side-part="kr-leaderboard"]').off('click').click(function(){
    toggleLeaderBoard();
  });

  $('[kr-side-part="kr-orderbook"]').off('click').click(function(){
    toggleOrderbook();
  });

  if($('.kr-trade-lst').length <= 0) return false;

  $.get($('body').attr('hrefapp') + '/app/modules/kr-trade/src/actions/getTradecoins.php').done(function(data){
    let jsonRes = jQuery.parseJSON(data);
    $.each(jQuery.parseJSON(jsonRes.symbols), function(k, symbol){
      addSubscribtion(symbol, jsonRes.currency, 0);
    });
  }).fail(function(){
    showAlert('Ooops', 'Fail to gat trade coins list', 'error');
  });

  subscribeStreamerCallback(function(dataCoin){
    updateTradeTable(dataCoin);
    updateTradeBalance(dataCoin);
  }, 0);

});

function _cancelOrder(orderid){

  $.post($('body').attr('hrefapp') + '/app/modules/kr-trade/src/actions/cancelTrade.php', {orderid:orderid}).done(function(data){
    let res = jQuery.parseJSON(data);
    if(res.error == 1){
      showAlert('Oops', res.msg, 'error');
    } else {
      showAlert('Success', 'Order cancel');
      $('[kr-orderlist-i="' + orderid + '"]').remove();
      _updateBalanceData();
    }
  }).fail(function(){
    showAlert('Oops', 'Fail to cancel order (404 or 500 error)', 'error');
  });

  return false;

}

function updateTradeTable(dataCoin){

  if($('.kr-trade').hasClass('kr-trade-hide')) return false;

  $('.kr-trade-lst.kr-trade-lst-global').prepend('<li>' +
    '<div class="kr-trade-lst-symbol">' +
      '<span class="kr-mono">' + dataCoin.FromSymbol + '</span>' +
    '</div>' +
    '<div>' +
      '<span class="kr-mono">' + dataCoin.Market + '</span>' +
    '</div>' +
    '<div>' +
      '<span class="kr-mono kr-trade-lst-' + dataCoin.Type.toLowerCase() + '">' + $.number(dataCoin.Total, 2, ',', ' ') + ' ' + dataCoin.ToCurrency + '</span>' +
    '</div>' +
    '<div>' +
      '<span class="kr-mono">' + dataCoin.Quantity + '</span' +
    '</div>' +
  '</li>');

  $('.kr-trade-lst.kr-trade-lst-global').find('li').slice(50).remove();
}

let balanceHistory = {
  'buy': [],
  'sell': []
};
function updateTradeBalance(dataCoin){

  if($('.kr-trade').hasClass('kr-trade-hide')) return false;

  if(dataCoin.Type.toLowerCase() == 'unknown') return false;
  balanceHistory[dataCoin.Type.toLowerCase()].push('1');
  if(balanceHistory[dataCoin.Type.toLowerCase()].length > 1200){
      balanceHistory['buy'] = []; balanceHistory['sell'] = [];
  }

  $('.kr-trade-balance > div:first-child').css('max-width', ((balanceHistory['buy'].length / (balanceHistory['buy'].length + balanceHistory['sell'].length)) * 100) + '%');
  $('.kr-trade-balance > div:last-child').css('max-width', ((balanceHistory['sell'].length / (balanceHistory['buy'].length + balanceHistory['sell'].length)) * 100) + '%');

}

function toggleLeaderBoard(){

  $('.kr-rankingside').toggleClass('kr-rankingside-show');
  if($('.kr-rankingside').hasClass('kr-rankingside-show')){
    $('[kr-side-part="kr-leaderboard"]').addClass('kr-leftnav-select');
    if($('.kr-rankingside-mine').length == 0){
      $.get($('body').attr('hrefapp') + '/app/modules/kr-trade/views/leaderboard.php').done(function(data){
        $('.kr-rankingside > *').not('header').remove();
        $('.kr-rankingside').append(data);
      });
    }
  } else {
    $('[kr-side-part="kr-leaderboard"]').removeClass('kr-leftnav-select');
  }
  checkGraphResize();
}

let timeOutOrderBook = null;

function orderBookSync(){

  $.get($('body').attr('hrefapp') + '/app/modules/kr-trade/views/orderBook.php').done(function(data){
    $('.kr-orderbookside > *').not('header').remove();
    $('.kr-orderbookside').append(data);
    timeOutOrderBook = setTimeout(function(){
      orderBookSync();
    }, 5000);
  });
}

function toggleOrderbook(){

  $('.kr-orderbookside').toggleClass('kr-orderbookside-show');
  if($('.kr-orderbookside').hasClass('kr-orderbookside-show')){
    $('[kr-side-part="kr-orderbook"]').addClass('kr-leftnav-select');
    $.get($('body').attr('hrefapp') + '/app/modules/kr-trade/views/orderBook.php').done(function(data){
      $('.kr-orderbookside > *').not('header').remove();
      $('.kr-orderbookside').append(data);
      timeOutOrderBook = setTimeout(function(){
        orderBookSync();
      }, 5000);
    });
  } else {
    $('[kr-side-part="kr-orderbook"]').removeClass('kr-leftnav-select');
    clearTimeout(timeOutOrderBook); timeOutOrderBook = null;
    closeOrderInfos();
  }
  checkGraphResize();
}

function showOrderInfos(order_id){
  $('.kr-orderinfoside').addClass('kr-orderinfoside-show');
  $('.kr-bookorder-native-select').removeClass('kr-bookorder-native-select');
  $('[kr-bookorder-if="' + order_id + '"]').addClass('kr-bookorder-native-select');
  checkGraphResize();
  $('.kr-orderinfoside').html('<div class="spinner"></div>');
  $.post($('body').attr('hrefapp') + '/app/modules/kr-trade/views/orderInfos.php', {order_id:order_id}).done(function(data){
    $('.kr-orderinfoside').html(data);
  });
}

function closeOrderInfos(){
  $('.kr-orderinfoside').removeClass('kr-orderinfoside-show');
  $('.kr-orderinfoside').html('<div class="spinner"></div>');
  checkGraphResize();
}

function initWidthdrawMethod(type){
  closeAccountView();
  $('body').addClass('kr-nblr');
  $.get($('body').attr('hrefapp') + '/app/modules/kr-trade/views/initWidthdraw.php', {type:type}).done(function(data){
    $.when($('body').prepend(data)).then(function(){
      _initWithdrawInitController();
    });
  }).fail(function(){
    showAlert('Oops', 'Fail to load widthdraw setup form', 'error');
  });
}

function _initWithdrawInitController(){
  $('.kr-thirdparty-setup-form').off('submit').submit(function(e){

    $('.kr-thirdparty-setup').find('form').hide();
    $('.kr-thirdparty-setup').find('.spinner').show();
    $.post($(this).attr('action'), $(this).serialize()).done(function(data){
      let jsonRes = jQuery.parseJSON(data);
      if(jsonRes.error == 1){
        showAlert('Oops', jsonRes.msg, 'error');
        $('.kr-thirdparty-setup').find('.spinner').hide();
        $('.kr-thirdparty-setup').find('form').show();
      } else {
        showAlert('Success', jsonRes.msg);
        closeThirdpartySetup();
      }
    }).fail(function(){
      showAlert('Oops', 'Fail to save widthdraw method (contact admin)', 'error');
    });
    e.preventDefault();
    return false;
  });
}
