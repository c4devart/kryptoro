function initManageDashboard(){

  initCoinGraphControllers();

  // Change dashboard configuration
  $('.kr-change-dashboard').click(function(){
    showDashboardManager();
  });

  $(document).mouseup(function(e)
  {
      var container = $('.kr-change-dashboard-selector');
      if (!container.is(e.target) && container.has(e.target).length === 0) hideDashboardManager();
  });

}

function addTopListGraphN(symbol, currency, market){

}

let listCoinGraphQuery = null;
/**
 * Update list coin select
 * @param  {String} [query=null]           Query search coin
 * @param  {String} [graph=null]           Container graph
 * @param  {Fucntion} [callbackclick=null] function callback click element
 */
function updateListCoinGraph(query = null, graph = null, callbackclick = null, reset = true, startat = 0){

  $('.kr-dash-pan-cry-select-lst').off('scroll').scroll(function(){
    if($(this).scrollTop() + $(this).innerHeight() >= $(this)[0].scrollHeight) {
        updateListCoinGraph(query, graph, callbackclick, false, $(this).find('li').length + 15);
    }
  });


  clearTimeout(listCoinGraphQuery); listCoinGraphQuery = null;
  listCoinGraphQuery = setTimeout(function(){

    // Get list coin
    $.get($('body').attr('hrefapp') + "/app/modules/kr-dashboard/src/actions/getCoinList.php", {q:query, s:startat}).done(function(data){

      // Parse json result
      let coinList = jQuery.parseJSON(data);

      // Clear coin list
      if(reset) $('.kr-dash-pan-cry-select[graph="' + graph + '"]').find('ul.kr-dash-pan-cry-select-lst').html('');

      // Fetch all coin
      $.each(coinList, function(k, coin){
        let elemSelect = $('<li class="' + (query == null || query.length == 0 ? 'animated flipInX' : '') + '">' +
          '<div class="kr-dash-pan-cry-select-lst-i">' +
            '<div>' + coin.icon + '</div>' +
            '<div><label>' + coin.name + '</label><span class="kr-mono">' + coin.symbol + '</span></div>' +
          '</div>' +
          '<div class="kr-dash-pan-cry-select-lst-tdn ' + ($('.kr-wtchl-item[symbol="' + coin.symbol +'"]').length > 0 ? 'watching-list-present' : '') + '" symbol="' + coin.symbol + '"><span></span></div>' +
        '</li>');

        // Enable callback click item
        elemSelect.click(function(){
          if(coin.source == "cryptocompare"){
            if(callbackclick != null) callbackclick(coin);
            else addTopListGraph(coin);
          } else {
            let url = 'https://wallet.crypto-bridge.org/market/BRIDGE.XXXXXXX_BRIDGE.BTC';
            let coinsymbol = coin.symbol.split('_');
            url = url.replace('XXXXXXX', coinsymbol[0]);
            changeView('mining', 'iframe', {url:url});
          }

        });

        // Enable action watching list
        let watchingListToggle = $('<svg class="lnr lnr-star"><use xlink:href="#lnr-star"></use></svg>');
        watchingListToggle.click(function(e){
          toggleWatchingList(coin.symbol, coin.currency);
          e.preventDefault();
          return false;
        });

        elemSelect.find('.kr-dash-pan-cry-select-lst-tdn').find('span').append(watchingListToggle);

        // Add coin list
        $('.kr-dash-pan-cry-select[graph="' + graph + '"]').find('ul.kr-dash-pan-cry-select-lst').append(elemSelect);

      });
    }).fail(function(){
      showAlert('Ooops', 'Fail to load coin list', 'error');
    });

  }, 300);

}

/**
 * Show dashboard change configuration
 */
function showDashboardManager(){ $('.kr-change-dashboard-selector').show(); }

/**
 * Hide dashboard change configuration
 */
function hideDashboardManager(){ $('.kr-change-dashboard-selector').hide(); }

/**
 * Init graph controllers
 */
function initCoinGraphControllers(){

  // Fetch all graph
  $('.kr-dash-pan-cry').each(function(){

    let containerInit = $(this).attr('id');


    // Select graph on click
    $(this).off('click').click(function(){
      $('.kr-dash-pan-cry').removeClass('kr-dash-pan-cry-selected');
      $(this).addClass('kr-dash-pan-cry-selected');
      // Update top select graph item
      changeSelectedTopList($(this).attr('container'), $(this).attr('symbol'), $(this).attr('currency'), $(this).attr('market'));
    });

    let graphContent = $(this).attr('id');
    $(this).find('.kr-dash-pan-tb-nopt-n').off('click').click(function(){
      showListCoinChangeGraph(graphContent);
    });

    // Show coin list for change coin graph
    $(this).find('.kr-dash-pan-lgl').off('click').click(function(){
      //showListCoinChangeGraph(graphContent, true);
    });

    // Show indicator list
    $(this).find('.kr-dash-pan-ads-sld').off('click').click(function(){
      showIndicator(graphContent);
    });

    $(this).find('[kr-graph-ctype="line"]').off('click').click(function(){
      chartList[containerInit]['option']['series'][0]['itemStyle']['normal']['opacity'] = 0;
      chartList[containerInit]['option']['series'][1]['lineStyle']['opacity'] = 1;
      chartList[containerInit]['option']['series'][1]['areaStyle']['normal']['opacity'] = 1;
      newType = "line";
      chartList[containerInit]['graph'].setOption(chartList[containerInit]['option']);
      let SVGIcon = $(this).find('svg').clone();
      $(this).parent().parent().find('div').html(SVGIcon);
      $.post($('body').attr('hrefapp') + '/app/modules/kr-dashboard/src/actions/changeTypeGraph.php', {graph_id:$('#' + containerInit).attr('graph-id'), type:newType}).done(function(data){
        let jsonRes = jQuery.parseJSON(data);
        if(jsonRes.error == 1){
          //showAlert('Oops', jsonRes.msg, 'error');
        }
      }).fail(function(){
        showAlert('Oops', 'Fail to change type graph in database', 'error');
      });
    });

    $(this).find('[kr-graph-ctype="candlestick"]').off('click').click(function(){
      chartList[containerInit]['option']['series'][0]['itemStyle']['normal']['opacity'] = 1;
      chartList[containerInit]['option']['series'][1]['lineStyle']['opacity'] = 0;
      chartList[containerInit]['option']['series'][1]['areaStyle']['normal']['opacity'] = 0;
      newType = "candlestick";
      chartList[containerInit]['graph'].setOption(chartList[containerInit]['option']);
      let SVGIcon = $(this).find('svg').clone();
      $(this).parent().parent().find('div').html(SVGIcon);
      $.post($('body').attr('hrefapp') + '/app/modules/kr-dashboard/src/actions/changeTypeGraph.php', {graph_id:$('#' + containerInit).attr('graph-id'), type:newType}).done(function(data){
        let jsonRes = jQuery.parseJSON(data);
        if(jsonRes.error == 1){
          //showAlert('Oops', jsonRes.msg, 'error');
        }
      }).fail(function(){
        showAlert('Oops', 'Fail to change type graph in database', 'error');
      });
    });

    // Update coin search
    $(this).find('.kr-dash-pan-cry-select').find('input[type="text"]').off('keyup').keyup(function(){
      updateListCoinGraph($(this).val(), $(this).attr('graph'), function(coin){
        // Call back coin item list
        addTopListGraph(coin, function(topItem){
          changeGraph(coin, $('.kr-dash-pan-cry.kr-dash-pan-cry-selected').attr('currency'),
                      $('.kr-dash-pan-cry.kr-dash-pan-cry-selected').attr('id'), topItem.attr('topitem'), true,
                      $('.kr-dash-pan-cry.kr-dash-pan-cry-selected').attr('market'));
        });
      });
    });

    // Enable fullscreen toggle
    $(this).find('.kr-dash-tgglfullscreen').off('click').click(function(){
      toggleGraphFullScreen('#' + containerInit);
    });

    // Close graph
    $(this).find('.kr-dash-close').off('click').click(function(){
      removeGraph(containerInit);
    });

    $(this).find('.kr-dash-export').off('click').click(function(){
      exportChart(containerInit);
    });

  });

  // Create indicator action element
  $('[kr-indicator]').off('click').click(function(){
    createIndicator($(this).attr('kr-graph'), $(this).attr('kr-indicator'));
  });

  $(document).mouseup(function(e)
  {
      var container = $(".kr-dash-pan-cry-select");
      if (!container.is(e.target) && container.has(e.target).length === 0) container.hide();

      // var container = $(".kr-dash-pan-ads");
      // if (!container.is(e.target) && container.has(e.target).length === 0) $('.kr-dash-pan-ads').attr('style', 'display:none;');
  });
}

function changeGraphType(type = "candlestick", container){

  if(type == "line"){
    chartList[container]['option']['series'][0]['itemStyle']['normal']['opacity'] = 0;
    chartList[container]['option']['series'][1]['lineStyle']['opacity'] = 1;
    chartList[container]['option']['series'][1]['areaStyle']['normal']['opacity'] = 1;
  } else if(type == "candlestick"){
    chartList[container]['option']['series'][0]['itemStyle']['normal']['opacity'] = 1;
    chartList[container]['option']['series'][1]['lineStyle']['opacity'] = 0;
    chartList[container]['option']['series'][1]['areaStyle']['normal']['opacity'] = 0;
  }


  chartList[container]['graph'].setOption(chartList[container]['option']);

  $.post($('body').attr('hrefapp') + '/app/modules/kr-dashboard/src/actions/changeTypeGraph.php', {graph_id:$('#' + container).attr('graph-id'), type:type}).done(function(data){
    let jsonRes = jQuery.parseJSON(data);
    if(jsonRes.error == 1){
      //showAlert('Oops', jsonRes.msg, 'error');
    }
  }).fail(function(){
    showAlert('Oops', 'Fail to change type graph in database', 'error');
  });
}

/**
 * Show list coin graph
 * @param  {String} container Graph container
 */
function showListCoinChangeGraph(container, change = false){
  let selectorCoin = $('#' + container).find('.kr-dash-pan-cry-select');
  let containerGraph = $('#' + container);

  // Adjust position
  let posTop = ($(document).height() - containerGraph.position().top - 20) - (selectorCoin.height() + selectorCoin.position().top);
  if(posTop < 0) selectorCoin.css('top', posTop + 'px');

  let posLeft = ($(document).width() - containerGraph.position().left - 120) - (selectorCoin.width() + selectorCoin.position().left);

  if(posLeft < 0) selectorCoin.css('left', posLeft + 'px');


  selectorCoin.css('display', 'flex');

  updateListCoinGraph(null, container, function(coin){
    if(containerGraph.attr('symbol') == "not_init"){
      addTopListGraph(coin, function(topItem){
        $('.kr-top-graphlist-item[topitem="' + topItem.attr('topitem') + '"]').attr('container', containerGraph.attr('id'));
        changeGraph(coin, $('.kr-dash-pan-cry.kr-dash-pan-cry-selected').attr('currency'),
                          $('.kr-dash-pan-cry.kr-dash-pan-cry-selected').attr('id'), topItem.attr('topitem'), true,
                        $('.kr-dash-pan-cry.kr-dash-pan-cry-selected').attr('market'));
      });
    } else {
      let topItem = $('.kr-top-graphlist-item[container="' + container + '"]').attr('topitem');
      changeGraph(coin, $('.kr-dash-pan-cry.kr-dash-pan-cry-selected').attr('currency'), $('.kr-dash-pan-cry.kr-dash-pan-cry-selected').attr('id'), topItem, true,
                  $('.kr-dash-pan-cry.kr-dash-pan-cry-selected').attr('market'));
    }

  });
}

/**
 * Show indicator list graph
 * @param  {String} container Container graph

 */
function showIndicator(container){



  let selectorCoin = $('#' + container).find('.kr-dash-pan-ads');
  selectorCoin.css('display', 'flex');
  let containerGraph = $('#' + container);

  // Adjust position
  let bottomMax = selectorCoin.offset().top + selectorCoin.height();
  if(bottomMax > $(document).height()){
    selectorCoin.css('bottom', '0px');
  }
  // let posTop = ($(document).height() - containerGraph.position().top - 20) - (selectorCoin.height() + selectorCoin.position().top);
  // if(posTop < 0) selectorCoin.css('top', posTop + 'px');

  let posLeft = ($(document).width() - containerGraph.position().left - 120) - (selectorCoin.width() + selectorCoin.position().left);

  if(posLeft < 0) selectorCoin.css('left', posLeft + 'px');

}

/**
 * Hide indicator selector
 */
function hideIndicator(){
  $('.kr-dash-pan-ads').hide();
}

/**
 * Edit indicator action
 * @param  {String} container Container graph
 * @param  {String} indicator Indicator type
 * @param  {String} index     Indicator index
 */
function editIndicator(container, indicator, index){

  // Get indicator form
  $.post($('body').attr('hrefapp') + "/app/modules/kr-dashboard/src/actions/editIndicator.php", {graph:container, indic:indicator, key:index}).done(function(data){

    // Hide indicaotor selector
    hideIndicator();

    // Set website blur
    $('body').addClass('kr-nblr');

    // Show edit form & enable controllers
    $.when($('body').prepend(data)).then(function(){
      initEditIndicatorControllers();
    });
  }).fail(function(){
    showAlert('Ooops', 'Fail to load edit indicator content', 'error');
  });
}

/**
 * Close edit indicator

 */
function closeEditIndicator(){
  $('.kr-overley').remove();
  $('body').removeClass('kr-nblr');
}

/**
 * Init indicator edit controllers
 */
function initEditIndicatorControllers(){

  // Close edit indicator button
  $('.btn-closeovrley').click(function(){
    closeEditIndicator();
  });

  // Color field
  $('.kr-indicator-cfg-color').each(function(){
    let colorField = $(this);
    colorField.find('ul').find('.kr-indicator-cfg-color-cell').off('click').click(function(){
      colorField.find('.kr-indicator-cfg-postv').val($(this).attr('color'));
      colorField.find('.kr-indicator-cfg-val').css('background-color', '#' + $(this).attr('color'));
    });
  });

  // Line field
  $('.kr-indicator-cfg-line').each(function(){
    let lineField = $(this);
    lineField.find('ul').find('li').click(function(){
      lineField.find('.kr-indicator-cfg-postv').val($(this).find('.kr-indicator-cfg-line').attr('border'));
      lineField.find('.kr-indicator-cfg-val').css('border-bottom-width', $(this).find('.kr-indicator-cfg-line').attr('border') + 'px');
    });
  });

  // Reset indicator
  $('.btn-resetindicator').off('click').click(function(){
    $('.kr-indicator-cfg-select').each(function(){
      $(this).find('input[type="hidden"]').val($(this).attr('default'));
    });

    $('.kr-indicator-cfg-txt').each(function(){
      $(this).val($(this).attr('default'));
    });

    $('.kr-indicator-update-pst').submit();
  });

  // Update indicator
  $('.kr-indicator-update-pst').off('submit').submit(function(e){

    $.post($(this).attr('action'), $(this).serialize()).done(function(data){

      // Parse json result
      let response = jQuery.parseJSON(data);
      if(response.error == 0){
        // Update indicator style & close edit indicator
        updateIndicatorStyle(jQuery.parseJSON(data));
        closeEditIndicator();
      } else {
        showAlert('Oops', response.msg, 'error');
      }
    }).fail(function(){
      showAlert('Ooops', 'Fail to update indicator', 'error');
    });
    e.preventDefault();
    return false;
  });

}

let tendance = {}

/**
 * Init tendance view
 * @param  {String} container Graph
 */
function initTendanceGraph(container){

  if(!tendance.hasOwnProperty($('#' + container).attr('symbol'))){
    tendance[$('#' + container).attr('symbol')] = {
      'BUY' : [],
      'SELL' : []
    };
  }

  subscribeStreamerCallback(function(dataCoin){
    if(dataCoin.FromSymbol == $('#' + container).attr('symbol') && dataCoin.ToCurrency == $('#' + container).attr('currency')){
      if(dataCoin.Type != "UNKNOWN"){
        if(jQuery.inArray(dataCoin.ID, tendance[dataCoin.FromSymbol][dataCoin.Type]) == -1){
          tendance[dataCoin.FromSymbol][dataCoin.Type].push(dataCoin.ID);
          if(tendance[dataCoin.FromSymbol][dataCoin.Type].length >= 300){
            tendance[dataCoin.FromSymbol]['BUY'] = tendance[dataCoin.FromSymbol]['BUY'].slice(0, tendance[dataCoin.FromSymbol]['BUY'].length / 2);
            tendance[dataCoin.FromSymbol]['SELL'] = tendance[dataCoin.FromSymbol]['SELL'].slice(0, tendance[dataCoin.FromSymbol]['SELL'].length / 2);
          }

          let total = tendance[dataCoin.FromSymbol]['SELL'].length + tendance[dataCoin.FromSymbol]['BUY'].length;
          if(total > 20){
            let percentage = Math.round(10 - Math.abs(((tendance[dataCoin.FromSymbol]['BUY'].length - total) / total) * 10));
            if($('#' + container).find('.kr-dash-pan-com-t > ul > li.kr-dash-pan-com-t-buy').length != percentage){
              let c = 10;
              $('#' + container).find('.kr-dash-pan-com-t > ul').find('li').each(function(){
                if(c <= percentage){
                  if(!$(this).hasClass('kr-dash-pan-com-t-buy')) $(this).addClass('kr-dash-pan-com-t-buy');
                } else {
                  $(this).removeClass('kr-dash-pan-com-t-buy');
                }
                c -= 1;
              });
            }


          }
        }
      }
    }

  }, 0);

}

function changeGraphPairSymbol(symbol, currency, market){
  let container = $('.kr-top-graphlist-item-selected').attr('container');
  let topitem = $('.kr-top-graphlist-item-selected').attr('topitem');
  let dataCoin = {
    "symbol": symbol,
    "icon": "1",
    "name": symbol,
  };
  changeGraph(dataCoin, currency, container, topitem, true, market);
}

//changeSelectedTopList($(this).attr('container'), $(this).attr('symbol'));
