function initBlockFolio(){

  initBlockFolioControllers();
  loadBlockFolioGraphList();





}

function initBlockFolioControllers(){
  $('.kr-port > section').each(function(){
    if(!$(this).hasClass('port-item-blured-add-act')){
      addSubscribtion($(this).attr('symbol'), $(this).attr('currency'));
      $(this).off('click').click(function(){
        changeView('coin', 'coin', {symbol:$(this).attr('symbol'), currency:$(this).attr('currency'), market:$(this).attr('market')});
      });
    }
  });

  $('.port-add-btn-circle, .port-item-blured-add-act').off('click').click(function(){
    showAddCryptoBlockfolio();
  });

  $('.kr-dash-pan-cry-select').find('input[type="text"]').off('keyup').keyup(function(){
    updateListCoinGraph($(this).val(), 'global_add', function(data){
      addCryptoBlockfolio(data.symbol);
    });
  });

  $(document).mouseup(function(e)
  {
      var container = $('.kr-port-add-btn');
      if (!container.is(e.target) && container.has(e.target).length === 0) closeAddCryptoBlockfolio();
  });

  $('.kr-blockfolio-remv').off('click').click(function(e){
    $('section[iid="' + $(this).attr('iid') + '"]').remove();
    $.post($('body').attr('hrefapp') + '/app/modules/kr-blockfolio/src/actions/removeItem.php', {iid:$(this).attr('iid')}).done(function(data){
      let response = jQuery.parseJSON(data);
      if(response.error != 0){
        showAlert('Oops', response.msg, 'error');
      }
    }).fail(function(){
      showAlert('Oops', 'Fail to access to delete controller', 'error');
    });
    e.preventDefault();
    return false;
  });

  subscribeStreamerCallback(function(dataCoin){
    if(dataCoin.PRICE != undefined){
      $('.kr-port').find('[symbol="' + dataCoin.FROMSYMBOL + '"][currency="' + dataCoin.TOSYMBOL + '"]').each(function(){
        $(this).find('[kr-port-d="PRICE"]').html(KRformatNumber(dataCoin.PRICE, 8) + ' ' + dataCoin.TOSYMBOL);

        let currencySymbol = $(this).find('.kr-port-holding').attr('kr-holding-cur');

        let holdingSize = $(this).find('.kr-port-holding').attr('kr-holding-size');
        $(this).find('[kr-holding-market-value]').html(KRformatNumber(holdingSize * dataCoin.PRICE, 8) + ' ' + currencySymbol);

        let diffProfLoss = (holdingSize * dataCoin.PRICE) - parseFloat($(this).find('.kr-port-holding').attr('kr-holding-buy-value'));
        $(this).find('[kr-holding-profit-loss]').html(KRformatNumber(diffProfLoss, 8) + ' ' + currencySymbol);
        if(diffProfLoss < 0) $(this).find('[kr-holding-profit-loss]').attr('class', 'kr-mono kr-block-profit-nav');
        else if(diffProfLoss > 0) $(this).find('[kr-holding-profit-loss]').attr('class', 'kr-mono kr-block-profit-pos');
        else $(this).find('[kr-holding-profit-loss]').attr('class', 'kr-mono kr-block-profit-neutral');


        $(this).find('[kr-port-d="CHANGE24HOURPCT"]').html(KRformatNumber(dataCoin.CHANGE24HOURPCT) + '%');
        if(dataCoin.CHANGE24HOURPCT < 0){
          $(this).find('[kr-port-d="CHANGE24HOURPCT"]').removeClass('kr-blockfolio-iact-positiv').addClass('kr-blockfolio-iact-negativ');
          $(this).removeClass('kr-port-positiv').addClass('kr-port-negativ');
        }
        if(dataCoin.CHANGE24HOURPCT >= 0){
           $(this).find('[kr-port-d="CHANGE24HOURPCT"]').removeClass('kr-blockfolio-iact-negativ').addClass('kr-blockfolio-iact-positiv');
           $(this).removeClass('kr-port-negativ').addClass('kr-port-positiv');
        }
      });
    }
  });

  $('.kr-port-holding-add').off('click').click(function(e){
    showAddTransaction($(this).attr('kr-symbol'));
    e.preventDefault();
    return false;
  });

  initAddTransactionControllers();

}

function loadBlockFolioGraphList(){

  Chart.defaults.NegativeTransparentLine = Chart.helpers.clone(Chart.defaults.line);
  Chart.controllers.NegativeTransparentLine = Chart.controllers.line.extend({
    update: function() {

      var min = Math.min.apply(null, this.chart.data.datasets[0].data);
      var max = Math.max.apply(null, this.chart.data.datasets[0].data);
      var yScale = this.getScaleForId(this.getDataset().yAxisID);

      var top = yScale.getPixelForValue(max);
      var zero = yScale.getPixelForValue(0);
      var bottom = yScale.getPixelForValue(min);

      var ctx = this.chart.chart.ctx;
      var gradient = ctx.createLinearGradient(0, top, 0, bottom);

      if((bottom - top) > 0 && (zero - top) > 0){
        var ratio = Math.min((zero - top) / (bottom - top), 1);
        gradient.addColorStop(0, 'rgba(41, 195, 89, 0.3)');
        gradient.addColorStop(ratio, 'rgba(41, 195, 89, 0.3)');
        gradient.addColorStop(ratio, 'rgba(227, 15, 15, 0.3)');
        gradient.addColorStop(1, 'rgba(227, 15, 15, 0.3)');
        this.chart.data.datasets[0].backgroundColor = gradient;

        gradient = ctx.createLinearGradient(0, top, 0, bottom);
        gradient.addColorStop(0, 'rgba(41, 195, 89, 1)');
        gradient.addColorStop(ratio, 'rgba(41, 195, 89, 1)');
        gradient.addColorStop(ratio, 'rgba(227, 15, 15, 1)');
        gradient.addColorStop(1, 'rgba(227, 15, 15, 1)');
        this.chart.data.datasets[0].borderColor = gradient;

      }


      return Chart.controllers.line.prototype.update.apply(this, arguments);
    }
  });

  $('.kr-port-graph').each(function() {

    // Get graph container (canvas)
    let graphPicture = $(this).find('canvas')[0].getContext('2d');


    let yVal = $(this).attr('yv').split(',');
    let startIndex = yVal[0];
    $.each(yVal, function(k, v){
      yVal[k] = parseFloat((v - startIndex))
    });
    let xVal = $(this).attr('xv').split(',');

    //
    var myLineChart = new Chart(graphPicture, {
      type: 'NegativeTransparentLine',
      data: {
        labels: xVal,
        datasets: [{
          yAxisID : 'y-axis-0',
          borderWidth:1,
          borderColor:'purple',
          data: yVal,
          fill: 'origin'
        }],
      },
      options: {
          animation: {
            duration: 0
          },
          legend: {
            display: false
          },
          tooltips: {
            enabled: false
          },
          elements: {
            point: {
              radius: 0
            }
          },
          scales: {
            yAxes: [{
              gridLines: {
                display: false
              },
              display: false
            }],
            xAxes: [{
              gridLines: {
                display: false
              },
              display: false
            }]
          }
      }
    });



  });
}

function showAddCryptoBlockfolio(){
  $('.kr-list-coins-add-blocfolio').show();
  $('.kr-list-coins-add-blocfolio').find('.kr-dash-pan-cry-select').show();
  updateListCoinGraph('', 'global_add', function(data){
    addCryptoBlockfolio(data.symbol);
  });
}

function closeAddCryptoBlockfolio(){
  $('.kr-list-coins-add-blocfolio').hide();
}

function addCryptoBlockfolio(symbol, currency, market){
  showDashboardLoading();
  $.post($('body').attr('hrefapp') + '/app/modules/kr-blockfolio/src/actions/addItem.php', {symbol:symbol, currency:currency, market:market}).done(function(data){
    
    let response = jQuery.parseJSON(data);
    if(response.error == 0){
      changeView('blockfolio', 'blockfolio');
    } else {
      showAlert('Oops', response.msg, 'error');
    }
  }).fail(function(){
    showAlert('Oops', 'Fail to add item (404, 505)', 'error');
  });

}

function showAddTransaction(symbol){
  $.get($('body').attr('hrefapp') + '/app/modules/kr-blockfolio/src/actions/addHoldingForm.php', {symbol:symbol}).done(function(data){
    try {
      let r = jQuery.parseJSON(data);
      if(r.error == 1) showAlert('Oops', r.msg, 'error');
    } catch (e) {
      $.when($('body').prepend(data)).then(function(){
        initAddTransactionControllers();
      });
    }
  }).fail(function(){
    showAlert('Oops', 'Fail to load add holding form', 'error');
  });
}

function initAddTransactionControllers(){

  $('.kr-block-add-holding-form').off('submit').submit(function(e){
    let dataAdd = $(this).serializeArray();
    dataAdd.push({name: 'type_trading', value: $('.kr-block-addh-type-selected').attr('t')});
    $.post($('body').attr('hrefapp') + '/app/modules/kr-blockfolio/src/actions/addHolding.php', dataAdd).done(function(data){
      closeAddTransaction();
      changeView('blockfolio', 'blockfolio');
    }).fail(function(){
      showAlert('Oops', 'Fail to add holding (Script error)', 'error');
    });
    e.preventDefault();
    return false;
  });

  $('.kr-block-addh-type > li').off('click').click(function(){
    $('.kr-block-addh-type').find('li').attr('class', '');
    $(this).addClass('kr-block-addh-type-selected');
  });

  $('.kr-hld-changetv').off('keyup').keyup(function(){
    calculateTotalValueAdded();
  });


}

function calculateTotalValueAdded(){
  try {
    let priceTr = parseFloat($('#kr-hld-tp').val());
    let quantityTr = parseFloat($('#kr-hld-qt').val());

    let ammount = priceTr * quantityTr;
    $('#kr-hld-tv').html(ammount);
  } catch (e) {
    $('#kr-hld-tv').html('Wrong format');
  }

}

function closeAddTransaction(){
  $('.kr-block-add-holding').remove();
}
