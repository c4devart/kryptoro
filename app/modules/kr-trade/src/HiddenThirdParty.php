<?php

class HiddenThirdParty extends MySQL {

  private $App = null;
  private $User = null;

  public function  __construct($User, $App){
    $this->App = $App;
    $this->User = $User;
  }

  public function _getApp(){ return $this->App; }
  public function _getUser(){ return $this->User; }

  public function _getExchange(){
    $Gdax = new Gdax($this->_getUser(), $this->_getApp(), [
      'YnVNd05pRzVTRUpLeFBIWjd6bm9hb05ER1laNUwwREdlMkJudEZVZ1h5MjcyQkdiVHRmc2xJM1RDbVdkOGdBRw==',
      'WllBL2NGQXdzK0JnWWJxV2NVZG83Zz09',
      'VnAwMVZRYlVhTDRHNUlsY2dGckNDNFFhTmk3QnNGSXJWQ1lheTRNMWVmNWs3V3k3aktZNVhjQTV6U0QzUnN1VGRaRzRxWHNDcGkwUWNkMitHRVVFdFZ0dTR2amc2cDJTVGoxTFBGeHdrUEszNHAxdFBqeXFHS285akxVSyttVmw=',
      0
    ]);

    var_dump($Gdax->_getApi()->deposit('USD', 20, null, ['payment_method_id' => '123']));
    echo "ok";
  }

}

?>
