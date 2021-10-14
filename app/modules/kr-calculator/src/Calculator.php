<?php

/**
 * Calculator class
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */
class Calculator extends MySQL
{
    /**
     * CryptoApi who communicate with REST Service
     *
     * @var CryptoApi CryptoApi object
     */
    private $CryptoApi = null;

    /**
     * Calculator constructor
     *
     * @param CryptoApi $CryptoApi CryptoAPI Object
     */
    public function __construct($CryptoApi = null)
    {
        // Save CryptoAPI in object
        $this->CryptoApi = $CryptoApi;
    }

    /**
     * Get Crypto API
     * @return CryptoAPI CryptoAPI Object
     */
    public function _getApi()
    {
        if (is_null($this->CryptoApi)) { // Check if CryptoAPI was not null
            throw new Exception("Error : Calculator, crypto api missing", 1);
        }
        return $this->CryptoApi;
    }

    /**
     * Convert method
     *
     * @param  String $from  From Symbol (ex : BTC)
     * @param  String $to    To Symbol (ex : USD)
     * @param  Float $value  Value converted
     *
     * @return Array         Result array => [result conversion, from currency for 1 equals, to currency for 1 equals]
     */
    public function _convertCurrency($from, $to, $value)
    {

        // Get price form currency from & to
        $priceData = $this->_getApi()->_getData('price', ['fsym' => $from, 'tsyms' => $to]);

        $resList = [];
        foreach ($priceData as $symbol => $vConv) {
          $resList[$symbol] = $vConv * $value;
        }
        return $resList;

    }

    public function _getListCurrencyUser($User){

      $r = parent::querySqlRequest("SELECT * FROM converter_krypto WHERE id_user=:id_user",
                                  [
                                    'id_user' => $User->_getUserID()
                                  ]);

      $s = json_decode('{ "coins": { "BTC": { "name": "Bitcoin", "value": 1 }, "ETH": { "name": "Etherum", "value": 0 }, "EUR": { "name": "Euro", "value": 0 }, "USD": { "name": "Dollar US", "value": 0 }, "LTC": { "name": "Litecoin", "value": 0 } } }', true);
      if(count($r) > 0 && json_decode($r[0]['list_converter']) != false){
        $s = json_decode($r[0]['list_converter'], true);
      }

      return $s['coins'];

    }

    public function _addItem($User, $Symbol, $Name){
      $getListItem = $this->_getListCurrencyUser($User);
      $getListItem[$Symbol] = [
        'name' => $Name,
        'value' => 0
      ];
      $r = parent::querySqlRequest("SELECT * FROM converter_krypto WHERE id_user=:id_user",
                                  [
                                    'id_user' => $User->_getUserID()
                                  ]);
      if(count($r) > 0){
        $r = parent::execSqlRequest("UPDATE converter_krypto SET list_converter=:list_converter WHERE id_user=:id_user",
                                    [
                                      'id_user' => $User->_getUserID(),
                                      'list_converter' => json_encode([
                                        'coins' => $getListItem
                                      ])
                                    ]);
      } else {
        $r = parent::execSqlRequest("INSERT INTO converter_krypto (id_user, list_converter) VALUES (:id_user, :list_converter)",
                                    [
                                      'id_user' => $User->_getUserID(),
                                      'list_converter' => json_encode([
                                        'coins' => $getListItem
                                      ])
                                    ]);
      }
    }
}
