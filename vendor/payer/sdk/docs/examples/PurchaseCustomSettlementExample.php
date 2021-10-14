<?php
/**
 * Copyright 2016 Payer Financial Services AB
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * PHP version 5.3
 *
 * @package   Payer_Sdk
 * @author    Payer <teknik@payer.se>
 * @copyright 2016 Payer Financial Services AB
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache license v2.0
 *
 * Description:
 *
 * This file  is called by Payer right after a payment has been performed.
 *
 * Payer will add following parameters to the HTTP request:
 *
 *   - orderid 						=  <OrderId>
 *   - payer_payment_id 			=  <PaymentId>
 *   - payer_testmode 				=  true|false
 *   - payer_callback_type 			=  settle
 *   - payer_settlement 			=  delayed|final
 *   - payer_payment_type 			=  invoice|card|bank|einvoice|installment|wywallet|xxx
 *   - payer_added_fee 				=  <Amount>
 *   - payer_merchant_reference_id 	=  <MerchantReferenceId>
 *   - md5hash						=  <Unique String>
 *
 *  Example:
 *  http://www.myshop.com/settle.php?payer_payment_id=XXXXXXXX&md5hash=F0F0F0F0F0F0F0F0F0F0F0F0F0F0F0F0
 *
 *  If you have any OrderID enclosed it will look similar:
 *  http://www.myshop.com/settle.php?orderid=S1234567&payer_payment_id=XXXXXXXX&md5hash=F0F0F0F0F0F0F0F0F0F0F0F0F0F0F0F0
 *
 *  Payer will only add to its parameters and not remove any.
 *  When you receive the signal from the settle, you can reserve goods/services in your shop.
 *  Should always print "TRUE" if it works.
 *
 *  @copyright Payer Financial Services AB
 *  @author    Payer <teknik@payer.se>
 *  @link      http://payer.se/
 *
 */

require_once "PayerCredentials.php";
require_once "../../vendor/autoload.php";

use Payer\Sdk\Client;
use Payer\Sdk\Resource\Purchase;

$data = array(
    'proxy' => array(
        '::1'   // For debugging purposes: Add the requestors ip to pass the firewall validation
    )
);

try {
    $gateway = Client::create($credentials);

    $purchase = new Purchase($gateway);
    $purchase->validateCallbackRequest($data);

   /*
    *  NOTICE: Everything is fine in here, do your
    *          necessary expressions in here!
    *
    *  http://example.com/PurchaseCustomSettlementExample.php?payer_testmode=false&payer_payment_type=card&payer_callback_type=settle&payer_added_fee=0&payer_payment_id=D3@SYDAFRIKAR47r6m8ejk3i&payread_payment_id=D3@SYDAFRIKAR47r6m8ejk3i&md5sum=DF6599C6BF77DDAA50B0672939B6530E
    *
    *  Available variables:
    *
    *  $testMode =      $_GET['payer_test_mode'];
    *  $paymentType =   $_GET['payer_payment_type'];
    *  $callbackType =  $_GET['payer_callback_type'];
    *  $addedFee =      $_GET['payer_added_fee'];
    *  $paymentId =     $_GET['payer_payment_id'];
    */

    $purchase->acceptCallbackRequest(); // Signaling 'TRUE'

} catch (PayerException $e) {
    var_dump($e);
}


