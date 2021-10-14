function loadChartNotification(container, symbol, value, type = 1, id = null){

  var opt = chartList[container]['option'];
  opt.series[0].markLine.data.push({
    name: 'notification-' + id,
    yAxis: value,
    symbol: 'circle',
    symbolSize: [0,0],
    lineStyle: {
      color: '#' + (type == null ? (value < opt.series[0].markLine.data[0].yAxis ? 'FF0000' : '3ae229') : (type == 0 ? '3ae229' : 'FF0000')),
      width: 1,
      type: 'solid'
    }
  });

  chartList[container]['graph'].setOption(chartList[container]['option']);

}

function addChartNotification(chart, symbol, val, currency, market = "CCCAGG"){

  $.each($('.kr-dash-pan-cry[symbol="' + symbol + '"]'), function(){
    loadChartNotification($(this).attr('id'), symbol, val, null);
  });

  $.post($('body').attr('hrefapp') + '/app/modules/kr-dashboard/src/actions/createNotification.php', {symb:symbol, currency:currency, market:market, value:val}).done(function(data){
    let response = jQuery.parseJSON(data);
    if(response.error == 1){
      showAlert('Oops', response.msg, 'error');
    }
  }).fail(function(){
    showAlert('Ooops', 'Fail to create chart indicator', 'error');
  });

}

function createNewNotification(symbol, currency, val = null, market = "CCCAGG"){
  $('body').addClass('kr-nblr');
  $.post($('body').attr('hrefapp') + '/app/modules/kr-dashboard/src/actions/createAlert.php', {symb:symbol, currency:currency, click:val, market:market}).done(function(data){
    try {
      let res = jQuery.parseJSON(data);
      if(res.error == 1){
        showAlert('Oops', res.msg, 'error');
      }
      $('body').removeClass('kr-nblr');
    } catch (e) {
      $.when($('body').prepend(data)).then(function(){
        initNewNotificationPopupControllers();
      });
    }
  }).fail(function(){
    showAlert('Ooops', 'Fail to open alert popup', 'error');
  });
}

function initNewNotificationPopupControllers(){
  $('form.createalert-popup-frm').off('submit').submit(function(e){

    let lVal = $(this).serializeArray();
    let allValid = true;
    for (val of lVal) {
      if(!allValid) return false;
      if(val.name != "symbol_alert" && val.name != "market" && val.name != "currency"){
        if(val.value.length > 0){
          try {
            var floatRegex = /^-?\d+(?:[.,]\d*?)?$/;
            if (!floatRegex.test(val.value)) throw 'Oops';
            let valIndicator = parseFloat(val.value);
          } catch (e) {
            $('[name="price_bellow_alert"]').parent().css('border', '1px solid #da4830');
            $('[name="price_above_alert"]').parent().css('border', '1px solid #da4830');
            allValid = false;
          }
        }
      }
    }

    if(allValid){
      for (val of $(this).serializeArray()) {
        if(val.name != "symbol_alert" && val.name != "market" && val.name != "currency"){
          if(val.value.length > 0){
        
            addChartNotification(null, lVal[2].value, val.value, lVal[4].value, lVal[3].value);
          }
        }
      }
    }

    closeAddNotificationPopup();

    e.preventDefault();
    return false;
  });

  $('form.createalert-popup-frm').find('input[type="button"]').off('click').click(function(){
    closeAddNotificationPopup();
  });

  $('.createalert-popup').find('header').find('div').off('click').click(function(){
    closeAddNotificationPopup();
  });

  $('.kr-list-notification-coin').find('li').find('.lnr-trash').off('click').click(function(){



    closeAddNotificationPopup();
    $.post($('body').attr('hrefapp') + '/app/modules/kr-dashboard/src/actions/deleteNotification.php', {notifid:$(this).parent().attr('kr-notification-id')}).done(function(data){
      let jsonRes = jQuery.parseJSON(data);
      if(jsonRes.error == 1){
        showAlert('Oops', jsonRes.msg, 'error');
      }
    }).fail(function(){
      showAlert('Oops', 'Fail to access delete controller notification', 'error');
    });
  });

}

function closeAddNotificationPopup(){
  $('.createalert-popup').remove();
  $('body').removeClass('kr-nblr');
}
