<?php
require_once 'lib/PayBearOrder.php';
require_once 'lib/PayBearTxn.php';
require_once('lib/CmsOrder.php');

$payBearOrder   = new PayBearOrder();
$payment_txn    = new PayBearTxn();
$cms_order      = new CmsOrder();

$order_id = $_GET['order_id'];

if (empty($order_id)) return;

$payment = $payBearOrder->findByOrderId($order_id);

if (empty($payment)) return;

$orderAmount = $payment->amount;
$cmsOrder = $cms_order->findByIncrementId($order_id);


$data = file_get_contents('php://input');
if ($data) {
    $params = json_decode($data);
    $invoice = $payment->invoice;
    $orderTotal = $payment->amount;

	//save number of confirmations to DB: $params->confirmations
    //compare $invoice with one saved in the database to ensure callback is legitimate
    if ($params->invoice == $invoice) {

        $newPayment = $payment_txn->isNewOrder($order_id);

        $payment->confirmations = $params->confirmations;

        if ($newPayment) {
            $payment->paid_at = date('Y-m-d H:i:s');
        }

        $payment->save();

        $payment_txn->setTxn($params, $order_id);

        $total_confirmed = $payment_txn->getTotalConfirmed($order_id, $payment->max_confirmation);

        if ($params->confirmations >= $params->maxConfirmations) {

            //compare $amountPaid with order total
            if ($total_confirmed >= $orderAmount) {
                //mark the order as paid

                $cmsOrder->status = 'Complete';
                $cmsOrder->save();

                echo $invoice; //stop further callbacks
            }else{
                $cmsOrder->status = 'Order mispayment';
                $cmsOrder->save();
            }

        } else {

            $cmsOrder->status = 'Waiting for confirmations';
            $cmsOrder->save();
            die("waiting for confirmations");
        }

    }
}