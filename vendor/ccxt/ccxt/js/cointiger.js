'use strict';

// ---------------------------------------------------------------------------

const huobipro = require ('./huobipro.js');
const { ExchangeError, ExchangeNotAvailable, AuthenticationError, InvalidOrder, InsufficientFunds, OrderNotFound } = require ('./base/errors');
const { ROUND, TRUNCATE } = require ('./base/functions/number');

// ---------------------------------------------------------------------------

module.exports = class cointiger extends huobipro {
    describe () {
        return this.deepExtend (super.describe (), {
            'id': 'cointiger',
            'name': 'CoinTiger',
            'countries': [ 'CN' ],
            'hostname': 'api.cointiger.pro',
            'has': {
                'fetchCurrencies': false,
                'fetchTickers': true,
                'fetchTradingLimits': false,
                'fetchOrder': true,
                'fetchOrders': false,
                'fetchOpenOrders': true,
                'fetchClosedOrders': true,
                'fetchOrderTrades': false, // not tested yet
                'cancelOrders': true,
            },
            'headers': {
                'Language': 'en_US',
            },
            'urls': {
                'logo': 'https://user-images.githubusercontent.com/1294454/39797261-d58df196-5363-11e8-9880-2ec78ec5bd25.jpg',
                'api': {
                    'public': 'https://api.cointiger.pro/exchange/trading/api/market',
                    'private': 'https://api.cointiger.pro/exchange/trading/api',
                    'exchange': 'https://www.cointiger.pro/exchange',
                    'v2public': 'https://api.cointiger.pro/exchange/trading/api/v2',
                    'v2': 'https://api.cointiger.pro/exchange/trading/api/v2',
                },
                'www': 'https://www.cointiger.pro',
                'referral': 'https://www.cointiger.pro/exchange/register.html?refCode=FfvDtt',
                'doc': 'https://github.com/cointiger/api-docs-en/wiki',
            },
            'api': {
                'v2public': {
                    'get': [
                        'timestamp',
                        'currencys',
                    ],
                },
                'v2': {
                    'get': [
                        'order/orders',
                        'order/match_results',
                        'order/make_detail',
                        'order/details',
                    ],
                    'post': [
                        'order',
                        'order/batch_cancel',
                    ],
                },
                'public': {
                    'get': [
                        'history/kline', // ??????K?????????
                        'detail/merged', // ??????????????????(Ticker)
                        'depth', // ?????? Market Depth ??????
                        'trade', // ?????? Trade Detail ??????
                        'history/trade', // ?????????????????????????????????
                        'detail', // ?????? Market Detail 24?????????????????????
                    ],
                },
                'exchange': {
                    'get': [
                        'footer/tradingrule.html',
                        'api/public/market/detail',
                    ],
                },
                'private': {
                    'get': [
                        'user/balance',
                        'order/new',
                        'order/history',
                        'order/trade',
                    ],
                    'post': [
                        'order',
                    ],
                    'delete': [
                        'order',
                    ],
                },
            },
            'fees': {
                'trading': {
                    'tierBased': false,
                    'percentage': true,
                    'maker': 0.001,
                    'taker': 0.001,
                },
            },
            'exceptions': {
                //    {"code":"1","msg":"????????????","data":null}
                //    {???code???:???1",???msg???:???Balance insufficient,???????????????,???data???:null}
                '1': ExchangeError,
                '2': ExchangeError,
                '5': InvalidOrder,
                '6': InvalidOrder,
                '8': OrderNotFound,
                '16': AuthenticationError, // funding password not set
                '100001': ExchangeError,
                '100002': ExchangeNotAvailable,
                '100003': ExchangeError,
                '100005': AuthenticationError,
            },
        });
    }

    async fetchMarkets () {
        const response = await this.v2publicGetCurrencys ();
        //
        //     {
        //         code: '0',
        //         msg: 'suc',
        //         data: {
        //             'bitcny-partition': [
        //                 {
        //                     baseCurrency: 'btc',
        //                     quoteCurrency: 'bitcny',
        //                     pricePrecision: 2,
        //                     amountPrecision: 4,
        //                     withdrawFeeMin: 0.0005,
        //                     withdrawFeeMax: 0.005,
        //                     withdrawOneMin: 0.01,
        //                     withdrawOneMax: 10,
        //                     depthSelect: { step0: '0.01', step1: '0.1', step2: '1' }
        //                 },
        //                 ...
        //             ],
        //             ...
        //         },
        //     }
        //
        const keys = Object.keys (response['data']);
        const result = [];
        for (let i = 0; i < keys.length; i++) {
            let key = keys[i];
            let partition = response['data'][key];
            for (let j = 0; j < partition.length; j++) {
                let market = partition[j];
                let baseId = this.safeString (market, 'baseCurrency');
                let quoteId = this.safeString (market, 'quoteCurrency');
                let base = baseId.toUpperCase ();
                let quote = quoteId.toUpperCase ();
                base = this.commonCurrencyCode (base);
                quote = this.commonCurrencyCode (quote);
                let id = baseId + quoteId;
                let uppercaseId = id.toUpperCase ();
                let symbol = base + '/' + quote;
                let precision = {
                    'amount': market['amountPrecision'],
                    'price': market['pricePrecision'],
                };
                let active = true;
                let entry = {
                    'id': id,
                    'uppercaseId': uppercaseId,
                    'symbol': symbol,
                    'base': base,
                    'quote': quote,
                    'baseId': baseId,
                    'quoteId': quoteId,
                    'info': market,
                    'active': active,
                    'precision': precision,
                    'limits': {
                        'amount': {
                            'min': Math.pow (10, -precision['amount']),
                            'max': undefined,
                        },
                        'price': {
                            'min': Math.pow (10, -precision['price']),
                            'max': undefined,
                        },
                        'cost': {
                            'min': 0,
                            'max': undefined,
                        },
                    },
                };
                result.push (entry);
            }
        }
        this.options['marketsByUppercaseId'] = this.indexBy (result, 'uppercaseId');
        return result;
    }

    parseTicker (ticker, market = undefined) {
        let symbol = undefined;
        if (market)
            symbol = market['symbol'];
        let timestamp = this.safeInteger (ticker, 'id');
        let close = this.safeFloat (ticker, 'last');
        let percentage = this.safeFloat (ticker, 'percentChange');
        return {
            'symbol': symbol,
            'timestamp': timestamp,
            'datetime': this.iso8601 (timestamp),
            'high': this.safeFloat (ticker, 'high24hr'),
            'low': this.safeFloat (ticker, 'low24hr'),
            'bid': this.safeFloat (ticker, 'highestBid'),
            'bidVolume': undefined,
            'ask': this.safeFloat (ticker, 'lowestAsk'),
            'askVolume': undefined,
            'vwap': undefined,
            'open': undefined,
            'close': close,
            'last': close,
            'previousClose': undefined,
            'change': undefined,
            'percentage': percentage,
            'average': undefined,
            'baseVolume': this.safeFloat (ticker, 'baseVolume'),
            'quoteVolume': this.safeFloat (ticker, 'quoteVolume'),
            'info': ticker,
        };
    }

    async fetchOrderBook (symbol, limit = undefined, params = {}) {
        await this.loadMarkets ();
        let market = this.market (symbol);
        let response = await this.publicGetDepth (this.extend ({
            'symbol': market['id'], // this endpoint requires a lowercase market id
            'type': 'step0',
        }, params));
        let data = response['data']['depth_data'];
        if ('tick' in data) {
            if (!data['tick']) {
                throw new ExchangeError (this.id + ' fetchOrderBook() returned empty response: ' + this.json (response));
            }
            let orderbook = data['tick'];
            let timestamp = data['ts'];
            return this.parseOrderBook (orderbook, timestamp, 'buys');
        }
        throw new ExchangeError (this.id + ' fetchOrderBook() returned unrecognized response: ' + this.json (response));
    }

    async fetchTicker (symbol, params = {}) {
        await this.loadMarkets ();
        let market = this.market (symbol);
        let marketId = market['uppercaseId'];
        let response = await this.exchangeGetApiPublicMarketDetail (params);
        if (!(marketId in response))
            throw new ExchangeError (this.id + ' fetchTicker symbol ' + symbol + ' (' + marketId + ') not found');
        return this.parseTicker (response[marketId], market);
    }

    async fetchTickers (symbols = undefined, params = {}) {
        await this.loadMarkets ();
        let response = await this.exchangeGetApiPublicMarketDetail (params);
        let result = {};
        let ids = Object.keys (response);
        for (let i = 0; i < ids.length; i++) {
            let id = ids[i];
            let market = undefined;
            let symbol = id;
            if (id in this.options['marketsByUppercaseId']) {
                // this endpoint returns uppercase ids
                symbol = this.options['marketsByUppercaseId'][id]['symbol'];
                market = this.options['marketsByUppercaseId'][id];
            }
            result[symbol] = this.parseTicker (response[id], market);
        }
        return result;
    }

    parseTrade (trade, market = undefined) {
        //
        //   {      volume: "0.014",
        //          symbol: "ethbtc",
        //         buy_fee: "0.00001400",
        //         orderId:  32235710,
        //           price: "0.06923825",
        //         created:  1531605169000,
        //              id:  3785005,
        //          source:  1,
        //            type: "buy-limit",
        //     bid_user_id:  326317         } ] }
        //
        // --------------------------------------------------------------------
        //
        //     {
        //         "volume": {
        //             "amount": "1.000",
        //             "icon": "",
        //             "title": "?????????"
        //                   },
        //         "price": {
        //             "amount": "0.04978883",
        //             "icon": "",
        //             "title": "????????????"
        //                  },
        //         "created_at": 1513245134000,
        //         "deal_price": {
        //             "amount": 0.04978883000000000000000000000000,
        //             "icon": "",
        //             "title": "????????????"
        //                       },
        //         "id": 138
        //     }
        //
        let id = this.safeString (trade, 'id');
        let orderId = this.safeString (trade, 'orderId');
        let orderType = this.safeString (trade, 'type');
        let type = undefined;
        let side = undefined;
        if (typeof orderType !== 'undefined') {
            let parts = orderType.split ('-');
            side = parts[0];
            type = parts[1];
        }
        side = this.safeString (trade, 'side', side);
        let amount = undefined;
        let price = undefined;
        let cost = undefined;
        if (typeof side === 'undefined') {
            price = this.safeFloat (trade['price'], 'amount');
            amount = this.safeFloat (trade['volume'], 'amount');
            cost = this.safeFloat (trade['deal_price'], 'amount');
        } else {
            side = side.toLowerCase ();
            price = this.safeFloat (trade, 'price');
            amount = this.safeFloat2 (trade, 'amount', 'volume');
        }
        let fee = undefined;
        if (typeof side !== 'undefined') {
            let feeCostField = side + '_fee';
            let feeCost = this.safeFloat (trade, feeCostField);
            if (typeof feeCost !== 'undefined') {
                let feeCurrency = undefined;
                if (typeof market !== 'undefined') {
                    feeCurrency = market['base'];
                }
                fee = {
                    'cost': feeCost,
                    'currency': feeCurrency,
                };
            }
        }
        if (typeof amount !== 'undefined')
            if (typeof price !== 'undefined')
                if (typeof cost === 'undefined')
                    cost = amount * price;
        let timestamp = this.safeInteger2 (trade, 'created_at', 'ts');
        timestamp = this.safeInteger (trade, 'created', timestamp);
        let symbol = undefined;
        if (typeof market !== 'undefined')
            symbol = market['symbol'];
        return {
            'info': trade,
            'id': id,
            'order': orderId,
            'timestamp': timestamp,
            'datetime': this.iso8601 (timestamp),
            'symbol': symbol,
            'type': type,
            'side': side,
            'price': price,
            'amount': amount,
            'cost': cost,
            'fee': fee,
        };
    }

    async fetchTrades (symbol, since = undefined, limit = 1000, params = {}) {
        await this.loadMarkets ();
        let market = this.market (symbol);
        let request = {
            'symbol': market['id'],
        };
        if (typeof limit !== 'undefined')
            request['size'] = limit;
        let response = await this.publicGetHistoryTrade (this.extend (request, params));
        return this.parseTrades (response['data']['trade_data'], market, since, limit);
    }

    async fetchMyTrades (symbol = undefined, since = undefined, limit = undefined, params = {}) {
        if (typeof symbol === 'undefined')
            throw new ExchangeError (this.id + ' fetchOrders requires a symbol argument');
        await this.loadMarkets ();
        let market = this.market (symbol);
        if (typeof limit === 'undefined')
            limit = 100;
        let response = await this.privateGetOrderTrade (this.extend ({
            'symbol': market['id'],
            'offset': 1,
            'limit': limit,
        }, params));
        return this.parseTrades (response['data']['list'], market, since, limit);
    }

    async fetchOHLCV (symbol, timeframe = '1m', since = undefined, limit = 1000, params = {}) {
        await this.loadMarkets ();
        let market = this.market (symbol);
        let request = {
            'symbol': market['id'],
            'period': this.timeframes[timeframe],
        };
        if (typeof limit !== 'undefined') {
            request['size'] = limit;
        }
        let response = await this.publicGetHistoryKline (this.extend (request, params));
        return this.parseOHLCVs (response['data']['kline_data'], market, timeframe, since, limit);
    }

    async fetchBalance (params = {}) {
        await this.loadMarkets ();
        let response = await this.privateGetUserBalance (params);
        //
        //     {
        //         "code": "0",
        //         "msg": "suc",
        //         "data": [{
        //             "normal": "1813.01144179",
        //             "lock": "1325.42036785",
        //             "coin": "btc"
        //         }, {
        //             "normal": "9551.96692244",
        //             "lock": "547.06506717",
        //             "coin": "eth"
        //         }]
        //     }
        //
        let balances = response['data'];
        let result = { 'info': response };
        for (let i = 0; i < balances.length; i++) {
            let balance = balances[i];
            let id = balance['coin'];
            let code = id.toUpperCase ();
            code = this.commonCurrencyCode (code);
            if (id in this.currencies_by_id) {
                code = this.currencies_by_id[id]['code'];
            }
            let account = this.account ();
            account['used'] = parseFloat (balance['lock']);
            account['free'] = parseFloat (balance['normal']);
            account['total'] = this.sum (account['used'], account['free']);
            result[code] = account;
        }
        return this.parseBalance (result);
    }

    async fetchOrderTrades (id, symbol = undefined, since = undefined, limit = undefined, params = {}) {
        if (typeof symbol === 'undefined') {
            throw new ExchangeError (this.id + ' fetchOrderTrades requires a symbol argument');
        }
        await this.loadMarkets ();
        let market = this.market (symbol);
        let request = {
            'symbol': market['id'],
            'order_id': id,
        };
        let response = await this.v2GetOrderMakeDetail (this.extend (request, params));
        //
        // the above endpoint often returns an empty array
        //
        //     { code:   "0",
        //        msg:   "suc",
        //       data: [ {      volume: "0.014",
        //                      symbol: "ethbtc",
        //                     buy_fee: "0.00001400",
        //                     orderId:  32235710,
        //                       price: "0.06923825",
        //                     created:  1531605169000,
        //                          id:  3785005,
        //                      source:  1,
        //                        type: "buy-limit",
        //                 bid_user_id:  326317         } ] }
        //
        return this.parseTrades (response['data'], market, since, limit);
    }

    async fetchOrdersByStatus (status = undefined, symbol = undefined, since = undefined, limit = undefined, params = {}) {
        if (typeof symbol === 'undefined')
            throw new ExchangeError (this.id + ' fetchOrders requires a symbol argument');
        await this.loadMarkets ();
        let market = this.market (symbol);
        if (typeof limit === 'undefined')
            limit = 100;
        let method = (status === 'open') ? 'privateGetOrderNew' : 'privateGetOrderHistory';
        let response = await this[method] (this.extend ({
            'symbol': market['id'],
            'offset': 1,
            'limit': limit,
        }, params));
        let orders = response['data']['list'];
        let result = [];
        for (let i = 0; i < orders.length; i++) {
            let order = this.extend (orders[i], {
                'status': status,
            });
            result.push (this.parseOrder (order, market, since, limit));
        }
        return result;
    }

    async fetchOpenOrders (symbol = undefined, since = undefined, limit = undefined, params = {}) {
        return this.fetchOrdersByStatus ('open', symbol, since, limit, params);
    }

    async fetchClosedOrders (symbol = undefined, since = undefined, limit = undefined, params = {}) {
        return this.fetchOrdersByStatus ('closed', symbol, since, limit, params);
    }

    async fetchOrder (id, symbol = undefined, params = {}) {
        //
        //     { code:   "0",
        //        msg:   "suc",
        //       data: {      symbol: "ethbtc",
        //                       fee: "0.00000200",
        //                 avg_price: "0.06863752",
        //                    source:  1,
        //                      type: "buy-limit",
        //                     mtime:  1531340305000,
        //                    volume: "0.002",
        //                   user_id:  326317,
        //                     price: "0.06863752",
        //                     ctime:  1531340304000,
        //               deal_volume: "0.00200000",
        //                        id:  31920243,
        //                deal_money: "0.00013727",
        //                    status:  2              } }
        //
        if (typeof symbol === 'undefined') {
            throw new ExchangeError (this.id + ' fetchOrder requires a symbol argument');
        }
        await this.loadMarkets ();
        let market = this.market (symbol);
        let request = {
            'symbol': market['id'],
            'order_id': id.toString (),
        };
        let response = await this.v2GetOrderDetails (this.extend (request, params));
        return this.parseOrder (response['data'], market);
    }

    parseOrderStatus (status) {
        let statuses = {
            '0': 'open', // pending
            '1': 'open',
            '2': 'closed',
            '3': 'open',
            '4': 'canceled',
            '6': 'error',
        };
        if (status in statuses)
            return statuses[status];
        return status;
    }

    parseOrder (order, market = undefined) {
        //
        //  v1
        //
        //      {
        //            volume: { "amount": "0.054", "icon": "", "title": "volume" },
        //         age_price: { "amount": "0.08377697", "icon": "", "title": "Avg price" },
        //              side: "BUY",
        //             price: { "amount": "0.00000000", "icon": "", "title": "price" },
        //        created_at: 1525569480000,
        //       deal_volume: { "amount": "0.64593598", "icon": "", "title": "Deal volume" },
        //   "remain_volume": { "amount": "1.00000000", "icon": "", "title": "????????????"
        //                id: 26834207,
        //             label: { go: "trade", title: "Traded", click: 1 },
        //          side_msg: "Buy"
        //      },
        //
        //  v2
        //
        //     { code:   "0",
        //        msg:   "suc",
        //       data: {      symbol: "ethbtc",
        //                       fee: "0.00000200",
        //                 avg_price: "0.06863752",
        //                    source:  1,
        //                      type: "buy-limit",
        //                     mtime:  1531340305000,
        //                    volume: "0.002",
        //                   user_id:  326317,
        //                     price: "0.06863752",
        //                     ctime:  1531340304000,
        //               deal_volume: "0.00200000",
        //                        id:  31920243,
        //                deal_money: "0.00013727",
        //                    status:  2              } }
        //
        let id = this.safeString (order, 'id');
        let side = this.safeString (order, 'side');
        let type = undefined;
        let orderType = this.safeString (order, 'type');
        let status = this.safeString (order, 'status');
        let timestamp = this.safeInteger (order, 'created_at');
        timestamp = this.safeInteger (order, 'ctime', timestamp);
        let lastTradeTimestamp = this.safeInteger2 (order, 'mtime', 'finished-at');
        let symbol = undefined;
        if (typeof market === 'undefined') {
            let marketId = this.safeString (order, 'symbol');
            if (marketId in this.markets_by_id) {
                market = this.markets_by_id[marketId];
            }
        }
        if (typeof market !== 'undefined') {
            symbol = market['symbol'];
        }
        let remaining = undefined;
        let amount = undefined;
        let filled = undefined;
        let price = undefined;
        let cost = undefined;
        let fee = undefined;
        let average = undefined;
        if (typeof side !== 'undefined') {
            side = side.toLowerCase ();
            amount = this.safeFloat (order['volume'], 'amount');
            remaining = ('remain_volume' in order) ? this.safeFloat (order['remain_volume'], 'amount') : undefined;
            filled = ('deal_volume' in order) ? this.safeFloat (order['deal_volume'], 'amount') : undefined;
            price = ('price' in order) ? this.safeFloat (order['price'], 'amount') : undefined;
            average = ('age_price' in order) ? this.safeFloat (order['age_price'], 'amount') : undefined;
        } else {
            if (typeof orderType !== 'undefined') {
                let parts = orderType.split ('-');
                side = parts[0];
                type = parts[1];
                cost = this.safeFloat (order, 'deal_money');
                price = this.safeFloat (order, 'price');
                average = this.safeFloat (order, 'avg_price');
                amount = this.safeFloat2 (order, 'amount', 'volume');
                filled = this.safeFloat (order, 'deal_volume');
                let feeCost = this.safeFloat (order, 'fee');
                if (typeof feeCost !== 'undefined') {
                    let feeCurrency = undefined;
                    if (typeof market !== 'undefined') {
                        if (side === 'buy') {
                            feeCurrency = market['base'];
                        } else if (side === 'sell') {
                            feeCurrency = market['quote'];
                        }
                    }
                    fee = {
                        'cost': feeCost,
                        'currency': feeCurrency,
                    };
                }
            }
            status = this.parseOrderStatus (status);
        }
        if (typeof amount !== 'undefined') {
            if (typeof remaining !== 'undefined') {
                if (typeof filled === 'undefined')
                    filled = Math.max (0, amount - remaining);
            } else if (typeof filled !== 'undefined') {
                cost = filled * price;
                if (typeof remaining === 'undefined')
                    remaining = Math.max (0, amount - filled);
            }
        }
        if (typeof status === 'undefined') {
            if ((typeof remaining !== 'undefined') && (remaining > 0))
                status = 'open';
        }
        let result = {
            'info': order,
            'id': id,
            'timestamp': timestamp,
            'datetime': this.iso8601 (timestamp),
            'lastTradeTimestamp': lastTradeTimestamp,
            'symbol': symbol,
            'type': type,
            'side': side,
            'price': price,
            'average': average,
            'cost': cost,
            'amount': amount,
            'filled': filled,
            'remaining': remaining,
            'status': status,
            'fee': fee,
            'trades': undefined,
        };
        return result;
    }

    costToPrecision (symbol, cost) {
        return this.decimalToPrecision (cost, ROUND, this.markets[symbol]['precision']['price']);
    }

    priceToPrecision (symbol, price) {
        return this.decimalToPrecision (price, ROUND, this.markets[symbol]['precision']['price']);
    }

    amountToPrecision (symbol, amount) {
        return this.decimalToPrecision (amount, TRUNCATE, this.markets[symbol]['precision']['amount']);
    }

    feeToPrecision (currency, fee) {
        return this.decimalToPrecision (fee, ROUND, this.currencies[currency]['precision']);
    }

    async createOrder (symbol, type, side, amount, price = undefined, params = {}) {
        await this.loadMarkets ();
        if (!this.password)
            throw new AuthenticationError (this.id + ' createOrder requires exchange.password to be set to user trading password (not login password!)');
        this.checkRequiredCredentials ();
        let market = this.market (symbol);
        let orderType = (type === 'limit') ? 1 : 2;
        let order = {
            'symbol': market['id'],
            'side': side.toUpperCase (),
            'type': orderType,
            'volume': this.amountToPrecision (symbol, amount),
            'capital_password': this.password,
        };
        if ((type === 'market') && (side === 'buy')) {
            if (typeof price === 'undefined') {
                throw new InvalidOrder (this.id + ' createOrder requires price argument for market buy orders to calculate total cost according to exchange rules');
            }
            order['volume'] = this.amountToPrecision (symbol, amount * price);
        }
        if (type === 'limit') {
            order['price'] = this.priceToPrecision (symbol, price);
        } else {
            if (typeof price === 'undefined') {
                order['price'] = this.priceToPrecision (symbol, 0);
            } else {
                order['price'] = this.priceToPrecision (symbol, price);
            }
        }
        let response = await this.privatePostOrder (this.extend (order, params));
        //
        //     { "order_id":34343 }
        //
        let timestamp = this.milliseconds ();
        return {
            'info': response,
            'id': response['data']['order_id'].toString (),
            'timestamp': timestamp,
            'datetime': this.iso8601 (timestamp),
            'lastTradeTimestamp': undefined,
            'status': undefined,
            'symbol': symbol,
            'type': type,
            'side': side,
            'price': price,
            'amount': amount,
            'filled': undefined,
            'remaining': undefined,
            'cost': undefined,
            'trades': undefined,
            'fee': undefined,
        };
    }

    async cancelOrder (id, symbol = undefined, params = {}) {
        await this.loadMarkets ();
        if (typeof symbol === 'undefined')
            throw new ExchangeError (this.id + ' cancelOrder requires a symbol argument');
        let market = this.market (symbol);
        let response = await this.privateDeleteOrder (this.extend ({
            'symbol': market['id'],
            'order_id': id,
        }, params));
        return {
            'id': id,
            'symbol': symbol,
            'info': response,
        };
    }

    async cancelOrders (ids, symbol = undefined, params = {}) {
        await this.loadMarkets ();
        if (typeof symbol === 'undefined')
            throw new ExchangeError (this.id + ' cancelOrders requires a symbol argument');
        let market = this.market (symbol);
        let marketId = market['id'];
        let orderIdList = {};
        orderIdList[marketId] = ids;
        let request = {
            'orderIdList': this.json (orderIdList),
        };
        let response = await this.v2PostOrderBatchCancel (this.extend (request, params));
        return {
            'info': response,
        };
    }

    sign (path, api = 'public', method = 'GET', params = {}, headers = undefined, body = undefined) {
        this.checkRequiredCredentials ();
        let url = this.urls['api'][api] + '/' + this.implodeParams (path, params);
        if (api === 'private' || api === 'v2') {
            let timestamp = this.milliseconds ().toString ();
            let query = this.keysort (this.extend ({
                'time': timestamp,
            }, params));
            let keys = Object.keys (query);
            let auth = '';
            for (let i = 0; i < keys.length; i++) {
                auth += keys[i] + query[keys[i]].toString ();
            }
            auth += this.secret;
            let signature = this.hmac (this.encode (auth), this.encode (this.secret), 'sha512');
            let urlParams = (method === 'POST') ? {} : query;
            url += '?' + this.urlencode (this.keysort (this.extend ({
                'api_key': this.apiKey,
                'time': timestamp,
            }, urlParams)));
            url += '&sign=' + signature;
            if (method === 'POST') {
                body = this.urlencode (query);
                headers = {
                    'Content-Type': 'application/x-www-form-urlencoded',
                };
            }
        } else if (api === 'public' || api === 'v2public') {
            url += '?' + this.urlencode (this.extend ({
                'api_key': this.apiKey,
            }, params));
        } else {
            if (Object.keys (params).length)
                url += '?' + this.urlencode (params);
        }
        return { 'url': url, 'method': method, 'body': body, 'headers': headers };
    }

    handleErrors (httpCode, reason, url, method, headers, body) {
        if (typeof body !== 'string')
            return; // fallback to default error handler
        if (body.length < 2)
            return; // fallback to default error handler
        if ((body[0] === '{') || (body[0] === '[')) {
            let response = JSON.parse (body);
            if ('code' in response) {
                //
                //     { "code": "100005", "msg": "request sign illegal", "data": null }
                //
                let code = this.safeString (response, 'code');
                if (typeof code !== 'undefined') {
                    const message = this.safeString (response, 'msg');
                    const feedback = this.id + ' ' + this.json (response);
                    if (code !== '0') {
                        const exceptions = this.exceptions;
                        if (code in exceptions) {
                            if (code === '1') {
                                //
                                //    {"code":"1","msg":"????????????","data":null}
                                //    {???code???:???1",???msg???:???Balance insufficient,???????????????,???data???:null}
                                //
                                if (message.indexOf ('Balance insufficient') >= 0) {
                                    throw new InsufficientFunds (feedback);
                                }
                            } else if (code === '2') {
                                if (message === 'offsetNot Null') {
                                    throw new ExchangeError (feedback);
                                } else if (message === 'Parameter error') {
                                    throw new ExchangeError (feedback);
                                }
                            }
                            throw new exceptions[code] (feedback);
                        } else {
                            throw new ExchangeError (this.id + ' unknown "error" value: ' + this.json (response));
                        }
                    } else {
                        //
                        // Google Translate:
                        // ????????????????????????,?????????????????? = Order status cannot be canceled
                        // ????????????????????????????????????,?????????????????? = The order was not queried according to the order number
                        //
                        // {"code":"0","msg":"suc","data":{"success":[],"failed":[{"err-msg":"????????????????????????,??????????????????","order-id":32857051,"err-code":"8"}]}}
                        // {"code":"0","msg":"suc","data":{"success":[],"failed":[{"err-msg":"Parameter error","order-id":32857050,"err-code":"2"},{"err-msg":"????????????????????????,??????????????????","order-id":32857050,"err-code":"8"}]}}
                        // {"code":"0","msg":"suc","data":{"success":[],"failed":[{"err-msg":"Parameter error","order-id":98549677,"err-code":"2"},{"err-msg":"????????????????????????????????????,??????????????????","order-id":98549677,"err-code":"8"}]}}
                        //
                        if (feedback.indexOf ('????????????????????????,??????????????????') >= 0) {
                            if (feedback.indexOf ('Parameter error') >= 0) {
                                throw new OrderNotFound (feedback);
                            } else {
                                throw new InvalidOrder (feedback);
                            }
                        } else if (feedback.indexOf ('????????????????????????????????????,??????????????????') >= 0) {
                            throw new OrderNotFound (feedback);
                        }
                    }
                }
            }
        }
    }
};

