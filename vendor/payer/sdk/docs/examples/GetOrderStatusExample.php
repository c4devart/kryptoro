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
use Payer\Sdk\Resource\Order;
use Payer\Sdk\Transport\Http\Response;

$data = array(
    'order_id' => '' // The id of the order to fetch
);

try {
    $gateway = Client::create($credentials);

    $order = new Order($gateway);

    // Fetch the order status
    $getOrderResponse = $order->getStatus($data);

    var_dump($getOrderResponse);

    $orderId            = $getOrderResponse['order_id'];
    $status             = $getOrderResponse['status'];
    $orderNumber        = $getOrderResponse['order_number'];
    $orderTotal         = $getOrderResponse['order_total'];
    $deliveredTotal     = $getOrderResponse['delivered_total'];
    $delivered_vat      = $getOrderResponse['delivered_vat'];
    $options            = $getOrderResponse['options'];

    $customerId         = $getOrderResponse['customer']['id'];
    $userId             = $getOrderResponse['customer']['user_id'];

    $invoiceNumber      = $getOrderResponse['invoice']['invoice_number'];
    $createDate         = $getOrderResponse['invoice']['create_date'];
    $invoiceDate        = $getOrderResponse['invoice']['invoice_date'];
    $dueDate            = $getOrderResponse['invoice']['due_date'];

} catch (PayerException $e) {
    var_dump($e);
}