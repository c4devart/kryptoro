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

    'reference_id'  => base64_encode(rand()),
    'description'   => 'This is an order description',
    'currency'      => 'SEK',
    'test_mode'     => true,
    'client_ip'     => $_SERVER['REMOTE_ADDR'],
    'charset'       => 'UTF-8',
    'customer'  => array(

        'identity_number' => '556736-8724',
        'first_name'      => 'Test',
        'last_name'       => 'Person',
        // 'organisation'    => 'Payer Financial Services AB',
        // 'your_reference'  => 'Test Reference',

        'address'       => array(
            'co'            => '',
            'address_1'     => 'Testvägen 123',
            'address_2'     => ''
        ),

        'zip_code'      => 12345,
        'city'          => 'Teststaden',
        'country_code'  => 'SE',

        'phone' => array(
            'home'      => '1234567890',
            'work'      => '0987654321',
            'mobile'    => '111222333444'
        ),

        'email' => 'demo@payer.se'

    ),

    'items' => array(
        array(
            'type'                  => 'freeform',
            'line_number'           => 1,
            'article_number'        => 'ABC123',
            'description'           => "This is an item description",
            'unit_price'            => 40,
            'unit_vat_percentage'   => 20,
            'quantity'              => 5,
            'unit'                  => null
        ),
        // array(
        //     'type'                  => 'infoline',
        //     'line_number'           => 2,
        //     'article_number'        => 'ABC123',
        //     'description'           => "This is an item description",
        //     'unit_price'            => 40,
        //     'unit'                  => null
        // )
    ),

    // 'options' => array()
);

try {
    $gateway = Client::create($credentials);

    $order = new Order($gateway);

    // Create the order
    $createOrderResponse = $order->create($data);

    var_dump($createOrderResponse);

    $orderId = $createOrderResponse['order_id'];

} catch (PayerException $e) {
    var_dump($e);
}

