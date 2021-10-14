function showAlert(title, text, type = "default", closable = true){

  let alert = $('<div class="kr-ov-nblr ' + (type == "error" ? 'kr-notif-alt-err' : '') + ' animated flipInX">' +
                  '<header><span>' + title + '</span><div><svg class="lnr lnr-cross"><use xlink:href="#lnr-cross"></use></svg></div></header>' +
                  '<div>' + text + '</div>' +
                '</div>');

  alert.find('svg').click(function(){ alert.remove(); });

  setTimeout(function(){
    alert.remove();
  }, 8000);

  $('.kr-notif-alt').append(alert);

}

function showCryptoAlert(symbol, amount, type = "buy", status = null, currency = null){
  let alert = $('<div class="kr-ov-nblr animated zoomInLeft kr-notif-alt-crypto kr-notif-alt-crypto-' + type + '">' +
        '<div>' +
          '<img src="' + $('body').attr('hrefapp') + '/assets/img/icons/crypto/' + symbol + '.svg" alt="">' +
        '</div>' +
        '<div>' +
          '<div class="kr-notif-alt-crypto-close"><svg class="lnr lnr-cross"><use xlink:href="#lnr-cross"></use></svg></div>' +
          '<div class="kr-notif-alt-crypto-desc">' +
            '<label>' +  symbol + (currency != null ? '/' + currency : '') + '</label>' +
            (type == "buy" ? '<span>&#x25B4; ' + KRformatNumber(amount, 8) + ' ' + symbol + '</span>' : '') +
            (type == "received" ? '<span>' + KRformatNumber(amount, 8) + ' ' + symbol + '</span>' : '') +
            (status != null ? '<i>' + status + '</i>' : '') +
          '</div>' +
        '</div>');

  alert.find('.kr-notif-alt-crypto-close').click(function(){ alert.remove(); });

  setTimeout(function(){
    alert.remove();
  }, 8000);

  $('.kr-notif-alt').append(alert);
}

function _setCookie(c_name,value,expiredays){
  var exdate=new Date()
  exdate.setDate(exdate.getDate()+expiredays)
  document.cookie=c_name+ "=" +escape(value)+";path=/"+((expiredays==null) ? "" : ";expires="+exdate.toGMTString())
}

$(document).ready(function(){
  if( document.cookie.indexOf("kr_cookie_accepted") === -1){
  $(".kr-cookie-approval").css('display', 'flex');
  }
  $('.kr-cookie-accept').off('click').click(function(){
    _setCookie('kr_cookie_accepted','1',365*10);
    $('.kr-cookie-approval').addClass('animated').addClass('bounceOutLeft');
  });
});

function _showContactPopup(){
  $('body').addClass('kr-nblr');
  $.get($('body').attr('hrefapp') + '/app/views/contact/contact.php').done(function(data){
    $('body').prepend(data);
  }).fail(function(){
    showAlert('Oops', 'Fail to load contact popup', 'error');
  })
}

function _closeContactPopup(){
  $('.kr-contact-zone').remove();
  $('body').removeClass('kr-nblr');
}
