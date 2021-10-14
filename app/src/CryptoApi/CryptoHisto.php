<?php
/**
 * CryptoHisto class
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */
class CryptoHisto {

  /**
   * Historic data
   * @var Array
   */
  private $HistoData = null;

  /**
   * CryptoHisto constructor
   * @param Array $HistoData Historic data
   */
  public function __construct($HistoData = null){
    if(is_null($HistoData)) throw new Exception("Error : CryptoHisto data can't be null", 1);
    $this->HistoData = $HistoData;
  }

  /**
   * Get Historic data by key
   * @param  String $k Key
   * @return String    Value associate to the key
   */
  public function _getDataKey($k){
    // Check if data is given or null
    if(is_null($this->HistoData)) throw new Exception("Error : Data is null for this Histo Coin", 1);

    // Check if key exist in data
    if(!array_key_exists($k, $this->HistoData)) throw new Exception("Error : ".$k." not exist in Histo Coin data", 1);

    // Return value associate to the key
    return $this->HistoData[$k];
  }

  /**
   * Get Historic date as timestamp
   * @return String Historic date timestamp
   */
  public function _getTime(){ return $this->_getDataKey('time'); }

  /**
   * Get Historic open value
   * @return String Historic open value
   */
  public function _getOpen(){ return floatval($this->_getDataKey('open')); }

  /**
   * Get Historic high value
   * @return String Historic high value
   */
  public function _getHigh(){ return floatval($this->_getDataKey('high')); }

  /**
   * Get Historic low value
   * @return String Historic low value
   */
  public function _getLow(){ return floatval($this->_getDataKey('low')); }

  /**
   * Get Historic close value
   * @return String Historic close value
   */
  public function _getClose(){ return floatval($this->_getDataKey('close')); }

  /**
   * Get Historic value from
   * @return String Historic value from
   */
  public function _getValuefrom(){ return floatval($this->_getDataKey('volumefrom')); }

  /**
   * Get Historic value to
   * @return String Historic value to
   */
  public function _getValueto(){ return floatval($this->_getDataKey('volumeto')); }

  /**
   * Get Historic formated date
   * @param  String Format needed
   * @return String Formated data
   */
  public function _getFormatedDate($format = 'd/m/Y H:i:s'){ return date($format, $this->_getTime()); }


}

?>
