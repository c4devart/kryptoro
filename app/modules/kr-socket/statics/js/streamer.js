/**
 * List lstCallbackStreamer
 * @type {Array}
 */
var lstCallbackStreamer = [];

/**
 * Current price list
 * @type {Object}
 */
var currentPrice = {};

/**
 * List current sub
 * @type {Array}
 */
var currentSub = [];

/**
 * Socket stream object
 * @type {Object}
 */
var socket = io.connect('https://streamer.cryptocompare.com/');

$(document).ready(function(){

	// Add socket event (on receive update)
	socket.on("m", function(message) {
		// Message type
		var messageType = message.substring(0, message.indexOf("~"));
		// Check message type is an price update
		if (messageType == CCC.STATIC.TYPE.CURRENTAGG || messageType == CCC.STATIC.TYPE.CURRENT) {
			parseMessage(CCC.CURRENT.unpack(message));
		}

		// Check message type is an trade update
		if (messageType == CCC.STATIC.TYPE.TRADE) {
			parseTrade(message);
		}

	});

});

/**
 * Parse message stream (update price coin)
 * @param  {Array} data  Update data
 */
function parseMessage(data){
	/**
	 * Get symbol from
	 * @type {String}
	 */
	var fsym = CCC.STATIC.CURRENCY.getSymbol(data['FROMSYMBOL']);

	/**
	 * Get symbol to
	 * @type {String}
	 */
	var tsym = CCC.STATIC.CURRENCY.getSymbol(data['TOSYMBOL']);

	/**
	 * Pair symbol
	 * @type {String}
	 */
	var pair = data['FROMSYMBOL'] + data['TOSYMBOL'];

	// Check if pair is not already in cache
	if (!currentPrice.hasOwnProperty(pair)) currentPrice[pair] = {};

	for (var key in data) {
		currentPrice[pair][key] = data[key];
	}

	if (currentPrice[pair]['LASTTRADEID']) currentPrice[pair]['LASTTRADEID'] = parseInt(currentPrice[pair]['LASTTRADEID']).toFixed(0);

	currentPrice[pair]['CHANGE24HOUR'] = CCC.convertValueToDisplay(tsym, (currentPrice[pair]['PRICE'] - currentPrice[pair]['OPEN24HOUR']));
	currentPrice[pair]['CHANGE24HOURPCT'] = ((currentPrice[pair]['PRICE'] - currentPrice[pair]['OPEN24HOUR']) / currentPrice[pair]['OPEN24HOUR'] * 100).toFixed(2);

	// Call streamer callback
	if(lstCallbackStreamer.hasOwnProperty(currentPrice[pair]['TYPE']) && lstCallbackStreamer[currentPrice[pair]['TYPE']].length > 0){
		for (callb of lstCallbackStreamer[currentPrice[pair]['TYPE']]){
			callb(currentPrice[pair]);
		}
	}


}

/**
 * Parse trade update
 * @param  {Array} data  Trade data
 */
function parseTrade(data){

	/**
	 * Get income trade unpacked
	 * @type {Object}
	 */
	var incomingTrade = CCC.TRADE.unpack(data);

	/**
	 * Get symbol from
	 * @type {String}
	 */
	var coinfsym = CCC.STATIC.CURRENCY.getSymbol(incomingTrade.FSYM);

	/**
	 * Get symbol to
	 * @type {String}
	 */
	var cointsym = CCC.STATIC.CURRENCY.getSymbol(incomingTrade.TSYM);

	/**
	 * New trade object
	 * @type {Object}
	 */
	var newTrade = {
		Market: incomingTrade['M'],
		Type: incomingTrade['T'],
		ID: incomingTrade['ID'],
		FromSymbol: incomingTrade.FSYM,
		ToCurrency: incomingTrade.TSYM,
		Price: CCC.convertValueToDisplay(cointsym, incomingTrade['P']),
		Quantity: CCC.convertValueToDisplay(coinfsym, incomingTrade['Q']),
		Total: incomingTrade['TOTAL']
	};

	// Parse trade type (sell, buy, unknow)
	if (incomingTrade['F'] & 1) {
		newTrade['Type'] = "SELL";
	}
	else if (incomingTrade['F'] & 2) {
		newTrade['Type'] = "BUY";
	}
	else {
		newTrade['Type'] = "UNKNOWN";
	}

	// Call callback streamer trade
	for (callb of lstCallbackStreamer['0']){
		callb(newTrade);
	}

}

/**
 * Add new streamer callback
 * @param  {Function} fnc      Callback function
 * @param  {Number} [type=5]   Type streamer (price update, trade)
 */
function subscribeStreamerCallback(fnc, type = 5){
	if (!lstCallbackStreamer.hasOwnProperty(type)) lstCallbackStreamer[type] = [];
	lstCallbackStreamer[type].push(fnc);
}

/**
 * Add new symbol subscription
 * @param {String} symbol   Symbol (ex : BTC)
 * @param {String} currency Currency (ex : USD)
 * @param {Number} [type=5] Type subscription
 */
function addSubscribtion(symbol, currency, type = 5, market = "CCCAGG"){
	if(market != "CCCAGG") market = market.charAt(0).toUpperCase() + market.slice(1).toLowerCase();
	if(jQuery.inArray(type + '~' + market + '~' + symbol + '~' + currency, currentSub) != -1) return false;

	if(type == "0"){
		// Get pair data
		$.get('https://min-api.cryptocompare.com/data/subs?fsym=' + symbol + '&tsyms=' + currency).done(function(data){
			if(!(currency in data) || !('TRADES' in data[currency])) return false;
			$.each(data[currency]['TRADES'], function(k, trade){
				currentSub.push(trade);
				socket.emit('SubAdd', { subs: [trade] });
			});
		});
	} else {
		currentSub.push(type + '~' + market + '~' + symbol + '~' + currency);
	  socket.emit('SubAdd', { subs: [type + '~' + market + '~' + symbol + '~' + currency] });
	}
}

/**
 * Delete subscription
 * @param  {String} symbol   Symbol (ex : BTC)
 * @param  {String} currency Currency (ex : USD)
 * @param  {Number} [type=5] Type subscription
 */
function deleteSubscription(symbol, currency, type = 5, market = 'CCCAGG'){
  socket.emit('SubRemove', { subs: [type + '~' + market + '~' + symbol + '~' + currency] });
	currentSub	= $.grep(currentSub, function(value) {
	  return value != type + '~' + market + '~' + symbol + '~' + currency;
	});
}
