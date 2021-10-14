function initCustomPage(){

}

/**
 * Init dashboard method
 */
function initDashboard(onlyGraphProperty = false){

  // List all graph & inith graph
  $('.kr-dash-pan-cry').each(function(){
    let symbol = $(this).attr('symbol');
    let currency = $(this).attr('currency');
    let market = $(this).attr('market');
    addSubscribtion(symbol, $(this).attr('currency'));
    if(symbol != "not_init"){
      let idPan = $(this).attr('id');
      // Init graph
      loadChart(symbol, function(){
        loadChartData(symbol, idPan);
      }, idPan, currency, market);
    }

  });

  if(!onlyGraphProperty){
    initManageDashboard();

    reloadTopListGraph();

    initTopListItemTouch();
  }

  // Change dashboard graph format
  $("[kr-dashboard-cfg]").off('click').click(function(){
    changeDashboardFormat($(this).attr('kr-dashboard-cfg'));
    setTimeout(function(){
      hideDashboardManager();
    }, 100);
  });

  // List coin search change
  $('.kr-dash-add-graph-selected').find('input[type="text"]').off('keyup').keyup(function(){
    updateListCoinGraph($(this).val(), $(this).attr('graph'), function(coin, currency){
      addGraphDashboard(coin, currency);
    });
  });

  // Add graph icon top
  $('.kr-addgraph-dashboard').click(function(){
    $('.kr-dash-add-graph-selected').css('display', 'flex');
    updateListCoinGraph($('.kr-dash-add-graph-selected').find('input[type="text"]').val(), $('.kr-dash-add-graph-selected').find('input[type="text"]').attr('graph'), function(coin, currency){
      addGraphDashboard(coin, currency);
    });
  });

  // Subscribe top list item
  subscribeStreamerCallback(function(dataMessage){
    if(isNaN(dataMessage.CHANGE24HOURPCT)) return false;
    $('.kr-top-graphlist-item[symbol="' + dataMessage.FROMSYMBOL + '"]').find('[kr-data="CHANGE24HOURPCT"]').html(dataMessage.CHANGE24HOURPCT + "%");
    $('.kr-top-graphlist-item[symbol="' + dataMessage.FROMSYMBOL + '"]').attr('kr-val-graph', dataMessage.PRICE);
    $('.kr-top-graphlist-item[symbol="' + dataMessage.FROMSYMBOL + '"]').find('[kr-data="CHANGE24HOURPCT"]').removeClass('kr-top-graphlist-item-evl-up').removeClass('kr-top-graphlist-item-evl-down')
    if(parseFloat(dataMessage.CHANGE24HOURPCT) < 0){
      $('.kr-top-graphlist-item[symbol="' + dataMessage.FROMSYMBOL + '"]').find('[kr-data="CHANGE24HOURPCT"]').addClass('kr-top-graphlist-item-evl-down');
    } else if(parseFloat(dataMessage.CHANGE24HOURPCT) > 0){
      $('.kr-top-graphlist-item[symbol="' + dataMessage.FROMSYMBOL + '"]').find('[kr-data="CHANGE24HOURPCT"]').addClass('kr-top-graphlist-item-evl-up');
    }
  });

  // Check if windows was resized -> reload graph size
  $(window).resize(function(){
    checkGraphResize();
  });

}

function addTopListDashboardSearchPop(symbol, currency, market){
  let coin = {
    "symbol": symbol,
    "currency": currency,
    "market": market
  };
  addGraphDashboard(coin, currency, market);
  setTimeout(function(){
    closeBigSearch();
  }, 100);
}

/**
 * Add graph dashboard
 * @param {String} coin     Symbol (ex : BTC)
 * @param {String} currency Currency (ex : USD)
 */
function addGraphDashboard(coin, currency, market){

  addTopListGraph(coin, function(addedTopList){
    addedTopList.trigger('click');
  });

  // Subscribe cryptocurrency
  addSubscribtion(coin.symbol, currency);

  setTimeout(function(){
    $('.kr-dash-pan-cry-select').hide();
  }, 100);

}

function addGraphDashboardNotInit(symbol, currency, market){
  let coin = {
    'symbol': symbol,
    'currency': currency,
    'market': market
  }
  addGraphDashboard(coin, currency, market);
  closeBigSearch();
}

/**
 * Reload top list graph item
 */
function reloadTopListGraph(){


  // List top list item
  $('.kr-top-graphlist-item').each(function(){

    $(this).find('.kr-top-graphlist-closeb').off('click').click(function(e){

      if($(this).parent().attr('container').length > 0 && $('.kr-dash-pan-cry[container="' + $(this).parent().attr('container') + '"]').length > 0){
        removeGraph($(this).parent().attr('container'));
      } else {
        let topItemID = $(this).parent().attr('topitem');
        $(this).parent().remove();
        $.post($('body').attr('hrefapp') + '/app/modules/kr-dashboard/src/actions/deleteTopList.php', {topitem:topItemID}).done(function(data){
          let response = jQuery.parseJSON(data);
          if(response.error == 1){
            showAlert('Oops', response.msg, 'error');
          }
        }).fail(function(){
          showAlert('Oops', 'Fail to access to top list delete action (404, 505)', 'error');
        });
      }

      e.preventDefault();
      return false;
    });

    // Add click action
    $(this).off('click').click(function(){

      if($('[kr-module="dashboard"][kr-view="dashboard"].kr-leftnav-select').length > 0){
        if(!$('[kr-view="dashboard"]').hasClass('kr-leftnav-select')){
          changeView('dashboard', 'dashboard', {}, changeTopItemAction($(this)));
        } else {
          changeTopItemAction($(this));
        }
      } else {
        changeView('coin', 'coin', {symbol:$(this).attr('symbol'), currency:$(this).attr('currency'), market:$(this).attr('market')});
      }

    });

    // Subscribe symbol
    addSubscribtion($(this).attr('symbol'), $(this).attr('currency'));

    // If graph not found in dashboard, delete class graphlist view
    if($('.kr-dash-pan-cry[container="' + $(this).attr('container') + '"][symbol="' + $(this).attr('symbol') + '"][currency="' + $(this).attr('currency') + '"][market="' + $(this).attr('market') + '"]').length == 0){
      $(this).removeClass('kr-top-graphlist-item-view');
    } else {
      $(this).addClass('kr-top-graphlist-item-view');
    }
  });

}

/**
 * Changes selected top list
 * @param  {String} container Container graph ID
 * @param  {String} symbol    Symbol
 */
function changeSelectedTopList(container, symbol, currency, market = "CCCAGG"){
  $('.kr-top-graphlist-item').removeClass('kr-top-graphlist-item-selected');
  $('.kr-top-graphlist-item[container="' + container + '"][symbol="' + symbol + '"][currency="' + currency + '"][market="' + market + '"]').addClass('kr-top-graphlist-item-selected');
  loadLeftInfosCoin(symbol, currency, market);

}

/**
 * Check graph resize
 * @return {[type]} [description]
 */
function checkGraphResize(){
  // Fetch all graph in dashboard
  $('.kr-dash-pan-cry').each(function(){
    if(chartList[$(this).attr('id')] != undefined){
      // Resize fetched graph
      chartList[$(this).attr('id')]['graph'].resize('100%', '100%');
    }
  });
}

/**
 * Change dashboard format
 * @param  {String} format New dashboard format
 */
function changeDashboardFormat(format){
  changeView('dashboard', 'dashboard', {nchart: format});
}

/**
 * Add top list graph item
 * @param {String} coin              Coin symbol (ex : BTC)
 * @param {Function} [callback=null] Callback
 */
function addTopListGraph(coin, callback = null){

  // Create to list element
  $.post($('body').attr('hrefapp') + '/app/modules/kr-dashboard/src/actions/addTopList.php', {symbol:coin.symbol, currency:coin.currency, market:coin.market}).done(function(data){

    let response = jQuery.parseJSON(data);
    if(response.error == 0){
      let topListItem = $('<li class="kr-mono kr-top-graphlist-item" container="" topitem="' + response.item_id + '" symbol="' + response.coin_infos.symbol + '" coinname="' + response.coin_infos.name + '" currency="' + response.coin_infos.currency + '" market="' + response.coin_infos.market + '" pasth="" kr-val-graph="">' +
                    '<div class="kr-top-graphlist-closeb">' +
                      '<svg class="lnr lnr-cross"><use xlink:href="#lnr-cross"></use></svg>' +
                    '</div>' +
                    (response.coin_infos.icon != undefined ? '<div class="kr-top-graphlist-pic">' + response.coin_infos.icon + '</div>' : '') +
                    '<div class="kr-top-graphlist-inf"><label>' + ($('body').attr('kr-hm') == "1" ? "" : response.coin_infos.market + ':') + response.coin_infos.symbol + '/' + response.coin_infos.currency + '</label><span kr-data="CHANGE24HOURPCT">~</span>' +
                    '</div></li>');

      $('.kr-top-graphlist').append(topListItem);

      // Reload top list graph
      reloadTopListGraph();

      if(callback != null) callback(topListItem);
    } else {
      showAlert('Oops', response.msg, 'error');
    }
  }).fail(function(){
    showAlert('Oops', 'Fail create new top item (404, 505)', 'error');
  });

}

let curXPos = 0;
let curDown = false;
/**
 * Init top list touch mode
 */
function initTopListItemTouch(){


  $('ul.kr-top-graphlist').off('mousemove').on("mousemove", function (event) {
    if(!curDown) return false;
    $('ul.kr-top-graphlist').scrollLeft(parseInt($('ul.kr-top-graphlist').scrollLeft() + (curXPos - event.pageX)));
    curXPos = event.pageX;
  });

  $('ul.kr-top-graphlist').off('mousedown').on("mousedown", function (e) { curDown = true; curXPos = e.pageX; e.preventDefault(); });
  $(window).off('mouseup').on("mouseup", function (e) { curDown = false; });
}

/**
 * Action change top list item
 * @param  {Object} item Top item object
 */
function changeTopItemAction(item){
  if(!item.hasClass('kr-top-graphlist-item-view')){

    // Selected item
    let selectElement = $('.kr-dash-pan-cry').first();
    if($('.kr-dash-pan-cry-selected').length > 0) selectElement = $('.kr-dash-pan-cry-selected');
    if($('.kr-dash-pan-cry[chart-init="false"]').length > 0) selectElement = $('.kr-dash-pan-cry[chart-init="false"]').first();

    loadLeftInfosCoin(item.attr('symbol'), item.attr('currency'), item.attr('market'));

    let containerID = selectElement.attr('id');
    selectElement.attr('symbol', $(this).attr('symbol'));
    selectElement.attr('currency', $(this).attr('currency'));
    selectElement.attr('market', $(this).attr('market'));
    $('.kr-top-graphlist-item[container="' + selectElement.attr('id') + '"]').attr('container', '');
    item.attr('container', containerID);

    changeGraph({
      icon: item.find('.kr-top-graphlist-pic').html(),
      name: item.attr('coinname'),
      symbol: item.attr('symbol')
    }, item.attr('currency'), containerID, item.attr('topitem'), false,
    item.attr('market'));




  } else {
    // If selected item is clicked item -> make boucne animation
    if(item.hasClass('kr-top-graphlist-item-selected')){
      let graphContent = $('.kr-dash-pan-cry[container="' + item.attr('container') + '"][symbol="' + item.attr('symbol') + '"][currency="' + item.attr('currency') + '"][market="' + item.attr('market') + '"]');
      graphContent.removeClass('animated').removeClass('bounceIn');
      setTimeout(function(){
        graphContent.addClass('animated').addClass('bounceIn');
      }, 100);
    } else {
      loadLeftInfosCoin(item.attr('symbol'), item.attr('currency'), item.attr('market'));
    }

    $('.kr-top-graphlist-item-selected').removeClass('kr-top-graphlist-item-selected');
    item.addClass('kr-top-graphlist-item-selected');

  }

  $('.kr-dash-pan-cry-selected').removeClass('kr-dash-pan-cry-selected');
  $('.kr-dash-pan-cry[container="' + item.attr('container') + '"]').addClass('kr-dash-pan-cry-selected');
}
