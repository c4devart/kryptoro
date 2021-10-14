/**
 * Change credit card type
 * @param  {Object} container Input card number
 */
function showCardType(container) {
  // Get credit card type
  let type = Stripe.card.cardType(container.val()).toLowerCase().replace(' ', '');
  if (type == "unknown") container.css('background-image', '');
  else container.css('background-image', 'url("' + $('body').attr('hrefapp') + '/assets/img/icons/payment/cc_' + type + '.svg")');
}

/**
 * On change credit card number
 * @param  {Object} field Input card number
 */
function changeCardnumber(field) {

  // Load card type
  showCardType(field);

  // Check card number
  if (Stripe.card.validateCardNumber(field.val())) {
    field.removeClass('kr-inp-notvalid').addClass('kr-inp-valid'); // Card number is correct
  } else {
    field.removeClass('kr-inp-valid').addClass('kr-inp-notvalid'); // Card number is not correct
  }
}

/**
 * On change expiration date credit card
 */
function changeExpirationDate() {
  // Check validity expiration
  if (Stripe.card.validateExpiry($('[name="kr_charges_expirationmonth"]').val(), $('[name="kr_charges_expirationyear"]').val())) { // If valid expiration data
    $('[name="kr_charges_expirationmonth"]').removeClass('kr-inp-notvalid').addClass('kr-inp-valid');
    $('[name="kr_charges_expirationyear"]').removeClass('kr-inp-notvalid').addClass('kr-inp-valid');
  } else { // Not valid expiration date
    $('[name="kr_charges_expirationmonth"]').removeClass('kr-inp-valid').addClass('kr-inp-notvalid');
    $('[name="kr_charges_expirationyear"]').removeClass('kr-inp-valid').addClass('kr-inp-notvalid');
  }
}

/**
 * On change CCV number
 * @param  {Object} field CCV Field
 */
function changeCCV(field) {
  // Check CCV validate
  if (Stripe.card.validateCVC(field.val())) field.removeClass('kr-inp-notvalid').addClass('kr-inp-valid');
  else field.removeClass('kr-inp-valid').addClass('kr-inp-notvalid');
}

// On change card holder name
function changeCardHolder(field) {
  // Check card holder length
  if (field.val().length > 0) field.removeClass('kr-inp-notvalid').addClass('kr-inp-valid');
  else field.removeClass('kr-inp-valid').addClass('kr-inp-notvalid');
}
