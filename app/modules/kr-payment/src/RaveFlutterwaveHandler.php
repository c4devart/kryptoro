<?php
class RaveFlutterwaveHandler
{

  private $RaveFlutterwave = null;

  public function __construct($RaveFlutterwave = null){
    if(is_null($RaveFlutterwave)) throw new Exception("Error RaveFlutterwave Handler : Main Rave can't be null", 1);
    $this->RaveFlutterwave = $RaveFlutterwave;
  }

  public function _getRaveFlutterwave(){
    if(is_null($this->RaveFlutterwave)) throw new Exception("Error RaveFlutterwave is null", 1);
    return $this->RaveFlutterwave;
  }
    /**
     * This is called when the Rave class is initialized
     */
	public function onInit($initializationData)
	{
  }
    /**
     * This is called only when a transaction is successful
     */
	public function onSuccessful($transactionData)
	{
        $this->_getRaveFlutterwave()->_processPayment($transactionData);
    }
    /**
     * This is called only when a transaction failed
     */
	public function onFailure($transactionData)
	{
      $this->_getRaveFlutterwave()->_setPaymentFail($transactionData);
  }

	public function onRequery($transactionReference)
	{


  }

	public function onRequeryError($requeryResponse)
	{
        // Do something, anything!
    }

	public function onCancel($transactionReference)
	{
        $this->_getRaveFlutterwave()->_setPaymentFail($transactionData);
    }

	function onTimeout($transactionReference, $data){}
}

?>
