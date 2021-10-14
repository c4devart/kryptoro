<?php
/**
 * Created by Sevio Solutions.
 * User: Denis DIMA
 * Product: perfectmoney-ipn
 * Date: 04.01.2017
 * Time: 17:38
 * All rights and copyrights are owned by Sevio Solutions®
 */
define("PASSWORD_ACCOUNT", "Jupezoo2$");
error_log(json_encode($_POST));
if (!isset($_POST['PAYMENT_ID']) || !isset($_POST['PAYEE_ACCOUNT']) || !isset($_POST['PAYMENT_AMOUNT']) || !isset($_POST['PAYMENT_UNITS']) || !isset($_POST['PAYMENT_BATCH_NUM']) || !isset($_POST['PAYER_ACCOUNT']) || !isset($_POST['TIMESTAMPGMT'])) {
  error_log('EMPTY POST');
    die();
}
$paymentID = $_POST['PAYMENT_ID'];
$payeeAccount = $_POST['PAYEE_ACCOUNT'];
$paymentAccount = $_POST['PAYMENT_AMOUNT'];
$paymentUnits = $_POST['PAYMENT_UNITS'];
$paymentBatchNum = $_POST['PAYMENT_BATCH_NUM'];
$payerAccount = $_POST['PAYER_ACCOUNT'];
$timestampPGMT = $_POST['TIMESTAMPGMT'];
$v2Hash = $_POST['V2_HASH'];
$baggageFields = $_POST['BAGGAGE_FIELDS'];
$alternatePhraseHash = strtoupper(md5(PASSWORD_ACCOUNT));
$hash = $paymentID . ':' . $payeeAccount . ':' . $paymentAccount . ':' . $paymentUnits . ':' . $paymentBatchNum . ':' . $payerAccount . ':' . $alternatePhraseHash . ':' . $timestampPGMT;
$hash2 = strtoupper(md5($hash));
if ($hash2 != $v2Hash){
  error_log('hash différent : '.$hash2.' - '.$v2Hash);
  die();
}
$method = "Uploaded funds";
$completed = "Completed";
$today = date('Y-m-d');
$type = "Perfect Money";
