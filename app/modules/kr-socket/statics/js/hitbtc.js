/**
 * Socket HITBTC stream object
 * @type {Object}
 */
var socketHITBTC = new WebSocket('wss://api.hitbtc.com/api/2/ws');

let socketeHITBTCCallback = {
  'updateOrderbook': []
};

$(document).ready(function(){

  socketHITBTC.onopen = function(event) {

    socketHITBTC.onmessage = function(event) {
      let response = jQuery.parseJSON(event.data);
      if(socketeHITBTCCallback.hasOwnProperty(response.method + '')){
        for (callb of socketeHITBTCCallback[response.method]){
      		callb(response);
      	}
      }
  	};

  };

});

function sendSocketHITBTC(msg){
  socketHITBTC.send(msg);
}

function subscribeHITBTCOrderBook(symbol, callback = null){

  if(callback != null){
    socketeHITBTCCallback['updateOrderbook'].push(callback);
  }

  sendSocketHITBTC('{ "method": "subscribeOrderbook", "params": { "symbol": "' + symbol + '" }, "id": 123 }');


}
