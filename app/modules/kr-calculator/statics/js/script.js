/**
 * Calculator method file
 */

$(document).ready(function(){

  reloadCalculatorItemAction();

  $('.add-new-calculator').click(function(){
    $('.kr-dash-add-calculator').css('display', 'flex');
    updateListCoinGraph($('.kr-dash-add-calculator').find('input[type="text"]').val(), $('.kr-dash-add-graph-selected').find('input[type="text"]').attr('graph'), function(coin, currency){
      addCalculatorItem(coin);
    });
  });

});

function addCalculatorItem(coin){
  //console.log(coin);
  let elem = $('<section symbol="' + coin.symbol + '">' +
    '<div><img src="' + $('body').attr('hrefapp') + '/assets/img/icons/crypto/' + coin.symbol + '.svg" alt="">' +
      '<label>' + coin.name + '</label></div>' +
    '<div><input type="text" name="" value="0">' +
      '<span>' + coin.symbol + '</span></div></section>');
  $('.kr-calculatorside-lc').append(elem);
  calculate($('.kr-calculatorside-lcsc').find('input[type="text"]').val());
  $('.kr-dash-add-calculator').hide();
  reloadCalculatorItemAction();
  $.post($('body').attr('hrefapp') + '/app/modules/kr-calculator/src/actions/addCalculatorItem.php', {symbol:coin.symbol, name:coin.name}).done(function(data){
    let json = jQuery.parseJSON(data);
    if(json.error == 1){
      showAlert('Oops', json.msg, 'error');
    }
  }).fail(function(){
    showAlert('Oops', 'Fail to access to add calculator item', 'error');
  });
}

function reloadCalculatorItemAction(){
  $('.kr-calculatorside-lc > section').each(function(){

  });

  $('.kr-calculatorside-lc > section').off('click').click(function(){
    $('.kr-calculatorside-lcsc').removeClass('kr-calculatorside-lcsc');
    $(this).addClass('kr-calculatorside-lcsc');
    $(this).find('input[type="text"]').focus();
  });

  $('.kr-calculatorside-lc > section').find('input[type="text"]').off('keyup').keyup(function(){
    if($(this).val().length > 0 && $(this).val() != 0){
      calculate($(this).val());
    }
  });

  $( ".kr-calculatorside-lc" ).sortable();
}

/**
 * Calculate function
 *
 * @param  {String} val
 */
function calculate(val){

  let symbollist = "",
      fromsymbol = "";
  $('.kr-calculatorside-lc > section').each(function(){
    if(!$(this).hasClass('kr-calculatorside-lcsc')){
      symbollist += $(this).attr('symbol') + ",";
    } else { fromsymbol = $(this).attr('symbol'); }
  });
  symbollist = symbollist.slice(0, -1);

  // Post calculator for get rate (args : from, to, value)
  $.post($('body').attr('hrefapp') + '/app/modules/kr-calculator/src/actions/getRates.php', {fromsymbol:fromsymbol, symbollist:symbollist, val:val}).done(function(data){

    let response = jQuery.parseJSON(data);

    if(response.error == 1){
      showAlert('Oops', response.msg, 'error');
    } else {
      $.each(response.result, function(symbol, vconv){
        $('.kr-calculatorside-lc > section[symbol="' + symbol + '"]').find('input[type="text"]').val(vconv);
      });
    }


  }).fail(function(){ // If fail to access to rate process (505, 404)
    showAlert('Ooops', 'Fail to get rate', 'error');
  });
}
