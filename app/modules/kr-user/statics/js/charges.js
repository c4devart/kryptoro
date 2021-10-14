$(document).ready(function(){
  if ($('body').attr('activeabo') == 0) showChargePopup('plan', {});
});

/**
 * Show popup charge
 * @param  {String} tc        View charge
 * @param  {Object} [args={}] Args view
 */
function showChargePopup(tc, args = {}) {

  // Check popup need to be shown or change
  if ($('.kr-ov-charges').length == 0) {
    let popupCharge = $('<div class="kr-overley kr-ov-nblr kr-ov-charges"> <section> <div class="kr-overley-loading"> <div><div class="sk-folding-cube sk-folding-cube-orange"> <div class="sk-cube1 sk-cube"></div> <div class="sk-cube2 sk-cube"></div> <div class="sk-cube4 sk-cube"></div> <div class="sk-cube3 sk-cube"></div> </div></div> </div> </section> </div>');
    $('body').addClass('kr-nblr');
    $('body').prepend(popupCharge);
  } else { // Change view
    showChargeLoading();
  }

  // Get plan view data
  $.post($('body').attr('hrefapp') + '/app/modules/kr-user/views/' + tc + '.php', args).done(function(data) {

    // Check if data can be json parse (= error)
    try {
      let res = jQuery.parseJSON(data);
      hideChargePopup();
      showAlert('Ooops', res.msg, 'error');
    } catch (e) {
      // When view is loaded, charge controllers
      $.when($('.kr-ov-charges').html(data)).then(function() {
        initChargePopupController();
      });
    }
  }).fail(function() { // If fail to access to plan view
    hideChargePopup();
    showAlert('Ooops', 'Fail to load plan popup', 'error');
  });
}

/**
 * Hide charge view popup
 */
function hideChargePopup() {
  $('.kr-ov-charges').hide();
  $('body').removeClass('kr-nblr');
}

/**
 * Init charge view controllers
 */
function initChargePopupController() {

  // Init change plan controllers
  $('[kr-charges-sel]').click(function() {
    showChargePopup('plan_selected', {
      plan: $(this).attr('kr-charges-sel')
    });
  });

  // Init payment controllers
  $('[kr-charges-payment]').click(function() {
    showChargePopup($(this).attr('kr-charges-payment'), {
      plan: $(this).attr('kr-charges-selected')
    });
  });

  // When card number was changed
  $('[name="kr_charges_cardnumber"]').keyup(function() {
    changeCardnumber($(this));
  });

  // When CCV number was changed
  $('[name="kr_charges_ccv"]').keyup(function() {
    changeCCV($(this));
  });

  // When expiration data was changed
  $('[name="kr_charges_expirationmonth"], [name="kr_charges_expirationyear"]').change(function() {
    changeExpirationDate();
  });

  // When card holder name was changed
  $('[name="kr_charges_cardholdername"]').keyup(function() {
    changeCardHolder($(this));
  });

  // Close charge view
  $('.kr-ov-charges > section > header > div').click(function() {
    $('.kr-overley').remove();
    $('body').removeClass('kr-nblr');
  });

  // Back button charge view
  $('.kr-payment-back').click(function(){
    showChargePopup('plan', {});
  });

  // Credit card payment action
  $('.kr-charges-creditcard').submit(function(e) {

    // Hide element and load loading view
    $(this).hide();
    $(this).parent().prepend('<div class="kr-overley kr-ov-nblr kr-ov-charges"> <section> <div class="kr-overley-loading"> <div><div class="sk-folding-cube sk-folding-cube-orange"> <div class="sk-cube1 sk-cube"></div> <div class="sk-cube2 sk-cube"></div> <div class="sk-cube4 sk-cube"></div> <div class="sk-cube3 sk-cube"></div> </div></div> </div> </section> </div>')

    $(this).find('.kr-msg').html('').hide();

    // Make creditcard action
    $.post($(this).attr('action'), $(this).serialize()).done(function(data) {
      // Parse json result
      let result = jQuery.parseJSON(data);
      if (result.error == 0) { // Check error (error null)
        window.location.replace($('body').attr('hrefapp') + "/dashboard.php?c=" + result.type + '&t=' + result.time + '&k=' + result.key);
      } else if (result.error == 1) { // Error found
        $('.kr-charges-creditcard').parent().find('.kr-overley').remove();
        $('.kr-charges-creditcard').find('.kr-msg').html(result.msg).show();
        $('.kr-charges-creditcard').show();
      } else if (result.error == 2) { // Error found (fields missing or fail)
        $('.kr-charges-creditcard').parent().find('.kr-overley').remove();
        $.each(result.fields, function(k, v) {
          $('[name="' + v + '"]').addClass('kr-inp-notvalid');
        });
        $('.kr-charges-creditcard').show();

      } else {
        showAlert('Error', result.msg, 'error');
      }
    }).fail(function(){ // Fail to process card payment action
      showAlert('Ooops', 'Fail to charge credit card', 'error');
    });

    e.preventDefault();
    return false;
  });

  $('.fortumo-payment-action').off('click').click(function(e){
    let uid = $(this).attr('fortumodu'),
        hashedData = $(this).attr('fortumod');

    window.open($('body').attr('hrefapp') + "/app/modules/kr-payment/views/fortumo.php?g=" + hashedData + '-' + uid, "popupWindow", "width=600, height=700, scrollbars=yes");
    showChargeLoading();
    startWatchingPayment('Fortumo', hashedData + '-' + uid);
    e.preventDefault();
    return false;
  });

  $('.coingate-payment-action').off('click').click(function(e){
    let uid = $(this).attr('coingatedu'),
        hashedData = $(this).attr('coingaged');

    window.open($('body').attr('hrefapp') + "/app/modules/kr-payment/views/coingate.php?g=" + hashedData + '-' + uid, "popupWindow", "width=1041, height=669, scrollbars=yes");
    showChargeLoading();
    startWatchingPayment('Coingate', hashedData + '-' + uid);
    e.preventDefault();
    return false;
  });

}

/**
 * Show loading charge view
 */
function showChargeLoading(){
  $('.kr-ov-charges').html('<section> <div class="kr-overley-loading"> <div><div class="sk-folding-cube sk-folding-cube-orange"> <div class="sk-cube1 sk-cube"></div> <div class="sk-cube2 sk-cube"></div> <div class="sk-cube4 sk-cube"></div> <div class="sk-cube3 sk-cube"></div> </div></div> </div> </section>');
}

/**
 * Start waiting payment
 * @param  {String} type Payment type
 * @param  {String} cuid CUID
 */
function startWatchingPayment(type, cuid){
  $.get($('body').attr('hrefapp') + '/app/modules/kr-payment/src/actions/check' + type + '.php', {cuid:cuid}).done(function(data){
    let response = jQuery.parseJSON(data);
    if(response.error == 0){
      if(response.status == 0){
        setTimeout(function(){
          startWatchingPayment(type, cuid);
        }, 500);
      } else {
        showChargePopup('result_' + type.toLowerCase(), {cuid:cuid});
      }
    } else {
      showAlert('Oops', response.msg, 'error');
    }
  }).fail(function(){
    showAlert('Oops', 'Fail to check ' + type + ' payment', 'error');
  });
}
