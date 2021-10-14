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
 */

require_once "PayerCredentials.php";
require_once "../../vendor/autoload.php";

use Payer\Sdk\Client;
use Payer\Sdk\Resource\Invoice;
use Payer\Sdk\Resource\Purchase;

$data = array(
     'settlement_id' => null, // Contact Payer for more info how to access this value
     'amount' => null
);

try {
    $gateway = Client::create($credentials);

    $purchase = new Purchase($gateway);
    $settlementResponse = $purchase->settlement($data);

    var_dump($settlementResponse);

    $transactionId = $settlementResponse['transaction_id'];

} catch (PayerException $e) {
    var_dump($e);
}
