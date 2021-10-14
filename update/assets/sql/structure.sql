[
  {
      "type": "table",
      "name": "balance_krypto",
      "database": "krypto_dev",
      "structure": [
        {
          "name": "id_balance",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": true,
          "auto_increment": true
        },
        {
          "name": "id_user",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "type_balance",
          "type": "varchar",
          "length": "20",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "created_balance",
          "type": "varchar",
          "length": "12",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "active_balance",
          "type": "int",
          "length": "11",
          "default": "0",
          "primary": false,
          "auto_increment": false
        }
      ]
  },
  {
      "type": "table",
      "name": "binance_krypto",
      "database": "krypto_dev",
      "structure": [
        {
          "name": "id_binance",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": true,
          "auto_increment": true
        },
        {
          "name": "key_binance",
          "type": "longtext",
          "length": "",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "secret_binance",
          "type": "longtext",
          "length": "",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "id_user",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": false,
          "auto_increment": false
        }
      ]
  },
  {
      "type": "table",
      "name": "hitbtc2_krypto",
      "database": "krypto_dev",
      "structure": [
        {
          "name": "id_hitbtc2",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": true,
          "auto_increment": true
        },
        {
          "name": "id_user",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "key_hitbtc2",
          "type": "longtext",
          "length": "",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "secret_hitbtc2",
          "type": "longtext",
          "length": "",
          "default": "",
          "primary": false,
          "auto_increment": false
        }
      ]
  },
  {
      "type": "table",
      "name": "poloniex_krypto",
      "database": "krypto_dev",
      "structure": [
        {
          "name": "id_poloniex",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": true,
          "auto_increment": true
        },
        {
          "name": "id_user",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "key_poloniex",
          "type": "longtext",
          "length": "",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "secret_poloniex",
          "type": "longtext",
          "length": "",
          "default": "",
          "primary": false,
          "auto_increment": false
        }
      ]
  },
  {
      "type": "table",
      "name": "bittrex_krypto",
      "database": "krypto_dev",
      "structure": [
        {
          "name": "id_bittrex",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": true,
          "auto_increment": true
        },
        {
          "name": "id_user",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "api_key_bittrex",
          "type": "longtext",
          "length": "",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "api_secret_bittrex",
          "type": "longtext",
          "length": "",
          "default": "",
          "primary": false,
          "auto_increment": false
        }
      ]
  },
  {
      "type": "table",
      "name": "blocked_user_chat_krypto",
      "database": "krypto_dev",
      "structure": [
        {
          "name": "id_blocked_user_chat",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": true,
          "auto_increment": true
        },
        {
          "name": "id_user",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "id_user_blocked",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "date_blocked_user_chat",
          "type": "varchar",
          "length": "12",
          "default": "",
          "primary": false,
          "auto_increment": false
        }
      ]
  },
  {
      "type": "table",
      "name": "blockfolio_krypto",
      "database": "krypto_dev",
      "structure": [
        {
          "name": "id_blockfolio",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": true,
          "auto_increment": true
        },
        {
          "name": "id_user",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "symbol_blockfolio",
          "type": "varchar",
          "length": "15",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "currency_blockfolio",
          "type": "varchar",
          "length": "15",
          "default": "",
          "primary": false,
          "auto_increment": false
        }
      ]
  },
  {
      "type": "table",
      "name": "cache_krypto",
      "database": "krypto_dev",
      "structure": [
        {
          "name": "id_cache",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": true,
          "auto_increment": true
        },
        {
          "name": "service_cache",
          "type": "longtext",
          "length": "",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "value_cache",
          "type": "longtext",
          "length": "",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "last_update_cache",
          "type": "varchar",
          "length": "15",
          "default": "",
          "primary": false,
          "auto_increment": false
        }
      ]
  },
  {
      "type": "table",
      "name": "cex_krypto",
      "database": "krypto_dev",
      "structure": [
        {
          "name": "id_cex",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": true,
          "auto_increment": true
        },
        {
          "name": "id_user",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "key_cex",
          "type": "longtext",
          "length": "",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "secret_cex",
          "type": "longtext",
          "length": "",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "uid_cex",
          "type": "longtext",
          "length": "",
          "default": "",
          "primary": false,
          "auto_increment": false
        }
      ]
  },
  {
      "type": "table",
      "name": "charges_krypto",
      "database": "krypto_dev",
      "structure": [
        {
          "name": "id_charges",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": true,
          "auto_increment": true
        },
        {
          "name": "id_user",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "key_charges",
          "type": "text",
          "length": "",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "date_charges",
          "type": "varchar",
          "length": "12",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "status_charges",
          "type": "varchar",
          "length": "10",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "ndays_charges",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "type_payment",
          "type": "varchar",
          "length": "20",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "data_payment",
          "type": "text",
          "length": "",
          "default": "",
          "primary": false,
          "auto_increment": false
        }
      ]
  },
  {
      "type": "table",
      "name": "coinlist_krypto",
      "database": "krypto_dev",
      "structure": [
        {
          "name": "id_coinlist",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": true,
          "auto_increment": true
        },
        {
          "name": "currencyid_coinlist",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "symbol_coinlist",
          "type": "varchar",
          "length": "8",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "fullname_coinlist",
          "type": "varchar",
          "length": "50",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "coinname_coinlist",
          "type": "varchar",
          "length": "50",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "order_coinlist",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "algorithm_coinlist",
          "type": "varchar",
          "length": "20",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "prooftype_coinlist",
          "type": "varchar",
          "length": "12",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "url_coinlist",
          "type": "varchar",
          "length": "60",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "status_coinslist",
          "type": "int",
          "length": "11",
          "default": "1",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "source_coinlist",
          "type": "varchar",
          "length": "50",
          "default": "cryptocompare",
          "primary": false,
          "auto_increment": false
        }
      ]
  },
  {
      "type": "table",
      "name": "converter_krypto",
      "database": "krypto_dev",
      "structure": [
        {
          "name": "id_converter",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": true,
          "auto_increment": true
        },
        {
          "name": "id_user",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "list_converter",
          "type": "longtext",
          "length": "",
          "default": "",
          "primary": false,
          "auto_increment": false
        }
      ]
  },
  {
      "type": "table",
      "name": "currency_krypto",
      "database": "krypto_dev",
      "structure": [
        {
          "name": "id_currency",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": true,
          "auto_increment": true
        },
        {
          "name": "name_currency",
          "type": "varchar",
          "length": "50",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "code_iso_currency",
          "type": "varchar",
          "length": "10",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "symbol_currency",
          "type": "varchar",
          "length": "5",
          "default": "",
          "primary": false,
          "auto_increment": false
        }
      ]
  },
  {
      "type": "table",
      "name": "dashboard_krypto",
      "database": "krypto_dev",
      "structure": [
        {
          "name": "id_dashboard",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": true,
          "auto_increment": true
        },
        {
          "name": "id_user",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "num_graph_dashboard",
          "type": "varchar",
          "length": "11",
          "default": "3_tm_ll",
          "primary": false,
          "auto_increment": false
        }
      ]
  },
  {
      "type": "table",
      "name": "deposit_history_krypto",
      "database": "krypto_dev",
      "structure": [
        {
          "name": "id_deposit_history",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": true,
          "auto_increment": true
        },
        {
          "name": "id_user",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "amount_deposit_history",
          "type": "varchar",
          "length": "50",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "date_deposit_history",
          "type": "varchar",
          "length": "12",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "balance_deposit_history",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "payment_status_deposit_history",
          "type": "varchar",
          "length": "50",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "payment_type_deposit_history",
          "type": "varchar",
          "length": "50",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "payment_data_deposit_history",
          "type": "text",
          "length": "",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "description_deposit_history",
          "type": "varchar",
          "length": "250",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "fees_deposit_history",
          "type": "varchar",
          "length": "50",
          "default": "0",
          "primary": false,
          "auto_increment": false
        }
      ]
  },
  {
      "type": "table",
      "name": "ethfinex_krypto",
      "database": "krypto_dev",
      "structure": [
        {
          "name": "id_ethfinex",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": true,
          "auto_increment": true
        },
        {
          "name": "id_user",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "key_ethfinex",
          "type": "longtext",
          "length": "",
          "default": "",
          "primary": false,
          "auto_increment": false
        }
      ]
  },
  {
      "type": "table",
      "name": "gdax_krypto",
      "database": "krypto_dev",
      "structure": [
        {
          "name": "id_gdax",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": true,
          "auto_increment": true
        },
        {
          "name": "id_user",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "key_gdax",
          "type": "text",
          "length": "",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "pass_gdax",
          "type": "text",
          "length": "",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "secret_gdax",
          "type": "text",
          "length": "",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "live_gdax",
          "type": "int",
          "length": "11",
          "default": "0",
          "primary": false,
          "auto_increment": false
        }
      ]
  },
  {
      "type": "table",
      "name": "gemini_krypto",
      "database": "krypto_dev",
      "structure": [
        {
          "name": "id_gemini",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": true,
          "auto_increment": true
        },
        {
          "name": "id_user",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "key_gemini",
          "type": "longtext",
          "length": "",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "secret_gemini",
          "type": "longtext",
          "length": "",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "live_gemini",
          "type": "int",
          "length": "11",
          "default": "0",
          "primary": false,
          "auto_increment": false
        }
      ]
  },
  {
      "type": "table",
      "name": "googletfs_krypto",
      "database": "krypto_dev",
      "structure": [
        {
          "name": "id_googletfs",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": true,
          "auto_increment": true
        },
        {
          "name": "id_user",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "date_googletfs",
          "type": "varchar",
          "length": "12",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "secret_googletfs",
          "type": "text",
          "length": "",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "status_googletfs",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": false,
          "auto_increment": false
        }
      ]
  },
  {
      "type": "table",
      "name": "graph_krypto",
      "database": "krypto_dev",
      "structure": [
        {
          "name": "id_graph",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": true,
          "auto_increment": true
        },
        {
          "name": "id_user",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "key_graph",
          "type": "varchar",
          "length": "25",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "type_graph",
          "type": "varchar",
          "length": "50",
          "default": "candlestick",
          "primary": false,
          "auto_increment": false
        }
      ]
  },
  {
      "type": "table",
      "name": "histo_krypto",
      "database": "krypto_dev",
      "structure": [
        {
          "name": "id_histo",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": true,
          "auto_increment": true
        },
        {
          "name": "coin_histo",
          "type": "varchar",
          "length": "50",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "currency_histo",
          "type": "varchar",
          "length": "50",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "type_histo",
          "type": "varchar",
          "length": "75",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "last_update_histo",
          "type": "varchar",
          "length": "20",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "data_histo",
          "type": "longtext",
          "length": "",
          "default": "",
          "primary": false,
          "auto_increment": false
        }
      ]
  },
  {
      "type": "table",
      "name": "holding_krypto",
      "database": "krypto_dev",
      "structure": [
        {
          "name": "id_holding",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": true,
          "auto_increment": true
        },
        {
          "name": "id_user",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "value_holding",
          "type": "varchar",
          "length": "200",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "type_holding",
          "type": "varchar",
          "length": "12",
          "default": "buy",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "date_holding",
          "type": "varchar",
          "length": "12",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "price_holding",
          "type": "varchar",
          "length": "200",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "symbol_holding",
          "type": "varchar",
          "length": "12",
          "default": "",
          "primary": false,
          "auto_increment": false
        }
      ]
  },
  {
      "type": "table",
      "name": "indicators_krypto",
      "database": "krypto_dev",
      "structure": [
        {
          "name": "id_indicators",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": true,
          "auto_increment": true
        },
        {
          "name": "key_graph",
          "type": "varchar",
          "length": "50",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "key_indicators",
          "type": "varchar",
          "length": "50",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "symbol_indicators",
          "type": "varchar",
          "length": "20",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "data_indicators",
          "type": "text",
          "length": "",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "title_indicators",
          "type": "varchar",
          "length": "50",
          "default": "",
          "primary": false,
          "auto_increment": false
        }
      ]
  },
  {
      "type": "table",
      "name": "internal_order_krypto",
      "database": "krypto_dev",
      "structure": [
        {
          "name": "id_internal_order",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": true,
          "auto_increment": true
        },
        {
          "name": "id_user",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "date_internal_order",
          "type": "varchar",
          "length": "12",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "id_balance",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "thirdparty_internal_order",
          "type": "varchar",
          "length": "50",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "amount_internal_order",
          "type": "float",
          "length": "",
          "default": "0",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "usd_amount_internal_order",
          "type": "float",
          "length": "",
          "default": "0",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "symbol_internal_order",
          "type": "varchar",
          "length": "50",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "fees_internal_order",
          "type": "varchar",
          "length": "50",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "order_key_internal_order",
          "type": "text",
          "length": "",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "side_internal_order",
          "type": "varchar",
          "length": "20",
          "default": "BUY",
          "primary": false,
          "auto_increment": false
        }
      ]
  },
  {
      "type": "table",
      "name": "kraken_krypto",
      "database": "krypto_dev",
      "structure": [
        {
          "name": "id_kraken",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": true,
          "auto_increment": true
        },
        {
          "name": "id_user",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "key_kraken",
          "type": "longtext",
          "length": "",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "private_kraken",
          "type": "longtext",
          "length": "",
          "default": "",
          "primary": false,
          "auto_increment": false
        }
      ]
  },
  {
      "type": "table",
      "name": "kucoin_krypto",
      "database": "krypto_dev",
      "structure": [
        {
          "name": "id_kucoin",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": true,
          "auto_increment": true
        },
        {
          "name": "id_user",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "key_kucoin",
          "type": "longtext",
          "length": "",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "secret_kucoin",
          "type": "longtext",
          "length": "",
          "default": "",
          "primary": false,
          "auto_increment": false
        }
      ]
  },
  {
      "type": "table",
      "name": "leader_board_krypto",
      "database": "krypto_dev",
      "structure": [
        {
          "name": "id_leader_board",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": true,
          "auto_increment": true
        },
        {
          "name": "id_user",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "benef_leader_board",
          "type": "varchar",
          "length": "50",
          "default": "",
          "primary": false,
          "auto_increment": false
        }
      ]
  },
  {
      "type": "table",
      "name": "msg_room_chat_krypto",
      "database": "krypto_dev",
      "structure": [
        {
          "name": "id_msg_room_chat",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": true,
          "auto_increment": true
        },
        {
          "name": "id_room_chat",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "id_user",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "type_msg_room_chat",
          "type": "varchar",
          "length": "20",
          "default": "text",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "value_msg_room_chat",
          "type": "longtext",
          "length": "",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "date_msg_room_chat",
          "type": "varchar",
          "length": "12",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "control_key_msg_room_chat",
          "type": "varchar",
          "length": "50",
          "default": "",
          "primary": false,
          "auto_increment": false
        }
      ]
  },
  {
      "type": "table",
      "name": "notification_center_krypto",
      "database": "krypto_dev",
      "structure": [
        {
          "name": "id_notification_center",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": true,
          "auto_increment": true
        },
        {
          "name": "title_notification_center",
          "type": "varchar",
          "length": "255",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "text_notification_center",
          "type": "text",
          "length": "",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "date_notification_center",
          "type": "varchar",
          "length": "12",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "icon_notification_center",
          "type": "text",
          "length": "",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "status_notification_center",
          "type": "double",
          "length": "",
          "default": "0",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "id_user",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": false,
          "auto_increment": false
        }
      ]
  },
  {
      "type": "table",
      "name": "notification_krypto",
      "database": "krypto_dev",
      "structure": [
        {
          "name": "id_notification",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": true,
          "auto_increment": true
        },
        {
          "name": "symbol_notification",
          "type": "varchar",
          "length": "20",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "actual_value_notification",
          "type": "varchar",
          "length": "50",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "value_notification",
          "type": "varchar",
          "length": "50",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "compare_notififcation",
          "type": "int",
          "length": "11",
          "default": "0",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "status_notification",
          "type": "int",
          "length": "11",
          "default": "0",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "id_user",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": false,
          "auto_increment": false
        }
      ]
  },
  {
      "type": "table",
      "name": "order_krypto",
      "database": "krypto_dev",
      "structure": [
        {
          "name": "id_order",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": true,
          "auto_increment": true
        },
        {
          "name": "id_user",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "time_order",
          "type": "varchar",
          "length": "50",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "type_order",
          "type": "varchar",
          "length": "5",
          "default": "BUY",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "amount_order",
          "type": "varchar",
          "length": "50",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "symbol_order",
          "type": "varchar",
          "length": "20",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "currency_order",
          "type": "varchar",
          "length": "15",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "thirdparty_order",
          "type": "varchar",
          "length": "50",
          "default": "",
          "primary": false,
          "auto_increment": false
        }
      ]
  },
  {
      "type": "table",
      "name": "plan_krypto",
      "database": "krypto_dev",
      "structure": [
        {
          "name": "id_plan",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": true,
          "auto_increment": true
        },
        {
          "name": "name_plan",
          "type": "varchar",
          "length": "50",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "price_plan",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "ndays_plan",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": false,
          "auto_increment": false
        }
      ]
  },
  {
      "type": "table",
      "name": "referal_histo_krypto",
      "database": "krypto_dev",
      "structure": [
        {
          "name": "referal_histo_id",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": true,
          "auto_increment": true
        },
        {
          "name": "id_user",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "code_referal",
          "type": "varchar",
          "length": "255",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "date_referal_histo",
          "type": "varchar",
          "length": "12",
          "default": "",
          "primary": false,
          "auto_increment": false
        }
      ]
  },
  {
      "type": "table",
      "name": "referal_krypto",
      "database": "krypto_dev",
      "structure": [
        {
          "name": "id_referal",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": true,
          "auto_increment": true
        },
        {
          "name": "id_user",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "code_referal",
          "type": "varchar",
          "length": "255",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "date_referal",
          "type": "varchar",
          "length": "12",
          "default": "",
          "primary": false,
          "auto_increment": false
        }
      ]
  },
  {
      "type": "table",
      "name": "room_chat_krypto",
      "database": "krypto_dev",
      "structure": [
        {
          "name": "id_room_chat",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": true,
          "auto_increment": true
        },
        {
          "name": "key_room_chat",
          "type": "varchar",
          "length": "50",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "date_created_room_chat",
          "type": "varchar",
          "length": "12",
          "default": "",
          "primary": false,
          "auto_increment": false
        }
      ]
  },
  {
      "type": "table",
      "name": "rssfeed_krypto",
      "database": "krypto_dev",
      "structure": [
        {
          "name": "id_rssfeed",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": true,
          "auto_increment": true
        },
        {
          "name": "name_rssfeed",
          "type": "varchar",
          "length": "100",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "url_rssfeed",
          "type": "text",
          "length": "",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "date_rssfeed",
          "type": "varchar",
          "length": "12",
          "default": "",
          "primary": false,
          "auto_increment": false
        }
      ]
  },
  {
      "type": "table",
      "name": "settings_krypto",
      "database": "krypto_dev",
      "structure": [
        {
          "name": "id_settings",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": true,
          "auto_increment": true
        },
        {
          "name": "key_settings",
          "type": "varchar",
          "length": "255",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "value_settings",
          "type": "text",
          "length": "",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "encrypted_settings",
          "type": "int",
          "length": "11",
          "default": "0",
          "primary": false,
          "auto_increment": false
        }
      ]
  },
  {
      "type": "table",
      "name": "social_krypto",
      "database": "krypto_dev",
      "structure": [
        {
          "name": "id_social",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": true,
          "auto_increment": true
        },
        {
          "name": "type_social",
          "type": "varchar",
          "length": "20",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "user_social",
          "type": "varchar",
          "length": "80",
          "default": "",
          "primary": false,
          "auto_increment": false
        }
      ]
  },
  {
      "type": "table",
      "name": "thirdparty_crypto_krypto",
      "database": "krypto_dev",
      "structure": [
        {
          "name": "id_thirdparty_crypto",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": true,
          "auto_increment": true
        },
        {
          "name": "symbol_thirdparty_crypto",
          "type": "varchar",
          "length": "12",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "to_thirdparty_crypto",
          "type": "varchar",
          "length": "20",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "name_thirdparty_crypto",
          "type": "varchar",
          "length": "50",
          "default": "",
          "primary": false,
          "auto_increment": false
        }
      ]
  },
  {
      "type": "table",
      "name": "top_list_krypto",
      "database": "krypto_dev",
      "structure": [
        {
          "name": "id_top_list",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": true,
          "auto_increment": true
        },
        {
          "name": "symbol_top_list",
          "type": "varchar",
          "length": "20",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "id_user",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "container_top_list",
          "type": "varchar",
          "length": "100",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "control_key_top_list",
          "type": "varchar",
          "length": "50",
          "default": "",
          "primary": false,
          "auto_increment": false
        }
      ]
  },
  {
      "type": "table",
      "name": "user_intro_krypto",
      "database": "krypto_dev",
      "structure": [
        {
          "name": "id_user_intro",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": true,
          "auto_increment": true
        },
        {
          "name": "id_user",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "date_user_intro",
          "type": "varchar",
          "length": "12",
          "default": "",
          "primary": false,
          "auto_increment": false
        }
      ]
  },
  {
      "type": "table",
      "name": "user_login_history_krypto",
      "database": "krypto_dev",
      "structure": [
        {
          "name": "id_user_login_history",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": true,
          "auto_increment": true
        },
        {
          "name": "id_user",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "date_user_login_history",
          "type": "varchar",
          "length": "12",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "ip_user_login_history",
          "type": "varchar",
          "length": "50",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "device_user_login_history",
          "type": "varchar",
          "length": "255",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "location_user_login_history",
          "type": "longtext",
          "length": "",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "country_code_user_login_history",
          "type": "varchar",
          "length": "50",
          "default": "",
          "primary": false,
          "auto_increment": false
        }
      ]
  },
  {
      "type": "table",
      "name": "user_newspopup",
      "database": "krypto_dev",
      "structure": [
        {
          "name": "id_newspopup",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": true,
          "auto_increment": true
        },
        {
          "name": "id_user",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "last_newspopup",
          "type": "varchar",
          "length": "12",
          "default": "",
          "primary": false,
          "auto_increment": false
        }
      ]
  },
  {
      "type": "table",
      "name": "user_room_chat_krypto",
      "database": "krypto_dev",
      "structure": [
        {
          "name": "id_user_room_chat",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": true,
          "auto_increment": true
        },
        {
          "name": "id_room_chat",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "id_user",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "date_user_room_chat",
          "type": "varchar",
          "length": "12",
          "default": "",
          "primary": false,
          "auto_increment": false
        }
      ]
  },
  {
      "type": "table",
      "name": "user_settings_krypto",
      "database": "krypto_dev",
      "structure": [
        {
          "name": "id_user_settings",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": true,
          "auto_increment": true
        },
        {
          "name": "id_user",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "key_user_settings",
          "type": "varchar",
          "length": "50",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "value_user_settings",
          "type": "text",
          "length": "",
          "default": "",
          "primary": false,
          "auto_increment": false
        }
      ]
  },
  {
      "type": "table",
      "name": "user_status_krypto",
      "database": "krypto_dev",
      "structure": [
        {
          "name": "id_user_status",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": true,
          "auto_increment": true
        },
        {
          "name": "id_user",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "last_update_user_status",
          "type": "varchar",
          "length": "12",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "type_user_status",
          "type": "int",
          "length": "11",
          "default": "0",
          "primary": false,
          "auto_increment": false
        }
      ]
  },
  {
      "type": "table",
      "name": "user_thirdparty_selected_krypto",
      "database": "krypto_dev",
      "structure": [
        {
          "name": "id_user_thirdparty_selected",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": true,
          "auto_increment": true
        },
        {
          "name": "id_user",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "name_user_thirdparty_selected",
          "type": "varchar",
          "length": "50",
          "default": "",
          "primary": false,
          "auto_increment": false
        }
      ]
  },
  {
      "type": "table",
      "name": "widthdraw_history_krypto",
      "database": "krypto_dev",
      "structure": [
        {
          "name": "id_widthdraw_history",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": true,
          "auto_increment": true
        },
        {
          "name": "id_user",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "id_balance",
          "type": "int",
          "length": "11",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "amount_widthdraw_history",
          "type": "varchar",
          "length": "50",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "date_widthdraw_history",
          "type": "varchar",
          "length": "12",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "status_widthdraw_history",
          "type": "int",
          "length": "11",
          "default": "0",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "paypal_widthdraw_history",
          "type": "varchar",
          "length": "150",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "token_widthdraw_history",
          "type": "text",
          "length": "",
          "default": "",
          "primary": false,
          "auto_increment": false
        },
        {
          "name": "description_widthdraw_history",
          "type": "varchar",
          "length": "150",
          "default": "",
          "primary": false,
          "auto_increment": false
        }
      ]
  }
]
