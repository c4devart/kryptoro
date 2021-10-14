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
 * Description:		Is called by Payer right after an authorize has been performed.
 * Payer will add two parameters:
 * payread_payment_id=XXXXXXXX
 * md5hash=F0F0F0F0F0F0F0F0F0F0F0F0F0F0F0F0
 *              Example:
 *              http://www.myshop.com/auth.php?payread_payment_id=XXXXXXXX&md5hash=F0F0F0F0F0F0F0F0F0F0F0F0F0F0F0F0
 *
 *              If you have any OrderID enclosed it will look similar:
 *              http://www.myshop.com/auth.php?orderid=S1234567&payread_payment_id=XXXXXXXX&md5hash=F0F0F0F0F0F0F0F0F0F0F0F0F0F0F0F0
 *              Payer will only add to its parameters and not remove any.
 *              When you receive the signal from the auth, you can reserve goods/services in your shop.
 *              Should always print "TRUE" if it works.
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
    $purchase->createAuthorizeResource();

} catch (PayerException $e) {
    var_dump($e);
}
