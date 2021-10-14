$(document).ready(function(){
  $('.kr-wallet-top > div').off('click').click(function(){
    $('.kr-wallet-top > section').css('display', 'flex');
  });
  $('[kr-wallet-change]').off('click').click(function(){
    _changeWalletBalance($(this).attr('kr-wallet-change'));
  });

  $('[kr-credit-balance]').off('click').click(function(e){
    _loadCreditForm('depositChooseBalance');
    e.preventDefault();
    return false;
  });

  // if($('[kr-credit-balance]').length == 1){
  //   $('[kr-credit-balance]').trigger('click');
  // }

  $(document).mouseup(function(e)
  {
      var container = $('.kr-wallet-top');
      if (!container.is(e.target) && container.has(e.target).length === 0) $('.kr-wallet-top > section').css('display', 'none');
  });

  $('[kr-credit-widthdraw]').off('click').click(function(){
    _askWidthdraw();
  });

  $('[kr-balance-transaction-history]').off('click').click(function(){
    $('.kr-wallet-top > section').css('display', 'none');
    changeView('trade', 'transactionsHistory');
  });

  $('.kr-wallet-balance-show-list').off('click').click(function(){
    if($(this).hasClass('kr-wallet-balance-show-list-native')){
      $('.kr-wallet-top > section').css('display', 'none');
      changeView('trade', 'balances');
    } else {
      changeView('trade', 'balances', {exchange:$(this).attr('kr-balance-exchange')});
    }

  });

  $('.kr-wallet-top-change').find('[kr-wallet-exch-name]').off('click').click(function(){
    $('.kr-wallet-top > section').css('display', 'none');
    $.post($('body').attr('hrefapp') + '/app/modules/kr-trade/src/actions/changeMainThirdparty.php', {exchange:$(this).attr('kr-wallet-exch-name')}).done(function(data){
      let jsonRes = jQuery.parseJSON(data);
      if(jsonRes.error == 1){
        showAlert('Oops', jsonRes.msg, 'error');
      } else {
        $('.kr-wallet-top-resum > ul').html('');
        _updateBalanceData();
      }
    }).fail(function(){
      showAlert('Oops', 'Fail to change exchange account', 'error');
    });
  });

});

function _askWidthdraw(symbol = null){
  _closeCreditForm();
  $.get($('body').attr('hrefapp') + '/app/modules/kr-trade/src/actions/askWidthdraw.php', {symbol:symbol}).done(function(data){
    $.when($('body').prepend(data)).then(function(){
      _initWidthdrawPopup();
    });
  }).fail(function(){
    showAlert('Oops', 'Fail to open widthdraw form (404, 505)', 'error');
  });
}

function _loadCreditForm(form, args = {}, title = 'Make a deposit'){
  if($('.kr-balance-credit').length == 0){
    $('body').prepend('<section class="kr-balance-credit kr-ov-nblr">' +
      '<section>' +
        '<header>' +
          '<span>' + title + '</span>' +
          '<div onclick="_closeCreditForm();"> <svg class="lnr lnr-cross"><use xlink:href="#lnr-cross"></use></svg> </div>' +
        '</header><div class="spinner"></div>' +
      '</section>' +
    '</section>');
    $('body').addClass('kr-nblr');
  } else {
    $('.kr-balance-credit > section > header > span').html(title);
  }

  $('.kr-balance-credit').attr('kr-balance-credit-view', form);

  $('.kr-balance-credit > section > section').remove();
  $('.kr-balance-credit > section > div').remove();
  $.post($('body').attr('hrefapp') + '/app/modules/kr-trade/src/actions/' + form + '.php', args).done(function(data){
    $.when($('.kr-balance-credit > section').append(data)).then(function(){
      _initCreditPopup();
    });
  }).fail(function(){
    showAlert('Oops', 'Fail to load credit form', 'error');
  });
}

function _reloadWithdrawInfos(val){
  let precision = parseInt($("#kr-credit-chosamount").attr('kr-chosamount-decimal'));
  $('[name="kr_widthdraw_amount"]').val(val);
  $('.kr-balance-range-inp').val(val);
  $('[kr-widthdraw-amount="true"] > i').html(KRformatNumber(val, $('[kr-widthdraw-amount-decimal]').attr('kr-widthdraw-amount-decimal'), precision));
  let nFees = parseFloat($('[kr-widthdraw-amount-fees]').attr('kr-widthdraw-amount-fees')) / 100;
  let totalWF = val - (val * nFees);
  $('[kr-widthdraw-fees="true"] > i').html(KRformatNumber(val * nFees, $('[kr-widthdraw-amount-decimal]').attr('kr-widthdraw-amount-decimal'), precision));
  $('[kr-widthdraw-total="true"] > i').html(KRformatNumber(totalWF, $('[kr-widthdraw-amount-decimal]').attr('kr-widthdraw-amount-decimal'), precision));
  $('[kr-widthdraw-convert="true"] > i').html(KRformatNumber(val * parseFloat($('.kr-balance-range-preview-convert').attr('kr-widthdraw-convert-t')), 2));
}

function _initWidthdrawPopup(){
  $("#kr-credit-chosamount").ionRangeSlider({
    step: $("#kr-credit-chosamount").attr('kr-chosamount-step'),
    min:$("#kr-credit-chosamount").attr('kr-chosamount-min'),
    max:$("#kr-credit-chosamount").attr('kr-chosamount-max'),
    grid: true,
    postfix: " " + $("#kr-credit-chosamount").attr('kr-chosamount-symbol'),
    onChange: function (data) {
      _reloadWithdrawInfos(data.from);

    }
  });

  var slider = $("#kr-credit-chosamount").data("ionRangeSlider");

  $('.kr-balance-range-inp').keyup(function(){
    slider.update({
        from: $('.kr-balance-range-inp').val()
    });
    _reloadWithdrawInfos($('.kr-balance-range-inp').val());
  });

  $('.kr-createwidthdraw').off('submit').submit(function(e){
    $('.kr-balance-widthdraw').hide();
    $('.kr-balance-approvecontract').hide();
    $('.kr-balance-widthdraw').parent().find('.spinner').show();
    $.post($(this).attr('action'), $(this).serialize()).done(function(data){
      let jsonRes = jQuery.parseJSON(data);
      //_closeCreditForm();
      if(jsonRes.error == 1){
        $('.kr-balance-widthdraw').parent().find('.spinner').hide();
        $('.kr-balance-approvecontract').hide();
        $('.kr-balance-widthdraw').show();
        showAlert('Oops', jsonRes.msg, 'error');
      } else if(jsonRes.error == 2) {
        $('.kr-balance-widthdraw').parent().find('.spinner').hide();
        $('.kr-balance-approvecontract').show();
      } else {
        showAlert('Success', jsonRes.msg, 'success');
        $('.kr-balance-widthdraw').parent().find('.spinner').hide();
        $('.kr-balance-checkemail').show();
        _updateBalanceData();
      }
    }).fail(function(){
      showAlert('Oops', 'Fail to create widthdraw (404, 505)', 'error');
    });
    e.preventDefault();
    return false;
  });


}

function _agreeWithdrawContract(){
  $('#kr_withdraw_agreement_completed').val("1");
  $('.kr-createwidthdraw').submit();
}

function _declineWithdrawContract(symbol){
  _askWidthdraw(symbol);
}

let checkPaymentTimeout = null;
function _initCreditPopup(){
  clearTimeout(checkPaymentTimeout);
  checkPaymentTimeout = null;
  _initCopyClipboard();
  $('[kr-balance-credit]').off('click').click(function(){
    if($(this).hasClass('kr-balance-credit-dibl')) return false;
    let type = $(this).attr('kr-balance-type');
    if(type == "practice"){
      $.post($('body').attr('hrefapp') + '/app/modules/kr-trade/src/actions/depositBalance.php', {bid:$(this).attr('kr-balance-idc')}).done(function(data){
        let jsonRes = jQuery.parseJSON(data);
        if(jsonRes.error == 1){
          showAlert('Oops', jsonRes.msg, 'error');
        } else {
          _closeCreditForm();
          _updateBalanceData();
        }
      }).fail(function(){
        showAlert('Oops', 'Fail to access to the deposit script (404, 505)', 'error');
      });
    } else {
      _loadCreditForm('depositRealBalance', {});
    }
  });

  $('[kr-charges-payment]').off('click').click(function(){
    let ptype = $(this).attr('kr-charges-payment');
    let paymentAmount = $('[kr-charges-payment-vamdepo]').val();
    $('.kr-balance-credit > section > section').hide();
    $('.kr-balance-credit > section > div.spinner').show();
    if(ptype == "creditcard"){
      _loadCreditForm('depositCreditCard', {amount:paymentAmount});
    } else if(ptype == "coingate"){
      let sopen = window.open($('body').attr('hrefapp') + "/app/modules/kr-payment/views/coingate.php?t=deposit&m=" + paymentAmount + '&cr=' + $('.kr-balance-credit-drel-cont').attr('kr-bssymbol'), "popupWindow", "width=1041, height=669, scrollbars=yes");
      let timeCreated = $(this).attr('kr-cng-lt');
      checkPaymentTimeout = setTimeout(function(){
        _checkPaymentStatus('coingate', timeCreated, sopen);
      }, 1000);
    } else if(ptype == "payeer"){
      let sopen = window.open($('body').attr('hrefapp') + "/app/modules/kr-payment/views/payeer.php?t=deposit&m=" + paymentAmount + '&cr=' + $('.kr-balance-credit-drel-cont').attr('kr-bssymbol'), "popupWindow", "width=1041, height=669, scrollbars=yes");
      let timeCreated = $(this).attr('kr-cng-lt');
      checkPaymentTimeout = setTimeout(function(){
        _checkPaymentStatus('payeer', timeCreated, sopen);
      }, 1000);
    } else if(ptype == "coinbasecommerce"){
      let sopen = window.open($('body').attr('hrefapp') + "/app/modules/kr-payment/views/coinbasecommerce.php?t=deposit&m=" + paymentAmount + '&cr=' + $('.kr-balance-credit-drel-cont').attr('kr-bssymbol'), "popupWindow", "width=530, height=669, scrollbars=yes");
      let timeCreated = $(this).attr('kr-cng-lt');
      checkPaymentTimeout = setTimeout(function(){
        _checkPaymentStatus('coinbasecommerce', timeCreated, sopen);
      }, 1000);
    } else if(ptype == "raveflutterwave"){
      let sopen = window.open($('body').attr('hrefapp') + "/app/modules/kr-payment/views/raveflutterwave.php?t=deposit&m=" + paymentAmount + '&cr=' + $('.kr-balance-credit-drel-cont').attr('kr-bssymbol'), "popupWindow", "width=530, height=669, scrollbars=yes");

      let timeCreated = $(this).attr('kr-cng-lt');
      checkPaymentTimeout = setTimeout(function(){
        _checkPaymentStatus('raveflutterwave', timeCreated, sopen);
      }, 1000);
    } else if(ptype == "blockonomics"){
      window.open($('body').attr('hrefapp') + "/app/modules/kr-payment/views/blockonomics.php?t=deposit&m=" + paymentAmount + '&cr=' + $('.kr-balance-credit-drel-cont').attr('kr-bssymbol'), "popupWindow", "width=400, height=582, scrollbars=yes");
      //let timeCreated = $(this).attr('kr-cng-lt');
      // setTimeout(function(){
      //   _checkCoinGatePayment(timeCreated);
      // }, 5000);
    } else if(ptype == "coinpayments"){
      let sopen = window.open($('body').attr('hrefapp') + "/app/modules/kr-payment/views/coinpayments.php?t=deposit&m=" + paymentAmount + '&cr=' + $('.kr-balance-credit-drel-cont').attr('kr-bssymbol'), "popupWindow", "width=530, height=669, scrollbars=yes");
      let timeCreated = $(this).attr('kr-cng-lt');
      checkPaymentTimeout = setTimeout(function(){
        _checkPaymentStatus('coinpayments', timeCreated, sopen);
      }, 1000);
    } else if(ptype == "polipayments"){
      let sopen = window.open($('body').attr('hrefapp') + "/app/modules/kr-payment/views/polipayments.php?t=deposit&m=" + paymentAmount + '&cr=' + $('.kr-balance-credit-drel-cont').attr('kr-bssymbol'), "popupWindow", "width=530, height=669, scrollbars=yes");
      let timeCreated = $(this).attr('kr-cng-lt');
      checkPaymentTimeout = setTimeout(function(){
        _checkPaymentStatus('polipayments', timeCreated, sopen);
      }, 1000);
    } else if(ptype == "paystack"){
      let sopen = window.open($('body').attr('hrefapp') + "/app/modules/kr-payment/views/paystack.php?t=deposit&m=" + paymentAmount + '&cr=' + $('.kr-balance-credit-drel-cont').attr('kr-bssymbol'), "popupWindow", "width=530, height=669, scrollbars=yes");
      let timeCreated = $(this).attr('kr-cng-lt');
      checkPaymentTimeout = setTimeout(function(){
        _checkPaymentStatus('paystack', timeCreated, sopen);
      }, 1000);
    } else if(ptype == "directdeposit"){
      let sopen = window.open($('body').attr('hrefapp') + "/app/modules/kr-payment/views/directdeposit.php?t=deposit&cr=" + $('.kr-balance-credit-drel-cont').attr('kr-bssymbol'), "popupWindow", "width=530, height=669, scrollbars=yes");
      let timeCreated = $(this).attr('kr-cng-lt');
      checkPaymentTimeout = setTimeout(function(){
        _checkPaymentStatus('directdeposit', timeCreated, sopen);
      }, 1000);
    } else {
      $.post($('body').attr('hrefapp') + '/app/modules/kr-payment/src/actions/deposit/processOther.php', {type:ptype, amount:paymentAmount, currency:$('.kr-balance-credit-drel-cont').attr('kr-bssymbol')}).done(function(data){
        let jsonRes = jQuery.parseJSON(data);
        if(jsonRes.error == 1){
          showAlert('Oops', jsonRes.msg, 'error');
        } else {
          window.location.replace(jsonRes.link);
        }
      });
    }
  });

  $('.kr-deposit-banktransfert-item').off('click').click(function(){
    let bankID = $(this).attr('bankid');
    window.open($('body').attr('hrefapp') + "/app/modules/kr-payment/views/banktransfert.php?t=" + bankID, "popupWindow", "width=800, height=669, scrollbars=yes");
  });

  $('.create-n-banktransfert').off('click').click(function(){
    window.open($('body').attr('hrefapp') + "/app/modules/kr-payment/views/banktransfert.php?s=new", "popupWindow", "width=800, height=669, scrollbars=yes");
    _closeCreditForm();
  });

  $("#kr-credit-chosamount").ionRangeSlider({
    step: $('#kr-credit-chosamount').attr('kr-chosamount-step'),
    grid: true,
    min:$("#kr-credit-chosamount").attr('kr-chosamount-min'),
    max:$("#kr-credit-chosamount").attr('kr-chosamount-max'),
    postfix: ' ' + $('#kr-credit-chosamount').attr('kr-chosamount-symbol'),
    onChange: function (data) {
      _recalCreditAmount(data.from);
      $('.kr-balance-range-inp-deposit').val(data.from);
    }
  });

  var sliderCredit = $("#kr-credit-chosamount").data("ionRangeSlider");

  $('.kr-balance-range-inp-deposit').keyup(function(){
    sliderCredit.update({
        from: $('.kr-balance-range-inp-deposit').val()
    });
    _recalCreditAmount($('.kr-balance-range-inp-deposit').val());
  });

  $('.kr-deposit-creditcard').off('submit').submit(function(e){
    $('.kr-balance-credit > section > section').hide();
    $('.kr-balance-credit > section > div.spinner').show();
    $.post($(this).attr('action'), $(this).serialize()).done(function(data){
      let jsonRes = jQuery.parseJSON(data);
      if(jsonRes.error == 1){
        showAlert('Oops', jsonRes.msg, 'error');
      } else {
        _closeCreditForm();
        _updateBalanceData();
      }
    }).fail(function(){
      showAlert('Oops', 'Fail to access to payment (404, 505)', 'error');
    });
    e.preventDefault();
    return false;
  });




}

function _recalCreditAmount(amount){
  let precision = parseFloat($('.kr-balance-range').attr('kr-chosamount-precision'));
  let fees = parseFloat($('[kr-credit-calcfees="fees"]').attr('kr-credit-calcfees-am')) / 100;
  $('[kr-charges-payment-vamdepo]').val(amount);
  $('[kr-credit-calcfees="amount"]').find('i').html(KRformatNumber(amount, precision).toString());
  let feesTotal = amount * fees;
  $('[kr-credit-calcfees="fees"]').find('i').html(KRformatNumber(feesTotal, precision).toString());
  $('[kr-credit-calcfees="total"]').find('i').html(KRformatNumber(parseFloat(feesTotal) + parseFloat(amount), precision).toString());
}

function _closeCreditForm(){
  $('.kr-balance-credit').remove();
  $('body').removeClass('kr-nblr');
}

function _changeWalletBalance(bid){
  $.post($('body').attr('hrefapp') + '/app/modules/kr-trade/src/actions/changeBalance.php', {bid:bid}).done(function(data){
    let jsonRes = jQuery.parseJSON(data);
    if(jsonRes.error == 1){
      showAlert('Oops', jsonRes.msg, 'error');
    } else {
      $('.kr-wallet-top > div').attr('class', 'kr-wallet-top-' + jsonRes.balance.type_balance);
      $('.kr-wallet-top > div > div > span:first-child').html(jsonRes.balance.title);
      $('.kr-wallet-top > div > div').find('[kr-balance-id]').attr('kr-balance-id', jsonRes.balance.enc_id_balance);
      _updateBalanceData();
      $('.kr-wallet-top > section').css('display', 'none');
    }
  }).fail(function(){
    showAlert('Oops', 'Fail to change balance (404, 505)', 'error');
  });
}

function walletNumberAnimation(now, tween) {
    var floored_number = now / Math.pow(10, 2);
    var  target = $(tween.elem);

    //floored_number = floored_number.toFixed();
    //floored_number = floored_number.toString().replace('.', ',');

  target.text(KRformatNumber(floored_number, (floored_number > 1 || floored_number < -1 ? 2 : 5)));
}

function _updateBalanceData(){
  $.get($('body').attr('hrefapp') + '/app/modules/kr-trade/src/actions/dataBalance.php').done(function(data){
    let jsonRes = jQuery.parseJSON(data);
    if(jsonRes.error == 1){
      showAlert('Oops', jsonRes.msg, 'error');
    } else {

      if(jsonRes.type == "native"){
        $('.kr-wallet-top-resum > h3').html(jsonRes.current_balance.title);
        $('.kr-wallet-top-resum').find('[kr-wallet-resum-profit]').attr('class', jsonRes.current_balance.profit_class);
        $.each(jsonRes.current_balance, function(k, v){
          if($('[kr-wallet-resum="' + k + '"]').length == 0) return true;
          let actualValue = parseFloat(KRunformatNumber($('[kr-wallet-resum="' + k + '"]').html())) * 100;
          $('[kr-wallet-resum="' + k + '"]').prop('number', actualValue)
              .animateNumber(
                {
                  number: v,
                  numberStep: walletNumberAnimation
                },
                1000
              );
        });

        $('li[kr-wallet-symbol]').remove();

        $.each(jsonRes.balances, function(symbol, amount){
          if($('.kr-wallet-top-resum > ul > li[kr-wallet-symbol="' + symbol + '"]').length > 0){
            let balanceWalletItem = $('.kr-wallet-top-resum > ul > li[kr-wallet-symbol="' + symbol + '"]');
            let currentBalance = balanceWalletItem.find('span:last-child > i:first-child');
            currentBalanceValue = parseFloat(KRunformatNumber(currentBalance.html())) * 100;
            currentBalance.prop('number', currentBalanceValue)
              .animateNumber(
                {
                  number: amount * 100,
                  numberStep: walletNumberAnimation
                },
                1000
              );
            balanceWalletItem.find('span:last-child > i:last-child').html(symbol);
            balanceWalletItem.find('span:first-child').html(symbol);
          } else {
            $('.kr-wallet-top-resum > ul').append('<li kr-wallet-symbol="' + symbol + '">' +
              '<span>' + symbol + '</span>' +
              '<div></div>' +
              '<span><i>' + KRformatNumber(amount, (amount > 10 ? 2 : 5))  + '</i> <i>' + symbol + '</i></span>' +
            '</li>')
          }
        });

        $.each(jsonRes.balance, function(k, v){
          if($('[kr-balance-id="' + v.enc_id + '"]').length == 0) return true;
          let actualBalance = parseFloat(KRunformatNumber($('[kr-balance-id="' + v.enc_id + '"]').find('i').html())) * 100;
          $('[kr-balance-id="' + v.enc_id + '"]').find('i')
              .prop('number', actualBalance)
              .animateNumber(
                {
                  number: v.balance,
                  numberStep: walletNumberAnimation
                },
                1000
              );
        });
      } else if(jsonRes.type == "external"){

        $('.kr-wallet-top-thirdparty > div > span:first-child').html(jsonRes.exchange_title);

        let actualValue = parseFloat(KRunformatNumber($('.kr-wallet-top-thirdparty > div > span:last-child > i:first-child').html())) * 100;
        $('.kr-wallet-top-thirdparty > div > span:last-child > i:first-child').prop('number', actualValue)
          .animateNumber(
            {
              number: jsonRes.first_balance * 100,
              numberStep: walletNumberAnimation
            },
            1000
          );

        $('.kr-wallet-top-thirdparty > div > span:last-child > i:last-child').html(jsonRes.first_balance_symbol);

        $.each(jsonRes.balances, function(k, v){
          if($('.kr-wallet-top-resum > ul > li[kr-wallet-exchange="' + jsonRes.exchange_name + '"][kr-wallet-symbol="' + v.symbol + '"]').length > 0){
            let balanceWalletItem = $('.kr-wallet-top-resum > ul > li[kr-wallet-exchange="' + jsonRes.exchange_name + '"][kr-wallet-symbol="' + v.symbol + '"]');
            let currentBalance = balanceWalletItem.find('span:last-child > i:first-child');
            currentBalanceValue = parseFloat(KRunformatNumber(currentBalance.html())) * 100;
            currentBalance.prop('number', currentBalanceValue)
              .animateNumber(
                {
                  number: v.amount * 100,
                  numberStep: walletNumberAnimation
                },
                1000
              );
            balanceWalletItem.find('span:last-child > i:last-child').html(v.symbol);
            balanceWalletItem.find('span:first-child').html(v.symbol);
          } else {
            $('.kr-wallet-top-resum > ul').append('<li kr-wallet-exchange="' + jsonRes.exchange_name + '" kr-wallet-symbol="' + v.symbol + '">' +
              '<span>' + v.symbol + '</span>' +
              '<div></div>' +
              '<span><i>' + KRformatNumber(v.amount, (v.amount > 10 ? 2 : 5))  + '</i> <i>' + v.symbol + '</i></span>' +
            '</li>')
          }
        });

        if(jsonRes.show_more){
          $('.kr-wallet-balance-show-list').show();
        } else {
          $('.kr-wallet-balance-show-list').hide();
        }

      }

    }
  }).fail(function(){
    showAlert('Oops', 'Fail to reload balance', 'error');
  })
}

function _checkPaymentStatus(type, time, openbox = null){
  clearTimeout(checkPaymentTimeout);
  checkPaymentTimeout = null;
  if (openbox && !openbox.closed){
    checkPaymentTimeout = setTimeout(function(){
      _checkPaymentStatus(type, time, openbox);
    }, 1000);
  } else{
    $.get($('body').attr('hrefapp') + '/app/modules/kr-payment/src/actions/deposit/checkPaymentStatus.php', {type:type, time:time}).done(function(data){

      let respond = jQuery.parseJSON(data);
      if(respond.error == 1){
        showAlert('Oops', respond.msg, 'error');
        _closeCreditForm();
      } else {
        _updateBalanceData();
        _closeCreditForm();
      }

    }).fail(function(){
      showAlert('Oops', 'Fail to check payment', 'error');
      _closeCreditForm();
    });
  }

}

let checkCoinGateTimeout = null;
function _checkCoinGatePayment(t){
  clearTimeout(checkCoinGateTimeout); checkCoinGateTimeout = null;
  $.get($('body').attr('hrefapp') + '/app/modules/kr-payment/src/actions/deposit/checkCoingate.php', {t:t}).done(function(data){
    let response = jQuery.parseJSON(data);
    if(response.error == 0){
      if(response.status != 0){
        _closeCreditForm();
        if(response.status == 1){
          _updateBalanceData();
        }
      } else {
        checkCoinGateTimeout = setTimeout(function(){
          _checkCoinGatePayment(t);
        }, 500);
      }
    } else {
      showAlert('Oops', response.msg, 'error');
    }

  }).fail(function(){
    showAlert('Oops', 'Fail to check coingate payment', 'error');
  });
}

function initBalanceView(){
  $('.kr-balances-view-tggsmall').off('click').click(function(){
    $(this).toggleClass('kr-balances-view-tggsmall-active');

    if($(this).hasClass('kr-balances-view-tggsmall-active')){
      $.each($('.kr-balanceitem-cv'), function(){
        if(parseFloat($(this).attr('kr-balance-item-value')) > 0.001){
          $(this).css('display', 'flex');
        } else {
          $(this).hide();
        }
      });
    } else {
      $('.kr-balanceitem-cv').css('display', 'flex');
    }
  });

  tippy('[title]');

  $('.kr-history-view-search').off('keyup').keyup(function(){
    let valSearch = $(this).val();
    $.each($('.kr-balanceitem-cv'), function(){
      let refHistory = $(this).attr('kr-history-ref');
      if(refHistory.indexOf(valSearch) != -1){
        $(this).css('display', 'flex');
      } else {
        $(this).hide();
      }
    });
  });

  $('.kr-balances-view-search').off('keyup').keyup(function(){
    let valSearch = $(this).val();
    $.each($('.kr-balanceitem-cv'), function(){
      let symbolName = $(this).attr('kr-balance-item-value-currency');
      if(symbolName.indexOf(valSearch) != -1){
        if($('.kr-balances-view-tggsmall').hasClass('kr-balances-view-tggsmall-active')){
          $.each($('.kr-balanceitem-cv'), function(){
            if(parseFloat($(this).attr('kr-balance-item-value')) > 0.001){
              $(this).css('display', 'flex');
            } else {
              $(this).hide();
            }
          });
        } else {
          $(this).css('display', 'flex');
        }
      } else {
        $(this).hide();
      }
    });
  });
}
