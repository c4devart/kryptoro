$(document).ready(function(){
  _loadUserAddrBL();
});

function _loadUserAddrBL(){
  $.get($('body').attr('hrefapp') + '/app/modules/kr-trade/src/actions/getBlockonomicsAddrUser.php').done(function(data){
    let jsonRes = jQuery.parseJSON(data);
    if(jsonRes.error == 1){
      showAlert('Oops', jsonRes.msg, 'error');
    } else {
      if(jsonRes.error == 2){

      } else {
        _initBlocknomicsPayment(jsonRes.address);
      }
    }
  }).fail(function(){
    showAlert('Oops', 'Fail to fetch user BTC address', 'error');
  });
}

function _convertSatoshiToStandard(satoshi){
  return satoshi / 100000000;
}

function _convertStatusToStr(status){
  if(status == 0) return 'Unconfirmed';
  if(status == 1) return 'Partially Confirmed';
  if(status == 2) return 'Confirmed';
}

let blockonomicsCloseOM = false;

function _initBlocknomicsPayment(addr){
  var blockonomicsWebsocket = new WebSocket("wss://www.blockonomics.co/payment/" + addr);

  blockonomicsWebsocket.onmessage = function (event) {
    let infosMsg = jQuery.parseJSON(event.data);
    if(blockonomicsCloseOM) window.close();
    showCryptoAlert('BTC', _convertSatoshiToStandard(infosMsg.value), (infosMsg.status == 2 ? 'buy' : 'received'), _convertStatusToStr(infosMsg.status));
  }
}
