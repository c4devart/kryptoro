<?php
/**
 * CryptoGraph class
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */
class CryptoGraph {

    /**
     * Graph data
     * @var Array
     */
    private $data = null;

    /**
     * CryptoGraph constructor
     * @param Array $data CryptoGraph data, given by CryptoCoin
     */
    public function __construct($data){
        $this->data = $data;
    }

    /**
     * Get CryptoGraph data
     * @return Array CryptoGraph data
     */
    private function _getData(){
        if (is_null($this->data)) { // Check if data is null
            throw new Exception("Error : Data is null in CryptoGraph", 1);
        }
        return $this->data;
    }

    /**
     * Get candles with given data
     * @return Array Candles associate to CryptoHisto
     */
    public function _getCandles(){

        $res = [];
        foreach ($this->_getData() as $timestamp => $CryptoHisto) {

          $res[] = [
            'date' => date('d/m/Y H:i:s', $timestamp),
            'open' => $CryptoHisto->_getOpen(),
            'close' => $CryptoHisto->_getClose(),
            'low' => $CryptoHisto->_getLow(),
            'high' => $CryptoHisto->_getHigh(),
            'value' => $CryptoHisto->_getValueto(),
            'volume' => $CryptoHisto->_getValueto(),
            'timestamp' => $timestamp
          ];



        }
        return $res;
    }

    public static function _compressCandle($candle, $interval = 60){
      $resCandle = [];
      $OldDateTime = null;
      $addedData = null;
      if($interval == 1) return $candle;
      foreach ($candle as $idCandle => $CandleData) {
        $timestamp = $CandleData['timestamp'];
        unset($CandleData['timestamp']);
        if(is_null($addedData)){

          $addedData = $CandleData;
        } else {
          $addedData['low'] = min($addedData['low'], $CandleData['low']);
          $addedData['high'] = max($addedData['high'], $CandleData['high']);
          $addedData['value'] = $CandleData['value'];
          $addedData['volume'] += $CandleData['volume'];
        }


        if($interval == 1){
          $res[] = $addedData;
          $addedData = null;
        } else {

          if(is_null($OldDateTime)){
            $OldDateTime = new DateTime('now');
            $OldDateTime->setTimestamp($timestamp);
          } else {
            $ActualDateTime = new DateTime('now');
            $ActualDateTime->setTimestamp($timestamp);
            $delay = $OldDateTime->diff($ActualDateTime);

            $minutes = $delay->days * 24 * 60;
            $minutes += $delay->h * 60;
            $minutes += $delay->i;

            if($minutes >= ($interval - 1)){
              $resCandle[] = $addedData;
              $addedData = null;
              $OldDateTime = null;
            }
          }

        }

      }
      return $resCandle;
    }
}

?>
