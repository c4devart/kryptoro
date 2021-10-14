<?php

require_once 'lib/PayBearOrder.php';
require_once 'lib/PayBearTxn.php';

$payBearOrder   = new PayBearOrder();
$payment_txn    = new PayBearTxn();

$order_id       = $_GET['order_id'];

if (empty($order_id)) return;

$payment = $payBearOrder->findByOrderId($order_id);

if (empty($payment)) return;

$confirmations          = $payment_txn->getTxnConfirmations($order_id);
$maxConfirmations       = $payment->max_confirmations;
$coinsPaid              = $payment_txn->getTotalPaid($order_id);
$coinsTotalConfirmed    = $payment_txn->getTotalConfirmed($order_id, $maxConfirmations);
$orderAmount            = $payment->amount;

$data = array();

if ($coinsTotalConfirmed >= $orderAmount) {
    $data['success'] = true;
} else {
    $data['success'] = false;
}

if (is_numeric($confirmations)) $data['confirmations'] = $confirmations;

$data['coinsPaid'] = $coinsPaid;

echo json_encode($data); //return this data to PayBear form
