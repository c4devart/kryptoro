/**
 * Init market coin list
 */
function initCoinlist(){

  // Init top movers
  //initTopmovers();

  // Init market navigation
  initMarketNav();

  // Init market item
  initMarketListControllers();

  // Add subscription for each market list item
  $('.kr-marketlist').find('.kr-marketlist-n-nn').each(function(){
    let symbol = $(this).find('span').html();
    addSubscribtion(symbol, $('.kr-marketlist').attr('kr-currency-mm'));
  });

  // Subscribe callback coin update
  subscribeStreamerCallback(function(dataCoin){
    if(dataCoin.TOSYMBOL == $('.kr-marketlist').attr('kr-currency-mm')){
      // Check dataCoin validty
      if(dataCoin.PRICE != undefined){

        _highlightNumber(KRformatNumber(dataCoin.PRICE, (dataCoin.PRICE > 10 ? 2 : 5)), $('[kr-symbol-mm="' + dataCoin.FROMSYMBOL + '"]').find('[kr-mm-c="PRICE"]'));

        // // If price change
        // if(dataCoin.PRICE > parseInt($('[kr-symbol-mm="' + dataCoin.FROMSYMBOL + '"]').find('[kr-mm-c="PRICE"]').attr('kr-mm-cp'))){ // Change up
        //   // Animate green background color
        //   $('[kr-symbol-mm="' + dataCoin.FROMSYMBOL + '"]').find('[kr-mm-c="PRICE"]')
        //   $('[kr-symbol-mm="' + dataCoin.FROMSYMBOL + '"]').find('[kr-mm-c="PRICE"]').css('color', '#29c359').animate({ color: 'rgb(255, 255, 255)' }, 500);
        // } else if(dataCoin.PRICE < parseInt($('[kr-symbol-mm="' + dataCoin.FROMSYMBOL + '"]').find('[kr-mm-c="PRICE"]').attr('kr-mm-cp'))){ // Change down
        //   // Animate red background color
        //   $('[kr-symbol-mm="' + dataCoin.FROMSYMBOL + '"]').find('[kr-mm-c="PRICE"]').css('color', '#e30f0f').animate({ color: 'rgb(255, 255, 255)' }, 500);
        // }
        //
        // // Save new value
        // $('[kr-symbol-mm="' + dataCoin.FROMSYMBOL + '"]').find('[kr-mm-c="PRICE"]').attr('kr-mm-cp', dataCoin.PRICE);
        //
        // // Datacoin price format & update
        // if(dataCoin.PRICE > 10) dataCoin.PRICE = $.number(dataCoin.PRICE, 2, ',', ' ' );
        // else ("" + dataCoin.PRICE).replace('.', ',');

        //$('[kr-symbol-mm="' + dataCoin.FROMSYMBOL + '"]').find('[kr-mm-c="PRICE"]').html($('[kr-currency-mm-symb]').attr('kr-currency-mm-symb') + ' ' + dataCoin.PRICE);

        // Update percentage change 24h
        if(!isNaN(dataCoin.CHANGE24HOURPCT)) {
          if(dataCoin.CHANGE24HOURPCT < 0) $('[kr-symbol-mm="' + dataCoin.FROMSYMBOL + '"]').find('[kr-mm-c="CHANGE24HOURPCT"]').removeClass('kr-marketlist-cellnumber-positiv').addClass('kr-marketlist-cellnumber-negativ');
          else $('[kr-symbol-mm="' + dataCoin.FROMSYMBOL + '"]').find('[kr-mm-c="CHANGE24HOURPCT"]').removeClass('kr-marketlist-cellnumber-negativ').addClass('kr-marketlist-cellnumber-positiv');
          $('[kr-symbol-mm="' + dataCoin.FROMSYMBOL + '"]').find('[kr-mm-c="CHANGE24HOURPCT"]').html($.number(dataCoin.CHANGE24HOURPCT, 2, ',', ' ' ) + '%');
        }

        // Update volume last 24 hours
        if(!isNaN(dataCoin.VOLUME24HOURTO)) {
          if(dataCoin.VOLUME24HOURTO > 1000000000) dataCoin.VOLUME24HOURTO = $.number((dataCoin.VOLUME24HOURTO / 1000000000), 2, ',', ' ') + ' B';
          else if(dataCoin.VOLUME24HOURTO > 1000000) dataCoin.VOLUME24HOURTO = $.number((dataCoin.VOLUME24HOURTO / 1000000), 2, ',', ' ') + ' M';
          else dataCoin.VOLUME24HOURTO = $.number(dataCoin.VOLUME24HOURTO, 2, ',', ' ');
          $('[kr-symbol-mm="' + dataCoin.FROMSYMBOL + '"]').find('[kr-mm-c="VOLUME24HOURTO"]').html($('[kr-currency-mm-symb]').attr('kr-currency-mm-symb') + ' ' + dataCoin.VOLUME24HOURTO);
        }
      }
    }
  });

}

// Init market analytic navigation
function initMarketNav(){
  $('.kr-marketnav').find('ul').find('li').each(function(){
    $(this).off('click').click(function(){
      changeView('marketanalysis', $(this).attr('kr-navview'));
    });
  });

  $('.kr-search-coin').off('submit').submit(function(e){
    let search = $(this).find('[name="kr-search-value"]').val();
    changeView('marketanalysis', 'coinlist', {search:search});
    e.preventDefault();
    return false;
  });

  $('.kr-search-market').off('submit').submit(function(e){
    let search = $(this).find('[name="kr-search-value"]').val();
    changeView('marketanalysis', 'marketlist', {search:search});
    e.preventDefault();
    return false;
  });
}

function initMarketListControllers(){
  $('.kr-marketlist-item').off('click').click(function(){
    changeView('coin', 'coin', {symbol:$(this).attr('kr-symbol-mm'), currency:$(this).attr('kr-symbol-tt'), market:$(this).attr('kr-symbol-market')}, null, true);
  });
}
