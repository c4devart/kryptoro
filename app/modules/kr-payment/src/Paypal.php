<?php

/**
 * Paypal class
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */
class Paypal extends MySQL
{
    /**
     * App object
     * @var App
     */
    private $App = null;

    /**
     * Charge key id
     * @var String
     */
    private $keycharge = null;

    /**
     * Paypal payment result
     * @var Object
     */
    private $result = null;

    /**
     * Paypal constructor
     * @param App $App          App object
     * @param String $keycharge Charge key
     */
    public function __construct($App = null, $keycharge = null)
    {
        if (is_null($App)) {
            throw new Exception("Error Paypal : App need to be given", 1);
        }
        $this->App = $App;
        $this->keycharge = $keycharge;

        // If key charge defined --> load payment
        if (!is_null($keycharge)) {
            $this->_loadPayment();
        }
    }

    /**
     * Get key charge
     * @return String Key charge
     */
    private function _getKeycharge()
    {
        if (is_null($this->keycharge)) {
            throw new Exception("Error Paypal : Key charge is null", 1);
        }
        return $this->keycharge;
    }

    /**
     * Get app object
     * @return App App object
     */
    private function _getApp()
    {
        if (is_null($this->App)) {
            throw new Exception("Error Paypal : App not defined", 1);
        }
        return $this->App;
    }

    /**
     * Get paypal credential
     * @return OAuthTokenCredential Paypal credential
     */
    private function _getCredential()
    {
        $apiContext = new \PayPal\Rest\ApiContext(new \PayPal\Auth\OAuthTokenCredential($this->_getApp()->_getPaypalClientID(), $this->_getApp()->_getPaypalClientSecret()));

        if($this->_getApp()->_paypalLiveModeEnabled()) {
          $apiContext->setConfig([
            'mode' => 'live'
          ]);
        }

        return $apiContext;
    }

    /**
     * Generate new payment link
     * @param  ChargesPlan $Plan Plan payment link
     * @return String            Payment url
     */
    public function _generateLink($Plan)
    {

        // Load credential
        $apiContext = $this->_getCredential();

        // Init paypal payer
        $payer = new \PayPal\Api\Payer();
        $payer->setPaymentMethod("paypal");

        // Init paypal item (plan)
        $PremiumPlan = new \PayPal\Api\Item();
        $PremiumPlan->setName($Plan->_getName())
        ->setCurrency(strtoupper($this->_getApp()->_getChargeCurrency()))
        ->setQuantity(1)
        ->setPrice(floatval(intval($Plan->_getPrice()) / 100));

        $itemList = new \PayPal\Api\ItemList();
        $itemList->setItems(array($PremiumPlan));

        // Define payment amount
        $amount = new \PayPal\Api\Amount();
        $amount->setCurrency(strtoupper($this->_getApp()->_getChargeCurrency()))
      ->setTotal(floatval(intval($Plan->_getPrice()) / 100));

        // Create new transaction
        $transaction = new \PayPal\Api\Transaction();
        $transaction->setAmount($amount)
      ->setItemList($itemList)
      ->setDescription($this->_getApp()->_getAppTitle().' - '.$Plan->_getName().' - '.$Plan->_getNumberMonth().' month'.($Plan->_getNumberMonth() > 1 ? 's' : ''))
      ->setInvoiceNumber(uniqid());

        // Init redirection url
        $redirectUrls = new \PayPal\Api\RedirectUrls();
        $redirectUrls->setReturnUrl(APP_URL."/app/modules/kr-payment/src/actions/processPaypal.php?scs=true")
        ->setCancelUrl(APP_URL."/app/modules/kr-payment/src/actions/processPaypal.php?scs=false");

        // Init paypal payment
        $payment = new \PayPal\Api\Payment();
        $payment->setIntent("sale")
        ->setPayer($payer)
        ->setRedirectUrls($redirectUrls)
        ->setTransactions(array($transaction));

        // Create paypal payment
        $payment->create($apiContext);

        // Return payment link
        return $payment->getApprovalLink();
    }

    public function _generateDepositLink($amount){

      // Load credential
      $apiContext = $this->_getCredential();

      // Init paypal payer
      $payer = new \PayPal\Api\Payer();
      $payer->setPaymentMethod("paypal");

      // Init paypal item (plan)
      $DepositItem = new \PayPal\Api\Item();
      $DepositItem->setName('Deposit ('.$this->_getApp()->_formatNumber($amount, 2).' $)')
      ->setCurrency(strtoupper('USD'))
      ->setQuantity(1)
      ->setPrice(floatval($amount));

      $DepositFee = new \PayPal\Api\Item();
      $DepositFee->setName('Deposit fees ('.$this->_getApp()->_formatNumber($this->_getApp()->_getFeesDeposit(), 2).'%)')
      ->setCurrency(strtoupper('USD'))
      ->setQuantity(1)
      ->setPrice(floatval(($amount * ($this->_getApp()->_getFeesDeposit() / 100))));

      $itemList = new \PayPal\Api\ItemList();
      $itemList->setItems(array($DepositItem, $DepositFee));

      $totalamount = new \PayPal\Api\Amount();
      $totalamount->setCurrency(strtoupper('USD'))
      ->setTotal(floatval($amount + ($amount * ($this->_getApp()->_getFeesDeposit() / 100))));

    $transaction = new \PayPal\Api\Transaction();
      $transaction->setAmount($totalamount)
    ->setItemList($itemList)
    ->setDescription($this->_getApp()->_getAppTitle().' - Deposit '.$this->_getApp()->_formatNumber($amount, 2).' $ (+'.$this->_getApp()->_formatNumber($amount * ($this->_getApp()->_getFeesDeposit() / 100), 2).' $ fees)')
    ->setInvoiceNumber(uniqid());


    $redirectUrls = new \PayPal\Api\RedirectUrls();
    $redirectUrls->setReturnUrl(APP_URL."/app/modules/kr-payment/src/actions/deposit/processPaypal.php?scs=true")
    ->setCancelUrl(APP_URL."/app/modules/kr-payment/src/actions/deposit/processPaypal.php?scs=false");

    $payment = new \PayPal\Api\Payment();
    $payment->setIntent("sale")
    ->setPayer($payer)
    ->setRedirectUrls($redirectUrls)
    ->setTransactions(array($transaction));

    $payment->create($apiContext);

    $_SESSION['kr_deposit_amount'] = [
      'amount' => $amount,
      'fees' => $amount * ($this->_getApp()->_getFeesDeposit() / 100)
    ];

    // Return payment link
    return $payment->getApprovalLink();

    }

    /**
     * Check paypal payment
     * @param  ChargesPlan $Plan Charged plan
     * @return Array             Result
     */
    public function _checkPayment($Plan)
    {

        // Check args given
        if (!isset($_GET['scs']) || !isset($_GET['token'])) {
            throw new Exception("Error Paypal : Invalid return", 1);
        }

        if (isset($_GET['scs']) && $_GET['scs'] == 'true') {

            // Get payment id
            $paymentId = $_GET['paymentId'];
            // Get payment
            $payment = \PayPal\Api\Payment::get($paymentId, $this->_getCredential());

            // Charge payment
            $execution = new \PayPal\Api\PaymentExecution();
            $execution->setPayerId($_GET['PayerID']);

            $transaction = new \PayPal\Api\Transaction();
            $amount = new \PayPal\Api\Amount();

            // Set price, item & total
            $amount->setCurrency(strtoupper($this->_getApp()->_getChargeCurrency()));
            $amount->setTotal(floatval(intval($Plan->_getPrice()) / 100));
            $transaction->setAmount($amount);
            $execution->addTransaction($transaction);

            // Execute payment process
            $this->result = $payment->execute($execution, $this->_getCredential());

            // Return payment result
            return $this->result;
        } else {
            throw new Exception("You have cancel payment", 1);
        }
    }

    public function _checkDepositPayment(){
      if (!isset($_GET['scs']) || !isset($_GET['token'])) {
          throw new Exception("Error Paypal : Invalid return", 1);
      }
      $paymentId = $_GET['paymentId'];
      $payment = \PayPal\Api\Payment::get($paymentId, $this->_getCredential());
      $totalPayed = $payment->toArray()['transactions'][0]['amount']['total'];

      $totalNeeded = $_SESSION['kr_deposit_amount']['amount'] + $_SESSION['kr_deposit_amount']['fees'];
      if($totalNeeded > $totalPayed) throw new Exception("Error : Transaction probleme, please contact support", 1);

      if (isset($_GET['scs']) && $_GET['scs'] == 'true') {

        $execution = new \PayPal\Api\PaymentExecution();
        $execution->setPayerId($_GET['PayerID']);

        $transaction = new \PayPal\Api\Transaction();
        $amount = new \PayPal\Api\Amount();

        $amount->setCurrency('USD');
        $amount->setTotal($totalNeeded);
        $transaction->setAmount($amount);
        $execution->addTransaction($transaction);

        $this->result = $payment->execute($execution, $this->_getCredential());

        return $this->result;

      } else {
        throw new Exception("You have cancel payment", 1);
      }




    }

    /**
     * Get payment status
     * @return Int Payment status (0 = failed, 1 = success)
     */
    public function _getStatus()
    {
        if ($this->result->getState() == "failed" || $this->result->getState() == "canceled" || $this->result->getState() == "expired") {
            return 0;
        }
        return 1;
    }

    /**
     * Load payment data
     */
    public function _loadPayment()
    {
        $this->result = \PayPal\Api\Payment::get($this->_getKeycharge(), $this->_getCredential());
    }
}
